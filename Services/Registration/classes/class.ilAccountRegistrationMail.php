<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAccountRegistrationMail
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilAccountRegistrationMail extends \ilMimeMailNotification
{
    const MODE_DIRECT_REGISTRATION = 1;
    const MODE_REGISTRATION_WITH_EMAIL_CONFIRMATION = 2;

    /** @var ilRegistrationSettings */
    private $settings;

    /** @var ilLanguage */
    private $lng;

    /** @var ilLogger */
    private $logger;

    /** @var int */
    private $mode = self::MODE_DIRECT_REGISTRATION;

    /**
     * ilAccountRegistrationMail constructor.
     * @param ilRegistrationSettings $settings
     * @param ilLanguage             $lng
     * @param ilLogger               $logger
     */
    public function __construct(ilRegistrationSettings $settings, ilLanguage $lng, ilLogger $logger)
    {
        $this->settings = $settings;
        $this->lng = $lng;
        $this->logger = $logger;

        parent::__construct(false);
    }

    /**
     * @return int
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @return \ilAccountRegistrationMail
     */
    public function withDirectRegistrationMode()
    {
        $clone = clone $this;
        $clone->mode = self::MODE_DIRECT_REGISTRATION;

        return $clone;
    }

    /**
     * @return \ilAccountRegistrationMail
     */
    public function withEmailConfirmationRegistrationMode()
    {
        $clone = clone $this;
        $clone->mode = self::MODE_REGISTRATION_WITH_EMAIL_CONFIRMATION;

        return $clone;
    }

    /**
     * @param $mailData
     * @return bool
     */
    private function isEmptyMailConfigurationData($mailData)
    {
        return !(
            isset($mailData['body']) &&
            is_string($mailData['body']) &&
            $mailData['body'] !== '' &&
            isset($mailData['subject']) &&
            is_string($mailData['subject']) &&
            $mailData['subject'] !== ''
        );
    }

    /**
     * @param ilObjUser $user
     * @param string    $rawPassword
     * @return bool
     */
    private function trySendingUserDefinedAccountMail(ilObjUser $user, $rawPassword)
    {
        $trimStrings = function ($value) {
            if (is_string($value)) {
                $value = trim($value);
            }

            return $value;
        };

        $this->logger->debug(sprintf(
            "Trying to send configurable email dependent welcome email to user %s (id: %s|language: %s) ...",
            $user->getLogin(),
            $user->getId(),
            $user->getLanguage()
        ));

        $mailData = ilObjUserFolder::_lookupNewAccountMail($user->getLanguage());
        if (!is_array($mailData)) {
            $this->logger->debug(sprintf(
                "Did not find any email configuration for language '%s' at all, skipping attempt ...",
                $user->getLanguage()
            ));
            return false;
        }

        $mailData = array_map($trimStrings, $mailData);

        if ($this->isEmptyMailConfigurationData($mailData)) {
            $this->logger->debug(sprintf(
                "Either subject or email missing, trying to determine email configuration via default language: %s",
                $this->language->getDefaultLanguage()
            ));

            $mailData = ilObjUserFolder::_lookupNewAccountMail($this->language->getDefaultLanguage());
            if (!is_array($mailData)) {
                $this->logger->debug(sprintf(
                    "Did not find any email configuration for language '%s' at all, skipping attempt ...",
                    $this->language->getDefaultLanguage()
                ));
                return false;
            }

            $mailData = array_map($trimStrings, $mailData);
            if ($this->isEmptyMailConfigurationData($mailData)) {
                $this->logger->debug(sprintf(
                    "Did not find any valid email configuration, skipping attempt ..."
                ));
                return false;
            }
        }

        $accountMail = new ilAccountMail();
        $accountMail->setUser($user);

        if ($this->settings->passwordGenerationEnabled()) {
            $accountMail->setUserPassword($rawPassword);
        }

        if (isset($mailData['att_file'])) {
            $fs = new ilFSStorageUserFolder(USER_FOLDER_ID);
            $fs->create();

            $pathToFile = '/' . implode('/', array_map(function ($pathPart) {
                return trim($pathPart, '/');
            }, [
                $fs->getAbsolutePath(),
                $mailData['lang'],
            ]));

            $accountMail->addAttachment($pathToFile, $mailData['att_file']);

            $this->logger->debug(sprintf(
                "Attaching '%s' as '%s' ...",
                $pathToFile,
                $mailData['att_file']
            ));
        } else {
            $this->logger->debug(sprintf(
                "Not attachments configured for this email configuration ..."
            ));
        }

        $accountMail->send();

        $this->logger->debug(sprintf(
            "Welcome email sent"
        ));

        return true;
    }

    /**
     * @param ilObjUser $user
     * @param string    $rawPassword
     * @param bool      $usedRegistrationCode
     */
    private function sendLanguageVariableBasedAccountMail(ilObjUser $user, $rawPassword, $usedRegistrationCode)
    {
        $this->logger->debug(sprintf(
            "Sending language variable dependent welcome email to user %s (id: %s|language: %s) as fallback ...",
            $user->getLogin(),
            $user->getId(),
            $user->getLanguage()
        ));

        $this->initMimeMail();

        $this->initLanguageByIso2Code($user->getLanguage());

        $this->setSubject($this->language->txt('reg_mail_subject'));

        $this->setBody($this->language->txt('reg_mail_body_salutation') . ' ' . $user->getFullname() . ',');
        $this->appendBody("\n\n");
        $this->appendBody($this->language->txt('reg_mail_body_text1'));
        $this->appendBody("\n\n");
        $this->appendBody($this->language->txt('reg_mail_body_text2'));
        $this->appendBody("\n");
        $this->appendBody(ILIAS_HTTP_PATH . '/login.php?client_id=' . CLIENT_ID);
        $this->appendBody("\n");
        $this->appendBody($this->language->txt('login') . ': ' . $user->getLogin());
        $this->appendBody("\n");

        if ($this->settings->passwordGenerationEnabled()) {
            $this->appendBody($this->language->txt('passwd') . ': ' . $rawPassword);
            $this->appendBody("\n");
        }

        if ($this->getMode() === self::MODE_DIRECT_REGISTRATION) {
            if ($this->settings->getRegistrationType() == IL_REG_APPROVE && !$usedRegistrationCode) {
                $this->appendBody("\n");
                $this->appendBody($this->language->txt('reg_mail_body_pwd_generation'));
                $this->appendBody("\n\n");
            }
        } elseif ($this->getMode() === self::MODE_REGISTRATION_WITH_EMAIL_CONFIRMATION) {
            $this->appendBody("\n");
            $this->appendBody($this->language->txt('reg_mail_body_forgot_password_info'));
            $this->appendBody("\n\n");
        }

        $this->appendBody($this->language->txt('reg_mail_body_text3'));
        $this->appendBody("\n");
        $this->appendBody($user->getProfileAsString($this->language));
        $this->appendBody(ilMail::_getInstallationSignature());

        $this->sendMimeMail($user->getEmail());

        $this->logger->debug(sprintf(
            "Welcome email sent"
        ));
    }

    /**
     * @param ilObjUser $user
     * @param string    $rawPassword
     * @param bool      $usedRegistrationCode
     */
    public function send(ilObjUser $user, $rawPassword = '', $usedRegistrationCode = false)
    {
        if (!$this->trySendingUserDefinedAccountMail($user, $rawPassword)) {
            $this->sendLanguageVariableBasedAccountMail($user, $rawPassword, $usedRegistrationCode);
        }
    }
}
