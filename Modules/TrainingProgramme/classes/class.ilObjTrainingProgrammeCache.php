<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * Cache for ilObjTrainingProgrammes.
 *
 * Implemented as singleton.
 *
 * @author : Richard Klees <richard.klees@concepts-and-training.de>
 */

class ilObjTrainingProgrammeCache {
	static private  $instance; // ilObjTrainingProgrammeCache
	
	private function __construct() {
		$this->instances = array();
	}

	static public function singleton() {
		if (self::$instance === null) {
			self::$instance = new ilObjTrainingProgrammeCache();
		}
		return self::$instance;
	}
	
	protected $instances; // [ilObjTrainingProgramme]
	
	public function getInstance($a_ref_id) {
		require_once("Modules/TrainingProgramme/classes/class.ilObjTrainingProgramme.php");	
		
		if (!array_key_exists($a_ref_id, $this->instances)) {
			$this->instances[$a_ref_id] = new ilObjTrainingProgramme($a_ref_id);
		}
		
		return $this->instances[$a_ref_id];
	}
}

?>