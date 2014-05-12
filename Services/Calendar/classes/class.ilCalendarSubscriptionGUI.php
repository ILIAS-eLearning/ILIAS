<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Calendar/classes/class.ilCalendarCategory.php';

/**
 * Show calendar subscription info
 * 
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilCalendarSubscriptionGUI
{
	private $cal_id = 0;
	private $calendar = null;

	/**
	 * Constructor
	 * @param int $a_clendar_id
	 */
	public function __construct($a_calendar_id)
	{
		$this->cal_id = $a_calendar_id;
		include_once './Services/Calendar/classes/class.ilCalendarCategory.php';
		$this->calendar = new ilCalendarCategory($this->cal_id);
	}

	/**
	 * Get current calendar id
	 * @return <type>
	 */
	public function getCalendarId()
	{
		return $this->cal_id;
	}

	public function getCalendar()
	{
		return $this->calendar ? $this->calendar : new ilCalendarCategory();
	}

	/**
	 * Execute command
	 */
	public function executeCommand()
	{
		global $ilCtrl;

		$next_class = $ilCtrl->getNextClass($this);
		switch($next_class)
		{
			default:
				$cmd = $ilCtrl->getCmd("show");

				$this->$cmd();
				break;
		}
		return true;
	}

	/**
	 * Show subscription info
	 */
	protected function show()
	{
		$token = $this->createToken();
		
		ilUtil::sendInfo($GLOBALS['lng']->txt('cal_subscription_info'));

		include_once './Services/InfoScreen/classes/class.ilInfoScreenGUI.php';
		$info = new ilInfoScreenGUI($this);
		$info->setFormAction($GLOBALS['ilCtrl']->getFormAction($this));

		$hash = $this->createToken();
		$url = ILIAS_HTTP_PATH.'/calendar.php?client_id='.CLIENT_ID.'&token='.$hash;
		$info->addSection($this->getCalendar()->getTitle());
		$info->addProperty($GLOBALS['lng']->txt('cal_ical_url'), $url, $url);

		$GLOBALS['tpl']->setContent($info->getHTML());
	}

	/**
	 * Create calendar token
	 */
	private function createToken()
	{
		include_once './Services/Calendar/classes/class.ilCalendarAuthenticationToken.php';
		$hash = ilCalendarAuthenticationToken::lookupAuthToken(
			$GLOBALS['ilUser']->getId(),
			ilCalendarAuthenticationToken::SELECTION_CALENDAR,
			$this->getCalendarId()
		);
		if(strlen($hash))
		{
			return $hash;
		}

		$token = new ilCalendarAuthenticationToken($GLOBALS['ilUser']->getId());
		$token->setSelectionType(ilCalendarAuthenticationToken::SELECTION_CALENDAR);
		$token->setCalendar($this->getCalendarId());
		return $token->add();
	}

}
?>
