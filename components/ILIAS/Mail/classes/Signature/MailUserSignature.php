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

namespace ILIAS\Mail\Signature;

use ilSetting;
use ILIAS\Mail\Placeholder\MailSignatureIliasUrlPlaceholder;
use ILIAS\Mail\Placeholder\MailSignatureInstallationNamePlaceholder;
use ILIAS\Mail\Placeholder\MailSignatureInstallationDescriptionPlaceholder;
use ILIAS\Mail\Placeholder\MailSignatureUserLoginPlaceholder;
use ILIAS\Mail\Placeholder\MailSignatureUserFullnamePlaceholder;
use ILIAS\Mail\Placeholder\Placeholder;

class MailUserSignature implements Signature
{
    public const MAIL_USER_SIGNATURE = 'mail_system_usr_general_signature';

    public function __construct(
        private readonly ilSetting $settings,
    ) {
    }

    public function getSignature(): string
    {
        return $this->settings->get($this->getPersistenceIdentifier(), '');
    }

    public function getPersistenceIdentifier(): string
    {
        return self::MAIL_USER_SIGNATURE;
    }

    public function supports(Placeholder $placeholder): bool
    {
        return match ($placeholder::class) {
            MailSignatureIliasUrlPlaceholder::class,
            MailSignatureInstallationNamePlaceholder::class,
            MailSignatureInstallationDescriptionPlaceholder::class,
            MailSignatureUserLoginPlaceholder::class,
            MailSignatureUserFullnamePlaceholder::class,
            => true,
            default => false,
        };
    }
}
