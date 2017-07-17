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

include_once './Services/Calendar/classes/class.ilCalendarSettings.php';

/** 
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ilCtrl_Calls ilCalendarPresentationGUI: ilCalendarMonthGUI, ilCalendarUserSettingsGUI, ilCalendarCategoryGUI, ilCalendarWeekGUI
* @ilCtrl_Calls ilCalendarPresentationGUI: ilCalendarAppointmentGUI, ilCalendarDayGUI, ilCalendarInboxGUI, ilCalendarSubscriptionGUI
* @ilCtrl_Calls ilCalendarPresentationGUI: ilConsultationHoursGUI
* @ingroup ServicesCalendar
*/

class ilCalendarPresentationGUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var mixed
	 */
	protected $tpl;

	/**
	 * @var ilLanguage
	 */
	protected $tabs_gui;

	/**
	 * @var ilObjUser
	 */
	protected $user;

	/**
	 * @var ilHelp
	 */
	protected $help;

	/**
	 * @var ilRbacSystem
	 */
	protected $rbacsystem;

	/**
	 * @var \ILIAS\DI\UIServices
	 */
	protected $ui;

	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;

	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function __construct()
	{
		global $DIC;


		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		$this->lng->loadLanguageModule('dateplaner');
		
		$this->tpl = $DIC["tpl"];
		$this->tabs_gui = $DIC->tabs();
		$this->user = $DIC->user();
		$this->rbacsystem = $DIC->rbac()->system();
		$this->help = $DIC["ilHelp"];
		$this->ui = $DIC->ui();
		$this->toolbar = $DIC->toolbar();
		
		
		include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');
		$cats = ilCalendarCategories::_getInstance($this->user->getId());
		
		include_once './Services/Calendar/classes/class.ilCalendarUserSettings.php';
		if(ilCalendarUserSettings::_getInstance()->getCalendarSelectionType() == ilCalendarUserSettings::CAL_SELECTION_MEMBERSHIP)
		{
			$cats->initialize(ilCalendarCategories::MODE_PERSONAL_DESKTOP_MEMBERSHIP);
		}
		else
		{
			$cats->initialize(ilCalendarCategories::MODE_PERSONAL_DESKTOP_ITEMS);
		}
	}
	
	
	/**
	 * Execute command
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function executeCommand()
	{
		$ilUser = $this->user;

		include_once('./Services/Calendar/classes/class.ilCalendarSettings.php');
		if(!ilCalendarSettings::_getInstance()->isEnabled())
		{
			ilUtil::sendFailure($this->lng->txt('permission_denied'),true);
			ilUtil::redirect('ilias.php?baseClass=ilPersonalDesktopGUI');
		}

		$this->initSeed();
		$this->prepareOutput();

		$next_class = $this->getNextClass();


		switch($next_class)
		{
			case 'ilcalendarinboxgui':
				$this->tabs_gui->activateTab('cal_agenda');
				$this->forwardToClass('ilcalendarinboxgui');
				$this->showViewSelection("cal_list");
				break;
				
			case 'ilconsultationhoursgui':
				$this->tabs_gui->activateTab('app_consultation_hours');
				$this->tabs_gui->clearTargets();

				// No side blocks
				$this->tabs_gui->setBackTarget(
					$this->lng->txt('cal_back_to_cal'),
					$this->ctrl->getLinkTargetByClass($this->readLastClass())
				);

				include_once './Services/Calendar/classes/ConsultationHours/class.ilConsultationHoursGUI.php';
				$gui = new ilConsultationHoursGUI();
				$this->ctrl->forwardCommand($gui);
				return true;
			
			case 'ilcalendarmonthgui':
				$this->tabs_gui->activateTab('cal_agenda');
				$this->forwardToClass('ilcalendarmonthgui');
				$this->showViewSelection("app_month");
				break;
				
			case 'ilcalendarweekgui':
				$this->tabs_gui->activateTab('cal_agenda');
				$this->forwardToClass('ilcalendarweekgui');
				$this->showViewSelection("app_week");
				break;

			case 'ilcalendardaygui':
				$this->tabs_gui->activateTab('cal_agenda');
				$this->forwardToClass('ilcalendardaygui');
				$this->showViewSelection("app_day");
				break;

			case 'ilcalendarusersettingsgui':
				$this->ctrl->setReturn($this,'');
				$this->tabs_gui->activateTab('settings');
				$this->setCmdClass('ilcalendarusersettingsgui');
				
				include_once('./Services/Calendar/classes/class.ilCalendarUserSettingsGUI.php');
				$user_settings = new ilCalendarUserSettingsGUI();
				$this->ctrl->forwardCommand($user_settings);
				// No side blocks
				return true;
				
			case 'ilcalendarappointmentgui':
				$this->ctrl->setReturn($this,'');
				$this->tabs_gui->activateTab($_SESSION['cal_last_tab']);
				
				include_once('./Services/Calendar/classes/class.ilCalendarAppointmentGUI.php');
				$app = new ilCalendarAppointmentGUI($this->seed, $this->seed,(int) $_GET['app_id']);
				$this->ctrl->forwardCommand($app);
				break;

			case 'ilcalendarsubscriptiongui':
				$this->ctrl->setReturn($this,'');
				$this->tabs_gui->activateTab($_SESSION['cal_last_tab']);
				include_once './Services/Calendar/classes/class.ilCalendarSubscriptionGUI.php';
				$sub = new ilCalendarSubscriptionGUI((int) $_REQUEST['cal_id']);
				$this->ctrl->forwardCommand($sub);
				break;
				
			case 'ilcalendarcategorygui':
				$this->ctrl->setReturn($this,'');
				
				include_once('Services/Calendar/classes/class.ilCalendarCategoryGUI.php');				
				$category = new ilCalendarCategoryGUI($ilUser->getId(),$this->seed);
				if($this->ctrl->forwardCommand($category))
				{
					$this->tabs_gui->activateTab("cal_manage");

					// no side blocks
					return;
				}
				else
				{
					$this->tabs_gui->activateTab($_SESSION['cal_last_tab']);
					break;
				}

			default:
				$cmd = $this->ctrl->getCmd("show");

				$this->$cmd();
				break;
		}

		$this->showSideBlocks();
		
		$this->synchroniseExternalCalendars();
		
		return true;
	}

	/**
	 * Show view selection
	 *
	 * @param string $a_active
	 */
	function showViewSelection($a_active = "cal_list")
	{
		$ui = $this->ui;
		$ctrl = $this->ctrl;
		$lng = $this->lng;
		$toolbar = $this->toolbar;

		$f = $ui->factory();

		$actions = array (
			$lng->txt("app_day") => $ctrl->getLinkTargetByClass('ilCalendarDayGUI',''),
			$lng->txt("app_week") => $ctrl->getLinkTargetByClass('ilCalendarWeekGUI',''),
			$lng->txt("app_month") => $ctrl->getLinkTargetByClass('ilCalendarMonthGUI',''),
			$lng->txt("cal_list") => $ctrl->getLinkTargetByClass('ilCalendarInboxGUI','')
		);

		$aria_label = $lng->txt("cal_change_calendar_view");
		$view_control = $f->viewControl()->mode($actions, $aria_label)->withActive($lng->txt($a_active));

		$toolbar->addComponent($view_control);
	}


	/**
	 * get next class
	 *
	 * @access public
	 */
	public function getNextClass()
	{
		if(strlen($next_class = $this->ctrl->getNextClass()))
		{
			return $next_class;
		}
		if($this->ctrl->getCmdClass() == strtolower(get_class($this)) or $this->ctrl->getCmdClass() == '')
		{
			return $this->readLastClass();
		}
	}
	
	/**
	 * Read last class from history
	 * @return 
	 */
	public function readLastClass()
	{
		$ilUser = $this->user;
		
		return $ilUser->getPref('cal_last_class') ? $ilUser->getPref('cal_last_class') : 'ilcalendarinboxgui';
				
	}
	
	public function setCmdClass($a_class)
	{
		// If cmd class == 'ilcalendarpresentationgui' the cmd class is set to the the new forwarded class
		// otherwise e.g ilcalendarmonthgui tries to forward (back) to ilcalendargui.

		if($this->ctrl->getCmdClass() == strtolower(get_class($this)))
		{
			$this->ctrl->setCmdClass(strtolower($a_class));
		}
		return true;
	}
	
	/**
	 * forward to class
	 *
	 * @access protected
	 */
	protected function forwardToClass($a_class)
	{
		$ilUser = $this->user;
		
		switch($a_class)
		{
			case 'ilcalendarmonthgui':
				$ilUser->writePref('cal_last_class',$a_class);
				$_SESSION['cal_last_tab'] = 'app_month'; 
				$this->setCmdClass('ilcalendarmonthgui');
				include_once('./Services/Calendar/classes/class.ilCalendarMonthGUI.php');
				$month_gui = new ilCalendarMonthGUI($this->seed);
				$this->ctrl->forwardCommand($month_gui);
				break;
				
			case 'ilcalendarweekgui':
				$ilUser->writePref('cal_last_class',$a_class);
				$_SESSION['cal_last_tab'] = 'app_week'; 
				$this->setCmdClass('ilcalendarweekgui');
				include_once('./Services/Calendar/classes/class.ilCalendarWeekGUI.php');
				$week_gui = new ilCalendarWeekGUI($this->seed);
				$this->ctrl->forwardCommand($week_gui);
				break;

			case 'ilcalendardaygui':
				$ilUser->writePref('cal_last_class',$a_class);
				$_SESSION['cal_last_tab'] = 'app_day'; 
				$this->setCmdClass('ilcalendardaygui');
				include_once('./Services/Calendar/classes/class.ilCalendarDayGUI.php');
				$day_gui = new ilCalendarDayGUI($this->seed);
				$this->ctrl->forwardCommand($day_gui);
				break;
				
			case 'ilcalendarinboxgui':
				$ilUser->writePref('cal_last_class',$a_class);
				$_SESSION['cal_last_tab'] = 'cal_upcoming_events_header';
				$this->setCmdClass('ilcalendarinboxgui');
				include_once('./Services/Calendar/classes/class.ilCalendarInboxGUI.php');
				$inbox_gui = new ilCalendarinboxGUI($this->seed);
				$this->ctrl->forwardCommand($inbox_gui);
				break;

		}
	}
	
	/**
	 * forward to last presentation class
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function loadHistory()
	{
		$ilUser = $this->user;
		
		$this->ctrl->setCmd('');
		$history = $ilUser->getPref('cal_last_class') ? $ilUser->getPref('cal_last_class') : 'ilcalendarmonthgui';
		$this->forwardToClass($history);
	}
	
	/**
	 * show side blocks
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function showSideBlocks()
	{
		$ilUser = $this->user;
		$ilCtrl = $this->ctrl;

		$tpl =  new ilTemplate('tpl.cal_side_block.html',true,true,'Services/Calendar');

		include_once('./Services/Calendar/classes/class.ilMiniCalendarGUI.php');
		$mini = new ilMiniCalendarGUI($this->seed, $this);
//		$mini->setPresentationMode(ilMiniCalendarGUI::PRESENTATION_CALENDAR);
		$tpl->setVariable('MINICAL',$mini->getHTML());
		
		include_once('./Services/Calendar/classes/class.ilCalendarCategoryGUI.php');
		$cat = new ilCalendarCategoryGUI($ilUser->getId(),$this->seed);
		$tpl->setVariable('CATEGORIES', $ilCtrl->getHTML($cat));

		$this->tpl->setRightContent($tpl->get());
	}
	
	
	/**
	 * Show
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function show()
	{
		$this->tpl->addCss(ilUtil::getStyleSheetLocation('filesystem','delos.css','Services/Calendar'));
	}
	
	
	/**
	 * get tabs
	 *
	 * @access public
	 */
	protected function prepareOutput()
	{
		$rbacsystem = $this->rbacsystem;
		$ilHelp = $this->help;

		$ilHelp->setScreenIdComponent("cal");
		
		$this->tabs_gui->addTab('cal_agenda',
			$this->lng->txt("cal_agenda"),
			$this->ctrl->getLinkTargetByClass('ilCalendarInboxGUI',''));
		//$this->tabs_gui->addTarget('cal_upcoming_events_header',$this->ctrl->getLinkTargetByClass('ilCalendarInboxGUI',''));
		
		if(
			$rbacsystem->checkAccess('add_consultation_hours', ilCalendarSettings::_getInstance()->getCalendarSettingsId()) and
			ilCalendarSettings::_getInstance()->areConsultationHoursEnabled()
		)
		{
			$this->tabs_gui->addTarget('app_consultation_hours',$this->ctrl->getLinkTargetByClass('ilConsultationHoursGUI',''));
		}
		$this->tabs_gui->addTarget('cal_manage',$this->ctrl->getLinkTargetByClass('ilCalendarCategoryGUI','manage'));
		$this->tabs_gui->addTarget('settings',$this->ctrl->getLinkTargetByClass('ilCalendarUserSettingsGUI',''));
	}
	
	/**
	 * init the seed date for presentations (month view, minicalendar)
	 *
	 * @access public
	 */
	public function initSeed()
	{
		include_once('Services/Calendar/classes/class.ilDate.php');
		$this->seed = $_REQUEST['seed'] ? new ilDate($_REQUEST['seed'],IL_CAL_DATE) : new ilDate(date('Y-m-d',time()),IL_CAL_DATE);
		$_GET['seed'] = $this->seed->get(IL_CAL_DATE,'');
		$this->ctrl->saveParameter($this,array('seed'));
 	}
	
	/**
	 * Sync external calendars
	 */
	protected function synchroniseExternalCalendars()
	{
		$ilUser = $this->user;

		if(!ilCalendarSettings::_getInstance()->isWebCalSyncEnabled())
		{
			return false;
		}
		// @todo make this thread safe
		
		$limit = new ilDateTime(time(),IL_CAL_UNIX);
		$limit->increment(IL_CAL_HOUR, -1 * ilCalendarSettings::_getInstance()->getWebCalSyncHours());
		
		$cats = ilCalendarCategories::_getInstance($ilUser->getId());
		foreach($cats->getCategoriesInfo() as $cat_id => $info)
		{
			if($info['remote'])
			{
				// Check for execution
				$category = new ilCalendarCategory($cat_id);
				
				if(ilDateTime::_before($category->getRemoteSyncLastExecution(), $limit))
				{
					// update in any case to avoid multiple updates of invalid calendar sources.
					$category->setRemoteSyncLastExecution(new ilDateTime(time(),IL_CAL_UNIX));
					$category->update();

					include_once './Services/Calendar/classes/class.ilCalendarRemoteReader.php';
					$remote = new ilCalendarRemoteReader($category->getRemoteUrl());
					$remote->setUser($category->getRemoteUser());
					$remote->setPass($category->getRemotePass());
					$remote->read();
					$remote->import($category);
					break;
				}
			}
		}
	}
	
}
?>