<?php

interface ManualAssessmentAccessHAndler {

	public function checkAccessOfUserToObj(ilObjUser $usr, ilObjManualAssessment $mass, $operation);
	public function initDefaultRolesForObject(ilObjManualAssessment $mass);
	public function assignUserToMemberRole(ilObjUser $usr, ilObjManualAssessment $mass);
	public function deassignUserFromMemberRole(ilObjUser $usr, ilObjManualAssessment $mass);
}