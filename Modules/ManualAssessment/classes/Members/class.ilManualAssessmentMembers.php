<?php
/**
 * Member administration related logic, add and remove members,
 * get the list of all members, etc..
 * @author	Denis Klöpfer <denis.kloepfer@concepts-and-training.de>
 */

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

	const LP_NOT_ATTEMPTED = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
	const LP_IN_PROGRESS = ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
	const LP_COMPLETED = ilLPStatus::LP_STATUS_COMPLETED_NUM;
	const LP_FAILED = ilLPStatus::LP_STATUS_FAILED_NUM;

	public function __construct(ilObjManualAssessment $mass) {
		$this->mass = $mass;
	}

	/**
	 * Countable Methods 
	 */
	public function count() {
		return count($this->member_records);
	}

	/**
	 * Iterator Methods
	 */
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

	/**
	 * Get the manual assessment object that is corresponding to this
	 *
	 * @return	ilObjManualAssessment
	 */
	public function referencedObject() {
		return $this->mass;
	}

	/**
	 * Check the validity of a record before adding it to this
	 *
	 * @param int|string|null[]	$record
	 * @return	bool
	 */
	public function recordOK(array $record) {
		if(isset($record[self::FIELD_USR_ID])) {
			if(!$this->userExists($record[self::FIELD_USR_ID])
				|| $this->userAllreadyMemberByUsrId($record[self::FIELD_USR_ID])) {
				return false;
			}
		}
		if(!in_array($record[self::FIELD_LEARNING_PROGRESS],
			array(self::LP_NOT_ATTEMPTED, self::LP_FAILED, self::LP_COMPLETED, self::LP_IN_PROGRESS))) {
			return false;
		}
		return true;
	}

	/**
	 * Check if a user with user_id is member of this
	 *
	 * @param int|string	$usr_id
	 * @return	bool
	 */
	public function userAllreadyMemberByUsrId($usr_id) {
		return isset($this->member_records[$usr_id]);
	}

	/**
	 * Check if a user is member of this
	 *
	 * @param ilObjUser	$usr
	 * @return	bool
	 */
	public function userAllreadyMember(ilObjUser $usr) {
		return $this->userAllreadyMemberByUsrId($usr->getId());
	}

	protected function userExists($usr_id) {
		return ilObjUser::_exists($usr_id,false,'usr');
	}

	/**
	 * Clone this and add an additional record
	 *
	 * @param	int|string|null[]	$record
	 * @return	ilManualAssessmentMembers
	 * @throws	ilManualAssessmentException
	 */
	public function withAdditionalRecord(array $record) {
		if($this->recordOK($record)) {
			$clone = clone $this;
			$clone->member_records[$record[self::FIELD_USR_ID]] = $record;
			return $clone;
		}
		throw new ilManualAssessmentException('illdefined record');
	}

	/**
	 * Clone this and add an additional record created for user
	 *
	 * @param	ilObjUser	$usr
	 * @return	ilManualAssessmentMembers
	 * @throws	ilManualAssessmentException
	 */
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
			, self::FIELD_LEARNING_PROGRESS		=> self::LP_NOT_ATTEMPTED
			, self::FIELD_EXAMINER_ID			=> null
			, self::FIELD_EXAMINER_FIRSTNAME	=> null
			, self::FIELD_EXAMINER_LASTNAME		=> null
			, self::FIELD_INTERNAL_NOTE			=> null
			, self::FIELD_FINALIZED				=> 0
			);
	}

	/**
	 * Clone this andremove record corresponding to user
	 *
	 * @param	ilObjUser	$usr
	 * @return	ilManualAssessmentMembers
	 * @throws	ilManualAssessmentException
	 */
	public function withoutPresentUser(ilObjUser $usr) {
		$usr_id = $usr->getId();
		if(isset($this->member_records[$usr_id]) && (string)$this->member_records[$usr_id][self::FIELD_FINALIZED] !== "1") {
			$clone = clone $this;
			unset($clone->member_records[$usr->getId()]);
			return $clone;
		}
		throw new ilManualAssessmentException('User not member or allready finished');
	}


	/**
	 * Get the ids of all the users being member in this mass
	 *
	 * @return	int|string[]
	 */
	public function membersIds() {
		return array_keys($this->member_records);
	}
	
	/**
	 * Store the data to a persistent medium 
	 *
	 * @param	ilManualAssessmentMembersStorage	$storage
	 * @param	ManualAssessmentAccessHandler	$access_handler
	 */
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