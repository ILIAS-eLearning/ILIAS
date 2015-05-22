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
	static private  $instance = null; // ilObjTrainingProgrammeCache

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
	
	public function getInstanceByRefId($a_ref_id) {
		require_once("Modules/TrainingProgramme/classes/class.ilObjTrainingProgramme.php");	
		
		// TODO: Maybe this should be done via obj_id instead of ref_id, since two
		// ref_ids could point to the same object, hence leading to two instances of
		// the same object. Since ilObjTrainingProgramme is a container, it should (??)
		// only have one ref_id...
		if (!array_key_exists($a_ref_id, $this->instances)) {
			$this->instances[$a_ref_id] = new ilObjTrainingProgramme($a_ref_id);
		}
		return $this->instances[$a_ref_id];
	}
	
	public function addInstance(ilObjTrainingProgramme $a_prg) {
		if (!$a_prg->getRefId()) {
			throw new ilException("ilObjTrainingProgrammeCache::addInstance: "
								 ."Can't add instance without ref_id.");
		}
		$this->instances[$a_prg->getRefId()] = $a_prg;
	}
	
	/**
	 * For testing purpose.
	 *
	 * TODO: Move to mock class in tests.
	 */ 
	public function test_clear() {
		$this->instances = array();
	}
	
	public function test_isEmpty() {
		return count($this->instances) == 0;
	}
}

?>