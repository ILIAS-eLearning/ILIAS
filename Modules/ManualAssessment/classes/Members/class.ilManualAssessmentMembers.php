<?php

require_once 'Services/User/classes/class.ilObjUser.php';
require_once 'Modules/ManualAssessment/classes/class.ilObjManualAssessment.php';

class ilManualAssessmentMembers implements Iterator, Countable {
	protected $member_records = array();
	protected $position = 0;
	protected $mass;

	public function __construct(ilObjManualAssessment $mass) {
		$this->mass = $mass;
	}

	public function count() {
		return count($this->member_records);
	}

	public function referencedObject() {
		return $this->mass;
	}

	public function recordOK(array $record) {
		if(isset($record[ilManualAssessmentMembersStorageDB::FIELD_USR_ID])) {
			if($this->userExists($record[ilManualAssessmentMembersStorageDB::FIELD_USR_ID]) && !$this->userAllreadyMemberByUsrId($record[ilManualAssessmentMembersStorageDB::FIELD_USR_ID])) {
				return true;
			}
		}
		return false;
	}

	public function userAllreadyMemberByUsrId($usr_id) {
		return isset($this->member_records[$usr_id]);
	}

	public function userAllreadyMember(ilObjUser $usr) {
		return $this->userAllreadyMemberByUsrId($usr->getId());
	}

	protected function userExists($usr_id) {
		return ilObjUser::_exists($usr_id,false,'usr');
	}

	public function withAdditionalRecord(array $record) {
		if($this->recordOK($record)) {
			$clone = clone $this;
			$clone->member_records[$record[ilManualAssessmentMembersStorageDB::FIELD_USR_ID]] = $record;
			return $clone;
		}
		throw new ilManualAssessmentException('illdefined record');
	}

	public function withAdditionalUser(ilObjUser $usr) {
		if(!$this->userAllreadyMember($usr)) {
			$clone = clone $this;
			$clone->member_records[$usr->getId()] = $this->buildNewRecordOfUser($usr);
			return $clone;
		}
		throw new ilManualAssessmentException('User allready member');
	}

	protected function buildNewRecordOfUser(ilObjUser $usr) {
		return array(
			  ilManualAssessmentMembersStorageDB::FIELD_USR_ID		=> $usr->getId()
			, ilManualAssessmentMembersStorageDB::FIELD_RECORD		=> $this->mass->getSettings()->recordTemplate()
			, ilManualAssessmentMembersStorageDB::FIELD_NOTIFY		=> 0
			, ilManualAssessmentMembersStorageDB::FIELD_FIRSTNAME	=> $usr->getFirstname()
			, ilManualAssessmentMembersStorageDB::FIELD_LASTNAME	=> $usr->getLastname()
			, ilManualAssessmentMembersStorageDB::FIELD_LOGIN		=> $usr->getLogin()
			, ilManualAssessmentMembersStorageDB::FIELD_GRADE 		=> null
			, ilManualAssessmentMembersStorageDB::FIELD_EXAMINER_ID => null
			, ilManualAssessmentMembersStorageDB::FIELD_EXAMINER_FIRSTNAME	=> null
			, ilManualAssessmentMembersStorageDB::FIELD_EXAMINER_LASTNAME	=> null
			, ilManualAssessmentMembersStorageDB::FIELD_INTERNAL_NOTE		=> null
			);
	}

	public function withoutPresentUser(ilObjUser $usr) {
		$clone = clone $this;
		if(isset($this->member_records[$usr->getId()])) {
			unset($clone->member_records[$usr->getId()]);
		}
		return $clone;
	}

	public function current() {
		return current($this->member_records);
	}

	public function key() {
		return key($this->member_records);
	}

	public function next() {
		$this->position++;
		next($this->member_records);
	}

	public function rewind() {
		$this->position = 0;
		reset($this->member_records);
	}

	public function valid() {
		return $this->position < count($this->member_records);
	}
	
}