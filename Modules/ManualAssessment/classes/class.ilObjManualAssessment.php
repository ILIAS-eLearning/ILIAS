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
require_once 'Modules/ManualAssessment/classes/AccessControl/class.ilManualAssessmentAccessHandler.php';
class ilObjManualAssessment extends ilObject {

	public function __construct($a_id = 0, $a_call_by_reference = true) {
		global $DIC;
		$this->type = 'mass';
		parent::__construct($a_id, $a_call_by_reference);
		$this->settings_storage = new ilManualAssessmentSettingsStorageDB($DIC['ilDB']);
		$this->members_storage =  new ilManualAssessmentMembersStorageDB($DIC['ilDB']);
		$this->access_handler = new ilManualAssessmentAccessHandler(
				 $DIC['ilAccess']
				,$DIC['rbacadmin']
				,$DIC['rbacreview']
				,$DIC['ilUser']);

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
		$members->updateStorageAndRBAC($this->members_storage, $this->access_handler);
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

	public function initDefaultRoles() {
		$this->access_handler->initDefaultRolesForObject($this);
	}

	public function accessHandler() {
		return $this->access_handler;
	}
}