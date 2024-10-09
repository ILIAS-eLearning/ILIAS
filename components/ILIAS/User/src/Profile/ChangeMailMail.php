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

namespace ILIAS\User\Profile;

use ILIAS\Language\Language;
use ILIAS\Data\URI;

class ChangeMailMail extends \ilMimeMailNotification
{
    public function __construct(
        private readonly \ilObjUser $user,
        private readonly URI $uri,
        private readonly Language $lng,
        private \ilLogger $logger
    ) {
        $lng->loadLanguageModule('mail');
        parent::__construct(false);
    }

    public function send(string $new_email, int $validity): void
    {
        $this->sendEmailToNewEmailAddress($new_email, $validity);
        $this->sendEmailToExistingAddress($new_email, $validity);
    }

    private function sendEmailToNewEmailAddress(string $new_email, int $validity): void
    {
        $this->initMimeMail();
        $this->initLanguageByIso2Code($this->user->getLanguage());
        $this->setSubject($this->lng->txt('change_email_email_confirmation_subject'));
        $this->setBody($this->lng->txt('mail_salutation_n') . ' ' . $this->user->getFullname() . ',');
        $this->appendBody("\n\n");
        $this->appendBody(
            sprintf(
                $this->lng->txt('change_email_email_confirmation_body'),
                $this->user->getLogin(),
                $this->uri->__toString(),
                floor($validity / 60)
            )
        );
        $this->appendBody(\ilMail::_getInstallationSignature());

        $this->sendMimeMail($new_email);
        $this->logger->debug(
            sprintf(
                'Email to confirm email change sent to user %s (id: %s|language: %s).',
                $this->user->getLogin(),
                $this->user->getId(),
                $this->user->getLanguage()
            )
        );
    }

    private function sendEmailToExistingAddress(string $new_email, int $validity): void
    {
        if (!$this->user->getEmail()) {
            $this->logger->debug(
                sprintf(
                    'Missing email address, did not send email to inform about email change to user %s (id: %s)',
                    $this->user->getLogin(),
                    $this->user->getId()
                )
            );
            return;
        }

        $this->initMimeMail();
        $this->initLanguageByIso2Code($this->user->getLanguage());
        $this->setSubject($this->lng->txt('change_email_email_information_subject'));
        $this->setBody($this->lng->txt('mail_salutation_n') . ' ' . $this->user->getFullname() . ',');
        $this->appendBody("\n\n");
        $this->appendBody(
            sprintf(
                $this->lng->txt('change_email_email_information_body'),
                $this->user->getLogin(),
                $new_email,
                floor($validity / 60)
            )
        );
        $this->appendBody(\ilMail::_getInstallationSignature());

        $this->sendMimeMail($this->user->getEmail());
        $this->logger->debug(
            sprintf(
                'Email to inform about email change sent to user %s (id: %s|language: %s).',
                $this->user->getLogin(),
                $this->user->getId(),
                $this->user->getLanguage()
            )
        );

    }
}
