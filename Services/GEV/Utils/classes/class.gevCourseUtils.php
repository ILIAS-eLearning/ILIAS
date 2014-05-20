<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Course seraching GUI for Generali
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

class gevCourseUtils {
	static $instances = array();
	
	protected function __construct($a_crs_id) {
		$this->crs_id = $a_crs_id;
	}
	
	static public function getInstance($a_crs_id) {
		if (array_key_exists($a_crs_id, self::$instances)) {
			return self::$instances[$a_crs_id];
		}

		self::$instances[$a_crs_id] = new gevCourseUtils($a_crs_id);
		return self::$instances[$a_crs_id];
	}
	
	static function getLinkTo($a_crs_id) {
		return "http://www.google.de"; //TODO: implement this properly
	}
	
	public function getLink() {
		return self::getLinkTo($this->crs_id);
	}
}

?>