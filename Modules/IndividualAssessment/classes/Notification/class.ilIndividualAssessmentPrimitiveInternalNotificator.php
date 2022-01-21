<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

/**
 * Notifies user, using internal mail system.
 */
class ilIndividualAssessmentPrimitiveInternalNotificator extends ilMailNotification implements ilIndividualAssessmentNotificator
{
    const OCCASION_FAILED = 0;
    const OCCASION_COMPLETED = 1;

    protected int $occasion;
    protected ilIndividualAssessmentMember $receiver;

    public function __construct()
    {
        parent::__construct();
        $this->setLangModules(array('iass'));
    }

    /**
     * @inheritdoc
     */
    public function withReceiver(
        ilIndividualAssessmentMember $member
    ) : ilIndividualAssessmentPrimitiveInternalNotificator {
        $clone = clone $this;
        $clone->receiver = $member;
        $clone->ref_id = $member->assessment()->getRefId();
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withOccasionFailed() : ilIndividualAssessmentPrimitiveInternalNotificator
    {
        $clone = clone $this;
        $clone->occasion = self::OCCASION_FAILED;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withOccasionCompleted() : ilIndividualAssessmentPrimitiveInternalNotificator
    {
        $clone = clone $this;
        $clone->occasion = self::OCCASION_COMPLETED;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function send() : void
    {
        if (
            !$this->receiver instanceof ilIndividualAssessmentMember ||
            !in_array($this->occasion, array(self::OCCASION_COMPLETED, self::OCCASION_FAILED))
        ) {
            throw new ilIndividualAssessmentException('can\'t notify');
        }
        $this->initLanguage($this->receiver->id());
        $this->initMail();
        $subject = $this->occasion === self::OCCASION_COMPLETED
            ? $this->getLanguageText('iass_subj_notification_completed')
            : $this->getLanguageText('iass_subj_notification_failed');
        $message = $this->occasion === self::OCCASION_COMPLETED
            ? $this->getLanguageText('iass_mess_notification_completed')
            : $this->getLanguageText('iass_mess_notification_failed');
        $assessment_title = $this->receiver->assessment()->getTitle();
        $this->setSubject(
            sprintf($subject, $assessment_title)
        );
        $this->setBody(ilMail::getSalutation($this->receiver->id(), $this->getLanguage()));
        $this->appendBody("\n\n");
        $this->appendBody(sprintf($message, $assessment_title));
        $this->appendBody("\n\n");
        $this->appendBody($this->receiver->record());
        $this->appendBody("\n\n");
        $this->appendBody($this->createPermanentLink());
        $this->getMail()->appendInstallationSignature(true);
        $this->sendMail(array($this->receiver->id()));
    }
}
