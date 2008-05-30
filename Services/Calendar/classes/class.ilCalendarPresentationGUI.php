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

/** 
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ilCtrl_Calls ilCalendarPresentationGUI: ilCalendarMonthGUI, ilCalendarUserSettingsGUI, ilCalendarCategoryGUI, ilCalendarWeekGUI
* @ilCtrl_Calls ilCalendarPresentationGUI: ilCalendarAppointmentGUI, ilCalendarDayGUI
* @ingroup ServicesCalendar
*/

class ilCalendarPresentationGUI
{
	protected $ctrl;
	protected $lng;
	protected $tpl;
	protected $tabs_gui;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function __construct()
	{
		global $ilCtrl,$lng,$tpl,$ilTabs,$ilUser;
	
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->lng->loadLanguageModule('dateplaner');
		
		$this->tpl = $tpl; 	
		$this->tabs_gui = $ilTabs;
		
		
		include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');
		$cats = ilCalendarCategories::_getInstance($ilUser->getId());
		$cats->initialize(ilCalendarCategories::MODE_PERSONAL_DESKTOP);
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
		global $ilUser, $ilSetting,$tpl;


		$this->initSeed();
		$this->prepareOutput();

		$next_class = $this->getNextClass();
		switch($next_class)
		{
			case 'ilcalendarmonthgui':
				$this->tabs_gui->setSubTabActive('app_month');
				$this->forwardToClass('ilcalendarmonthgui');
				break;
				
			case 'ilcalendarweekgui':
				$this->tabs_gui->setSubTabActive('app_week');
				$this->forwardToClass('ilcalendarweekgui');
				break;

			case 'ilcalendardaygui':
				$this->tabs_gui->setSubTabActive('app_day');
				$this->forwardToClass('ilcalendardaygui');
				break;

			case 'ilcalendarusersettingsgui':
				$this->ctrl->setReturn($this,'');
				$this->tabs_gui->setSubTabActive('properties');
				$this->setCmdClass('ilcalendarusersettingsgui');
				
				include_once('./Services/Calendar/classes/class.ilCalendarUserSettingsGUI.php');
				$user_settings = new ilCalendarUserSettingsGUI();
				$this->ctrl->forwardCommand($user_settings);
				break;
				
			case 'ilcalendarappointmentgui':
				$this->ctrl->setReturn($this,'');
				$this->tabs_gui->setSubTabActive($_SESSION['cal_last_tab']);
				
				include_once('./Services/Calendar/classes/class.ilCalendarAppointmentGUI.php');
				$app = new ilCalendarAppointmentGUI($this->seed,(int) $_GET['app_id']);
				$this->ctrl->forwardCommand($app);
				break;
				
			case 'ilcalendarcategorygui':
				$this->ctrl->setReturn($this,'');
				$this->tabs_gui->setSubTabActive($_SESSION['cal_last_tab']);

				include_once('Services/Calendar/classes/class.ilCalendarCategoryGUI.php');				
				$category = new ilCalendarCategoryGUI($ilUser->getId(),$this->seed);
				$this->ctrl->forwardCommand($category);
				break;
			
			default:
				$cmd = $this->ctrl->getCmd("show");
				$this->$cmd();
				break;
		}
		
		$this->showSideBlocks();
		
		return true;
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
			return isset($_SESSION['cal_last_class']) ? $_SESSION['cal_last_class'] : 'ilcalendarmonthgui';
		}
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
		$_SESSION['cal_last_class'] = $a_class;
		switch($a_class)
		{
			case 'ilcalendarmonthgui':
				$_SESSION['cal_last_tab'] = 'app_month'; 
				$this->setCmdClass('ilcalendarmonthgui');
				include_once('./Services/Calendar/classes/class.ilCalendarMonthGUI.php');
				$month_gui = new ilCalendarMonthGUI($this->seed);
				$this->ctrl->forwardCommand($month_gui);
				break;
				
			case 'ilcalendarweekgui':
				$_SESSION['cal_last_tab'] = 'app_week'; 
				$this->setCmdClass('ilcalendarweekgui');
				include_once('./Services/Calendar/classes/class.ilCalendarWeekGUI.php');
				$week_gui = new ilCalendarWeekGUI($this->seed);
				$this->ctrl->forwardCommand($week_gui);
				break;

			case 'ilcalendardaygui':
				$_SESSION['cal_last_tab'] = 'app_day'; 
				$this->setCmdClass('ilcalendardaygui');
				include_once('./Services/Calendar/classes/class.ilCalendarDayGUI.php');
				$day_gui = new ilCalendarDayGUI($this->seed);
				$this->ctrl->forwardCommand($day_gui);
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
		$this->ctrl->setCmd('');
		$history = isset($_SESSION['cal_last_class']) ? $_SESSION['cal_last_class'] : 'ilcalendarmonthgui'; 
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
		global $ilUser;

		$tpl =  new ilTemplate('tpl.cal_side_block.html',true,true,'Services/Calendar');

		include_once('./Services/Calendar/classes/class.ilMiniCalendarGUI.php');
		$mini = new ilMiniCalendarGUI($this->seed);
		$mini->setPresentationMode(ilMiniCalendarGUI::PRESENTATION_CALENDAR);
		$tpl->setVariable('MINICAL',$mini->getHTML());
		
		include_once('./Services/Calendar/classes/class.ilCalendarCategoryGUI.php');
		$cat = new ilCalendarCategoryGUI($ilUser->getId(),$this->seed);
		$tpl->setVariable('CATEGORIES',$cat->getHTML());

		$this->tpl->setLeftContent($tpl->get());
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
		
		$this->tabs_gui->addSubTabTarget('app_day',$this->ctrl->getLinkTargetByClass('ilCalendarDayGUI',''));
		$this->tabs_gui->addSubTabTarget('app_week',$this->ctrl->getLinkTargetByClass('ilCalendarWeekGUI',''));
		$this->tabs_gui->addSubTabTarget('app_month',$this->ctrl->getLinkTargetByClass('ilCalendarMonthGUI',''));
		$this->tabs_gui->addSubTabTarget('properties',$this->ctrl->getLinkTargetByClass('ilCalendarUserSettingsGUI',''));
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
	
}
?>