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

declare(strict_types=1);

/**
 * Send mails to users (usually triggered by cron)
 */
class ilPRGMail
{
    protected const LANGMODULE = 'prg';

    /**
     * var <string, ilLanguage> $languages
     */
    protected array $languages;

    public function __construct(
        protected ilComponentLogger $log,
        ilLanguage $lng
    ) {
        $lng->loadLanguageModule(self::LANGMODULE);
        $lng->loadLanguageModule("mail");
        $this->languages[$lng->getLangKey()] = $lng;
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

    protected function getUserLanguage(int $usr_id): string
    {
        return \ilObjUser::_getPreferences($usr_id)['language'];
    }

    protected function txt(string $identifier, string $lang): string
    {
        if(!array_key_exists($lang, $this->languages)) {
            $lng = new \ilLanguage($lang);
            $lng->loadLanguageModule(self::LANGMODULE);
            $lng->loadLanguageModule("mail");
            $this->languages[$lang] = $lng;
        }
        $lng = $this->languages[$lang];
        return $lng->txtlng(self::LANGMODULE, $identifier, $lang);
    }

    protected function sendMail(
        ilObjStudyProgramme $prg,
        ilPRGAssignment $assignment,
        string $subject,
        string $body_template
    ): bool {
        $user_info = $assignment->getUserInformation();
        $gender = $user_info->getGender() ?: 'anonymous';
        $name = implode(' ', [$user_info->getFirstname(), $user_info->getLastname()]);
        $login = $user_info->getLogin();
        $prg_link = \ilLink::_getStaticLink(ilObjStudyProgramme::getRefIdFor($assignment->getRootId()), 'prg');

        $lang = $this->getUserLanguage($assignment->getUserId());
        $salutation = $this->txt("mail_salutation_" . $gender, $lang);
        $subject = $this->txt($subject, $lang);
        $body_template = $this->txt($body_template, $lang);

        $body = sprintf(
            $body_template,
            $salutation,
            $name,
            $prg->getTitle()
        )
        . '<br /><br />' . $prg_link;

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

        $subject = "info_to_re_assign_mail_subject";
        $body_template = "info_to_re_assign_mail_body";
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

        $lang = $this->getUserLanguage($ass->getUserId());
        $subject = "risky_to_fail_mail_subject";
        $body_template = "risky_to_fail_mail_body";
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

        $lang = $this->getUserLanguage($ass->getUserId());
        $subject = "re_assigned_mail_subject";
        $body_template = "re_assigned_mail_body";
        $sent = $this->sendMail($prg, $ass, $subject, $body_template);

        return $sent;
    }
}
