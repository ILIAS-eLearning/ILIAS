<?php

require_once 'Services/Tracking/classes/class.ilLPStatusWrapper.php';
require_once 'Modules/ManualAssessment/classes/Members/class.ilManualAssessmentMembersStorageDB.php';
class ilManualAssessmentLPInterface {
	protected static $members_storage = null;

	public static function updateLPStatusOfMember(ilManualAssessmentMember $member) {
		ilLPStatusWrapper::_refreshStatus($member->assessmentId(), array($member->id()));
	}

	public static function determineStatusOfMember($mass_id, $usr_id) {
		if(self::$members_storage  === null) {
			self::$members_storage = self::getMembersStorage();
		}
		$mass = new ilObjManualAssessment($mass_id,false);
		$members = $mass->loadMembers($mass);
		$usr =  new ilObjUser($usr_id);
		if($members->userAllreadyMember($usr)) {
			return self::$members_storage->loadMember($mass ,$usr)->LPStatus();
		} else {
			return ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
		}
	}

	protected static function getMembersStorage() {
		global $DIC;
		return new ilManualAssessmentMembersStorageDB($DIC['ilDB']);
	}

	public static function getMembersHavingStatusIn($mass_id, $status) {
		if(self::$members_storage  === null) {
			self::$members_storage = self::getMembersStorage();
		}
		$members = self::$members_storage->loadMembers(new ilObjManualAssessment($mass_id,false));
		$return = array();
		foreach($members as $usr_id => $record) {
			if((string)$record[ilManualAssessmentMembers::FIELD_LEARNING_PROGRESS] === (string)$status) {
				$return[] = $usr_id;
			}
		}
		return $return;
	}
}