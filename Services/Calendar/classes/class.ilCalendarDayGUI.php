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
		global $lng;
		
		$this->tpl = new ilTemplate('tpl.day_view.html',true,true,'Services/Calendar');
		
		include_once('./Services/YUI/classes/class.ilYuiUtil.php');
		ilYuiUtil::initDragDrop();
		ilYuiUtil::initPanel();
		
		include_once('Services/Calendar/classes/class.ilCalendarSchedule.php');
		$this->scheduler = new ilCalendarSchedule($this->seed,ilCalendarSchedule::TYPE_DAY);
		$this->scheduler->addSubitemCalendars(true);
		$this->scheduler->calculate();
		$daily_apps = $this->scheduler->getByDay($this->seed,$this->timezone);
		$hours = $this->parseHourInfo($daily_apps,
			$this->user_settings->getDayStart(),
			$this->user_settings->getDayEnd()
		);
		$colspan = $this->calculateColspan($hours);
		
		$navigation = new ilCalendarHeaderNavigationGUI($this,$this->seed,ilDateTime::DAY);
		$this->ctrl->setParameterByClass('ilcalendarappointmentgui','seed',$this->seed->get(IL_CAL_DATE));
		
		// add milestone link
		include_once('Services/Calendar/classes/class.ilCalendarSettings.php');
		$settings = ilCalendarSettings::_getInstance();

		if ($settings->getEnableGroupMilestones())
		{
			$this->tpl->setCurrentBlock("new_ms");
			$this->tpl->setVariable('H_NEW_MS_SRC',ilUtil::getImagePath('ms_add.gif'));
			$this->tpl->setVariable('H_NEW_MS_ALT',$this->lng->txt('cal_new_ms'));
			$this->tpl->setVariable('NEW_MS_LINK',$this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui','addMilestone'));
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setVariable('NAVIGATION',$navigation->getHTML());
		
		$this->tpl->setVariable('HEADER_DATE',$this->seed_info['mday'].' '.ilCalendarUtil::_numericMonthToString($this->seed_info['mon'],false));
		$this->tpl->setVariable('HEADER_DAY',ilCalendarUtil::_numericDayToString($this->seed_info['wday'],true));
		$this->tpl->setVariable('HCOLSPAN',$colspan - 1);
		
		$this->tpl->setVariable('H_NEW_APP_SRC',ilUtil::getImagePath('date_add.gif'));
		$this->tpl->setVariable('H_NEW_APP_ALT',$this->lng->txt('cal_new_app'));
		
		$this->tpl->setVariable('NEW_APP_LINK',$this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui','add'));
		$this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
		
		
		$this->tpl->setVariable('TXT_TIME', $lng->txt("time"));

		// show fullday events
		foreach($daily_apps as $event)
		{
			if($event['fullday'])
			{
				$this->showFulldayAppointment($event);
			}
		}
		$this->tpl->setCurrentBlock('fullday_apps');
		$this->tpl->setVariable('TXT_F_DAY', $lng->txt("cal_all_day"));
		$this->tpl->setVariable('COLSPAN',$colspan - 1);
		$this->tpl->parseCurrentBlock();
		

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
			
			if ($ilUser->prefs["screen_reader_optimization"])
			{
				$this->tpl->touchBlock('scrd_app_cell');
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

		// milestone icon
		if ($a_app['event']->isMilestone())
		{
			$this->tpl->setCurrentBlock('fullday_ms_icon');
			$this->tpl->setVariable('ALT_FD_MS', $this->lng->txt("cal_milestone"));
			$this->tpl->setVariable('SRC_FD_MS', ilUtil::getImagePath("icon_ms_s.gif"));
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock('fullday_app');
		include_once('./Services/Calendar/classes/class.ilCalendarAppointmentPanelGUI.php');
		$this->tpl->setVariable('PANEL_F_DAY_DATA',ilCalendarAppointmentPanelGUI::_getInstance()->getHTML($a_app));
		$this->tpl->setVariable('F_DAY_ID',$this->num_appointments);
		
		$compl = ($a_app['event']->isMilestone() && $a_app['event']->getCompletion() > 0)
			? " (".$a_app['event']->getCompletion()."%)"
			: "";
		$this->tpl->setVariable('F_APP_TITLE',$a_app['event']->getPresentationTitle().$compl);
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
		global $ilUser;
		
		$this->tpl->setCurrentBlock('panel_code');
		$this->tpl->setVariable('NUM',$this->num_appointments);
		$this->tpl->parseCurrentBlock();
		
		if (!$ilUser->prefs["screen_reader_optimization"])
		{
			$this->tpl->setCurrentBlock('app');
		}
		else
		{
			$this->tpl->setCurrentBlock('scrd_app');
		}

		include_once('./Services/Calendar/classes/class.ilCalendarAppointmentPanelGUI.php');
		$this->tpl->setVariable('PANEL_DATA',ilCalendarAppointmentPanelGUI::_getInstance()->getHTML($a_app));
		$this->tpl->setVariable('PANEL_NUM',$this->num_appointments);

		$this->tpl->setVariable('APP_ROWSPAN',$a_app['rowspan']);
		$this->tpl->setVariable('APP_TITLE',$a_app['event']->getPresentationTitle());

		switch($this->user_settings->getTimeFormat())
		{
			case ilCalendarSettings::TIME_FORMAT_24:
				$title = $a_app['event']->getStart()->get(IL_CAL_FKT_DATE,'H:i',$this->timezone);
				break;
				
			case ilCalendarSettings::TIME_FORMAT_12:
				$title = $a_app['event']->getStart()->get(IL_CAL_FKT_DATE,'h:ia',$this->timezone);
				break;
		}
		
		// add end time for screen readers
		if ($ilUser->prefs["screen_reader_optimization"])
		{
			switch($this->user_settings->getTimeFormat())
			{
				case ilCalendarSettings::TIME_FORMAT_24:
					$title.= "-".$a_app['event']->getEnd()->get(IL_CAL_FKT_DATE,'H:i',$this->timezone);
					break;
					
				case ilCalendarSettings::TIME_FORMAT_12:
					$title.= "-".$a_app['event']->getEnd()->get(IL_CAL_FKT_DATE,'h:ia',$this->timezone);
					break;
			}
		}
		$title .= (' '.$a_app['event']->getPresentationTitle());

		$this->tpl->setVariable('APP_TITLE',$title);

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
			global $ilUser;
			
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
			
			// set end to next hour for screen readers
			if ($ilUser->prefs["screen_reader_optimization"])
			{
				$end = $start +1;
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
					if (!$ilUser->prefs["screen_reader_optimization"])
					{
						$app['rowspan'] = $end - $start;
					}
					else  	// screen readers get always a rowspan of 1
					{
						$app['rowspan'] = 1;
					}
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
		global $ilUser;
		
		$colspan = 1;
		foreach($hours as $hour)
		{
			$colspan = max($colspan,$hour['apps_num'] + 1);
		}
		
		// screen reader: always two cols (time and event col)
		if ($ilUser->prefs["screen_reader_optimization"])
		{
			$colspan = 2;
		}
		
		return max($colspan,2);
	}
	
}
?>