<?php

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
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExerciseMailNotification extends ilMailNotification
{
    public const TYPE_FEEDBACK_FILE_ADDED = 20;
    public const TYPE_SUBMISSION_UPLOAD = 30;
    public const TYPE_FEEDBACK_TEXT_ADDED = 40;
    public const TYPE_GRADING_DONE = 70;
    protected \ILIAS\Exercise\InternalDomainService $domain;
    public const TYPE_MESSAGE_FROM_PF_GIVER = 50;
    public const TYPE_MESSAGE_FROM_PF_RECIPIENT = 60;
    public const TYPE_DEADLINE_REQUESTED = 70;
    public const TYPE_IDL_DEADLINE_SET = 80;
    protected string $additional_text = "";
    protected int $peer_id = 0;
    protected \ILIAS\Exercise\PermanentLink\PermanentLinkManager $permanent_link;

    protected ilObjUser $user;
    protected int $ass_id;

    public function __construct()
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->domain = $DIC->exercise()->internal()->domain();
        $this->permanent_link = $DIC->exercise()->internal()->gui()->permanentLink();
        parent::__construct();
    }

    public function setAssignmentId(int $a_val): void
    {
        $this->ass_id = $a_val;
    }

    public function getAssignmentId(): int
    {
        return $this->ass_id;
    }

    protected function addOpenSubmission(): void
    {
        $ass = new ilExAssignment($this->getAssignmentId());
        $types = ilExAssignmentTypes::getInstance();
        $type = $types->getById($ass->getType());
        if ($type->supportsWebDirAccess()) {
            $submission = new ilExSubmission($ass, $this->user->getId());
            if ($submission->hasSubmittedPrintVersion()) {
                $this->appendBody("\n\n");
                $this->appendBody(sprintf(
                    $this->getLanguageText('exc_submission_open_notification_link'),
                    $this->permanent_link->getOpenSubmissionsLink($this->ref_id, $this->getAssignmentId(), $this->user->getId())
                ));
            }
        }
    }

    public function setPeerId(int $a_val): void
    {
        $this->peer_id = $a_val;
    }

    public function getPeerId(): int
    {
        return $this->peer_id;
    }

    public function setAdditionalText(string $a_val): void
    {
        $this->additional_text = $a_val;
    }

    public function getAdditionalText(): string
    {
        return $this->additional_text;
    }

    public function send(): bool
    {
        $ilUser = $this->user;
        $perma = $this->permanent_link;
        // parent::send();

        switch ($this->getType()) {
            case self::TYPE_FEEDBACK_FILE_ADDED:

                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(
                        sprintf(
                            $this->getLanguageText('exc_msg_new_feedback_file_uploaded'),
                            $this->getObjectTitle(true)
                        )
                    );
                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        $this->getLanguageText('exc_msg_new_feedback_file_uploaded2')
                    );
                    $this->appendBody("\n");
                    $this->appendBody(
                        $this->getLanguageText('obj_exc') . ": " . $this->getObjectTitle(true)
                    );
                    $this->appendBody("\n");
                    $this->appendBody(
                        $this->getLanguageText('exc_assignment') . ": " .
                        ilExAssignment::lookupTitle($this->getAssignmentId())
                    );
                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguageText('exc_mail_permanent_link'));
                    $this->appendBody("\n");
                    $this->appendBody($this->permanent_link->getPermanentLink($this->ref_id, $this->getAssignmentId()));
                    $this->getMail()->appendInstallationSignature(true);

                    $this->sendMail(array($rcp));
                }
                break;

            case self::TYPE_SUBMISSION_UPLOAD:

                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(
                        sprintf(
                            $this->getLanguageText('exc_submission_notification_subject'),
                            $this->getObjectTitle(true)
                        )
                    );
                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        sprintf($this->getLanguageText('exc_submission_notification_body'), $this->getObjectTitle(true))
                    );
                    $this->appendBody("\n");
                    $this->appendBody(
                        $this->getLanguageText('exc_assignment') . ": " .
                        ilExAssignment::lookupTitle($this->getAssignmentId())
                    );
                    $this->appendBody("\n");
                    $this->appendBody(
                        $this->getLanguageText('user') . ": " .
                        $ilUser->getFullname()
                    );
                    $this->appendBody("\n\n");
                    $this->appendBody(sprintf(
                        $this->getLanguageText('exc_submission_notification_link'),
                        $this->permanent_link->getPermanentLink($this->ref_id, $this->getAssignmentId())
                    ));

                    if (ilExAssignment::lookupType($this->getAssignmentId()) == ilExAssignment::TYPE_UPLOAD) {
                        $this->appendBody("\n\n");

                        //new files uploaded
                        //$assignment = new ilExAssignment($this->getAssignmentId());
                        //$submission = new ilExSubmission($assignment, $ilUser->getId());

                        // since mails are sent immediately after upload the files should always be new
                        //if($submission->lookupNewFiles($submission->getTutor()))
                        //{
                        $this->appendBody(sprintf(
                            $this->getLanguageText('exc_submission_downloads_notification_link'),
                            $this->permanent_link->getDownloadSubmissionLink($this->ref_id, $this->getAssignmentId(), $ilUser->getId())
                        ));
                        //}
                        //else
                        //{
                        //	$this->appendBody(sprintf($this->getLanguageText('exc_submission_downloads_notification_link'),
                        //		$this->getLanguageText("exc_submission_no_new_files")));
                        //}
                    }
                    $this->addOpenSubmission();

                    $this->appendBody("\n\n");
                    $this->appendBody(sprintf(
                        $this->getLanguageText('exc_submission_and_grades_notification_link'),
                        $this->permanent_link->getGradesLink($this->ref_id, $this->getAssignmentId())
                    ));

                    $this->getMail()->appendInstallationSignature(true);

                    $this->sendMail(array($rcp));
                }
                break;

            case self::TYPE_FEEDBACK_TEXT_ADDED:

                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(
                        sprintf(
                            $this->getLanguageText('exc_msg_new_feedback_text_uploaded'),
                            $this->getObjectTitle(true)
                        )
                    );
                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        $this->getLanguageText('exc_msg_new_feedback_text_uploaded2')
                    );
                    $this->appendBody("\n");
                    $this->appendBody(
                        $this->getLanguageText('obj_exc') . ": " . $this->getObjectTitle(true)
                    );
                    $this->appendBody("\n");
                    $this->appendBody(
                        $this->getLanguageText('exc_assignment') . ": " .
                        ilExAssignment::lookupTitle($this->getAssignmentId())
                    );
                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguageText('exc_mail_permanent_link'));
                    $this->appendBody("\n");
                    $this->appendBody($this->permanent_link->getPermanentLink($this->ref_id, $this->getAssignmentId()));
                    $this->getMail()->appendInstallationSignature(true);

                    $this->sendMail(array($rcp));
                }
                break;

            case self::TYPE_GRADING_DONE:

                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(
                        sprintf(
                            $this->getLanguageText('exc_msg_grading_done'),
                            $this->getObjectTitle(true)
                        )
                    );
                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        sprintf(
                            $this->getLanguageText('exc_msg_grading_done_body'),
                            $this->getObjectTitle(false)
                        )
                    );
                    $this->appendBody("\n");
                    $this->appendBody(
                        $this->getLanguageText('obj_exc') . ": " . $this->getObjectTitle(true)
                    );
                    $this->appendBody("\n");
                    $this->appendBody(
                        $this->getLanguageText('exc_assignment') . ": " .
                        ilExAssignment::lookupTitle($this->getAssignmentId())
                    );
                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguageText('exc_mail_permanent_link'));
                    $this->appendBody("\n");
                    $this->appendBody($this->permanent_link->getPermanentLink($this->ref_id, $this->getAssignmentId()));
                    $this->getMail()->appendInstallationSignature(true);

                    $this->sendMail(array($rcp));
                }
                break;

            case self::TYPE_MESSAGE_FROM_PF_GIVER:

                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->sendExerciseNotification(
                        $rcp,
                        sprintf(
                            $this->getLanguageText('exc_msg_new_message_from_pf_giver'),
                            $this->getObjectTitle(true)
                        ),
                        $this->getLanguageText('exc_msg_new_message_from_pf_giver2') .
                        "\n\n" . $this->getAdditionalText(),
                        $perma->getPermanentLink($this->ref_id, $this->getAssignmentId())
                    );
                }
                break;

            case self::TYPE_MESSAGE_FROM_PF_RECIPIENT:

                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->sendExerciseNotification(
                        $rcp,
                        sprintf(
                            $this->getLanguageText('exc_msg_new_message_from_pf_recipient'),
                            $this->getObjectTitle(true)
                        ),
                        $this->getLanguageText('exc_msg_new_message_from_pf_recipient2') .
                        "\n\n" . $this->getAdditionalText(),
                        $perma->getGivenFeedbackLink($this->ref_id, $this->getAssignmentId(), $this->getPeerId())
                    );
                }
                break;

            case self::TYPE_DEADLINE_REQUESTED:
                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->sendExerciseNotification(
                        $rcp,
                        sprintf(
                            $this->getLanguageText('exc_msg_deadline_request_subject'),
                            $this->getObjectTitle(true)
                        ),
                        $this->getLanguageText('exc_msg_deadline_request_body') .
                        "\n\n" . $this->getLanguageText('user') . ": " .
                            $ilUser->getFullname(),
                        $perma->getGradesLink($this->ref_id, $this->getAssignmentId())
                    );
                }
                break;

            case self::TYPE_IDL_DEADLINE_SET:
                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->sendExerciseNotification(
                        $rcp,
                        sprintf(
                            $this->getLanguageText('exc_msg_idl_set_subject'),
                            $this->getObjectTitle(true)
                        ),
                        $this->getLanguageText('exc_msg_idl_set_body'),
                        $perma->getPermanentLink($this->ref_id, $this->getAssignmentId())
                    );
                }
                break;
        }
        return true;
    }

    protected function sendExerciseNotification(
        int $rcp,
        string $subject,
        string $text,
        string $link = ""
    ): void {
        $this->initLanguage($rcp);
        $this->initMail();
        $this->setSubject($subject);
        $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
        $this->appendBody("\n\n" . $text);
        $this->appendBody(
            "\n\n" . $this->getLanguageText('obj_exc') . ": " . $this->getObjectTitle(true)
        );
        $this->appendBody("\n");
        if ($this->getAssignmentId() > 0) {
            $this->appendBody(
                $this->getLanguageText('exc_assignment') . ": " .
                ilExAssignment::lookupTitle($this->getAssignmentId())
            );
        }
        $this->appendBody("\n\n");
        $this->appendBody($this->getLanguageText('exc_mail_permanent_link'));
        $this->appendBody("\n");
        $this->appendBody($link);
        $this->getMail()->appendInstallationSignature(true);
        $this->sendMail(array($rcp));
    }

    /**
     * Add language module exc
     */
    protected function initLanguage(int $a_usr_id): void
    {
        parent::initLanguage($a_usr_id);
        $this->getLanguage()->loadLanguageModule('exc');
    }
}
