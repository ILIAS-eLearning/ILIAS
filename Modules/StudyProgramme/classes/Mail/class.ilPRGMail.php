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
 * Send mails to users (usually triggered by cron)
 */
class ilPRGMail
{
    protected ilComponentLogger $log;
    protected ilLanguage $lng;

    public function __construct(
        ilComponentLogger $log,
        ilLanguage $lng
    ) {
        $this->log = $log;
        $this->lng = $lng;
        $this->lng->loadLanguageModule("prg");
        $this->lng->loadLanguageModule("mail");
    }

    /**
     * @return array [ilPRGAssignment, ilObjStudyProgramme]
     */
    protected function getAssignmentAndProgramme(int $assignment_id, int $root_prg_id): array
    {
        $prg = ilObjStudyProgramme::getInstanceByObjId($root_prg_id);
        $ass = $prg->getSpecificAssignment($assignment_id);
        return [$ass, $prg];
    }

    protected function sendMail(
        ilObjStudyProgramme $prg,
        ilPRGAssignment $assignment,
        string $subject,
        string $body_template
    ): bool {
        $user_info = $assignment->getUserInformation();
        $gender = $user_info->getGender();
        $name = $user_info->getFullname();
        $login = $user_info->getLogin();

        $body = sprintf(
            $body_template,
            $this->lng->txt("mail_salutation_" . $gender),
            $name,
            $prg->getTitle()
        );

        $mail = new ilMail(ANONYMOUS_USER_ID);
        try {
            $mail->enqueue($login, '', '', $subject, $body, []);
            return true;
        } catch (Exception $e) {
            $this->log->write($e->getMessage());
            return false;
        }
    }

    public function sendInformToReAssignMail(int $assignment_id, int $root_prg_id): void
    {
        list($ass, $prg) = $this->getAssignmentAndProgramme($assignment_id, $root_prg_id);

        if (! $prg->getSettings()->getAutoMailSettings()->getReminderNotRestartedByUserDays() > 0) {
            $this->log->write("Send info to re-assign mail is deactivated in study programme settings");
            return;
        }

        $subject = $this->lng->txt("info_to_re_assign_mail_subject");
        $body_template = $this->lng->txt("info_to_re_assign_mail_body");
        $sent = $this->sendMail($prg, $ass, $subject, $body_template);

        if ($sent) {
            $prg->storeExpiryInfoSentFor($ass);
        }
    }

    public function resetExpiryInfoSentFor(int $assignment_id, int $root_prg_id): void
    {
        list($ass, $prg) = $this->getAssignmentAndProgramme($assignment_id, $root_prg_id);
        $now = new \DateTimeImmutable();
        $vq = $ass->getProgressTree()->getValidityOfQualification();

        if ($vq && $vq > $now) {
            $prg->resetExpiryInfoSentFor($ass);
        }
    }

    public function sendRiskyToFailMail(int $assignment_id, int $root_prg_id): void
    {
        list($ass, $prg) = $this->getAssignmentAndProgramme($assignment_id, $root_prg_id);

        if (! $prg->getSettings()->getAutoMailSettings()->getProcessingEndsNotSuccessfulDays() > 0) {
            $this->log->write("Send risky to fail mail is deactivated in study programme settings");
            return;
        }

        $subject = $this->lng->txt("risky_to_fail_mail_subject");
        $body_template = $this->lng->txt("risky_to_fail_mail_body");
        $sent = $this->sendMail($prg, $ass, $subject, $body_template);

        if ($sent) {
            $prg->storeRiskyToFailSentFor($ass);
        }
    }

    public function resetRiskyToFailSentFor(int $assignment_id, int $root_prg_id): void
    {
        list($ass, $prg) = $this->getAssignmentAndProgramme($assignment_id, $root_prg_id);
        $now = new \DateTimeImmutable();
        $deadline = $ass->getProgressTree()->getDeadline();
        if ($deadline && $deadline > $now) {
            $prg->resetRiskyToFailSentFor($ass);
        }
    }

    public function sendReAssignedMail(int $assignment_id, int $root_prg_id): bool
    {
        list($ass, $prg) = $this->getAssignmentAndProgramme($assignment_id, $root_prg_id);

        if (! $prg->getSettings()->getAutoMailSettings()->getSendReAssignedMail()) {
            $this->log->write("Send re assign mail is deactivated in study programme settings");
            return false;
        }

        $subject = $this->lng->txt("re_assigned_mail_subject");
        $body_template = $this->lng->txt("re_assigned_mail_body");
        $sent = $this->sendMail($prg, $ass, $subject, $body_template);

        return $sent;
    }
}
