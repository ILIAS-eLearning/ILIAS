<?php
require_once 'Modules/ManualAssessment/classes/class.ilObjManualAssessment.php';
require_once 'Services/User/classes/class.ilObjUser.php';
require_once 'Modules/ManualAssessment/exceptions/class.ilManualAssessmentException.php';
require_once 'Modules/ManualAssessment/classes/Members/class.ilManualAssessmentMembers.php';
/**
 * Edit the record of a user, set LP.
 * @author	Denis Klöpfer <denis.kloepfer@concepts-and-training.de>
 */
class ilManualAssessmentMember {
	protected $mass;
	protected $usr;

	protected $record;
	protected $internal_note;
	protected $examiner_id;
	protected $notify;
	protected $finalized;
	protected $notification_ts;
	protected $lp_status;

	public function __construct(ilObjManualAssessment $mass, ilObjUser $usr, array $data) {

		$this->record = $data[ilManualAssessmentMembers::FIELD_RECORD];
		$this->internal_note = $data[ilManualAssessmentMembers::FIELD_INTERNAL_NOTE];
		$this->examiner_id = $data[ilManualAssessmentMembers::FIELD_EXAMINER_ID];
		$this->notify = $data[ilManualAssessmentMembers::FIELD_NOTIFY] ? true : false;
		$this->finalized = $data[ilManualAssessmentMembers::FIELD_FINALIZED] ? true : false;
		$this->lp_status = $data[ilManualAssessmentMembers::FIELD_LEARNING_PROGRESS];
		$this->notification_ts = $data[ilManualAssessmentMembers::FIELD_NOTIFICATION_TS];
		$this->mass = $mass;
		$this->usr = $usr;
	}

	/**
	 * @return	string
	 */
	public function record() {
		return $this->record;
	}

	/**
	 * @return	string
	 */
	public function internalNote() {
		return $this->internal_note;
	}

	/**
	 * Get the user id of the examiner
	 *
	 * @return	int|string
	 */
	public function examinerId() {
		return $this->examiner_id;
	}

	/**
	 *	Will the user be notified after finalization?
	 *
	 * @return	bool
	 */
	public function notify() {
		return $this->notify;
	}

	/**
	 * Notify a user, if he is supposed to be notified,
	 * using some notificator object.
	 *
	 * @param	ilManualAssessmentNotificator	$notificator
	 * @return	ilManualAssessmentMember	$this
	 */
	public function maybeSendNotification(ilManualAssessmentNotificator $notificator) {
		if(!$this->finalized()) {
			throw new ilManualAssessmentException('must finalize before notification');
		}
		if($this->notify) {
			$notificator = (string)$this->lp_status === (string)ilManualAssessmentMembers::LP_COMPLETED ?
				$notificator->withOccasionCompleted() :
				$notificator->withOccasionFailed();
			$notificator->withReciever($this)->send();
			$this->notification_ts = time();
		}
		return $this;
	}

	/**
	 * Get the user id corresponding to this membership
	 *
	 * @return	int|string
	 */
	public function id() {
		return $this->usr->getId();
	}

	/**
	 * Get the ilObjManualAssessment id corresponding to this membership
	 *
	 * @return	int|string
	 */
	public function assessmentId() {
		return $this->mass->getId();
	}

	/**
	 * Get the ilObjManualAssessment corresponding to this membership
	 *
	 * @return	ilObjManualAssessment
	 */
	public function assessment() {
		return $this->mass;
	}

	/**
	 * Is this membership allready finalized?
	 *
	 * @return	bool
	 */
	public function finalized() {
		return (string)$this->finalized === "1" ? true : false;
	}

	/**
	 * Can this membership be finalized?
	 *
	 * @return	bool
	 */
	public function mayBeFinalized() {
		return ((string)$this->lp_status === (string)ilManualAssessmentMembers::LP_COMPLETED
				||(string)$this->lp_status === (string)ilManualAssessmentMembers::LP_FAILED)
				&& !$this->finalized();
	}

