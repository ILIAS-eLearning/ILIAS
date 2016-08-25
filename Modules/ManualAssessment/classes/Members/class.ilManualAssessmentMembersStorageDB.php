<?php
require_once 'Modules/ManualAssessment/interfaces/Members/interface.ilManualAssessmentMembersStorage.php';
require_once 'Modules/ManualAssessment/classes/Members/class.ilManualAssessmentMembers.php';
require_once 'Modules/ManualAssessment/classes/Members/class.ilManualAssessmentMember.php';
require_once 'Modules/ManualAssessment/classes/class.ilObjManualAssessment.php';
/**
 * Store member infos to DB
 * @inheritdoc
 */
class ilManualAssessmentMembersStorageDB implements ilManualAssessmentMembersStorage {

	protected $db;

	public function __construct($ilDB) {
		$this->db = $ilDB;
	}

	/**
	 * @inheritdoc
	 */
	public function loadMembers(ilObjManualAssessment $obj) {
		$members = new ilManualAssessmentMembers($obj);
		$obj_id = $obj->getId();
		$sql = $this->loadMembersQuery($obj_id);
		$res = $this->db->query($sql);
		while($rec = $this->db->fetchAssoc($res)) {
			$members = $members->withAdditionalRecord($rec);
		}
		return $members;
	}

	/**
	 * @inheritdoc
	 */
	public function loadMember(ilObjManualAssessment $obj, ilObjUser $usr) {
		$obj_id = $obj->getId();
		$usr_id = $usr->getId();
		$sql = 'SELECT massme.*'
				.' FROM mass_members massme'
				.'	JOIN usr_data usr ON massme.usr_id = usr.usr_id'
				.'	LEFT JOIN usr_data ex ON massme.examiner_id = ex.usr_id'
				.'	WHERE obj_id = '.$this->db->quote($obj_id, 'integer')
				.'		AND massme.usr_id = '.$this->db->quote($usr_id,'integer');
		$rec = $this->db->fetchAssoc($this->db->query($sql));
		if($rec) {
			$member = new ilManualAssessmentMember($obj, $usr, $rec);
			return $member;
		} else {
			throw new ilManualAssessmentException("invalid usr-obj combination");
		}
	}

	/**
	 * @inheritdoc
	 */
	public function updateMember(ilManualAssessmentMember $member) {
		$sql = 'UPDATE mass_members SET '
				.'	'.ilManualAssessmentMembers::FIELD_LEARNING_PROGRESS.' = '.$this->db->quote($member->LPStatus(),'text')
				.'	,'.ilManualAssessmentMembers::FIELD_EXAMINER_ID.' = '.$this->db->quote($member->examinerId(),'integer')
				.'	,'.ilManualAssessmentMembers::FIELD_RECORD.' = '.$this->db->quote($member->record(),'text')
				.'	,'.ilManualAssessmentMembers::FIELD_INTERNAL_NOTE.' = '.$this->db->quote($member->internalNote(),'text')
				.'	,'.ilManualAssessmentMembers::FIELD_NOTIFY.' = '.$this->db->quote($member->notify() ? 1 : 0,'integer')
				.'	,'.ilManualAssessmentMembers::FIELD_FINALIZED.' = '.$this->db->quote($member->finalized() ? 1 : 0,'integer')
				.'	,'.ilManualAssessmentMembers::FIELD_NOTIFICATION_TS.' = '.$this->db->quote($member->notificationTS(),'integer')
				.'	WHERE obj_id = '.$this->db->quote($member->assessmentId(),'integer')
				.'		AND usr_id = '.$this->db->quote($member->id(),'integer');
		$this->db->manipulate($sql);
	}

	/**
	 * @inheritdoc
	 */
	public function deleteMembers(ilObjManualAssessment $obj) {
		$sql = "DELETE FROM mass_members WHERE obj_id = ".$this->db->quote($obj->getId(), 'integer');
		$this->db->manipulate($sql);
	}

	/**
	 * @inheritdoc
	 */
	protected function loadMembersQuery($obj_id) {
		return 'SELECT ex.firstname as '.ilManualAssessmentMembers::FIELD_EXAMINER_FIRSTNAME
				.'	, ex.lastname as '.ilManualAssessmentMembers::FIELD_EXAMINER_LASTNAME
				.'	,usr.firstname as '.ilManualAssessmentMembers::FIELD_FIRSTNAME
				.'	,usr.lastname as '.ilManualAssessmentMembers::FIELD_LASTNAME
				.'	,usr.login as '.ilManualAssessmentMembers::FIELD_LOGIN
				.'	,massme.*'
				.' FROM mass_members massme'
				.'	JOIN usr_data usr ON massme.usr_id = usr.usr_id'
				.'	LEFT JOIN usr_data ex ON massme.examiner_id = ex.usr_id'
				.'	WHERE obj_id = '.$this->db->quote($obj_id, 'integer');
	}

	/**
	 * @inheritdoc
	 */
	public function insertMembersRecord(ilObjManualAssessment $mass, array $record) {
		$sql = 'INSERT INTO mass_members (obj_id,usr_id,record,learning_progress,notify) '
				.'	VALUES ('
				.'		'.$this->db->quote($mass->getId(),'integer')
				.'		,'.$this->db->quote($record[ilManualAssessmentMembers::FIELD_USR_ID],'integer')
				.'		,'.$this->db->quote($record[ilManualAssessmentMembers::FIELD_RECORD],'text')
				.'		,'.$this->db->quote($record[ilManualAssessmentMembers::FIELD_LEARNING_PROGRESS],'integer')
				.'		,'.$this->db->quote(0,'integer')
				.'	)';
		$this->db->manipulate($sql);
	}

	/**
	 * @inheritdoc
	 */
	public function removeMembersRecord(ilObjManualAssessment $mass,array $record) {
		$sql = 'DELETE FROM mass_members'
				.'	WHERE obj_id = '.$this->db->quote($mass->getId(), 'integer')
				.'		AND usr_id = '.$this->db->quote($record[ilManualAssessmentMembers::FIELD_USR_ID], 'integer');
		$this->db->manipulate($sql);
	}
}