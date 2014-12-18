<?php
/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
        |                                                                             |
        | This program is free software; you can redistribute it and/or               |
        | modify it under the terms of the GNU General Public License                 |
        | as published by the Free Software Foundation; either version 2              |
        | of the License, or (at your option) any later version.                      |
        |                                                                             |
        | This program is distributed in the hope that it will be useful,             |
        | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
        | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
        | GNU General Public License for more details.                                |
        |                                                                             |
        | You should have received a copy of the GNU General Public License           |
        | along with this program; if not, write to the Free Software                 |
        | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
        +-----------------------------------------------------------------------------+
*/

include_once('Services/Calendar/classes/class.ilDate.php');
include_once('Services/Calendar/classes/class.ilCalendarHeaderNavigationGUI.php');
include_once('Services/Calendar/classes/class.ilCalendarUserSettings.php');
include_once('Services/Calendar/classes/class.ilCalendarAppointmentColors.php');
include_once('./Services/Calendar/classes/class.ilCalendarSchedule.php');



/**
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilCalendarInboxGUI: ilCalendarAppointmentGUI
* 
* @ingroup ServicesCalendar
*/
class ilCalendarInboxGUI
{
	protected $seed = null;
	protected $user_settings = null;
		
	protected $lng;
	protected $ctrl;
	protected $tabs_gui;
	protected $tpl;
	
	protected $timezone = 'UTC';

	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function __construct(ilDate $seed_date)
	{
		global $ilCtrl, $lng, $ilUser,$ilTabs,$tpl;
		
		$this->seed = $seed_date;

		$this->tpl = $tpl;
		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->tabs_gui = $ilTabs;
		
		$this->user_settings = ilCalendarUserSettings::_getInstanceByUserId($ilUser->getId());
		$this->app_colors = new ilCalendarAppointmentColors($ilUser->getId());
		
		$this->timezone = $ilUser->getTimeZone();
	}
	
	/**
	 * Execute command
	 *
	 * @access public
	 * 
	 */
	public function executeCommand()
	{
		global $ilCtrl,$tpl;

		$next_class = $ilCtrl->getNextClass();
		switch($next_class)
		{
			case 'ilcalendarappointmentgui':
				$this->ctrl->setReturn($this,'');
				$this->tabs_gui->setSubTabActive($_SESSION['cal_last_tab']);
				
				include_once('./Services/Calendar/classes/class.ilCalendarAppointmentGUI.php');
				$app = new ilCalendarAppointmentGUI($this->seed,$this->seed, (int) $_GET['app_id']);
				$this->ctrl->forwardCommand($app);
				break;
			
			default:
				$cmd = $this->ctrl->getCmd("inbox");
				$this->$cmd();
				$tpl->setContent($this->tpl->get());
				break;
		}
		
		return true;
	}
	
	/**
	 * show inbox
	 *
	 * @access protected
	 * @return
	 */
	protected function inbox()
	{
		global $ilCtrl;

		$this->tpl = new ilTemplate('tpl.inbox.html',true,true,'Services/Calendar');


		include_once('./Services/Calendar/classes/class.ilCalendarInboxSharedTableGUI.php');
		include_once('./Services/Calendar/classes/class.ilCalendarShared.php');

		$table = new ilCalendarInboxSharedTableGUI($this,'inbox');
		$table->setCalendars(ilCalendarShared::getSharedCalendarsForUser());

		if($table->parse())
		{
			$this->tpl->setVariable('SHARED_CAL_TABLE',$table->getHTML());
		}

		include_once('./Services/Calendar/classes/class.ilCalendarChangedAppointmentsTableGUI.php');

		$table_gui = new ilCalendarChangedAppointmentsTableGUI($this,'inbox');

		$schedule = new ilCalendarSchedule(new ilDate(time(),IL_CAL_UNIX),ilCalendarSchedule::TYPE_INBOX);
		$schedule->setEventsLimit($table_gui->getLimit());
		$schedule->addSubitemCalendars(true);		
		$schedule->calculate();
		
		if(isset($_GET['changed']))
		{
			$title = $this->lng->txt('cal_changed_events_header');
			$events = $schedule->getChangedEvents(true);

			$ilCtrl->setParameter($this, 'changed', 1);
		}
		else
		{
			// type inbox will show upcoming events (today or later)
			$title = $this->lng->txt('cal_upcoming_events_header');
			//$events = $schedule->getEvents();
			$events = $schedule->getScheduledEvents();
		}

		$table_gui->setTitle($title);
		$table_gui->setAppointments($events);
		
		$this->tpl->setVariable('CHANGED_TABLE',$table_gui->getHTML());

	}
	
	/**
	 * accept shared calendar
	 *
	 * @access protected
	 * @return
	 */
	protected function acceptShared()
	{
		global $ilUser;
		
		if(!$_POST['cal_ids'] or !is_array($_POST['cal_ids']))
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			$this->inbox();
			return false;
		}
		
		include_once('./Services/Calendar/classes/class.ilCalendarSharedStatus.php');
		$status = new ilCalendarSharedStatus($ilUser->getId());
		
		include_once('./Services/Calendar/classes/class.ilCalendarShared.php');
		foreach($_POST['cal_ids'] as $calendar_id)
		{
			if(!ilCalendarShared::isSharedWithUser($ilUser->getId(),$calendar_id))
			{
				ilUtil::sendFailure($this->lng->txt('permission_denied'));
				$this->inbox();
				return false;
			}
			$status->accept($calendar_id);
		}
		
		ilUtil::sendSuccess($this->lng->txt('settings_saved'),true);
		// redfirect for loading new calendar+
		$this->ctrl->redirect($this,'inbox');
		return true;
	}
	
	/**
	 * accept shared calendar
	 *
	 * @access protected
	 * @return
	 */
	protected function declineShared()
	{
		global $ilUser;

		if(!$_POST['cal_ids'] or !is_array($_POST['cal_ids']))
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			$this->inbox();
			return false;
		}
		
		include_once('./Services/Calendar/classes/class.ilCalendarSharedStatus.php');
		$status = new ilCalendarSharedStatus($ilUser->getId());
		
		include_once('./Services/Calendar/classes/class.ilCalendarShared.php');
		foreach($_POST['cal_ids'] as $calendar_id)
		{
			if(!ilCalendarShared::isSharedWithUser($ilUser->getId(),$calendar_id))
			{
				ilUtil::sendFailure($this->lng->txt('permission_denied'));
				$this->inbox();
				return false;
			}
			$status->decline($calendar_id);
		}
		
		ilUtil::sendSuccess($this->lng->txt('settings_saved'));
		$this->inbox();
		return true;
	}
	
}
?>