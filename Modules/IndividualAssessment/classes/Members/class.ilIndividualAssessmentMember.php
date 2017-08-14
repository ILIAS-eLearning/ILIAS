<?php
require_once 'Modules/IndividualAssessment/classes/class.ilObjIndividualAssessment.php';
require_once 'Services/User/classes/class.ilObjUser.php';
require_once 'Modules/IndividualAssessment/exceptions/class.ilIndividualAssessmentException.php';
require_once 'Modules/IndividualAssessment/classes/Members/class.ilIndividualAssessmentMembers.php';
/**
 * Edit the record of a user, set LP.
 * @author	Denis Klöpfer <denis.kloepfer@concepts-and-training.de>
 * @author	Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilIndividualAssessmentMember {
	protected $iass;
	protected $usr;

	protected $record;
	protected $internal_note;
	protected $examiner_id;
	protected $notify;
	protected $finalized;
	protected $notification_ts;
	protected $lp_status;
	protected $place;
	protected $event_time;

	public function __construct(ilObjIndividualAssessment $iass, ilObjUser $usr, array $data) {

		$this->record = $data[ilIndividualAssessmentMembers::FIELD_RECORD];
		$this->internal_note = $data[ilIndividualAssessmentMembers::FIELD_INTERNAL_NOTE];
		$this->examiner_id = $data[ilIndividualAssessmentMembers::FIELD_EXAMINER_ID];
		$this->notify = $data[ilIndividualAssessmentMembers::FIELD_NOTIFY] ? true : false;
		$this->finalized = $data[ilIndividualAssessmentMembers::FIELD_FINALIZED] ? true : false;
		$this->lp_status = $data[ilIndividualAssessmentMembers::FIELD_LEARNING_PROGRESS];
		$this->notification_ts = $data[ilIndividualAssessmentMembers::FIELD_NOTIFICATION_TS];
		$this->place = $data[ilManualAssessmentMembers::FIELD_PLACE];
		$this->event_time = new ilDateTime($data[ilManualAssessmentMembers::FIELD_EVENTTIME], IL_CAL_UNIX);
		$this->iass = $iass;
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
	 * @param	ilIndividualAssessmentNotificator	$notificator
	 * @return	ilIndividualAssessmentMember	$this
	 */
	public function maybeSendNotification(ilIndividualAssessmentNotificator $notificator) {
		if(!$this->finalized()) {
			throw new ilIndividualAssessmentException('must finalize before notification');
		}
		if($this->notify) {
			$notificator = (string)$this->lp_status === (string)ilIndividualAssessmentMembers::LP_COMPLETED ?
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
	 * Get the ilObjIndividualAssessment id corresponding to this membership
	 *
	 * @return	int|string
	 */
	public function assessmentId() {
		return $this->iass->getId();
	}

	/**
	 * Get the ilObjIndividualAssessment corresponding to this membership
	 *
	 * @return	ilObjIndividualAssessment
	 */
	public function assessment() {
		return $this->iass;
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
		return ((string)$this->lp_status === (string)ilIndividualAssessmentMembers::LP_COMPLETED
				||(string)$this->lp_status === (string)ilIndividualAssessmentMembers::LP_FAILED)
				&& !$this->finalized();
	}

	/**
	 * Clone this object and set a record
	 *
	 * @param	string	$record
	 * @return	ilIndividualAssessmentMember
	 */
	public function withRecord($record) {
		assert('is_string($record) || $record === null');
		if(!$this->finalized()) {
			$clone = clone $this;
			$clone->record = $record;
			return $clone;
		}
		throw new ilIndividualAssessmentException('user allready finalized');
	}

	/**
	 * Clone this object and set an internal note
	 *
	 * @param	string	$internal_note
	 * @return	ilIndividualAssessmentMember
	 */
	public function withInternalNote($internal_note) {
		assert('is_string($internal_note) || $internal_note === null');
		if(!$this->finalized()) {
			$clone = clone $this;
			$clone->internal_note = $internal_note;
			return $clone;
		}
		throw new ilIndividualAssessmentException('user allready finalized');
	}

	/**
	 * Clone this object and set an internal note
	 *
	 * @param	string	$place
	 * @return	ilManualAssessmentMember
	 */
	public function withPlace($place)
	{
		assert('is_string($place) || is_null($place)');
		$clone = clone $this;
		$clone->place = $place;
		return $clone;
	}

	/**
	 * Clone this object and set an internal note
	 *
	 * @param	ilDateTime | null	$internal_note
	 * @return	ilManualAssessmentMember
	 */
	public function withEventTime($event_time)
	{
		assert('$event_time instanceof ilDateTime || is_null($event_time)');
		$clone = clone $this;
		$clone->event_time = $event_time;
		return $clone;
	}

	/**
	 * Clone this object and set an examiner_id
	 *
	 * @param	int|string	$examiner_id
	 * @return	ilIndividualAssessmentMember
	 */
	public function withExaminerId($examiner_id) {
		assert('is_numeric($examiner_id)');
		if(!$this->finalized()) {
			assert('ilObjUser::_exists($examiner_id)');
			$clone = clone $this;
			$clone->examiner_id = $examiner_id;
			return $clone;
		}
		throw new ilIndividualAssessmentException('user allready finalized');
	}

	/**
	 * Clone this object and set wether the examinee should be notified.
	 *
	 * @param	bool	$notify
	 * @return	ilIndividualAssessmentMember
	 */
	public function withNotify($notify) {
		assert('is_bool($notify)');
		if(!$this->finalized()) {
			$clone = clone $this;
			$clone->notify = (bool)$notify;
			return $clone;
		}
		throw new ilIndividualAssessmentException('user allready finalized');
	}

	protected function LPStatusValid($lp_status) {
		return (string)$lp_status === (string)ilIndividualAssessmentMembers::LP_NOT_ATTEMPTED
				||(string)$lp_status === (string)ilIndividualAssessmentMembers::LP_IN_PROGRESS
				||(string)$lp_status === (string)ilIndividualAssessmentMembers::LP_COMPLETED
				||(string)$lp_status === (string)ilIndividualAssessmentMembers::LP_FAILED;
	}

	/**
	 * Clone this object and set LP-status.
	 *
	 * @param	string	$lp_status
	 * @return	ilIndividualAssessmentMember
	 */
	public function withLPStatus($lp_status) {
		if(!$this->finalized() && $this->LPStatusValid($lp_status)) {
			$clone = clone $this;
			$clone->lp_status = $lp_status;
			return $clone;
		}
		throw new ilIndividualAssessmentException('user allready finalized or invalid learning progress status');
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
	 * @return	ilIndividualAssessmentMember
	 */
	public function withFinalized() {
		if($this->mayBeFinalized()) {
			$clone = clone $this;
			$clone->finalized = 1;
			return $clone;
		}
		throw new ilIndividualAssessmentException('user cant be finalized');
	}

	/**
	 * Get the timestamp, at which the notification was sent.
	 *
	 * @return	int|string
	 */
	public function notificationTS() {
		return $this->notification_ts;
	}

	/**
	 * Get place where ia was held
	 *
	 * @return string
	 */
	public function place()
	{
		return $this->place;
	}

	/**
	 * Get date when ia was
	 *
	 * @return ilDateTime
	 */
	public function eventTime()
	{
		return $this->event_time;
	}
}