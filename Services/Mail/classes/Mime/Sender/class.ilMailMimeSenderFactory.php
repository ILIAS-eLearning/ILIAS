<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailMimeSenderFactory
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailMimeSenderFactory
{
    /** @var ilSetting */
    protected $settings;

    /** @var ilMailMimeSender[] */
    protected $senders = [];

    /** @var int */
    protected $anonymousUsrId = 0;

    /**
     * ilMailMimeSenderFactory constructor.
     * @param ilSetting $settings
     * @param int|null $anonymousUsrId
     */
    public function __construct(ilSetting $settings, int $anonymousUsrId = null)
    {
        $this->settings = $settings;
        if (null === $anonymousUsrId && defined('ANONYMOUS_USER_ID')) {
            $anonymousUsrId = ANONYMOUS_USER_ID;
        }
        $this->anonymousUsrId = $anonymousUsrId;
    }

    /**
     * @param int $usrId
     * @return bool
     */
    protected function isSystemMail(int $usrId) : bool
    {
        return $usrId === $this->anonymousUsrId;
    }

    /**
     * @param int $usrId
     * @return ilMailMimeSender
     */
    public function getSenderByUsrId(int $usrId) : ilMailMimeSender
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
    public function system() : ilMailMimeSenderSystem
    {
        return new ilMailMimeSenderSystem($this->settings);
    }

    /**
     * @param int $usrId
     * @return ilMailMimeSenderUser
     */
    public function user(int $usrId) : ilMailMimeSenderUser
    {
        return new ilMailMimeSenderUserById($this->settings, $usrId);
    }

    /**
     * @param string $emailAddress
     * @return ilMailMimeSenderUser
     */
    public function userByEmailAddress(string $emailAddress) : ilMailMimeSenderUser
    {
        return new ilMailMimeSenderUserByEmailAddress($this->settings, $emailAddress);
    }
}