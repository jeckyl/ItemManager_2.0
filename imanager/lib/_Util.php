<?php
namespace Imanager;

class Util
{
	/**
	 * Function for checking session status
	 *
	 * @return bool
	 */
	public static function is_session_started()
	{
		if(php_sapi_name() !== 'cli') {
			if (version_compare(phpversion(), '5.4.0', '>='))
			{
				return session_status() === PHP_SESSION_ACTIVE ? true : false;
			} else
			{
				return session_id() === '' ? false : true;
			}
		}
		return false;
	}

	public static function reparse_url($parsed_url, $imcat)
	{
		$scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'].'://' : '';
		$host     = isset($parsed_url['host'])   ? $parsed_url['host'] : '';
		$path     = isset($parsed_url['path'])   ? $parsed_url['path'] : '';
		$query    = isset($parsed_url['query'])  ? '?'.$parsed_url['query'] : '';
		$pairs = explode('&', $query);
		foreach($pairs as $pair)
		{
			$part = explode('=', $pair);
			if($part[0] == 'page')
			{
				return ($scheme.$host.$path.'?id=imanager&cat='. $imcat->current_category().'&page=');
			}
		}
		return ;
	}
}