<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailMimeSenderFactory
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailMimeSenderFactory
{
    protected ilSetting $settings;

    /** @var ilMailMimeSender[] */
    protected array $senders = [];
    protected int $anonymousUsrId = 0;

    /**
     * ilMailMimeSenderFactory constructor.
     * @param ilSetting $settings
     * @param int|null $anonymousUsrId
     */
    public function __construct(ilSetting $settings, int $anonymousUsrId = null)
    {
        $this->settings = $settings;
        if (null === $anonymousUsrId && defined('ANONYMOUS_USER_ID')) {
            $anonymousUsrId = (int) ANONYMOUS_USER_ID;
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

        if ($this->isSystemMail($usrId)) {
            $sender = $this->system();
        } else {
            $sender = $this->user($usrId);
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
