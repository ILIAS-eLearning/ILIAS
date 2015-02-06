<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("./Services/Container/classes/class.ilContainer.php");
require_once("./Modules/TrainingProgramme/classes/model/class.ilTrainingProgramme.php");

/**
 * Class ilObjTrainingProgramme
 *
 * @author : Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilObjTrainingProgramme extends ilContainer {
	protected $settings; // ilTrainingProgramme
	
	/**
	 * @param int  $a_id
	 * @param bool $a_call_by_reference
	 */
	public function __construct($a_id = 0, $a_call_by_reference = true) {
		$this->type = "orgu";
		$this->settings = null;
		$this->ilContainer($a_id, $a_call_by_reference);
	}
	
	
	/**
	 * Get an instance of ilObjTrainingProgramme, use cache.
	 *
	 * @param  int  $a_ref_id
	 * @return ilObjTrainingProgramme
	 */
	static public function getInstance($a_ref_id) {
		require_once("Modules/TrainingProgramme/classes/class.ilObjTrainingProgrammeCache.php");
		return ilObjTrainingProgrammeCache::singelton()->getInstance($a_ref_id);
	}
	
	
	////////////////////////////////////
	// CRUD
	////////////////////////////////////
	
	/**
	 * Load Settings from DB.
	 * Throws when settings are already loaded or id is null.
	 */
	protected function readSettings() {
		if ($this->settings !== null) {
			throw new ilException("ilObjTrainingProgramme::loadSettings: already loaded.");
		}
		$id = $this->getId();
		if (!$id) {
			throw new ilException("ilObjTrainingProgramme::loadSettings: no id.");
		}
		$this->settings = new ilTrainingProgramme($this->getId());
	}
	
	/**
	 * Create new settings object.
	 * Throws when settings are already loaded or id is null.
	 */
	protected function createSettings() {
		if ($this->settings !== null) {
			throw new ilException("ilObjTrainingProgramme::createSettings: already loaded.");
		}
		
		$id = $this->getId();
		if (!$id) {
			throw new ilException("ilObjTrainingProgramme::loadSettings: no id.");
		}
		$this->settings = ilTrainingProgramme::createForObject($this);
	}
	
	/**
	 * Update settings in DB.
	 * Throws when settings are not loaded.
	 */
	protected function updateSettings() {
		if ($this->settings === null) {
			throw new ilException("ilObjTrainingProgramme::updateSettings: no settings loaded.");
		}
		$this->settings->update();
	}
	
	/**
	 * Delete settings from DB.
	 * Throws when settings are not loaded.
	 */
	protected function deleteSettings() {
		if ($this->settings === null) {
			throw new Exception("ilObjTrainingProgramme::deleteSettings: no settings loaded.");
		}
		$this->settings->delete();
	}

	public function read() {
		parent::read();
		$this->readSettings();
	}


	public function create() {
		$id = parent::create();
		$this->createSettings();
	}


	public function update() {
		parent::update();
		$this->updateSettings();
	}

	/**
	 * Delete Training Programme and all related data.
	 *
	 * @return    boolean    true if all object data were removed; false if only a references were removed
	 */
	public function delete() {
		// always call parent delete function first!!
		if (!parent::delete()) {
			return false;
		}

		$this->deleteSettings();
		return true;
	}
	
	////////////////////////////////////
	// GETTERS AND SETTERS
	////////////////////////////////////
	
	/**
	 * Get the timestamp of the last change on this program or sub program.
	 *
	 * @return ilDateTime
	 */
	public function getLastChange() {
		return $this->settings->getLastChange();
	}
	
	/**
	 * Get the amount of points
	 *
	 * @return integer  - larger than zero
	 */
	public function getPoints() {
	    return $this->settings->getPoints();
	}
	
	/**
	 * Set the amount of points.
	 * 
	 * @param integer   $a_points   - larger than zero 
	 * @throws ilException 
	 */
	public function setPoints($a_points) {
		$this->settings->setPoints($a_points);
	} 
	
	/**
	 * Get the lp mode.
	 *
	 * @return integer  - one of ilTrainingProgramme::$MODES
	 */
	public function getLPMode() {
		return $this->settings->getLPMode();
	}
	
	/**
	 * Get the status.
	 *
	 * @return integer  - one of ilTrainingProgramme::$STATUS
	 */
	public function getStatus() {
		return $this->settings->getStatus();
	}
	
	/**
	 * Set the status of the node.
	 *
	 * @param integer $a_status     - one of ilTrainingProgramme::$STATUS
	 */
	public function setStatus($a_status) {
		$this->settings->setStatus($a_status);
	}
	
	////////////////////////////////////
	// TREE NAVIGATION
	////////////////////////////////////

	/**
	 * Get a list of all ilObjTrainingProgrammes in the subtree starting at
	 * $a_ref_id. Includes object identified by $a_ref_id.
	 *
	 * @param  int $a_ref_id
	 * @return [ilObjTrainingProgramme]
	 */
	static public function getAllChildren($a_ref_id) {
		
	}

	/**
	 * Get all ilObjTrainingProgrammes that are direct children of this
	 * object.
	 *
	 * @return [ilObjTrainingProgramme]
	 */
	public function getChildren() {
		
	} 

	/**
	 * Get the parent ilObjTrainingProgramme of this object. Returns null if
	 * parent is no TrainingProgramme.
	 *
	 * @return ilObjTrainingProgramme | null
	 */
	public function getParent() {
		
	}

	/**
	 * Does this TrainingProgramme have other ilObjTrainingProgrammes as children?
	 *
	 * @return bool
	 */
	public function hasChildren() {
		return $this->getAmountOfChildren() > 0;
	}

	/**
	 * Get the amount of other TrainingProgrammes this TrainingProgramme has as
	 * children.
	 *
	 * @return int
	 */
	public function getAmountOfChildren() {
		return count($this->getChildren());
	}

	/**
	 * Get the depth of this TrainingProgramme in the tree starting at the topmost
	 * TrainingProgramme (not root node of the repo tree!).
	 *
	 * @return int
	 */
	public function getDepth() {
		
	}

	/**
	 * Get the ilObjTrainingProgramme that is the root node of the tree this programme
	 * is in.
	 *
	 * @return ilObjTrainingProgramme
	 */
	public function getRoot() {
		
	}
}

?>