<?php
require_once 'Services/Mail/classes/class.ilMailNotification.php';
require_once 'Modules/IndividualAssessment/interfaces/Notification/interface.ilIndividualAssessmentNotificator.php';
/**
 * Notificate user using internal mail system.
 * @inheritdoc
 */
class ilIndividualAssessmentPrimitiveInternalNotificator extends ilMailNotification implements ilIndividualAssessmentNotificator
{
    const OCCASION_FAILED = 0;
    const OCCASION_COMPLETED = 1;

    protected $occasion;
    protected $reciever;

    public function __construct()
    {
        parent::__construct();
        $this->setLangModules(array('iass'));
    }

    /**
     * @inheritdoc
     */
    public function withReciever(ilIndividualAssessmentMember $member)
    {
        $clone = clone $this;
        $clone->reciever = $member;
        $clone->ref_id = $member->assessment()->getRefId();
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withOccasionFailed()
    {
        $clone = clone $this;
        $clone->occasion = self::OCCASION_FAILED;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withOccasionCompleted()
    {
        $clone = clone $this;
        $clone->occasion = self::OCCASION_COMPLETED;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function send()
    {
        if (!$this->reciever instanceof ilIndividualAssessmentMember || !in_array($this->occasion, array(self::OCCASION_COMPLETED,self::OCCASION_FAILED))) {
            throw new ilIndividualAssessmentException('can\'t notify');
        }
        $this->initLanguage($this->reciever->id());
        $this->initMail();
        $subject = $this->occasion === self::OCCASION_COMPLETED
            ? $this->getLanguageText('iass_subj_notification_completed')
            : $this->getLanguageText('iass_subj_notification_failed');
        $message = $this->occasion === self::OCCASION_COMPLETED
            ? $this->getLanguageText('iass_mess_notification_completed')
            : $this->getLanguageText('iass_mess_notification_failed');
        $assessment_title = $this->reciever->assessment()->getTitle();
        $this->setSubject(
            sprintf($subject, $assessment_title)
        );
        $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
        $this->appendBody("\n\n");
        $this->appendBody(sprintf($message, $assessment_title));
        $this->appendBody("\n\n");
        $this->appendBody($this->reciever->record());
        $this->appendBody("\n\n");
        $this->appendBody($this->createPermanentLink());
        $this->getMail()->appendInstallationSignature(true);
        $this->sendMail(array($this->reciever->id()), array('system'));
    }
}
