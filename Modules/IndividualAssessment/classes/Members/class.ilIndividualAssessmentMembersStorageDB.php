<?php
require_once 'Modules/IndividualAssessment/interfaces/Members/interface.ilIndividualAssessmentMembersStorage.php';
require_once 'Modules/IndividualAssessment/classes/Members/class.ilIndividualAssessmentMembers.php';
require_once 'Modules/IndividualAssessment/classes/Members/class.ilIndividualAssessmentMember.php';
require_once 'Modules/IndividualAssessment/classes/class.ilObjIndividualAssessment.php';
/**
 * Store member infos to DB
 * @inheritdoc
 */
class ilIndividualAssessmentMembersStorageDB implements ilIndividualAssessmentMembersStorage {

	protected $db;

	public function __construct($ilDB) {
		$this->db = $ilDB;
	}

	/**
	 * @inheritdoc
	 */
	public function loadMembers(ilObjIndividualAssessment $obj) {
		$members = new ilIndividualAssessmentMembers($obj);
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
	public function loadMember(ilObjIndividualAssessment $obj, ilObjUser $usr) {
		$obj_id = $obj->getId();
		$usr_id = $usr->getId();
		$sql = 'SELECT iassme.*'
				.' FROM iass_members iassme'
				.'	JOIN usr_data usr ON iassme.usr_id = usr.usr_id'
				.'	LEFT JOIN usr_data ex ON iassme.examiner_id = ex.usr_id'
				.'	WHERE obj_id = '.$this->db->quote($obj_id, 'integer')
				.'		AND iassme.usr_id = '.$this->db->quote($usr_id,'integer');
		$rec = $this->db->fetchAssoc($this->db->query($sql));
		if($rec) {
			$member = new ilIndividualAssessmentMember($obj, $usr, $rec);
			return $member;
		} else {
			throw new ilIndividualAssessmentException("invalid usr-obj combination");
		}
	}

	/**
	 * @inheritdoc
	 */
	public function updateMember(ilIndividualAssessmentMember $member) {
		$sql = 'UPDATE iass_members SET '
				.'	'.ilIndividualAssessmentMembers::FIELD_LEARNING_PROGRESS.' = '.$this->db->quote($member->LPStatus(),'text')
				.'	,'.ilIndividualAssessmentMembers::FIELD_EXAMINER_ID.' = '.$this->db->quote($member->examinerId(),'integer')
				.'	,'.ilIndividualAssessmentMembers::FIELD_RECORD.' = '.$this->db->quote($member->record(),'text')
				.'	,'.ilIndividualAssessmentMembers::FIELD_INTERNAL_NOTE.' = '.$this->db->quote($member->internalNote(),'text')
				.'	,'.ilIndividualAssessmentMembers::FIELD_NOTIFY.' = '.$this->db->quote($member->notify() ? 1 : 0,'integer')
				.'	,'.ilIndividualAssessmentMembers::FIELD_FINALIZED.' = '.$this->db->quote($member->finalized() ? 1 : 0,'integer')
				.'	,'.ilIndividualAssessmentMembers::FIELD_NOTIFICATION_TS.' = '.$this->db->quote($member->notificationTS(),'integer')
				.'	WHERE obj_id = '.$this->db->quote($member->assessmentId(),'integer')
				.'		AND usr_id = '.$this->db->quote($member->id(),'integer');
		$this->db->manipulate($sql);
	}

	/**
	 * @inheritdoc
	 */
	public function deleteMembers(ilObjIndividualAssessment $obj) {
		$sql = "DELETE FROM iass_members WHERE obj_id = ".$this->db->quote($obj->getId(), 'integer');
		$this->db->manipulate($sql);
	}

	/**
	 * @inheritdoc
	 */
	protected function loadMembersQuery($obj_id) {
		return 'SELECT ex.firstname as '.ilIndividualAssessmentMembers::FIELD_EXAMINER_FIRSTNAME
				.'	, ex.lastname as '.ilIndividualAssessmentMembers::FIELD_EXAMINER_LASTNAME
				.'	,usr.firstname as '.ilIndividualAssessmentMembers::FIELD_FIRSTNAME
				.'	,usr.lastname as '.ilIndividualAssessmentMembers::FIELD_LASTNAME
				.'	,usr.login as '.ilIndividualAssessmentMembers::FIELD_LOGIN
				.'	,iassme.*'
				.' FROM iass_members iassme'
				.'	JOIN usr_data usr ON iassme.usr_id = usr.usr_id'
				.'	LEFT JOIN usr_data ex ON iassme.examiner_id = ex.usr_id'
				.'	WHERE obj_id = '.$this->db->quote($obj_id, 'integer');
	}

	/**
	 * @inheritdoc
	 */
	public function insertMembersRecord(ilObjIndividualAssessment $iass, array $record) {
		$sql = 'INSERT INTO iass_members (obj_id,usr_id,record,learning_progress,notify) '
				.'	VALUES ('
				.'		'.$this->db->quote($iass->getId(),'integer')
				.'		,'.$this->db->quote($record[ilIndividualAssessmentMembers::FIELD_USR_ID],'integer')
				.'		,'.$this->db->quote($record[ilIndividualAssessmentMembers::FIELD_RECORD],'text')
				.'		,'.$this->db->quote($record[ilIndividualAssessmentMembers::FIELD_LEARNING_PROGRESS],'integer')
				.'		,'.$this->db->quote(0,'integer')
				.'	)';
		$this->db->manipulate($sql);
	}

	/**
	 * @inheritdoc
	 */
	public function removeMembersRecord(ilObjIndividualAssessment $iass,array $record) {
		$sql = 'DELETE FROM iass_members'
				.'	WHERE obj_id = '.$this->db->quote($iass->getId(), 'integer')
				.'		AND usr_id = '.$this->db->quote($record[ilIndividualAssessmentMembers::FIELD_USR_ID], 'integer');
		$this->db->manipulate($sql);
	}
}