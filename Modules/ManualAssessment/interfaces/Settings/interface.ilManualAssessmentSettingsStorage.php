<?php
require_once 'Modules/ManualAssessment/classes/class.ilObjManualAssessment.php';
require_once "Modules/ManualAssessment/exceptions/class.ilManualAssessmentException.php";
interface ilManualAssessmentSettingsStorage {
	public function createSettings(ilManualAssessmentSettings $settings);

	public function loadSettings(ilObjManualAssessment $obj);
	
	public function updateSettings(ilManualAssessmentSettings $settings);

	public function deleteSettings(ilObjManualAssessment $obj);
}