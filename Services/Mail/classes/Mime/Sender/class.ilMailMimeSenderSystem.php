<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailMimeSenderSystem
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailMimeSenderSystem implements ilMailMimeSender
{
    /** @var ilSetting */
    protected $settings;

    /**
     * ilMailMimeSenderSystem constructor.
     * @param ilSetting $settings
     */
    public function __construct(ilSetting $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @inheritdoc
     */
    public function hasReplyToAddress() : bool
    {
        return strlen($this->settings->get('mail_system_sys_reply_to_addr')) > 0;
    }

    /**
     * @inheritdoc
     */
    public function getReplyToAddress() : string
    {
        return $this->settings->get('mail_system_sys_reply_to_addr', '');
    }

    /**
     * @inheritdoc
     */
    public function getReplyToName() : string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function hasEnvelopFromAddress() : bool
    {
        return strlen($this->settings->get('mail_system_sys_env_from_addr')) > 0;
    }

    /**
     * @inheritdoc
     */
    public function getEnvelopFromAddress() : string
    {
        return $this->settings->get('mail_system_sys_env_from_addr', '');
    }

    /**
     * @inheritdoc
     */
    public function getFromAddress() : string
    {
        return $this->settings->get('mail_system_sys_from_addr', '');
    }

    /**
     * @inheritdoc
     */
    public function getFromName() : string
    {
        return $this->settings->get('mail_system_sys_from_name', '');
    }
}