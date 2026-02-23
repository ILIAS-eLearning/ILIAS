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

use ILIAS\User\Settings\NewAccountMail\Repository as NewAccountMailRepository;

/**
 * Class ilAccountRegistrationMail
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilAccountRegistrationMail extends ilMimeMailNotification
{
    protected const MODE_DIRECT_REGISTRATION = 1;
    protected const MODE_REGISTRATION_WITH_EMAIL_CONFIRMATION = 2;

    private int $mode = self::MODE_DIRECT_REGISTRATION;
    private ?string $permanent_link_target = null;

    public function __construct(
        private readonly ilRegistrationSettings $settings,
        private readonly ilLogger $logger,
        private readonly NewAccountMailRepository $account_mail_repository
    ) {
        parent::__construct(false);
    }

    public function getMode(): int
    {
        return $this->mode;
    }

    public function withPermanentLinkTarget(string $permanent_link_target): self
    {
        if ($permanent_link_target === '') {
            throw new InvalidArgumentException(
                'Permanent link target must not be empty'
            );
        }

        $clone = clone $this;
        $clone->permanent_link_target = $permanent_link_target;
        return $clone;
    }

    public function withDirectRegistrationMode(): ilAccountRegistrationMail
    {
        $clone = clone $this;
        $clone->mode = self::MODE_DIRECT_REGISTRATION;
        return $clone;
    }

    public function withEmailConfirmationRegistrationMode(): ilAccountRegistrationMail
    {
        $clone = clone $this;
        $clone->mode = self::MODE_REGISTRATION_WITH_EMAIL_CONFIRMATION;
        return $clone;
    }

    private function isEmptyMailConfigurationData(array $mailData): bool
    {
        return !(
            isset($mailData['body'], $mailData['subject']) &&
            is_string($mailData['body']) &&
            $mailData['body'] !== '' &&
            is_string($mailData['subject']) &&
            $mailData['subject'] !== ''
        );
    }

    private function trySendingUserDefinedAccountMail(ilObjUser $user, string $rawPassword): bool
    {
        $this->logger->debug(sprintf(
            'Trying to send configurable email dependent welcome email to user %s (id: %s|language: %s) ...',
            $user->getLogin(),
            $user->getId(),
            $user->getLanguage()
        ));

        $mailData = $this->account_mail_repository->getFor($user->getLanguage());

        if ($mailData->getBody() === '' && $mailData->getSubject() === '') {
            $this->logger->debug(sprintf(
                'Either subject or email missing, trying to determine email configuration via default language: %s',
                $this->language->getDefaultLanguage()
            ));

            $mailData = $this->account_mail_repository->getFor($this->language->getDefaultLanguage());

            if ($mailData->getBody() === '' && $mailData->getSubject() === '') {
                $this->logger->debug('Did not find any valid email configuration, skipping attempt ...');
                return false;
            }
        }

        $accountMail = new ilAccountMail();
        $accountMail->setUser($user);
        $accountMail->setPermanentLinkTarget($this->permanent_link_target);

        if ($this->settings->passwordGenerationEnabled()) {
            $accountMail->setUserPassword($rawPassword);
        }

        $accountMail->send();

        $this->logger->debug('Welcome email sent');

        return true;
    }

    private function sendLanguageVariableBasedAccountMail(
        ilObjUser $user,
        string $rawPassword,
        bool $usedRegistrationCode
    ): void {
        if (!$user->getEmail()) {
            $this->logger->debug(sprintf(
                'Missing email address, did not send account registration mail for user %s (id: %s) ...',
                $user->getLogin(),
                $user->getId()
            ));
            return;
        }

        $this->logger->debug(sprintf(
            'Sending language variable dependent welcome email to user %s (id: %s|language: %s) as fallback ...',
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
            if ($this->settings->getRegistrationType() === ilRegistrationSettings::IL_REG_APPROVE && !$usedRegistrationCode) {
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

        $this->logger->debug('Welcome email sent');
    }

    public function send(ilObjUser $user, string $rawPassword = '', bool $usedRegistrationCode = false): void
    {
        if (!$this->trySendingUserDefinedAccountMail($user, $rawPassword)) {
            $this->sendLanguageVariableBasedAccountMail($user, $rawPassword, $usedRegistrationCode);
        }
    }
}
