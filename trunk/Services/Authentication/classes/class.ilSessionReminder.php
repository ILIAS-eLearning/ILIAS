<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @ingroup ServicesAuthentication
 */
class ilSessionReminder
{
	/**
	 * @var int
	 */
	const MIN_LEAD_TIME = 2;

	/**
	 * @var int
	 */
	const SUGGESTED_LEAD_TIME = 5;
	
	
	/**
	 * @var $user ilObjUser
	 */
	protected $user;

	/**
	 * @var int
	 */
	protected $lead_time = 0;

	/**
	 * @var int
	 */
	protected $expiration_time = 0;

	/**
	 * @var int
	 */
	protected $current_time = 0;

	/**
	 * @var int
	 */
	protected $seconds_until_expiration = 0;

	/**
	 * @var int
	 */
	protected $seconds_until_reminder = 0;

	/**
	 * Constructor
	 */
	protected function __construct()
	{
	}

	/**
	 * @static
	 * @return ilSessionReminder
	 */
	public static function createInstanceWithCurrentUserSession()
	{
		/**
		 * @var $ilUser ilObjUser
		 */
		global $ilUser;

		$reminder = new self();
		$reminder->setUser($ilUser);
		$reminder->initWithUserContext();

		return $reminder;
	}

	/**
	 *
	 */
	protected function initWithUserContext()
	{
		/**
		 * @var $ilAuth Auth
		 */
		global $ilAuth;

		$this->setLeadTime(max(self::MIN_LEAD_TIME, (float)$this->getUser()->getPref('session_reminder_lead_time')) * 60);
		$this->setExpirationTime($ilAuth->sessionValidThru());
		$this->setCurrentTime(time());

		$this->calculateSecondsUntilExpiration();
		$this->calculateSecondsUntilReminder();
	}

	/**
	 *
	 */
	public function calculateSecondsUntilExpiration()
	{
		$this->setSecondsUntilExpiration($this->getExpirationTime() - $this->getCurrentTime());
	}

	/**
	 *
	 */
	public function calculateSecondsUntilReminder()
	{
		$this->setSecondsUntilReminder($this->getSecondsUntilExpiration() - $this->getLeadTime());
	}

	/**
	 * @return bool
	 */
	protected function isEnoughtTimeLeftForReminder()
	{
		return $this->getLeadTime() < $this->getSecondsUntilExpiration();
	}

	/**
	 * @return bool
	 */
	public function isActive()
	{
		return
			self::isGloballyActivated() &&
			$this->getUser()->getId() != ANONYMOUS_USER_ID &&
			(int)$this->getUser()->getPref('session_reminder_enabled') &&
			$this->isEnoughtTimeLeftForReminder();
	}

	/**
	 * @static
	 * @return bool
	 */
	public static function isGloballyActivated()
	{
		/**
		 * @var $ilSetting ilSetting
		 */
		global $ilSetting;

		return
			$ilSetting->get('session_handling_type', ilSession::SESSION_HANDLING_FIXED) == ilSession::SESSION_HANDLING_FIXED &&
			(int)$ilSetting->get('session_reminder_enabled', 0);
	}

	/**
	 * @param ilObjUser $user
	 * @return ilSessionReminder
	 */
	public function setUser($user)
	{
		$this->user = $user;
		return $this;
	}

	/**
	 * @return ilObjUser
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @param int $current_time
	 * @return ilSessionReminder
	 */
	public function setCurrentTime($current_time)
	{
		$this->current_time = $current_time;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getCurrentTime()
	{
		return $this->current_time;
	}

	/**
	 * @param int $expiration_time
	 * @return ilSessionReminder
	 */
	public function setExpirationTime($expiration_time)
	{
		$this->expiration_time = $expiration_time;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getExpirationTime()
	{
		return $this->expiration_time;
	}

	/**
	 * @param int $lead_time
	 * @return ilSessionReminder
	 */
	public function setLeadTime($lead_time)
	{
		$this->lead_time = $lead_time;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getLeadTime()
	{
		return $this->lead_time;
	}

	/**
	 * @param int $seconds_until_expiration
	 * @return ilSessionReminder
	 */
	public function setSecondsUntilExpiration($seconds_until_expiration)
	{
		$this->seconds_until_expiration = $seconds_until_expiration;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getSecondsUntilExpiration()
	{
		return $this->seconds_until_expiration;
	}

	/**
	 * @param int $seconds_until_reminder
	 * @return ilSessionReminder
	 */
	public function setSecondsUntilReminder($seconds_until_reminder)
	{
		$this->seconds_until_reminder = $seconds_until_reminder;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getSecondsUntilReminder()
	{
		return $this->seconds_until_reminder;
	}
}
