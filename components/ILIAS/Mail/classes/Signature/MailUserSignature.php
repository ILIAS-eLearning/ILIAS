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
    private int $user_id;

    public function __construct(int $user_id)
    {
        global $DIC;
        $this->settings = $DIC->settings();
        $this->user_id = $user_id;
    }

    public function getSignature(): string
    {
        return $this->settings->get($this->getSettingsKeyword(), '');
    }

    public function getSettingsKeyword(): string
    {
        return self::MAIL_USER_SIGNATURE;
    }

    public function getPlaceholder(): Placeholder
    {
        if (!isset($this->placeholder_chain)) {
            $p1 = new MailSignatureIliasUrlPlaceholder();
            $p2 = new MailSignatureInstallationNamePlaceholder();
            $p3 = new MailSignatureInstallationDescriptionPlaceholder();
            $p4 = new MailSignatureUserNamePlaceholder($this->user_id);
            $p5 = new MailSignatureUserFullnamePlaceholder($this->user_id);
            $p1->setNext($p2)->setNext($p3)->setNext($p4)->setNext($p5);
            $this->placeholder_chain = $p1;
        }
        return $this->placeholder_chain;
    }
}
