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
		global $ilCtrl,$lng,$tpl,$ilTabs;
	
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->lng->loadLanguageModule('dateplaner');
		
		$this->tpl = $tpl; 	
		$this->tabs_gui = $ilTabs;
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

			case 'ilcalendarusersettingsgui':
				$this->ctrl->setReturn($this,'loadHistory');
				$this->tabs_gui->setSubTabActive('properties');
				$this->setCmdClass('ilcalendarusersettingsgui');
				
				include_once('./Services/Calendar/classes/class.ilCalendarUserSettingsGUI.php');
				$user_settings = new ilCalendarUserSettingsGUI();
				$this->ctrl->forwardCommand($user_settings);
				break;
				
			case 'ilcalendarcategorygui':
				$this->ctrl->setReturn($this,'loadHistory');
				$this->tabs_gui->setSubTabActive('app_month');

				include_once('Services/Calendar/classes/class.ilCalendarCategoryGUI.php');				
				$category = new ilCalendarCategoryGUI($ilUser->getId());
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
		if($this->ctrl->getCmdClass() != 'ilcalendarpresentationgui')
		{
			return 'ilcalendarmonthgui';
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
		switch($a_class)
		{
			case 'ilcalendarmonthgui':
				$this->setCmdClass('ilcalendarmonthgui');
				include_once('./Services/Calendar/classes/class.ilCalendarMonthGUI.php');
				$month_gui = new ilCalendarMonthGUI($this->seed);
				$this->ctrl->forwardCommand($month_gui);
				break;
				
			case 'ilcalendarweekgui':
				$this->setCmdClass('ilcalendarweekgui');
				include_once('./Services/Calendar/classes/class.ilCalendarWeekGUI.php');
				$week_gui = new ilCalendarWeekGUI($this->seed);
				$this->ctrl->forwardCommand($week_gui);
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
		$this->forwardToClass('ilcalendarmonthgui');
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
		$cat = new ilCalendarCategoryGUI($ilUser->getId());
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
		#$this->tabs_gui->addSubTabTarget('app_week',$this->ctrl->getLinkTargetByClass('ilCalendarWeekGUI',''));
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
		$this->seed = $_REQUEST['seed'] ? new ilDate($_REQUEST['seed'],IL_CAL_DATE) : new ilDate(time(),IL_CAL_UNIX);
		$this->ctrl->saveParameter($this,array('seed'));
 	}
	
}
?>