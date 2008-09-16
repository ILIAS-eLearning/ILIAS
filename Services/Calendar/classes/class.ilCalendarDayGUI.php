<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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
* Presentation day view
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
* 
* @ilCtrl_Calls ilCalendarDayGUI: ilCalendarAppointmentGUI
* @ingroup ServicesCalendar 
*/

include_once('./Services/Calendar/classes/class.ilDate.php');
include_once('./Services/Calendar/classes/class.ilCalendarUtil.php');
include_once('./Services/Calendar/classes/class.ilCalendarHeaderNavigationGUI.php');
include_once('./Services/Calendar/classes/class.ilCalendarUserSettings.php');
include_once('./Services/Calendar/classes/class.ilCalendarAppointmentColors.php');


class ilCalendarDayGUI
{
	protected $seed = null;
	protected $seed_info = array();
	protected $user_settings = null;

	protected $lng;
	protected $ctrl;
	protected $tabs_gui;
	protected $tpl;
	
	protected $num_appointments = 1; 
	
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
		$this->seed_info = $this->seed->get(IL_CAL_FKT_GETDATE);

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
				$app = new ilCalendarAppointmentGUI($this->seed,(int) $_GET['app_id']);
				$this->ctrl->forwardCommand($app);
				break;
			
			default:
				$cmd = $this->ctrl->getCmd("show");
				$this->$cmd();
				$tpl->setContent($this->tpl->get());
				break;
		}
		return true;
	}
	
	/**
	 * fill data section
	 *
	 * @access protected
	 * 
	 */
	protected function show()
	{
		$this->tpl = new ilTemplate('tpl.day_view.html',true,true,'Services/Calendar');
		
		include_once('./Services/YUI/classes/class.ilYuiUtil.php');
		ilYuiUtil::initDragDrop();
		ilYuiUtil::initPanel();
		
		
		$navigation = new ilCalendarHeaderNavigationGUI($this,$this->seed,ilDateTime::DAY);
		$this->tpl->setVariable('NAVIGATION',$navigation->getHTML());
		
		$this->tpl->setVariable('HEADER_DATE',$this->seed_info['mday'].' '.ilCalendarUtil::_numericMonthToString($this->seed_info['mon'],false));
		$this->tpl->setVariable('HEADER_DAY',ilCalendarUtil::_numericDayToString($this->seed_info['wday'],true));
		
		$this->tpl->setVariable('H_NEW_APP_SRC',ilUtil::getImagePath('date_add.gif'));
		$this->tpl->setVariable('H_NEW_APP_ALT',$this->lng->txt('cal_new_app'));
		
		
		$this->ctrl->setParameterByClass('ilcalendarappointmentgui','seed',$this->seed->get(IL_CAL_DATE));
		$this->tpl->setVariable('NEW_APP_LINK',$this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui','add'));
		$this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
		
		include_once('Services/Calendar/classes/class.ilCalendarSchedule.php');
		$this->scheduler = new ilCalendarSchedule($this->seed,ilCalendarSchedule::TYPE_DAY);
		$this->scheduler->calculate();
		
		$daily_apps = $this->scheduler->getByDay($this->seed,$this->timezone);
		
		$hours = $this->parseHourInfo($daily_apps);
		$colspan = $this->calculateColspan($hours);

		$this->tpl->setVariable('COLSPAN',$colspan);

		// show fullday events
		foreach($daily_apps as $event)
		{
			if($event['fullday'])
			{
				$this->showFulldayAppointment($event);
			}
		}

		// parse the hour rows
		foreach($hours as $numeric => $hour)
		{
			$this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
			$this->ctrl->setParameterByClass('ilcalendarappointmentgui','seed',$this->seed->get(IL_CAL_DATE));
			$this->ctrl->setParameterByClass('ilcalendarappointmentgui','hour',$numeric);
			$this->tpl->setVariable('NEW_APP_HOUR_LINK',$this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui','add'));
			
			$this->tpl->setVariable('NEW_APP_SRC',ilUtil::getImagePath('date_add.gif'));
			$this->tpl->setVariable('NEW_APP_ALT',$this->lng->txt('cal_new_app'));
			

			foreach($hour['apps_start'] as $app)
			{
				$this->showAppointment($app);
			}
			for($i = ($colspan - 1);$i > $hour['apps_num'];$i--)
			{
				$this->tpl->touchBlock('empty_cell');
			}
			$this->tpl->setCurrentBlock('time_row');
			$this->tpl->setVariable('TIME',$hour['txt']);
			$this->tpl->parseCurrentBlock();
			
		}
	}
	
	/**
	 * show fullday appointment
	 *
	 * @access protected
	 * @param array appointment
	 * @return
	 */
	protected function showFulldayAppointment($a_app)
	{
		$this->tpl->setCurrentBlock('panel_code');
		$this->tpl->setVariable('NUM',$this->num_appointments);
		$this->tpl->parseCurrentBlock();


		$this->tpl->setCurrentBlock('fullday_app');
		
		include_once('./Services/Calendar/classes/class.ilCalendarAppointmentPanelGUI.php');
		$this->tpl->setVariable('PANEL_F_DAY_DATA',ilCalendarAppointmentPanelGUI::_getInstance()->getHTML($a_app));
		$this->tpl->setVariable('F_DAY_ID',$this->num_appointments);
		
		$this->tpl->setVariable('F_APP_TITLE',$a_app['event']->getPresentationTitle());
		$color = $this->app_colors->getColorByAppointment($a_app['event']->getEntryId());
		$this->tpl->setVariable('F_APP_BGCOLOR',$color);
		$this->tpl->setVariable('F_APP_FONTCOLOR',ilCalendarUtil::calculateFontColor($color));
		
		$this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
		$this->ctrl->setParameterByClass('ilcalendarappointmentgui','seed',$this->seed->get(IL_CAL_DATE));
		$this->ctrl->setParameterByClass('ilcalendarappointmentgui','app_id',$a_app['event']->getEntryId());
		$this->tpl->setVariable('F_APP_EDIT_LINK',$this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui','edit'));
		
		$this->tpl->parseCurrentBlock();
		
		
		$this->num_appointments++;
	}
	
	/**
	 * show appointment
	 *
	 * @access protected
	 * @param array appointment
	 */
	protected function showAppointment($a_app)
	{
		$this->tpl->setCurrentBlock('panel_code');
		$this->tpl->setVariable('NUM',$this->num_appointments);
		$this->tpl->parseCurrentBlock();
		
		
		
		$this->tpl->setCurrentBlock('app');

		include_once('./Services/Calendar/classes/class.ilCalendarAppointmentPanelGUI.php');
		$this->tpl->setVariable('PANEL_DATA',ilCalendarAppointmentPanelGUI::_getInstance()->getHTML($a_app));
		$this->tpl->setVariable('PANEL_NUM',$this->num_appointments);

		$this->tpl->setVariable('APP_ROWSPAN',$a_app['rowspan']);
		$this->tpl->setVariable('APP_TITLE',$a_app['event']->getPresentationTitle());

		$color = $this->app_colors->getColorByAppointment($a_app['event']->getEntryId());
		$this->tpl->setVariable('APP_BGCOLOR',$color);
		$this->tpl->setVariable('APP_COLOR',ilCalendarUtil::calculateFontColor($color));
		
		$this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
		$this->ctrl->setParameterByClass('ilcalendarappointmentgui','seed',$this->seed->get(IL_CAL_DATE));
		$this->ctrl->setParameterByClass('ilcalendarappointmentgui','app_id',$a_app['event']->getEntryId());
		$this->tpl->setVariable('APP_EDIT_LINK',$this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui','edit'));
		
		$this->tpl->parseCurrentBlock();
		
		$this->num_appointments++;
	}
	
	/**
	 * calculate overlapping hours 
	 *
	 * @access protected
	 * @return array hours
	 */
	protected function parseHourInfo($daily_apps, $morning_aggr = 7,
		$evening_aggr = 20)
	{
		for($i = $morning_aggr;$i <= $evening_aggr;$i++)
		{
			$hours[$i]['apps_start'] = array();
			$hours[$i]['apps_num'] = 0;
	
			switch($this->user_settings->getTimeFormat())
			{
				case ilCalendarSettings::TIME_FORMAT_24:
					if ($morning_aggr > 0 && $i == $morning_aggr)
					{
						$hours[$i]['txt'] = sprintf('%02d:00',0)."-";
					}
					$hours[$i]['txt'].= sprintf('%02d:00',$i);
					if ($evening_aggr < 23 && $i == $evening_aggr)
					{
						$hours[$i]['txt'].= "-".sprintf('%02d:00',23);
					}
					break;
				
				case ilCalendarSettings::TIME_FORMAT_12:
					if ($morning_aggr > 0 && $i == $morning_aggr)
					{
						$hours[$i]['txt'] = date('h a',mktime(0,0,0,1,1,2000))."-";
					}
					$hours[$i]['txt'] = date('h a',mktime($i,0,0,1,1,2000));
					if ($evening_aggr < 23 && $i == $evening_aggr)
					{
						$hours[$i]['txt'].= "-".date('h a',mktime(23,0,0,1,1,2000));
					}
					break; 					
			}
		}
		
		
		foreach($daily_apps as $app)
		{
			// fullday appointment are not relavant
			if($app['fullday'])
			{
				continue;
			}
			// start hour for this day
			if($app['start_info']['mday'] != $this->seed_info['mday'])
			{
				$start = 0;
			}
			else
			{
				$start = $app['start_info']['hours'];
			}
			// end hour for this day
			if($app['end_info']['mday'] != $this->seed_info['mday'])
			{
				$end = 23;
			}
			elseif($app['start_info']['hours'] == $app['end_info']['hours'])
			{
				$end = $start +1;
			}
			else
			{
				$end = $app['end_info']['hours'];
			}
			if ($start < $morning_aggr)
			{
				$start = $morning_aggr;
			}
			if ($end <= $morning_aggr)
			{
				$end = $morning_aggr+1;
			}
			if ($start > $evening_aggr)
			{
				$start = $evening_aggr;
			}
			if ($end > $evening_aggr+1)
			{
				$end = $evening_aggr+1;
			}
			if ($end <= $start)
			{
				$end = $start + 1;
			}
			
			$first = true;
			for($i = $start;$i < $end;$i++)
			{
				if($first)
				{
					$app['rowspan'] = $end - $start;
					$hours[$i]['apps_start'][] = $app;
					$first = false;
				}
				$hours[$i]['apps_num']++;
			}
		}
		return $hours;
	}
	
	/**
	 * calculate colspan
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function calculateColspan($hours)
	{
		$colspan = 1;
		foreach($hours as $hour)
		{
			$colspan = max($colspan,$hour['apps_num'] + 1);
		}
		return max($colspan,2);
	}
	
}
?>