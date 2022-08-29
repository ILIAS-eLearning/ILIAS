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
 * Class ilMailMimeSenderFactory
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailMimeSenderFactory
{
    /** @var ilMailMimeSender[] */
    protected array $senders = [];
    protected int $anonymousUsrId = 0;

    public function __construct(protected ilSetting $settings, int $anonymousUsrId = null)
    {
        if (null === $anonymousUsrId && defined('ANONYMOUS_USER_ID')) {
            $anonymousUsrId = ANONYMOUS_USER_ID;
        }
        if (null === $anonymousUsrId) {
            throw new Exception();
        }

        $this->anonymousUsrId = $anonymousUsrId;
    }

    protected function isSystemMail(int $usrId): bool
    {
        return $usrId === $this->anonymousUsrId;
    }

    public function getSenderByUsrId(int $usrId): ilMailMimeSender
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

    public function system(): ilMailMimeSenderSystem
    {
        return new ilMailMimeSenderSystem($this->settings);
    }

    public function user(int $usrId): ilMailMimeSenderUser
    {
        return new ilMailMimeSenderUserById($this->settings, $usrId);
    }

    public function userByEmailAddress(string $emailAddress): ilMailMimeSenderUser
    {
        return new ilMailMimeSenderUserByEmailAddress($this->settings, $emailAddress);
    }
}
