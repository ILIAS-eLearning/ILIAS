<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Mail/classes/Mime/Sender/interface.ilMailMimeSender.php';

/**
 * Class ilMailMimeSenderSystem
 */
class ilMailMimeSenderSystem implements ilMailMimeSender
{
    /**
     * @var \ilSetting
     */
    protected $settings;

    /**
     * ilMailMimeSenderSystem constructor.
     * @param ilSetting $settings
     */
    public function __construct(\ilSetting $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @inheritdoc
     */
    public function hasReplyToAddress()
    {
        return strlen($this->settings->get('mail_system_sys_reply_to_addr')) > 0;
    }

    /**
     * @inheritdoc
     */
    public function getReplyToAddress()
    {
        return $this->settings->get('mail_system_sys_reply_to_addr');
    }

    /**
     * @inheritdoc
     */
    public function getReplyToName()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function hasEnvelopFromAddress()
    {
        return strlen($this->settings->get('mail_system_sys_env_from_addr')) > 0;
    }

    /**
     * @inheritdoc
     */
    public function getEnvelopFromAddress()
    {
        return $this->settings->get('mail_system_sys_env_from_addr');
    }

    /**
     * @inheritdoc
     */
    public function getFromAddress()
    {
        return $this->settings->get('mail_system_sys_from_addr');
    }

    /**
     * @inheritdoc
     */
    public function getFromName()
    {
        return $this->settings->get('mail_system_sys_from_name');
    }
}
