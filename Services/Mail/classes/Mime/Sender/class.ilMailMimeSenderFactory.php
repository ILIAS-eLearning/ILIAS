<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailMimeSenderFactory
 */
class ilMailMimeSenderFactory
{
    /**
     * @var \ilSetting
     */
    protected $settings;

    /**
     * @var ilMailMimeSender[]
     */
    protected $senders = array();

    /**
     * ilMailMimeSenderFactory constructor.
     * @param ilSetting $settings
     */
    public function __construct(\ilSetting $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @param int $usrId
     * @return bool
     */
    protected function isSystemMail($usrId)
    {
        return $usrId == ANONYMOUS_USER_ID;
    }

    /**
     * @param int $usrId
     * @return ilMailMimeSender
     */
    public function getSenderByUsrId($usrId)
    {
        if (array_key_exists($usrId, $this->senders)) {
            return $this->senders[$usrId];
        }

        switch (true) {
            case $this->isSystemMail($usrId):
                $sender = $this->system();
                break;

            default:
                $sender = $this->user($usrId);
                break;
        }

        $this->senders[$usrId] = $sender;

        return $sender;
    }

    /**
     * @return ilMailMimeSenderSystem
     */
    public function system()
    {
        require_once 'Services/Mail/classes/Mime/Sender/class.ilMailMimeSenderSystem.php';
        return new ilMailMimeSenderSystem($this->settings);
    }

    /**
     * @param int $usrId
     * @return ilMailMimeSenderUser
     */
    public function user($usrId)
    {
        require_once 'Services/Mail/classes/Mime/Sender/class.ilMailMimeSenderUser.php';
        return ilMailMimeSenderUser::byUsrId($this->settings, $usrId);
    }

    /**
     * @param $emailAddress
     * @return ilMailMimeSenderUser
     */
    public function userByEmailAddress($emailAddress)
    {
        require_once 'Services/Mail/classes/Mime/Sender/class.ilMailMimeSenderUser.php';
        return ilMailMimeSenderUser::byEmailAddress($this->settings, $emailAddress);
    }
}
