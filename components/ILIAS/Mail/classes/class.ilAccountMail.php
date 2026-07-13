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

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\User\Settings\NewAccountMail\Repository as NewAccountMailRepository;
use ILIAS\User\Settings\NewAccountMail\Mail as NewAccountMail;
use ILIAS\ResourceStorage\Services as ResourceStorage;

class ilAccountMail
{
    private readonly GlobalHttpState $http;
    private readonly ilSetting $settings;
    private readonly Refinery $refinery;
    private readonly ilTree $repository_tree;
    private readonly ilMailMimeSenderFactory $sender_factory;
    public string $u_password = '';
    public ?ilObjUser $user = null;
    private bool $lang_variables_as_fallback = false;
    private readonly ResourceStorage $irss;
    private readonly NewAccountMailRepository $account_mail_repo;
    private array $amail = [];
    private ?string $permanent_link_target = null;

    public function __construct()
    {
        global $DIC;
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->settings = $DIC->settings();
        $this->repository_tree = $DIC->repositoryTree();
        $this->sender_factory = $DIC->mail()->mime()->senderFactory();
        $this->irss = $DIC->resourceStorage();
        $this->account_mail_repo = new NewAccountMailRepository($DIC->database());
    }

    public function useLangVariablesAsFallback(bool $a_status): void
    {
        $this->lang_variables_as_fallback = $a_status;
    }

    public function areLangVariablesUsedAsFallback(): bool
    {
        return $this->lang_variables_as_fallback;
    }

    public function setUserPassword(string $a_pwd): void
    {
        $this->u_password = $a_pwd;
    }

    public function getUserPassword(): string
    {
        return $this->u_password;
    }

    public function setUser(ilObjUser $a_user): void
    {
        $this->user = $a_user;
    }

    public function setPermanentLinkTarget(?string $permanent_link_target): void
    {
        if ($permanent_link_target === '') {
            throw new InvalidArgumentException(
                'Permanent link target must not be empty'
            );
        }

        $this->permanent_link_target = $permanent_link_target;
    }

    public function getUser(): ?ilObjUser
    {
        return $this->user;
    }

    public function reset(): void
    {
        $this->user = null;
        $this->u_password = '';
        $this->permanent_link_target = null;
    }

    /**
     * @return array{lang?: string, subject?: string, body?: string, sal_f?: string, sal_g?: string, sal_m?: string, type?: string}
     */
    private function readAccountMail(string $a_lang): NewAccountMail
    {
        if (!isset($this->amail[$a_lang]) || !($this->amail[$a_lang] instanceof NewAccountMail)) {
            $this->amail[$a_lang] = $this->account_mail_repo->getFor($a_lang);
        }

        return $this->amail[$a_lang];
    }

    /**
     * Sends the mail with its object properties as MimeMail
     * It first tries to read the mail body, subject and sender address from posted named formular fields.
     * If no field values found the defaults are used.
     * Placehoders will be replaced by the appropriate data.
     */
    public function send(): bool
    {
        $user = $this->getUser();
        if (!$user instanceof ilObjUser) {
            throw new RuntimeException('A user instance must be passed when sending emails');
        }

        if (!$user->getEmail() === '') {
            return false;
        }

        // determine language and get account mail data
        // fall back to default language if acccount mail data is not given for user language.
        $amail = $this->readAccountMail($user->getLanguage());
        $lang = $user->getLanguage();
        if ($amail->getBody() === '' || $amail->getSubject() === '') {
            $fallback_language = 'en';
            $amail = $this->readAccountMail($this->settings->get('language', $fallback_language));
            $lang = $this->settings->get('language', $fallback_language);
        }

        $mmail = new ilMimeMail();

        // fallback if mail data is still not given
        if (($amail->getBody() === '' || $amail->getSubject() === '') && $this->areLangVariablesUsedAsFallback()) {
            $lang = $user->getLanguage();
            $tmp_lang = new ilLanguage($lang);

            $mail_subject = $tmp_lang->txt('reg_mail_subject');

            $timelimit = '';
            if (!$user->checkTimeLimit()) {
                $tmp_lang->loadLanguageModule('registration');

                // #6098
                $timelimit_from = new ilDateTime($user->getTimeLimitFrom(), IL_CAL_UNIX);
                $timelimit_until = new ilDateTime($user->getTimeLimitUntil(), IL_CAL_UNIX);
                $timelimit = ilDatePresentation::formatPeriod($timelimit_from, $timelimit_until);
                $timelimit = "\n" . sprintf($tmp_lang->txt('reg_mail_body_timelimit'), $timelimit) . "\n\n";
            }

            // mail body
            $mail_body = $tmp_lang->txt('reg_mail_body_salutation') . ' ' . $user->getFullname() . ",\n\n" .
                $tmp_lang->txt('reg_mail_body_text1') . "\n\n" .
                $tmp_lang->txt('reg_mail_body_text2') . "\n" .
                ILIAS_HTTP_PATH . '/login.php?client_id=' . CLIENT_ID . "\n";
            $mail_body .= $tmp_lang->txt('login') . ': ' . $user->getLogin() . "\n";
            $mail_body .= $tmp_lang->txt('passwd') . ': ' . $this->u_password . "\n";
            $mail_body .= "\n" . $timelimit;
            $mail_body .= $tmp_lang->txt('reg_mail_body_text3') . "\n\r";
            $mail_body .= $user->getProfileAsString($tmp_lang);
        } else {
            $attachment = $amail->getAttachment($this->irss);
            if ($attachment !== null) {
                $mmail->Attach($attachment->getPath(), '', 'attachment', $attachment->getFilename());
            }

            // replace placeholders
            $mail_subject = $this->replacePlaceholders($amail->getSubject(), $user, $amail, $lang);
            $mail_body = $this->replacePlaceholders($amail->getBody(), $user, $amail, $lang);
        }

        $mmail->From($this->sender_factory->system());
        $mmail->Subject($mail_subject, true);
        $mmail->To($user->getEmail());
        $mmail->Body($mail_body);

        $mmail->Send();

        return true;
    }

