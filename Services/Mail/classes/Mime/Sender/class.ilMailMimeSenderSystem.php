<?php

declare(strict_types=1);

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

/**
 * Class ilMailMimeSenderSystem
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailMimeSenderSystem implements ilMailMimeSender
{
    protected ilSetting $settings;

    public function __construct(ilSetting $settings)
    {
        $this->settings = $settings;
    }

    public function hasReplyToAddress(): bool
    {
        return $this->settings->get('mail_system_sys_reply_to_addr', '') !== '' && $this->settings->get('mail_system_sys_reply_to_addr', '') !== null;
    }

    public function getReplyToAddress(): string
    {
        return $this->settings->get('mail_system_sys_reply_to_addr', '');
    }

    public function getReplyToName(): string
    {
        return '';
    }

    public function hasEnvelopFromAddress(): bool
    {
        return $this->settings->get('mail_system_sys_env_from_addr', '') !== '' && $this->settings->get('mail_system_sys_env_from_addr', '') !== null;
    }

    public function getEnvelopFromAddress(): string
    {
        return $this->settings->get('mail_system_sys_env_from_addr', '');
    }

    public function getFromAddress(): string
    {
        return $this->settings->get('mail_system_sys_from_addr', '');
    }

    public function getFromName(): string
    {
        return $this->settings->get('mail_system_sys_from_name', '');
    }
}
