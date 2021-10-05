<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

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

    public function __construct(ilSetting $settings, int $anonymousUsrId = null)
    {
        $this->settings = $settings;
        if (null === $anonymousUsrId && defined('ANONYMOUS_USER_ID')) {
            $anonymousUsrId = ANONYMOUS_USER_ID;
        }
        if (null === $anonymousUsrId) {
            throw new Exception();
        }

        $this->anonymousUsrId = $anonymousUsrId;
    }

    protected function isSystemMail(int $usrId) : bool
    {
        return $usrId === $this->anonymousUsrId;
    }

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

    public function system() : ilMailMimeSenderSystem
    {
        return new ilMailMimeSenderSystem($this->settings);
    }

    public function user(int $usrId) : ilMailMimeSenderUser
    {
        return new ilMailMimeSenderUserById($this->settings, $usrId);
    }

    public function userByEmailAddress(string $emailAddress) : ilMailMimeSenderUser
    {
        return new ilMailMimeSenderUserByEmailAddress($this->settings, $emailAddress);
    }
}
