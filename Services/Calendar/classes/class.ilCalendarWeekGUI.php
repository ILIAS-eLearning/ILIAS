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
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilCalendarWeekGUI: ilCalendarAppointmentGUI
*
* @ingroup ServicesCalendar 
*/

include_once('Services/Calendar/classes/class.ilDate.php');
include_once('Services/Calendar/classes/class.ilCalendarHeaderNavigationGUI.php');
include_once('Services/Calendar/classes/class.ilCalendarUserSettings.php');
include_once('Services/Calendar/classes/class.ilCalendarAppointmentColors.php');



class ilCalendarWeekGUI
{
	protected $num_appointments = 1;
	protected $seed = null;
	protected $user_settings = null;
	protected $weekdays = array();

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
		$this->seed_info = $this->seed->get(IL_CAL_FKT_GETDATE,'','UTC');

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
		$this->tpl = new ilTemplate('tpl.week_view.html',true,true,'Services/Calendar');
		
		include_once('./Services/YUI/classes/class.ilYuiUtil.php');
		ilYuiUtil::initDragDrop();
		ilYuiUtil::initPanel();
		
		
		$navigation = new ilCalendarHeaderNavigationGUI($this,$this->seed,ilDateTime::WEEK);
		$this->tpl->setVariable('NAVIGATION',$navigation->getHTML());
		
		include_once('Services/Calendar/classes/class.ilCalendarSchedule.php');
		$this->scheduler = new ilCalendarSchedule($this->seed,ilCalendarSchedule::TYPE_WEEK);
		$this->scheduler->calculate();
		
		$counter = 0;
		$hours = null;
		$all_fullday = array();
		foreach(ilCalendarUtil::_buildWeekDayList($this->seed,$this->user_settings->getWeekStart())->get() as $date)
		{
			$daily_apps = $this->scheduler->getByDay($date,$this->timezone);
			$hours = $this->parseHourInfo($daily_apps,$date,$counter,$hours);
			$this->weekdays[] = $date;
			
			$all_fullday[] = $daily_apps;
			$counter++;
		}
		
		$colspans = $this->calculateColspans($hours);
		
