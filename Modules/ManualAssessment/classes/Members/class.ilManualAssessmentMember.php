<?php
require_once 'Modules/ManualAssessment/classes/class.ilObjManualAssessment.php';
require_once 'Services/User/classes/class.ilObjUser.php';
class ilManualAssessmentMember {
	protected $mass;
	protected $usr;

	protected $record;
	protected $internal_note;
	protected $examiner_id;
	protected $notify;
	protected $grade;

	public function __construct(ilObjManualAssessment $mass, ilObjUser $usr) {
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

	public function grade() {
		return $this->grade;
	}

	public function id() {
		return $this->usr->getId();
	}

	public function assessmentId() {
		return $this->mass->getId();
	}

	public function withRecord($record) {
		$clone = clone $this;
		$clone->record = $record;
		return $clone;
	}

	public function withInternalNote($internal_note) {
		$clone = clone $this;
		$clone->internal_note = $internal_note;
		return $clone;
	}

	public function withExaminerId($examiner_id) {
		assert('ilObjUser::_exists($examiner_id)');
		$clone = clone $this;
		$clone->examiner_id = $examiner_id;
		return $clone;
	}

	public function withNotify($notify) {
		$clone = clone $this;
		$clone->notify = (bool)$notify;
		return $clone;
	}

	public function withGrade($grade) {
		$clone = clone $this;
		$clone->grade = $grade;
		return $clone;
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

}