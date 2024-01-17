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

class MailUserSignature implements Signature
{
    public const MAIL_USER_SIGNATURE = 'mail_system_usr_general_signature';
    private ilSetting $settings;
    private MailSignatureIliasUrlPlaceholder $placeholder_chain;

    public function __construct(int $user_id)
    {
        global $DIC;
        $this->settings = $DIC->settings();
    }

    public function getSignature(): string
    {
        return $this->settings->get($this->getSettingsKeyword(), '');
    }

    public function getSettingsKeyword(): string
    {
        return self::MAIL_USER_SIGNATURE;
    }

    public function supports(Placeholder $placeholder): bool
    {
        return match (get_class($placeholder)) {
            MailSignatureIliasUrlPlaceholder::class,
            MailSignatureInstallationNamePlaceholder::class,
            MailSignatureInstallationDescriptionPlaceholder::class,
            MailSignatureUserNamePlaceholder::class,
            MailSignatureUserFullnamePlaceholder::class,
            => true,
            default => false,
        };
    }
}
