<?php
require_once("/Library/WebServer/Documents/dev/4_4_generali2_new_wbd/Services/GEV/WBD/classes/Error/class.gevWBDError.php");
abstract class gevWBDRequest extends WBDRequest {

	public function __construct() {
		parent::__construct();

		$this->crs_id = 0;
	}

	/**
	* parses the error values out of the response xml
	*
	* @param xml 		$response 			repsonse xml
	*
	*/
	public function createWBDError($response) {
		$this->wbd_error = new gevWBDError($this->parseReason($response),$this->user_id,$this->row_id,$this->crs_id);
		$this->is_error = true;
		return true;
	}

	/**
	* replaces the placeholder with mapped values
	*
	* @param string 	$template 	XML Template String
	* @return string 				XML String with replaced palceholders
	*/
	public function replaceArguments($template) {
		$reflect = new ReflectionClass($this);
		$props = $reflect->getProperties(ReflectionProperty::IS_PROTECTED);

		foreach ($props as $key => $value) {
			if($this->{$value->name} instanceof gevWBDData) {
				$template = str_replace("{".$this->{$value->name}->WBDTagName()."}"
									, $this->{$value->name}->WBDValue()
									, $template);
			}
		}
		echo $template;
		return $template;
	}
}