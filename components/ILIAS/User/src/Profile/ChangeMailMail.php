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

    public function send(): bool
    {
        if (!$this->user->getEmail()) {
            $this->logger->debug(
                sprintf(
                    'Missing email address, did not send email to confirm email change to user %s (id: %s)',
                    $this->user->getLogin(),
                    $this->user->getId()
                )
            );
            return false;
        }

        $this->initMimeMail();
        $this->initLanguageByIso2Code($this->user->getLanguage());

        $this->setSubject($this->lng->txt('change_email_email_subject'));
        $this->setBody($this->lng->txt('mail_salutation_n') . ' ' . $this->user->getFullname() . ',');
        $this->appendBody("\n\n");
        $this->appendBody(
            sprintf(
                $this->lng->txt('change_email_email_body'),
                $this->user->getLogin(),
                $this->uri->__toString()
            )
        );
        $this->appendBody(\ilMail::_getInstallationSignature());

        $this->sendMimeMail($this->user->getEmail());
        $this->logger->debug(
            sprintf(
                'Email to confirm email change sent to user %s (id: %s|language: %s).',
                $this->user->getLogin(),
                $this->user->getId(),
                $this->user->getLanguage()
            )
        );
        return true;
    }
}
