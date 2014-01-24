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
		 * @var $tpl ilTemplate
		 */
		global $lng, $tpl;

		if($this->getSessionReminder()->isActive())
		{
			require_once 'Services/jQuery/classes/class.iljQueryUtil.php';
			iljQueryUtil::initjQuery();

			require_once 'Services/YUI/classes/class.ilYuiUtil.php';
			ilYuiUtil::initCookie();
			
			$tpl->addJavaScript('./Services/Authentication/js/session_reminder.js');
			
			$reminder_tpl = new ilTemplate('tpl.session_reminder.html', true, true, 'Services/Authentication');
			$reminder_tpl->setVariable('DEBUG', defined('DEVMODE') && DEVMODE ? 1 : 0);
			$reminder_tpl->setVariable('CLIENT_ID', CLIENT_ID);
			$reminder_tpl->setVariable('SESSION_NAME', session_name());
			$reminder_tpl->setVariable('FREQUENCY', 60);
			$reminder_tpl->setVariable('SESSION_ID', session_id());
			$reminder_tpl->setVariable(
				'URL',
				'./sessioncheck.php?client_id=' . CLIENT_ID . 
				'&lang='.$lng->getLangKey()
			);
			
			return $reminder_tpl->get();
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
