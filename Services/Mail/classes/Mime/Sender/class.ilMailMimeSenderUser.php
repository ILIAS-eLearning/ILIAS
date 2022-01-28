<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailMimeSenderSystem
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilMailMimeSenderUser implements ilMailMimeSender
{
    protected ilSetting $settings;
    protected ilObjUser $user;

    public function __construct(ilSetting $settings, ilObjUser $user)
    {
        $this->settings = $settings;
        $this->user = $user;
    }

    public function hasReplyToAddress() : bool
    {
        return true;
    }

    public function getReplyToAddress() : string
    {
        if (
            true === (bool) $this->settings->get('use_global_reply_to_addr', '0') &&
            is_string($this->settings->get('global_reply_to_addr', '')) &&
            $this->settings->get('global_reply_to_addr', '') !== ''
        ) {
            return $this->settings->get('global_reply_to_addr', '');
        }

        return (string) $this->user->getEmail();
    }

    public function getReplyToName() : string
    {
        return (string) $this->user->getFullname();
    }

    public function hasEnvelopFromAddress() : bool
    {
        return $this->settings->get('mail_system_usr_env_from_addr', '') !== '';
    }

    public function getEnvelopFromAddress() : string
    {
        return $this->settings->get('mail_system_usr_env_from_addr', '');
    }

    public function getFromAddress() : string
    {
        return $this->settings->get('mail_system_usr_from_addr', '');
    }

    public function getFromName() : string
    {
        $from = $this->settings->get('mail_system_usr_from_name', '');
        if ($from === '') {
            return (string) $this->user->getFullname();
        }

        $name = str_ireplace('[FULLNAME]', (string) $this->user->getFullname(), $from);
        $name = str_ireplace('[FIRSTNAME]', (string) $this->user->getFirstname(), $name);
        $name = str_ireplace('[LASTNAME]', (string) $this->user->getLastname(), $name);
        if ($name !== $from) {
            return $name;
        }

        return $from;
    }
}
