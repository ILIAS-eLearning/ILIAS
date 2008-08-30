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


/** 
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* @ilCtrl_Calls ilCalendarMonthGUI: ilCalendarAppointmentGUI
* 
* @ingroup ServicesCalendar 
*/


class ilCalendarMonthGUI
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
		$this->tabs_gui->setSubTabActive('app_month');
		
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
				$app = new ilCalendarAppointmentGUI($this->seed,(int) $_GET['app_id']);
				$this->ctrl->forwardCommand($app);
				break;
			
			default:
				$time = microtime(true);
				$cmd = $this->ctrl->getCmd("show");
				$this->$cmd();
				$tpl->setContent($this->tpl->get());
				
				#echo "Zeit: ".(microtime(true) - $time);
				break;
		}
		return true;
	}
	
	/**
	 * fill data section
	 *
	 * @access public
	 * 
	 */
	public function show()
	{
		global $tpl;

		$this->tpl = new ilTemplate('tpl.month_view.html',true,true,'Services/Calendar');
		
		include_once('./Services/YUI/classes/class.ilYuiUtil.php');
		ilYuiUtil::initDragDrop();
		ilYuiUtil::initPanel();
		
		$navigation = new ilCalendarHeaderNavigationGUI($this,$this->seed,ilDateTime::MONTH);
		$this->tpl->setVariable('NAVIGATION',$navigation->getHTML());
		
		for($i = (int) $this->user_settings->getWeekStart();$i < (7 + (int) $this->user_settings->getWeekStart());$i++)
		{
			$this->tpl->setCurrentBlock('month_header_col');
			$this->tpl->setVariable('TXT_WEEKDAY',ilCalendarUtil::_numericDayToString($i,true));
			$this->tpl->parseCurrentBlock();
		}
		
		include_once('Services/Calendar/classes/class.ilCalendarSchedule.php');
		$this->scheduler = new ilCalendarSchedule($this->seed,ilCalendarSchedule::TYPE_MONTH);
		$this->scheduler->calculate();
		
		$counter = 0;
		foreach(ilCalendarUtil::_buildMonthDayList($this->seed->get(IL_CAL_FKT_DATE,'m'),
			$this->seed->get(IL_CAL_FKT_DATE,'Y'),
			$this->user_settings->getWeekStart())->get() as $date)
		{
			$counter++;
			$this->showEvents($date);
			
			$this->tpl->setCurrentBlock('month_col');
			
			if(ilDateTime::_equals($date,$this->seed,IL_CAL_DAY))
			{
				$this->tpl->setVariable('TD_CLASS','calnow');
			}
			elseif(ilDateTime::_equals($date,$this->seed,IL_CAL_MONTH))
			{
				$this->tpl->setVariable('TD_CLASS','calstd');
			}
			elseif(ilDateTime::_before($date,$this->seed,IL_CAL_MONTH))
			{
				$this->tpl->setVariable('TD_CLASS','calprev');
			}
			else
			{
				$this->tpl->setVariable('TD_CLASS','calnext');
			}
			
			$day = $date->get(IL_CAL_FKT_DATE,'j');
			$month = $date->get(IL_CAL_FKT_DATE,'n');
			
			if($day == 1)
			{
				$month_day = '1 '.ilCalendarUtil::_numericMonthToString($month,false);
			}
			else
			{
				$month_day = $day;
			}
			
			$this->ctrl->clearParametersByClass('ilcalendardaygui');
			$this->ctrl->setParameterByClass('ilcalendardaygui','seed',$date->get(IL_CAL_DATE));
			$this->tpl->setVariable('OPEN_DAY_VIEW',$this->ctrl->getLinkTargetByClass('ilcalendardaygui',''));
			$this->ctrl->clearParametersByClass('ilcalendardaygui');
			
			$this->tpl->setVariable('MONTH_DAY',$month_day);
			//$this->tpl->setVariable('NEW_SRC',ilUtil::getImagePath('new.gif','calendar'));
			$this->tpl->setVariable('NEW_SRC',ilUtil::getImagePath('date_add.gif'));
			$this->tpl->setVariable('NEW_ALT',$this->lng->txt('cal_new_app'));
			$this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
			$this->ctrl->setParameterByClass('ilcalendarappointmentgui','seed',$date->get(IL_CAL_DATE));
			$this->tpl->setVariable('ADD_LINK',$this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui','add'));
			
			$this->tpl->setVariable('OPEN_SRC',ilUtil::getImagePath('open.gif','calendar'));
			$this->tpl->parseCurrentBlock();
			
			
			if($counter and !($counter % 7))
			{
				$this->tpl->setCurrentBlock('month_row');
				$this->tpl->parseCurrentBlock();
			}
		}
	}
	
	/**
	 * 
	 * Show events
	 *
	 * @access protected
	 */
	protected function showEvents(ilDate $date)
	{
		global $tree;
		static $counter = 1;
		
		foreach($this->scheduler->getByDay($date,$this->timezone) as $item)
		{
			$this->tpl->setCurrentBlock('panel_code');
			$this->tpl->setVariable('NUM',$counter);
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock('il_event');
			$this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
			$this->ctrl->setParameterByClass('ilcalendarappointmentgui','app_id',$item['event']->getEntryId());
			$this->tpl->setVariable('EVENT_EDIT_LINK',$this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui','edit'));
			$this->tpl->setVariable('EVENT_NUM',$item['event']->getEntryId());
			
			if($item['event']->isFullDay())
			{
				$title = $item['event']->getPresentationTitle();
			}
			else
			{
				switch($this->user_settings->getTimeFormat())
				{
					case ilCalendarSettings::TIME_FORMAT_24:
						$title = $item['event']->getStart()->get(IL_CAL_FKT_DATE,'H:i',$this->timezone);
						break;
						
					case ilCalendarSettings::TIME_FORMAT_12:
						$title = $item['event']->getStart()->get(IL_CAL_FKT_DATE,'h:ia',$this->timezone);
						break;
				}
				
				
				$title .= (' '.$item['event']->getPresentationTitle());
			}
			$this->tpl->setVariable('EVENT_TITLE',$title);
			
			// Panel variables
			$this->tpl->setVariable('PANEL_NUM',$counter);
			$this->tpl->setVariable('PANEL_TITLE',$item['event']->getTitle());
			$this->tpl->setVariable('PANEL_DETAILS',$this->lng->txt('cal_details'));
			$this->tpl->setVariable('PANEL_TXT_DATE',$this->lng->txt('date'));
			
			if($item['fullday'])
			{
				$this->tpl->setVariable('PANEL_DATE',ilDatePresentation::formatPeriod(
					new ilDate($item['dstart'],IL_CAL_UNIX),
					new ilDate($item['dend'],IL_CAL_UNIX)));
			}
			else
			{
				$this->tpl->setVariable('PANEL_DATE',ilDatePresentation::formatPeriod(
					new ilDateTime($item['dstart'],IL_CAL_UNIX),
					new ilDateTime($item['dend'],IL_CAL_UNIX)));
			}
			if($item['event']->getLocation())
			{
				$this->tpl->setVariable('PANEL_TXT_WHERE',$this->lng->txt('cal_where'));
				$this->tpl->setVariable('PANEL_WHERE',$item['event']->getLocation());
			}
			if($item['event']->getDescription())
			{
				$this->tpl->setVariable('PANEL_TXT_DESC',$this->lng->txt('description'));
				$this->tpl->setVariable('PANEL_DESC',nl2br($item['event']->getDescription()));
			}

			include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');
			$cat_id = ilCalendarCategoryAssignments::_lookupCategory($item['event']->getEntryId());
			$cat_info = ilCalendarCategories::_getInstance()->getCategoryInfo($cat_id);
			if($cat_info['type'] == ilCalendarCategory::TYPE_OBJ)
			{
				$refs = ilObject::_getAllReferences($cat_info['obj_id']);
				
				include_once('classes/class.ilLink.php');
				$href = ilLink::_getStaticLink(current($refs),ilObject::_lookupType($cat_info['obj_id']),true);
				$parent = $tree->getParentId(current($refs));
				$parent_title = ilObject::_lookupTitle(ilObject::_lookupObjId($parent));
				$this->tpl->setVariable('PANEL_TXT_LINK',$this->lng->txt('ext_link'));
				$this->tpl->setVariable('PANEL_LINK_HREF',$href);
				$this->tpl->setVariable('PANEL_LINK_NAME',ilObject::_lookupTitle($cat_info['obj_id']));
				$this->tpl->setVariable('PANEL_PARENT',$parent_title);
			}	

			$this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
			$this->ctrl->setParameterByClass('ilcalendarappointmentgui','app_id',$item['event']->getEntryId());
			$this->tpl->setVariable('PANEL_EDIT_LINK',$this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui','edit'));
			
			$color = $this->app_colors->getColorByAppointment($item['event']->getEntryId());
			$this->tpl->setVariable('EVENT_BGCOLOR',$color);
			$this->tpl->setVariable('EVENT_FONTCOLOR',ilCalendarUtil::calculateFontColor($color));
			
			$this->tpl->parseCurrentBlock();
			
			$counter++;
		}
	}
	
}


?>