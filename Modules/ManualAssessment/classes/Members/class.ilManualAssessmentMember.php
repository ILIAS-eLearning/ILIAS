<?php
require_once 'Modules/ManualAssessment/classes/class.ilObjManualAssessment.php';
require_once 'Services/User/classes/class.ilObjUser.php';
require_once 'Modules/ManualAssessment/exceptions/class.ilManualAssessmentException.php';
require_once 'Modules/ManualAssessment/classes/Members/class.ilManualAssessmentMembers.php';

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
		$this->notify = $data[ilManualAssessmentMembers::FIELD_NOTIFY];
		$this->grade = $data[ilManualAssessmentMembers::FIELD_LEARNING_PROGRESS];
		$this->finalized = $data[ilManualAssessmentMembers::FIELD_FINALIZED];
		$this->lp_status = $data[ilManualAssessmentMembers::FIELD_LEARNING_PROGRESS];
		$this->notification_ts = $data[ilManualAssessmentMembers::FIELD_NOTIFICATION_TS];
		$this->mass = $mass;
		$this->usr = $usr;
	}

	public function record() {
		return $this->record;
	}

	public function internalNote() {
		return $this->internal_note;
	}

	public function examinerId() {
		return $this->examiner_id;
	}

	public function notify() {
		return $this->notify;
	}

	public function maybeSendNotification(ilManualAssessmentNotificator $notificator) {
		if(!$this->finalized()) {
			throw new ilManualAssessmentException('must finalize before notification');
		}
		if($this->notify) {
			$notificator = (string)$this->grade === (string)ilManualAssessmentMembers::LP_COMPLETED ?
				$notificator->withOccasionCompleted() :
				$notificator->withOccasionFailed();
			$notificator->withReciever($this)->send();
			$this->notification_ts = time();
		}
		return $this;
	}

	public function grade() {
		return $this->grade;
	}

	public function id() {
		return $this->usr->getId();
	}

	public function assessmentId() {
		return $this->mass->getId();
	}

	public function assessment() {
		return $this->mass;
	}

	public function finalized() {
		return (string)$this->finalized === "1" ? true : false;
	}

	public function mayBeFinalized() {
		return ((string)$this->lp_status === (string)ilManualAssessmentMembers::LP_COMPLETED
				||(string)$this->lp_status === (string)ilManualAssessmentMembers::LP_FAILED)
				&& !$this->finalized();
	}

	public function withRecord($record) {
		if(!$this->finalized()) {
			$clone = clone $this;
			$clone->record = $record;
			return $clone;
		}
		throw new ilManualAssessmentException('user allready finalized');
	}

	public function withInternalNote($internal_note) {
		if(!$this->finalized()) {
			$clone = clone $this;
			$clone->internal_note = $internal_note;
			return $clone;
		}
		throw new ilManualAssessmentException('user allready finalized');
	}

	public function withExaminerId($examiner_id) {
		if(!$this->finalized()) {
			assert('ilObjUser::_exists($examiner_id)');
			$clone = clone $this;
			$clone->examiner_id = $examiner_id;
			return $clone;
		}
		throw new ilManualAssessmentException('user allready finalized');
	}

	public function withNotify($notify) {
		if(!$this->finalized()) {
			$clone = clone $this;
			$clone->notify = (bool)$notify;
			return $clone;
		}
		throw new ilManualAssessmentException('user allready finalized');
	}

	protected function LPStatusValid($lp_status) {
		return (string)$lp_status === (string)ilManualAssessmentMembers::LP_IN_PROGRESS
				||(string)$lp_status === (string)ilManualAssessmentMembers::LP_COMPLETED
				||(string)$lp_status === (string)ilManualAssessmentMembers::LP_FAILED;
	}

	public function withLPStatus($lp_status) {
		if(!$this->finalized() && $this->LPStatusValid($lp_status)) {
			$clone = clone $this;
			$clone->lp_status = $lp_status;
			return $clone;
		}
		throw new ilManualAssessmentException('user allready finalized or invalid learning progress status');
	}

	public function lastname() {
		return $this->usr->getLastname();
	}

	public function firstname() {
		return $this->usr->getFirstname();
	}

	public function login() {
		return $this->usr->getLogin();
	}

	public function name() {
		return $this->usr->getLastname().', '.$this->usr->getFirstname();
	}

	public function LPStatus() {
		return $this->lp_status;
	}

	public function withFinalized() {
		if($this->mayBeFinalized()) {
			$clone = clone $this;
			$clone->finalized = 1;
			return $clone;
		}
		throw new ilManualAssessmentException('user cant be finalized');
	}

	public function notificationTS() {
		return $this->notification_ts;
	}
}