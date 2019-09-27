<?php declare(strict_types=1);
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @ingroup ServicesAuthentication
 */
class ilSessionReminder
{
    /** @var int */
    const MIN_LEAD_TIME = 2;

    /** @var int  */
    const SUGGESTED_LEAD_TIME = 5;

    /** @var $user ilObjUser */
    protected $user;

    /** @var int */
    protected $lead_time = 0;

    /** @var int */
    protected $expiration_time = 0;

    /** @var int */
    protected $current_time = 0;

    /** @var int */
    protected $seconds_until_expiration = 0;

    /** @var int */
    protected $seconds_until_reminder = 0;

    /**
     * ilSessionReminder constructor.
     */
    protected function __construct()
    {
    }

    /**
     * @return ilSessionReminder
     */
    public static function createInstanceWithCurrentUserSession() : self
    {
        global $DIC;

        if (isset($DIC['ilUser'])) {
            $user = $DIC['ilUser'];
        } else {
            $user = new ilObjUser();
            $user->setId(0);
        }

        $reminder = new self();
        $reminder->setUser($user);
        $reminder->initWithUserContext();

        return $reminder;
    }

    /**
     *
     */
    protected function initWithUserContext() : void
    {
        $this->setLeadTime(
            ((int) max(
                self::MIN_LEAD_TIME,
                (float) $this->getUser()->getPref('session_reminder_lead_time')
            )) * 60
        );

        $this->setExpirationTime(ilSession::getIdleValue(true) + time());
        $this->setCurrentTime(time());

        $this->calculateSecondsUntilExpiration();
        $this->calculateSecondsUntilReminder();
    }

    /**
     *
     */
    public function calculateSecondsUntilExpiration() : void
    {
        $this->setSecondsUntilExpiration($this->getExpirationTime() - $this->getCurrentTime());
    }

    /**
     *
     */
    public function calculateSecondsUntilReminder(): void
    {
        $this->setSecondsUntilReminder($this->getSecondsUntilExpiration() - $this->getLeadTime());
    }

    /**
     * @return bool
     */
    protected function isEnoughTimeLeftForReminder() : bool
    {
        return $this->getLeadTime() < $this->getSecondsUntilExpiration();
    }

    /**
     * @return bool
     */
    public function isActive() : bool
    {
        return (
            self::isGloballyActivated() &&
            !$this->getUser()->isAnonymous() &&
            (int) $this->getUser()->getId() > 0 &&
            (int) $this->getUser()->getPref('session_reminder_enabled') &&
            $this->isEnoughTimeLeftForReminder()
        );
    }

    /**
     * @return bool
     */
    public static function isGloballyActivated() : bool
    {
        /**
         * @var $ilSetting ilSetting
         */
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        $isSessionReminderEnabled = (bool) $ilSetting->get('session_reminder_enabled', false);
        $sessionHandlingMode = $ilSetting->get('session_handling_type', ilSession::SESSION_HANDLING_FIXED);

        return (
            $isSessionReminderEnabled &&
            $sessionHandlingMode == ilSession::SESSION_HANDLING_FIXED
        );
    }

    /**
     * @param ilObjUser $user
     * @return $this
     */
    public function setUser(ilObjUser $user) : self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return ilObjUser
     */
    public function getUser() : ilObjUser
    {
        return $this->user;
    }

    /**
     * @param int $current_time
     * @return $this
     */
    public function setCurrentTime(int $current_time) : self
    {
        $this->current_time = $current_time;

        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentTime() : int
    {
        return $this->current_time;
    }

    /**
     * @param int $expiration_time
     * @return $this
     */
    public function setExpirationTime(int $expiration_time) : self
    {
        $this->expiration_time = $expiration_time;

        return $this;
    }

    /**
     * @return int
     */
    public function getExpirationTime() : int
    {
        return $this->expiration_time;
    }

    /**
     * @param int $lead_time
     * @return $this
     */
    public function setLeadTime(int $lead_time) : self
    {
        $this->lead_time = $lead_time;

        return $this;
    }

    /**
     * @return int
     */
    public function getLeadTime() : int
    {
        return $this->lead_time;
    }

    /**
     * @param int $seconds_until_expiration
     * @return $this
     */
    public function setSecondsUntilExpiration(int $seconds_until_expiration) : self
    {
        $this->seconds_until_expiration = $seconds_until_expiration;

        return $this;
    }

    /**
     * @return int
     */
    public function getSecondsUntilExpiration() : int
    {
        return $this->seconds_until_expiration;
    }

    /**
     * @param int $seconds_until_reminder
     * @return $this
     */
    public function setSecondsUntilReminder(int $seconds_until_reminder) : self
    {
        $this->seconds_until_reminder = $seconds_until_reminder;

        return $this;
    }

    /**
     * @return int
     */
    public function getSecondsUntilReminder() : int
    {
        return $this->seconds_until_reminder;
    }
}
