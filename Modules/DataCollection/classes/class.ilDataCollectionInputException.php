<?php
/**
 * Created by JetBrains PhpStorm.
 * User: root
 * Date: 8/9/12
 * Time: 10:28 AM
 * To change this template use File | Settings | File Templates.
 */
class ilDataCollectionInputException extends Exception
{
	const TYPE_EXCEPTION = 0;
	const LENGTH_EXCEPTION = 1;
	const REGEX_EXCEPTION = 2;
	const UNIQUE_EXCEPTION = 3;

	private $exception_type;

	function __construct($exception_type){
		$this->exception_type = $exception_type;
	}

	public function getExceptionType(){
		return $this->exception_type;
	}

	public function __toString(){
		global $lng;
		switch($this->exception_type){
			case self::TYPE_EXCEPTION:
				return $lng->txt("dcl_wrong_input_type");
			case self::LENGTH_EXCEPTION:
				return $lng->txt("dcl_wrong_length");
			case self::REGEX_EXCEPTION:
				return $lng->txt("dcl_wrong_regex");
			case self::UNIQUE_EXCEPTION:
				return $lng->txt("dcl_not_unique");
			default:
				return $lng->txt("dcl_unknown_exception");
		}
	}

}
