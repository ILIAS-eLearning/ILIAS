<?php
require_once 'Services/Mail/classes/class.ilMailNotification.php';
require_once 'Modules/ManualAssessment/interfaces/Notification/interface.ilManualAssessmentNotificator.php';

class ilManualAssessmentPrimitiveMailNotificator extends ilMailNotification implements ilManualAssessmentNotificator {
	const OCCASION_FAILED = 0;
	const OCCASION_COMPLETED = 1;

	protected $occasion;
	protected $reciever;

	public function __construct() {
		$this->occasion = null;
		$this->reciever = null;
	}

	public function withReciever(ilManualAssessmentMember $member) {
		$clone = clone $this;
		$clone->reciever = $member;
		return $clone;
	}

	public function withOccasionFailed() {
		$clone = clone $this;
		$clone->occasion = self::OCCASION_FAILED;
		return $clone;
	}

	public function withOccasionCompleted() {
		$clone = clone $this;
		$clone->occasion = self::OCCASION_COMPLETED;
		return $clone;
	}

	public function send() {
		if(! $this->reciever instanceof ilManualAssessmentMember || !in_array($this->occasion, array(self::OCCASION_COMPLETED,self::OCCASION_FAILED))) {
			throw new ilManualAssessmentException('can\'t notify');
		}
		$this->initLanguage($this->reciever->id());
		$this->initMail();
		$subject = $this->occasion === self::OCCASION_COMPLETED ? $this->getLanguageText('mass_subj_notification_completed') : $this->getLanguageText('mass_subj_notification_failed');
		$this->setSubject(
			sprintf($subject,$this->reciever->assessment()->getTitle())
		);
		$this->setBody(ilMail::getSalutation($rcp,$this->getLanguage()));
		$this->appendBody("\n\n");
		$this->appendBody($this->reciever->record());
		$this->appendBody("\n\n");
		$this->appendBody($this->createPermanentLink());
		$this->getMail()->appendInstallationSignature(true);
		$this->sendMail(array($this->reciever->id()),array('system'));
	}
}