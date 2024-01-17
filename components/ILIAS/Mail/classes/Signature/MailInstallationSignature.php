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

class MailInstallationSignature implements Signature
{
    public const MAIL_INSTALLATION_SIGNATURE = 'mail_system_sys_general_signature';
    private ilSetting $settings;
    private MailSignatureIliasUrlPlaceholder $placeholder_chain;

    public function __construct()
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
        return self::MAIL_INSTALLATION_SIGNATURE;
    }

    public function getPlaceholder(): Placeholder
    {
        if (!isset($this->placeholder_chain)) {
            $p1 = new MailSignatureIliasUrlPlaceholder();
            $p2 = new MailSignatureInstallationNamePlaceholder();
            $p3 = new MailSignatureInstallationDescriptionPlaceholder();
            $p1->setNext($p2)->setNext($p3);
            $this->placeholder_chain = $p1;
        }
        return $this->placeholder_chain;
    }
}
