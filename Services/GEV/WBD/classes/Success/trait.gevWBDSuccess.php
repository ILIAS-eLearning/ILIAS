<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* trait for gev WBD-Success
*
* @author	Stefan Hecken <shecken@concepts-and-training.de>
* @version	$Id$
*
*/
require_once("Services/Calendar/classes/class.ilDate.php");
trait gevWBDSuccess{

	static $DATE_SPLITTER = "T";

	/**
	* get the date out of iso date
	*
	* @param string 		$iso_date_string
	*
	* @return ilDate
	*/
	public function createDate($iso_date_string) {
		$date_str = $iso_date_string;

		if(strpos($iso_date_string, self::$DATE_SPLITTER)) {
			$split = explode(self::$DATE_SPLITTER,$iso_date_string);
			$date_str = $split[0];
		}

		return new ilDate($date_str,IL_CAL_DATE);
	}

	/**
	* gets the dictionary
	*
	* @return gevWBDDictionary
	*/
	public function getDictionary() {
		if($this->dictionary === null) {
			$this->dictionary = new gevWBDDictionary();
		}

		return $this->dictionary;
	}
}