    public function replacePlaceholders(string $a_string, ilObjUser $a_user, NewAccountMail $a_amail, string $a_lang): string
    {
        global $DIC;
        $settings = $DIC->settings();
        $template_engine_factory = $DIC->mail()->templateEngineFactory();

        $replacements = [];

        // determine salutation
        $replacements['MAIL_SALUTATION'] = $template_engine_factory->getBasicEngine()->render(
            match ($a_user->getGender()) {
                'f' => trim($a_amail->getSalutationFemale()),
                'm' => trim($a_amail->getSalutationMale()),
                default => trim($a_amail->getSalutationNoneSpecific()),
            },
            [
                'FIRST_NAME' => $a_user->getFirstname(),
                'LAST_NAME' => $a_user->getLastname(),
                'LOGIN' => $a_user->getLogin(),
            ]
        );
        $replacements['LOGIN'] = $a_user->getLogin();
        $replacements['FIRST_NAME'] = $a_user->getFirstname();
        $replacements['LAST_NAME'] = $a_user->getLastname();
        // BEGIN Mail Include E-Mail Address in account mail
        $replacements['EMAIL'] = $a_user->getEmail();
        // END Mail Include E-Mail Address in account mail
        $replacements['PASSWORD'] = $this->getUserPassword();
        $replacements['ILIAS_URL'] = ILIAS_HTTP_PATH . '/login.php?client_id=' . CLIENT_ID;
        $replacements['CLIENT_NAME'] = CLIENT_NAME;
        $replacements['ADMIN_MAIL'] = $this->settings->get('admin_email');
        $replacements['IF_PASSWORD'] = $this->getUserPassword() !== '';
        $replacements['IF_NO_PASSWORD'] = $this->getUserPassword() === '';

        // #13346
        if (!$a_user->getTimeLimitUnlimited()) {
            // #6098
            $replacements['IF_TIMELIMIT'] = !$a_user->getTimeLimitUnlimited();
            $timelimit_from = new ilDateTime($a_user->getTimeLimitFrom(), IL_CAL_UNIX);
            $timelimit_until = new ilDateTime($a_user->getTimeLimitUntil(), IL_CAL_UNIX);
            $timelimit = ilDatePresentation::formatPeriod($timelimit_from, $timelimit_until);
            $replacements['TIMELIMIT'] = $timelimit;
        }

        // target
        $replacements['IF_TARGET'] = false;
        if ($this->permanent_link_target !== null) {
            $tarr = explode('_', $this->permanent_link_target);
            if ($this->repository_tree->isInTree((int) $tarr[1])) {
                $obj_id = ilObject::_lookupObjId((int) $tarr[1]);
                $type = ilObject::_lookupType($obj_id);
                if ($type === $tarr[0]) {
                    $replacements['TARGET_TITLE'] = ilObject::_lookupTitle($obj_id);
                    $replacements['TARGET'] = ILIAS_HTTP_PATH . '/goto.php?client_id=' . CLIENT_ID . '&target=' . $this->permanent_link_target;

                    // this looks complicated, but we may have no initilised $lng object here
                    // if mail is send during user creation in authentication
                    $replacements['TARGET_TYPE'] = ilLanguage::_lookupEntry($a_lang, 'common', 'obj_' . $tarr[0]);
                    $replacements['IF_TARGET'] = true;
                }
            }
        }

        return $template_engine_factory->getBasicEngine()->render($a_string, $replacements);
    }
}
