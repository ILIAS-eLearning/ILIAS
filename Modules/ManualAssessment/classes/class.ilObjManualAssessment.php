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
class ilObjManualAssessment extends ilObject {
	public function __construct($a_id = 0, $a_call_by_reference = true) {
		global $DIC;
		$this->type = "mass";
		parent::__construct($a_id, $a_call_by_reference);
		$this->settings_storage = new ilManualAssessmentSettingsStorageDB($DIC["ilDB"]);
	}

	public function create() {
		parent::create();
		$this->settings = new ilManualAssessmentSettings($this);
		$this->settings_storage->createSettings($this->settings);
	}

	public function loadSettings() {
		return $this->settings_storage->loadSettings($this);
	}

	public function updateSettings(ilManualAssessmentSettings $settings) {
		$this->settings_storage->updateSettings($settings);
	}

	public function delete() {
		$this->settings_storage->deleteSettings($this);
		parent::delete();
	}
}