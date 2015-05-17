<?php
class InputText implements Inputinterface
{
	protected $values;
	protected $field;
	protected $error = array();

	public function __construct(Field $field)
	{
		$this->field = $field;
		$this->values = new stdClass();
		$this->values->value = null;
	}

	/* */
	public function prepareInput($value, $sanitize=false)
	{
		$this->values->value = !$sanitize ? $value : $this->sanitize($value);

		// check input required
		if(!empty($this->field->required) && $this->field->required == 1)
		{

			if(empty($this->values->value))
				return self::ERR_REQUIRED;
		}
		// check min value
		if(!empty($this->field->minimum) && $this->field->minimum > 0)
		{
			if(strlen($this->values->value) < intval($this->field->minimum))
				return self::ERR_MIN_VALUE;
		}
		// check input max value
		if(!empty($this->field->maximum) && $this->field->maximum > 0)
		{
			if(strlen($this->values->value) > intval($this->field->maximum))
				return self::ERR_MAX_VALUE;
		}

		return $this->values;
	}

	public function prepareOutput(){return $this->values;}

	protected function sanitize($value){return safe_slash_html_input($value);}
}