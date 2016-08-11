<?php
/**
 * For the purpose of streamlining the grading and learning-process status definition
 * outside of tests, SCORM courses e.t.c. the ManualAssessment is used.
 * It caries a LPStatus, which is set manually.
 *
 * @author Denis Klöpfer <denis.kloepfer@concepts-and-training.de>
 */


require_once 'Services/Object/classes/class.ilObject.php';
require_once 'Modules/ManualAssessment/classes/Settings/class.ilManualAssessmentSettings.php';
require_once 'Modules/ManualAssessment/classes/Settings/class.ilManualAssessmentSettingsStorageDB.php';
require_once 'Modules/ManualAssessment/classes/Members/class.ilManualAssessmentMembersStorageDB.php';
class ilObjManualAssessment extends ilObject {
	protected $gDic;
	public function __construct($a_id = 0, $a_call_by_reference = true) {
		global $DIC;
		$this->gDic = $DIC;
		$this->type = 'mass';
		parent::__construct($a_id, $a_call_by_reference);
		$this->settings_storage = new ilManualAssessmentSettingsStorageDB($this->gDic['ilDB']);
		$this->members_storage =  new ilManualAssessmentMembersStorageDB($this->gDic['ilDB']);
	}

	public function create() {
		parent::create();
		$this->settings = new ilManualAssessmentSettings($this);
		$this->settings_storage->createSettings($this->settings);
	}

	public function getSettings() {
		if(!$this->settings) {
			$this->settings = $this->settings_storage->loadSettings($this);
		}
		return $this->settings;
	}

	public function loadMembers() {
		return $this->members_storage->loadMembers($this);
	}

	public function updateMembers(ilManualAssessmentMembers $members) {
		$this->members_storage->updateMembers($members);
	}

	public function delete() {
		$this->settings_storage->deleteSettings($this);
		$this->members_storage->deleteMembers($this);
		parent::delete();
	}

	public function update() {
		parent::update();
		$this->settings_storage->updateSettings($this->settings);
	}

	public function membersStorage() {
		return $this->members_storage;
	}
}