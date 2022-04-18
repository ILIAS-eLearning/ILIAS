<?php declare(strict_types=1);

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

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Clock\ClockInterface;

class ilSessionReminder
{
    public const MIN_LEAD_TIME = 2;
    public const SUGGESTED_LEAD_TIME = 5;

    private ClockInterface $clock;
    private ilObjUser $user;
    private int $lead_time = self::SUGGESTED_LEAD_TIME;
    private int $expiration_time = 0;
    private int $current_time = 0;
    private int $seconds_until_expiration = 0;
    private int $seconds_until_reminder = 0;

    public static function byLoggedInUser() : self
    {
        global $DIC;

        if (isset($DIC['ilUser'])) {
            $user = $DIC->user();
        } else {
            $user = new ilObjUser();
            $user->setId(0);
        }

        $reminder = new self(
            $user,
            (new DataFactory())->clock()->utc()
        );

        return $reminder;
    }

    public static function isGloballyActivated() : bool
    {
        /** @var ilSetting $ilSetting */
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        $isSessionReminderEnabled = (bool) $ilSetting->get('session_reminder_enabled', null);
        $sessionHandlingMode = (int) $ilSetting->get(
            'session_handling_type',
            (string) ilSession::SESSION_HANDLING_FIXED
        );

        return (
            $isSessionReminderEnabled &&
            $sessionHandlingMode === ilSession::SESSION_HANDLING_FIXED
        );
    }

    public function __construct(ilObjUser $user, ClockInterface $clock)
    {
        $this->user = $user;
        $this->clock = $clock;

        $this->init();
    }

    private function init() : void
    {
        $this->setLeadTime(
            ((int) max(
                self::MIN_LEAD_TIME,
                (float) $this->getUser()->getPref('session_reminder_lead_time')
            )) * 60
        );

        $this->setExpirationTime(ilSession::getIdleValue(true) + $this->clock->now()->getTimestamp());
        $this->setCurrentTime($this->clock->now()->getTimestamp());

        $this->calculateSecondsUntilExpiration();
        $this->calculateSecondsUntilReminder();
    }

    private function calculateSecondsUntilExpiration() : void
    {
        $this->setSecondsUntilExpiration($this->getExpirationTime() - $this->getCurrentTime());
    }

    private function calculateSecondsUntilReminder() : void
    {
        $this->setSecondsUntilReminder($this->getSecondsUntilExpiration() - $this->getLeadTime());
    }

    private function isEnoughTimeLeftForReminder() : bool
    {
        return $this->getLeadTime() < $this->getSecondsUntilExpiration();
    }

    public function isActive() : bool
    {
        return (
            self::isGloballyActivated() &&
            !$this->getUser()->isAnonymous() &&
            $this->getUser()->getId() > 0 &&
            (int) $this->getUser()->getPref('session_reminder_enabled') &&
            $this->isEnoughTimeLeftForReminder()
        );
    }

    public function setUser(ilObjUser $user) : self
    {
        $this->user = $user;

        return $this;
    }

    public function getUser() : ilObjUser
    {
        return $this->user;
    }

    public function setCurrentTime(int $current_time) : self
    {
        $this->current_time = $current_time;

        return $this;
    }

    public function getCurrentTime() : int
    {
        return $this->current_time;
    }

    public function setExpirationTime(int $expiration_time) : self
    {
        $this->expiration_time = $expiration_time;

        return $this;
    }

    public function getExpirationTime() : int
    {
        return $this->expiration_time;
    }

    public function setLeadTime(int $lead_time) : self
    {
        $this->lead_time = $lead_time;

        return $this;
    }

    public function getLeadTime() : int
    {
        return $this->lead_time;
    }

    public function setSecondsUntilExpiration(int $seconds_until_expiration) : self
    {
        $this->seconds_until_expiration = $seconds_until_expiration;

        return $this;
    }

    public function getSecondsUntilExpiration() : int
    {
        return $this->seconds_until_expiration;
    }

    public function setSecondsUntilReminder(int $seconds_until_reminder) : self
    {
        $this->seconds_until_reminder = $seconds_until_reminder;

        return $this;
    }

    public function getSecondsUntilReminder() : int
    {
        return $this->seconds_until_reminder;
    }
}
