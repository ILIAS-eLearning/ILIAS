<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Authentication/classes/class.ilSessionReminder.php';

/**
 * @author Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @ingroup ServicesAuthentication
 */
class ilSessionReminderGUI
{
	/**
	 * @var ilSessionReminder
	 */
	protected $session_reminder;

	/**
	 * @param ilSessionReminder $session_reminder
	 */
	public function __construct(ilSessionReminder $session_reminder)
	{
		$this->setSessionReminder($session_reminder);
	}

	/**
	 * @return string
	 */
	public function getHtml()
	{
		/**
		 * @var $lng ilLanguage
		 * @var $ilUser ilObjUser
		 */
		global $lng, $ilUser;

		if($this->getSessionReminder()->isActive())
		{
			$tpl = new ilTemplate('tpl.session_reminder.html', true, true, 'Services/Authentication');

			$tpl->setVariable('ILIAS_SESSION_COUNTDOWN', $this->getSessionReminder()->getSecondsUntilReminder() * 1000);
			$tpl->setVariable('ILIAS_SESSION_EXTENDER_URL', './ilias.php?baseClass=ilPersonalDesktopGUI');
			$tpl->setVariable('ILIAS_SESSION_CHECKER_URL',
				'./sessioncheck.php' .
					'?client_id=' . CLIENT_ID .
					'&session_id=' . session_id() . // used to identify the user withour init the auth service
					'&countdown=' . $this->getSessionReminder()->getSecondsUntilReminder());
			$tpl->setVariable('CONFIRM_TXT', $lng->txt('session_reminder_alert'));

			return $tpl->get();
		}

		return '';
	}

	/**
	 * @param ilSessionReminder $session_reminder
	 * @return ilSessionReminderGUI
	 */
	public function setSessionReminder($session_reminder)
	{
		$this->session_reminder = $session_reminder;
		return $this;
	}

	/**
	 * @return ilSessionReminder
	 */
	public function getSessionReminder()
	{
		return $this->session_reminder;
	}
}
