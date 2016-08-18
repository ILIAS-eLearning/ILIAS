<?php

require_once 'Services/User/classes/class.ilObjUser.php';
require_once 'Modules/ManualAssessment/classes/class.ilObjManualAssessment.php';
require_once 'Services/Tracking/classes/class.ilLPStatus.php';

class ilManualAssessmentMembers implements Iterator, Countable {
	protected $member_records = array();
	protected $position = 0;
	protected $mass;

	const FIELD_FIRSTNAME = 'firstname';
	const FIELD_LASTNAME = 'lastname';
	const FIELD_LOGIN = 'login';
	const FIELD_USR_ID = 'usr_id';
	const FIELD_LEARNING_PROGRESS = 'learning_progress';
	const FIELD_EXAMINER_ID = 'examiner_id';
	const FIELD_EXAMINER_FIRSTNAME = 'examiner_firstname';
	const FIELD_EXAMINER_LASTNAME = 'examiner_lastname';
	const FIELD_RECORD = 'record';
	const FIELD_INTERNAL_NOTE = 'internal_note';
	const FIELD_NOTIFY = 'notify';
	const FIELD_FINALIZED = 'finalized';
	const FIELD_NOTIFICATION_TS = 'notification_ts';

	const LP_IN_PROGRESS = ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
	const LP_COMPLETED = ilLPStatus::LP_STATUS_COMPLETED_NUM;
	const LP_FAILED = ilLPStatus::LP_STATUS_FAILED_NUM;

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
		if(isset($record[self::FIELD_USR_ID])) {
			if(!$this->userExists($record[self::FIELD_USR_ID])
				|| $this->userAllreadyMemberByUsrId($record[self::FIELD_USR_ID])) {
				var_dump($record);
				die();
				return fasle;
			}
		}
		if(!in_array($record[self::FIELD_LEARNING_PROGRESS],
			array(self::LP_FAILED, self::LP_COMPLETED, self::LP_IN_PROGRESS))) {
			var_dump($record);
			die();
			return false;
		}
		return true;
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
			$clone->member_records[$record[self::FIELD_USR_ID]] = $record;
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
			  self::FIELD_USR_ID				=> $usr->getId()
			, self::FIELD_RECORD				=> $this->mass->getSettings()->recordTemplate()
			, self::FIELD_NOTIFY				=> 0
			, self::FIELD_FIRSTNAME				=> $usr->getFirstname()
			, self::FIELD_LASTNAME				=> $usr->getLastname()
			, self::FIELD_LOGIN					=> $usr->getLogin()
			, self::FIELD_LEARNING_PROGRESS		=> self::LP_IN_PROGRESS
			, self::FIELD_EXAMINER_ID			=> null
			, self::FIELD_EXAMINER_FIRSTNAME	=> null
			, self::FIELD_EXAMINER_LASTNAME		=> null
			, self::FIELD_INTERNAL_NOTE			=> null
			, self::FIELD_FINALIZED				=> 0
			);
	}

	public function withoutPresentUser(ilObjUser $usr) {
		$usr_id = $usr->getId();
		if(isset($this->member_records[$usr_id]) && (string)$this->member_records[$usr_id][self::FIELD_FINALIZED] !== "1") {
			$clone = clone $this;
			unset($clone->member_records[$usr->getId()]);
			return $clone;
		}
		throw new ilManualAssessmentException('User not member or allready finished');
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

	public function membersIds() {
		return array_keys($this->member_records);
	}
	
	public function updateStorageAndRBAC(ilManualAssessmentMembersStorage $storage, ManualAssessmentAccessHandler $access_handler) {
		$current = $storage->loadMembers($this->referencedObject());
		$mass = $this->referencedObject();
		foreach($this as $usr_id => $record) {
			if(!$current->userAllreadyMemberByUsrId($usr_id)) {
				$storage->insertMembersRecord($this->referencedObject(),$record);
				$access_handler->assignUserToMemberRole(new ilObjUser($usr_id),$mass);
			}
		}
		foreach($current as $usr_id => $record) {
			if(!$this->userAllreadyMemberByUsrId($usr_id)) {
				$storage->removeMembersRecord($this->referencedObject(),$record);
				$access_handler->deassignUserFromMemberRole(new ilObjUser($usr_id),$mass);
			}
		}
	}
}