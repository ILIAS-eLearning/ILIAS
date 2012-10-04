<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Authentication/classes/class.ilSessionReminder.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
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
		 * @var $lng    ilLanguage
		 * @var $ilClientIniFile ilIniFile
		 */
		global $lng, $ilClientIniFile;

		if($this->getSessionReminder()->isActive())
		{
			require_once 'Services/jQuery/classes/class.iljQueryUtil.php';
			iljQueryUtil::initjQuery();

			require_once 'Services/YUI/classes/class.ilYuiUtil.php';
			ilYuiUtil::initCookie();
			
			$tpl = new ilTemplate('tpl.session_reminder.html', true, true, 'Services/Authentication');

			$tpl->setVariable('ILIAS_SESSION_POLL_INTERVAL', 1000 * 60);
			$tpl->setVariable('ILIAS_SESSION_EXTENDER_URL', './ilias.php?baseClass=ilPersonalDesktopGUI');
			$tpl->setVariable('ILIAS_SESSION_CHECKER_URL',
				'./sessioncheck.php' .
				'?client_id=' . CLIENT_ID .
				'&session_id=' . session_id()); // used to identify the user without init the auth service
			$tpl->setVariable('CONFIRM_TXT', $lng->txt('session_reminder_alert'));
			$tpl->setVariable('CLIENT_ID', CLIENT_ID);
			$tpl->setVariable('INSTALLATION_NAME', json_encode($ilClientIniFile->readVariable('client', 'name'). ' | '.ilUtil::_getHttpPath()));

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
