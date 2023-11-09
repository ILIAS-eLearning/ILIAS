<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Notifies user, using internal mail system.
 */
class ilIndividualAssessmentPrimitiveInternalNotificator extends ilMailNotification implements ilIndividualAssessmentNotificator
{
    public const OCCASION_FAILED = 0;
    public const OCCASION_COMPLETED = 1;

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
    ): ilIndividualAssessmentPrimitiveInternalNotificator {
        $clone = clone $this;
        $clone->receiver = $member;
        $clone->ref_id = $member->assessment()->getRefId();
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withOccasionFailed(): ilIndividualAssessmentPrimitiveInternalNotificator
    {
        $clone = clone $this;
        $clone->occasion = self::OCCASION_FAILED;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withOccasionCompleted(): ilIndividualAssessmentPrimitiveInternalNotificator
    {
        $clone = clone $this;
        $clone->occasion = self::OCCASION_COMPLETED;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function send(): void
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
