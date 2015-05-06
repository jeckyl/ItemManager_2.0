<?php
/**
 * Plugin Name: ItemManager
 * Description: A simple flat-file Framework.
 * Version: 2.0
 * Author: Juri Ehret
 * Author URL: http://ehret-studio.com
 *
 * This file is part of ItemManager.
 *
 * ItemManager is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or any
 * later version.
 *
 * ItemManager is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with ItemManager.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
class ImTplEngine
{
	/**
	 * @var array of the object of type Template
	 */
	public $templates;
	/**
	 * @var string path of templates
	 */
	private $tplpath;


	public function __construct($path=''){$this->tplpath = !empty($path) ? $path : IM_TEMPLATE_DIR;}

	/**
	 * Initializes all the templates and made them available in ImTplEngine::$templates
	 */
	public function init()
	{
		$templates = array();
		foreach (glob($this->tplpath . '*' . IM_TEMPLATE_FILE_SUFFIX) as $file)
		{
			$tpl = new Template();

			$base = basename($file, '.im.tpl');
			$strp = strpos($base, '.');
			$name = substr($base, 0, $strp);
			$member = substr($base, $strp+1);
			$tpl->set('name', $name.'.'.$member);
			$tpl->content = file_get_contents($file);
			$this->templates[] = $tpl;
		}
	}



	/**
	 * Returns the number of templates
	 *
	 * @param array $templates
	 * @return int
	 */
	public function countTemplates(array $templates=array())
		{return count(!empty($templates) ? $templates : $this->templates);}


	/**
	 * Returns an object of type Template
	 * NOTE: However if no $templates argument is passed to the function, the templates
	 * must already be in the buffer: ImTplEngine::$templates. Call the ImTplEngine::init()
	 * method before to assign the templates to the buffer.
	 *
	 * You can search for template by "name": ImTplEngine::getTemplate('template_name')
	 * or similar to ImTplEngine::getTemplate('name=template_name')
	 * NOTE: Its not possible to search for memebership and specific terms
	 *
	 * @param string $stat
	 * @param array $templates
	 * @return boolean|object of the type Template
	 */
	public function getTemplate($stat, array $templates=array())
	{
		$loctpl = !empty($templates) ? $templates : $this->templates;
		// nothing to select
		if(empty($templates))
		{
			if(!$this->countTemplates() || $this->countTemplates() <= 0)
				return false;
		}

		if(false !== strpos($stat, '='))
		{
			$data = explode('=', $stat, 2);
			$key = strtolower(trim($data[0]));
			$val = trim($data[1]);

			if(false !== strpos($key, ' '))
				return false;

			foreach($loctpl as $tpl_id => $t)
			{
				if(!isset($t->$key) || $t->$key != $val)
					continue;

				return $loctpl[$tpl_id];;
			}
		} else
		{
			foreach($loctpl as $tpl_id => $t)
			{
				if(!isset($t->name) || $t->name != $stat)
					continue;

				return $loctpl[$tpl_id];
			}
		}
		return false;
	}


	/**
	 * Returns an array of object of type Template
	 * NOTE: However if no $templates argument is passed to the function, the templates
	 * must already be in the buffer: ImTplEngine::$templates. Call the ImTplEngine::init()
	 * method before to assign the templates to the buffer.
	 *
	 * You can get all templates by a membership
	 * Example, to get all templates with "general" membership, you can do the following:
	 * ImTplEngine::getTemplates('general', $your_template_array)
	 *
	 * @param string $stat
	 * @param array $templates An array of Template objects
	 * @return boolean|array
	 */
	public function getTemplates($stat, array $templates=array())
	{

		$loctpl = !empty($templates) ? $templates : $this->templates;
		// nothing to select
		if(empty($templates))
		{
			if(!$this->countTemplates() || $this->countTemplates() <= 0)
				return false;
		}

		$tplcontainer = array();

		if(false !== strpos($stat, '='))
		{
			$data = explode('=', $stat, 2);
			$key = strtolower(trim($data[0]));
			$val = trim($data[1]);

			if(false !== strpos($key, ' '))
				return false;

			foreach($loctpl as $tpl_id => $t)
			{
				$member = $t->get($key);

				if(!isset($member) || $member != $val)
					continue;

				$tplcontainer[$tpl_id] = $loctpl[$tpl_id];
			}
		} else
		{
			foreach($loctpl as $tpl_id => $t)
			{
				$member = $t->get('member');
				if(!isset($member) || $member != $stat)
					continue;
				$tplcontainer[$tpl_id] = $loctpl[$tpl_id];
			}
		}

		if(!empty($tplcontainer))
			return $tplcontainer;

		return false;
	}


	/**
	 * Unset a given array of Template objects
	 * @param array $templates
	 */
	public function destroyTemplates(array $templates=array())
		{$tpls=!empty($templates) ? $templates : $this->templates;unset($tpls);}


	/**
	 * Renders template by replacing $tvs and optionally language $lvs by setting the $lflag to true.
	 * You can use $clean flag to delete the tvs left in template
	 *
	 * @param Template object $tpl
	 * @param array $tvs
	 * @param bool $lflag
	 * @param array $lvs
	 * @param bool $clean
	 * @return Template object
	 */
	public function render(Template $template, array $tvs=array(),
			$lflag=false, array $lvs=array(), $clean=false)
	{
		$tpl = clone $template;
		if($lflag) $tpl->content = $this->renderLvs($tpl->content, $lvs);

		if(!empty($tvs))
			foreach($tvs as $key => $val)
				$tpl->content = preg_replace('%\[\[( *)'.$key.'( *)\]\]%', $val, $tpl->content);

		if($clean) return preg_replace('%\[\[(.*)\]\]%', '', $tpl->content);

		return $tpl->content;
	}


	public function process(Template $template, array $tvs=array(),
						   $lflag=false, array $lvs=array(), $clean=false)
	{
		$tpl = clone $template;
		if($lflag) $tpl->content = $this->renderLvs($tpl->content, $lvs);

		if(!empty($tvs))
			foreach($tvs as $key => $val)
				$tpl->content = preg_replace('%\[\[( *)'.$key.'( *)\]\]%', $val, $tpl->content);

		if($clean) return preg_replace('%\[\[(.*)\]\]%', '', $tpl->content);

		return $tpl->content;
	}

	/**
	 * Replaces language placeholders (lvs)
	 *
	 * @param string $ipl
	 * @return string
	 */
	private function renderLvs($tpl, array $lvs=array())
	{

		if(empty($lvs))
		{
			$lvs = $this->imI18n('imanager');
			if(!$lvs) $lvs = $this->imI18n('imanager','en_US');
		}
		if(empty($lvs)) return false;

		foreach($lvs as $key => $val)
		{
			if(strpos($key, $tpl) !== true)
				$tpl = preg_replace('%\[\[( *)'.$key.'( *)\]\]%', $val, $tpl);
		}
		return $tpl;
	}


	/**
	 * Returns language array by plugin name and curent system language
	 *
	 * @param $plugin
	 * @param null $language
	 * @return array|bool
	 */
	private function imI18n($plugin, $language=null)
	{
		$l = array();
		if($this->imPrepI18n($plugin, $language ? $language : IM_LANGUAGE, $l))
			return $l;
		return false;
	}


	/**
	 * @param $plugin
	 * @param $lang
	 * @param $lp
	 * @return bool
	 */
	private function imPrepI18n($plugin, $lang, &$lvs)
	{
		$i18n = array();
		if(!file_exists(GSPLUGINPATH.$plugin.'/lang/'.$lang.'.php')) return false;

		// bug in PHP functionality's been missing since at least 2006
		@include(GSPLUGINPATH.$plugin.'/lang/'.$lang.'.php');
		if(count($i18n) <= 0)
			return false;

		foreach($i18n as $code => $text)
		{
			if(!array_key_exists($plugin.'/'.$code, $lvs))
				$lvs['lang/'.$code] = $text;
		}
		return true;
	}
}
?>
