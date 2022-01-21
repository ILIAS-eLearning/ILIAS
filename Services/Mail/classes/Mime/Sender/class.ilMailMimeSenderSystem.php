<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

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

    public function hasReplyToAddress() : bool
    {
        return $this->settings->get('mail_system_sys_reply_to_addr', '') !== '';
    }

    public function getReplyToAddress() : string
    {
        return $this->settings->get('mail_system_sys_reply_to_addr', '');
    }

    public function getReplyToName() : string
    {
        return '';
    }

    public function hasEnvelopFromAddress() : bool
    {
        return $this->settings->get('mail_system_sys_env_from_addr', '') !== '';
    }

    public function getEnvelopFromAddress() : string
    {
        return $this->settings->get('mail_system_sys_env_from_addr', '');
    }

    public function getFromAddress() : string
    {
        return $this->settings->get('mail_system_sys_from_addr', '');
    }

    public function getFromName() : string
    {
        return $this->settings->get('mail_system_sys_from_name', '');
    }
}