	/**
	 * Clone this object and set a record
	 *
	 * @param	string	$record
	 * @return	ilManualAssessmentMember
	 */
	public function withRecord($record) {
		assert('is_string($record) || $record === null');
		if(!$this->finalized()) {
			$clone = clone $this;
			$clone->record = $record;
			return $clone;
		}
		throw new ilManualAssessmentException('user allready finalized');
	}

	/**
	 * Clone this object and set an internal note
	 *
	 * @param	string	$internal_note
	 * @return	ilManualAssessmentMember
	 */
	public function withInternalNote($internal_note) {
		assert('is_string($internal_note) || $internal_note === null');
		if(!$this->finalized()) {
			$clone = clone $this;
			$clone->internal_note = $internal_note;
			return $clone;
		}
		throw new ilManualAssessmentException('user allready finalized');
	}

	/**
	 * Clone this object and set an examiner_id
	 *
	 * @param	int|string	$examiner_id
	 * @return	ilManualAssessmentMember
	 */
	public function withExaminerId($examiner_id) {
		assert('is_numeric($examiner_id)');
		if(!$this->finalized()) {
			assert('ilObjUser::_exists($examiner_id)');
			$clone = clone $this;
			$clone->examiner_id = $examiner_id;
			return $clone;
		}
		throw new ilManualAssessmentException('user allready finalized');
	}

	/**
	 * Clone this object and set wether the examinee should be notified.
	 *
	 * @param	bool	$notify
	 * @return	ilManualAssessmentMember
	 */
	public function withNotify($notify) {
		assert('is_bool($notify)');
		if(!$this->finalized()) {
			$clone = clone $this;
			$clone->notify = (bool)$notify;
			return $clone;
		}
		throw new ilManualAssessmentException('user allready finalized');
	}

	protected function LPStatusValid($lp_status) {
		return  (string)$lp_status === (string)ilManualAssessmentMembers::LP_NOT_ATTEMPTED
				||(string)$lp_status === (string)ilManualAssessmentMembers::LP_IN_PROGRESS
				||(string)$lp_status === (string)ilManualAssessmentMembers::LP_COMPLETED
				||(string)$lp_status === (string)ilManualAssessmentMembers::LP_FAILED;
	}

	/**
	 * Clone this object and set LP-status.
	 *
	 * @param	string	$lp_status
	 * @return	ilManualAssessmentMember
	 */
	public function withLPStatus($lp_status) {
		if(!$this->finalized() && $this->LPStatusValid($lp_status)) {
			$clone = clone $this;
			$clone->lp_status = $lp_status;
			return $clone;
		}
		throw new ilManualAssessmentException('user allready finalized or invalid learning progress status');
	}

	/**
	 * Get the examinee lastname corresponding to this membership
	 *
	 * @return	int|string
	 */
	public function lastname() {
		return $this->usr->getLastname();
	}

	/**
	 * Get the examinee firstname corresponding to this membership
	 *
	 * @return	int|string
	 */
	public function firstname() {
		return $this->usr->getFirstname();
	}

	/**
	 * Get the examinee login corresponding to this membership
	 *
	 * @return	int|string
	 */
	public function login() {
		return $this->usr->getLogin();
	}

	/**
	 * Get the examinee name corresponding to this membership
	 *
	 * @return	int|string
	 */
	public function name() {
		return $this->usr->getFullname();
	}

	/**
	 * Get the LP-status corresponding to this membership
	 *
	 * @return	int|string
	 */
	public function LPStatus() {
		return $this->lp_status;
	}

	/**
	 * Clone this object and finalize.
	 *
	 * @return	ilManualAssessmentMember
	 */
	public function withFinalized() {
		if($this->mayBeFinalized()) {
			$clone = clone $this;
			$clone->finalized = 1;
			return $clone;
		}
		throw new ilManualAssessmentException('user cant be finalized');
	}

	/**
	 * Get the timestamp, at which the notification was sent.
	 *
	 * @return	int|string
	 */
	public function notificationTS() {
		return $this->notification_ts;
	}
}