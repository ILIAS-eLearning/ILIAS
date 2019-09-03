<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailMimeSenderSystem
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilMailMimeSenderUser implements ilMailMimeSender
{
    /** @var ilSetting */
    protected $settings;

    /** @var ilObjUser */
    protected $user;

    /**
     * ilMailMimeSenderSystem constructor.
     * @param ilSetting $settings
     * @param ilObjUser ilObjUser
     */
    public function __construct(ilSetting $settings, ilObjUser $user)
    {
        $this->settings = $settings;
        $this->user = $user;
    }

    /**
     * @inheritdoc
     */
    public function hasReplyToAddress() : bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getReplyToAddress() : string
    {
        return (string) $this->user->getEmail();
    }

    /**
     * @inheritdoc
     */
    public function getReplyToName() : string
    {
        return (string) $this->user->getFullname();
    }

    /**
     * @inheritdoc
     */
    public function hasEnvelopFromAddress() : bool
    {
        return strlen($this->settings->get('mail_system_usr_env_from_addr')) > 0;
    }

    /**
     * @inheritdoc
     */
    public function getEnvelopFromAddress() : string
    {
        return $this->settings->get('mail_system_usr_env_from_addr', '');
    }

    /**
     * @inheritdoc
     */
    public function getFromAddress() : string
    {
        return $this->settings->get('mail_system_usr_from_addr', '');
    }

    /**
     * @inheritdoc
     */
    public function getFromName() : string
    {
        $from = $this->settings->get('mail_system_usr_from_name', '');
        if (0 == strlen($from)) {
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