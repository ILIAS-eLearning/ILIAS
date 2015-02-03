<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once "./Services/Container/classes/class.ilContainer.php";

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
		$this->readSettings();
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
}

?>