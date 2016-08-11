<?php
require_once 'Modules/ManualAssessment/interfaces/Members/interface.ilManualAssessmentMembersStorage.php';
require_once 'Modules/ManualAssessment/classes/Members/class.ilManualAssessmentMembers.php';
require_once 'Modules/ManualAssessment/classes/Members/class.ilManualAssessmentMember.php';
require_once 'Modules/ManualAssessment/classes/class.ilObjManualAssessment.php';
class ilManualAssessmentMembersStorageDB implements ilManualAssessmentMembersStorage {

	protected $db;

	public function __construct($ilDB) {
		$this->db = $ilDB;
	}

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
			$member = new ilManualAssessmentMember($obj,$usr);
			$member = $member->withRecord($rec[self::FIELD_RECORD])
						->withInternalNote($rec[self::FIELD_INTERNAL_NOTE])
						->withNotify($rec[self::FIELD_NOTIFY])
						->withGrade($rec[self::FIELD_GRADE]);
			$member = $rec[self::FIELD_EXAMINER_ID] !== null ? $member->withExaminerId($rec[self::FIELD_EXAMINER_ID]) : $member;
			return $member;
		} else {
			throw new ilManualAssessmentException("invalid usr-obj combination");
		}
	}

	public function updateMembers(ilManualAssessmentMembers $new) {
		$current = $this->loadMembers($new->referencedObject());
		foreach($new as $usr_id => $record) {
			if(!$current->userAllreadyMemberByUsrId($usr_id)) {
				$this->insertMembersRecord($new->referencedObject(),$record);
			}
		}

		foreach($current as $usr_id => $record) {
			if(!$new->userAllreadyMemberByUsrId($usr_id)) {
				$this->removeMembersRecord($new->referencedObject(),$record);
			}
		}
	}

	public function updateMember(ilManualAssessmentMember $member) {
		$sql = 'UPDATE mass_members SET '
				.'	'.self::FIELD_GRADE.' = '.$this->db->quote($member->grade(),'text')
				.'	,'.self::FIELD_EXAMINER_ID.' = '.$this->db->quote($member->examinerId(),'integer')
				.'	,'.self::FIELD_RECORD.' = '.$this->db->quote($member->record(),'text')
				.'	,'.self::FIELD_INTERNAL_NOTE.' = '.$this->db->quote($member->internalNote(),'text')
				.'	,'.self::FIELD_NOTIFY.' = '.$this->db->quote($member->notify(),'integer')
				.'	WHERE obj_id = '.$this->db->quote($member->assessmentId(),'integer')
				.'		AND usr_id = '.$this->db->quote($member->id(),'integer');
		$this->db->manipulate($sql);
	}

	public function deleteMembers(ilObjManualAssessment $obj) {
		$sql = "DELETE FROM mass_members WHERE obj_id = ".$this->db->quote($obj->getId(), 'integer');
		$this->db->manipulate($sql);
	}

	protected function loadMembersQuery($obj_id) {
		return 'SELECT ex.firstname as '.self::FIELD_EXAMINER_FIRSTNAME
				.'	, ex.lastname as '.self::FIELD_EXAMINER_LASTNAME
				.'	,usr.firstname as '.self::FIELD_FIRSTNAME
				.'	,usr.lastname as '.self::FIELD_LASTNAME
				.'	,usr.login as '.self::FIELD_LOGIN
				.'	,massme.*'
				.' FROM mass_members massme'
				.'	JOIN usr_data usr ON massme.usr_id = usr.usr_id'
				.'	LEFT JOIN usr_data ex ON massme.examiner_id = ex.usr_id'
				.'	WHERE obj_id = '.$this->db->quote($obj_id, 'integer');
	}

	protected function insertMembersRecord(ilObjManualAssessment $mass, array $record) {
		$sql = 'INSERT INTO mass_members (obj_id,usr_id,record,notify) '
				.'	VALUES ('
				.'		'.$this->db->quote($mass->getId(),'integer')
				.'		,'.$this->db->quote($record[self::FIELD_USR_ID],'integer')
				.'		,'.$this->db->quote($record[self::FIELD_RECORD],'text')
				.'		,'.$this->db->quote(0,'integer')
				.'	)';
		$this->db->manipulate($sql);
	}

	protected function removeMembersRecord(ilObjManualAssessment $mass,array $record) {
		$sql = 'DELETE FROM mass_members'
				.'	WHERE obj_id = '.$this->db->quote($mass->getId(), 'integer')
				.'		AND usr_id = '.$this->db->quote($record[self::FIELD_USR_ID], 'integer');
		$this->db->manipulate($sql);
	}
}