		// Table header
		$counter = 0;
		foreach(ilCalendarUtil::_buildWeekDayList($this->seed,$this->user_settings->getWeekStart())->get() as $date)
		{	
			$date_info = $date->get(IL_CAL_FKT_GETDATE,'','UTC');
			$this->tpl->setCurrentBlock('day_header_row');
			
			$this->ctrl->setParameterByClass('ilcalendarappointmentgui','seed',$date->get(IL_CAL_DATE));
			$this->ctrl->setParameterByClass('ilcalendardaygui','seed',$date->get(IL_CAL_DATE));
			$this->tpl->setVariable('NEW_APP_LINK',$this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui','add'));
			$this->tpl->setVariable('DAY_VIEW_LINK',$this->ctrl->getLinkTargetByClass('ilcalendardaygui',''));
			$this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
			$this->ctrl->clearParametersByClass('ilcalendardaygui');

			$this->tpl->setVariable('NEW_APP_SRC',ilUtil::getImagePath('date_add.gif'));
			$this->tpl->setVariable('NEW_APP_ALT',$this->lng->txt('cal_new_app'));

			$this->tpl->setVariable('DAY_COLSPAN',max($colspans[$counter],1));
		
			$this->tpl->setVariable('HEADER_DATE',$date_info['mday'].' '.ilCalendarUtil::_numericMonthToString($date_info['mon'],false));
			$this->tpl->setVariable('DAYNAME',ilCalendarUtil::_numericDayToString($date->get(IL_CAL_FKT_DATE,'w'),true));
			$this->tpl->parseCurrentBlock();
			$counter++;
		}
	
		// show fullday events
		$counter = 0;
		foreach($all_fullday as $daily_apps)
		{
			foreach($daily_apps as $event)
			{
				if($event['fullday'])
				{
					$this->showFulldayAppointment($event);
				}
			}
			$this->tpl->setCurrentBlock('f_day_row');
			$this->tpl->setVariable('COLSPAN',max($colspans[$counter],1));
			$this->tpl->parseCurrentBlock();
			$counter++;
		}
		
		$new_link_counter = 0;
		foreach($hours as $num_hour => $hours_per_day)
		{
			foreach($hours_per_day as $num_day => $hour)
			{
				foreach($hour['apps_start'] as $app)
				{
					$this->showAppointment($app);
				}
				#echo "NUMDAY: ".$num_day;
				#echo "COLAPANS: ".max($colspans[$num_day],1).'<br />';
				$num_apps = $hour['apps_num'];
				$colspan = max($colspans[$num_day],1);
				
				
				// Show new apointment link
				if(!$hour['apps_num'])
				{
					$this->tpl->setCurrentBlock('new_app_link');
					$this->ctrl->setParameterByClass('ilcalendarappointmentgui','seed',$this->weekdays[$num_day]->get(IL_CAL_DATE));
					$this->ctrl->setParameterByClass('ilcalendarappointmentgui','hour',$num_hour);
					$this->tpl->setVariable('DAY_NEW_APP_LINK',$this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui','add'));
					$this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
				
					$this->tpl->setVariable('DAY_NEW_APP_SRC',ilUtil::getImagePath('date_add.gif'));
					$this->tpl->setVariable('DAY_NEW_APP_ALT',$this->lng->txt('cal_new_app'));
					$this->tpl->setVariable('DAY_NEW_ID',++$new_link_counter);
					$this->tpl->parseCurrentBlock();
				}

				for($i = $colspan;$i > $hour['apps_num'];$i--)
				{
					$this->tpl->setCurrentBlock('day_cell');
					if($i == ($hour['apps_num'] + 1))
					{
						$this->tpl->setVariable('TD_CLASS','calempty calrightborder');
						#$this->tpl->setVariable('TD_STYLE',$add_style);
					}
					else
					{
						$this->tpl->setVariable('TD_CLASS','calempty');
						#$this->tpl->setVariable('TD_STYLE',$add_style);
					}
					
					if(!$hour['apps_num'])
					{
						$this->tpl->setVariable('DAY_ID',$new_link_counter);
					}
					$this->tpl->setVariable('TD_ROWSPAN',1);
					$this->tpl->parseCurrentBlock();
				}
				
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
		
		
		$this->tpl->setCurrentBLock('not_empty');

		include_once('./Services/Calendar/classes/class.ilCalendarAppointmentPanelGUI.php');
		$this->tpl->setVariable('PANEL_DATA',ilCalendarAppointmentPanelGUI::_getInstance()->getHTML($a_app));
		
		$this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
		$this->ctrl->setParameterByClass('ilcalendarappointmentgui','app_id',$a_app['event']->getEntryId());
		$this->tpl->setVariable('APP_EDIT_LINK',$this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui','edit'));
		$this->tpl->setVariable('APP_TITLE',$a_app['event']->getPresentationTitle());
		$this->tpl->setVariable('LINK_NUM',$this->num_appointments);
		$this->tpl->parseCurrentBlock();
		
		$this->tpl->setCurrentBlock('day_cell');
		$this->tpl->setVariable('DAY_CELL_NUM',$this->num_appointments);
		$this->tpl->setVariable('TD_ROWSPAN',$a_app['rowspan']);
		
		$color = $this->app_colors->getColorByAppointment($a_app['event']->getEntryId());
		$style = 'background-color: '.$color.';';
		$style .= ('color:'.ilCalendarUtil::calculateFontColor($color));
		$this->tpl->setVariable('TD_STYLE',$style);
		$this->tpl->setVariable('TD_CLASS','calevent');
		$this->tpl->parseCurrentBlock();
		
		$this->num_appointments++;

	}
	
	
	
	/**
	 * calculate overlapping hours 
	 *
	 * @access protected
	 * @return array hours
	 */
	protected function parseHourInfo($daily_apps,$date,$num_day,$hours = null,
		$morning_aggr = 7, $evening_aggr = 20)
	{
		for($i = $morning_aggr;$i <= $evening_aggr;$i++)
		{
			$hours[$i][$num_day]['apps_start'] = array();
			$hours[$i][$num_day]['apps_num'] = 0;
			switch($this->user_settings->getTimeFormat())
			{
				case ilCalendarSettings::TIME_FORMAT_24:
					if ($morning_aggr > 0 && $i == $morning_aggr)
					{
						$hours[$i][$num_day]['txt'] = sprintf('%02d:00',0)."-";
					}
					$hours[$i][$num_day]['txt'].= sprintf('%02d:00',$i);
					if ($evening_aggr < 23 && $i == $evening_aggr)
					{
						$hours[$i][$num_day]['txt'].= "-".sprintf('%02d:00',23);
					}
					break;
				
				case ilCalendarSettings::TIME_FORMAT_12:
					if ($morning_aggr > 0 && $i == $morning_aggr)
					{
						$hours[$i][$num_day]['txt'] = date('h a',mktime(0,0,0,1,1,2000))."-";
					}
					$hours[$i][$num_day]['txt'].= date('h a',mktime($i,0,0,1,1,2000));
					if ($evening_aggr < 23 && $i == $evening_aggr)
					{
						$hours[$i][$num_day]['txt'].= "-".date('h a',mktime(23,0,0,1,1,2000));
					}
					break; 					
			}
		}
		
		$date_info = $date->get(IL_CAL_FKT_GETDATE,'','UTC');
		
		
		foreach($daily_apps as $app)
		{
			// fullday appointment are not relavant
			if($app['fullday'])
			{
				continue;
			}
			// start hour for this day
			if($app['start_info']['mday'] != $date_info['mday'])
			{
				$start = 0;
			}
			else
			{
				$start = $app['start_info']['hours'];
			}
			// end hour for this day
			if($app['end_info']['mday'] != $date_info['mday'])
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
					$hours[$i][$num_day]['apps_start'][] = $app;
					$first = false;
				}
				$hours[$i][$num_day]['apps_num']++;
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
	protected function calculateColspans($hours)
	{
		foreach($hours as $hour_num => $hours_per_day)
		{
			foreach($hours_per_day as $num_day => $hour)
			{
				$colspans[$num_day] = max($colspans[$num_day],$hour['apps_num']);
			}
		}
		return $colspans;
	}
	
	
}
	
?>