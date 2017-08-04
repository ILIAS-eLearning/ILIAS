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
	public function __construct($a_calendar_id, $a_ref_id = 0)
	{
		global $DIC;

		$this->cal_id = $a_calendar_id;
		$this->ref_id = $a_ref_id;
		$this->user = $DIC->user();
		$this->lng = $DIC->language();
		$this->tpl = $DIC["tpl"];

		include_once './Services/Calendar/classes/class.ilCalendarCategory.php';
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
		ilUtil::sendInfo($this->lng->txt('cal_subscription_info'));

		include_once './Services/InfoScreen/classes/class.ilInfoScreenGUI.php';
		$info = new ilInfoScreenGUI($this);
		$info->setFormAction($GLOBALS['ilCtrl']->getFormAction($this));

		if ($this->cal_id > 0)
		{
			$selection = ilCalendarAuthenticationToken::SELECTION_CALENDAR;
			$id = $this->cal_id;
		}
		else if ($this->ref_id > 0)
		{
			$selection = ilCalendarAuthenticationToken::SELECTION_CATEGORY;
			$id = ilObject::_lookupObjId((int) $this->ref_id);
		}
		else
		{
			$selection = ilCalendarAuthenticationToken::SELECTION_PD;
			$id = 0;
		}

		$hash = $this->createToken($this->user->getID(), $selection, $id);
		$url = ILIAS_HTTP_PATH.'/calendar.php?client_id='.CLIENT_ID.'&token='.$hash;
		$info->addSection($this->lng->txt("cal_subscription"));
		$info->addProperty($this->lng->txt('cal_ical_url'), $url, $url);

		$this->tpl->setContent($info->getHTML());
	}

	/**
	 * Create calendar token
	 */
	private function createToken($user_id, $selection, $id)
	{
		include_once './Services/Calendar/classes/class.ilCalendarAuthenticationToken.php';
		$hash = ilCalendarAuthenticationToken::lookupAuthToken($user_id, $selection, $id);
		if(strlen($hash))
		{
			return $hash;
		}
		$token = new ilCalendarAuthenticationToken($user_id);
		$token->setSelectionType($selection);
		$token->setCalendar($id);
		return $token->add();
	}

}
?>
