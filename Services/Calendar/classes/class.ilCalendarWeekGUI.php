<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilCalendarWeekGUI: ilCalendarAppointmentGUI
* @ilCtrl_Calls ilCalendarWeekGUI: ilCalendarAppointmentPresentationGUI
*
* @ingroup ServicesCalendar 
*/

include_once('Services/Calendar/classes/class.ilDate.php');
include_once('Services/Calendar/classes/class.ilCalendarHeaderNavigationGUI.php');
include_once('Services/Calendar/classes/class.ilCalendarUserSettings.php');
include_once('Services/Calendar/classes/class.ilCalendarAppointmentColors.php');
include_once('Services/Calendar/classes/class.ilCalendarViewGUI.php');


class ilCalendarWeekGUI extends ilCalendarViewGUI
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
		//$DIC elements initialization
		$this->initialize(ilCalendarViewGUI::CAL_PRESENTATION_WEEK);

		$this->seed = $seed_date;
		$this->seed_info = $this->seed->get(IL_CAL_FKT_GETDATE,'','UTC');
		
		$this->user_settings = ilCalendarUserSettings::_getInstanceByUserId($this->user->getId());
		$this->app_colors = new ilCalendarAppointmentColors($this->user->getId());
		
		$this->timezone = $this->user->getTimeZone();
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

		$this->ctrl->saveParameter($this,'seed');

		$next_class = $ilCtrl->getNextClass();
		switch($next_class)
		{
			case "ilcalendarappointmentpresentationgui":
				$this->ctrl->setReturn($this, "");
				include_once("./Services/Calendar/classes/class.ilCalendarAppointmentPresentationGUI.php");
				$gui = ilCalendarAppointmentPresentationGUI::_getInstance(new ilDate($this->seed, IL_CAL_DATE), $this->getCurrentApp());
				$this->ctrl->forwardCommand($gui);
				break;
			case 'ilcalendarappointmentgui':
				$this->ctrl->setReturn($this,'');
				$this->tabs_gui->setSubTabActive($_SESSION['cal_last_tab']);
				
				// initial date for new calendar appointments
				$idate = new ilDate($_REQUEST['idate'], IL_CAL_DATE);

				include_once('./Services/Calendar/classes/class.ilCalendarAppointmentGUI.php');
				$app = new ilCalendarAppointmentGUI($this->seed,$idate,(int) $_GET['app_id']);
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
		global $ilUser, $lng;
		
		
		// config
		$raster = 15;	
		if($this->user_settings->getDayStart())
		{
			// push starting point to last "slot" of hour BEFORE morning aggregation
			$morning_aggr = ($this->user_settings->getDayStart()-1)*60+(60-$raster);
		}
		else
		{
			$morning_aggr = 0;
		}
		$evening_aggr = $this->user_settings->getDayEnd()*60;
		
		
		$this->tpl = new ilTemplate('tpl.week_view.html',true,true,'Services/Calendar');
		
		include_once('./Services/YUI/classes/class.ilYuiUtil.php');
		ilYuiUtil::initDragDrop();
		
		$navigation = new ilCalendarHeaderNavigationGUI($this,$this->seed,ilDateTime::WEEK);
		$this->tpl->setVariable('NAVIGATION',$navigation->getHTML());

		if(isset($_GET["bkid"]))
		{
			$user_id = $_GET["bkid"];
			$disable_empty = true;
			$no_add = true;
		}
		elseif($ilUser->getId() == ANONYMOUS_USER_ID)
		{
			$user_id = $ilUser->getId();
			$disable_empty = false;
			$no_add = true;
		}
		else
		{
			$user_id = $ilUser->getId();
			$disable_empty = false;
			$no_add = false;
		}
		include_once('Services/Calendar/classes/class.ilCalendarSchedule.php');
		$this->scheduler = new ilCalendarSchedule($this->seed,ilCalendarSchedule::TYPE_WEEK,$user_id,$disable_empty);
		$this->scheduler->addSubitemCalendars(true);		
		$this->scheduler->calculate();
		
		$counter = 0;
		$hours = null;
		$all_fullday = array();
		foreach(ilCalendarUtil::_buildWeekDayList($this->seed,$this->user_settings->getWeekStart())->get() as $date)
		{
			$daily_apps = $this->scheduler->getByDay($date,$this->timezone);
			$hours = $this->parseHourInfo($daily_apps,$date,$counter,$hours,
				$morning_aggr,
				$evening_aggr,
				$raster
			);
			$this->weekdays[] = $date;

			$num_apps[$date->get(IL_CAL_DATE)] = count($daily_apps);
			
			$all_fullday[] = $daily_apps;
			$counter++;
		}

		$colspans = $this->calculateColspans($hours);

		include_once('Services/Calendar/classes/class.ilCalendarSettings.php');
		$settings = ilCalendarSettings::_getInstance();
		
		include_once "Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php";

		// Table header
		$counter = 0;
		foreach(ilCalendarUtil::_buildWeekDayList($this->seed,$this->user_settings->getWeekStart())->get() as $date)
		{	
			$date_info = $date->get(IL_CAL_FKT_GETDATE,'','UTC');
			$this->ctrl->setParameterByClass('ilcalendarappointmentgui','seed',$date->get(IL_CAL_DATE));
			$this->ctrl->setParameterByClass('ilcalendarappointmentgui','idate',$date->get(IL_CAL_DATE));
			$this->ctrl->setParameterByClass('ilcalendardaygui','seed',$date->get(IL_CAL_DATE));

			if(!$no_add)
			{
				$new_app_url = $this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui','add');
				
				if ($settings->getEnableGroupMilestones())
				{
					$new_ms_url = $this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui','addMilestone');
					
					$this->tpl->setCurrentBlock("new_ms");
					$this->tpl->setVariable('DD_ID', $date->get(IL_CAL_UNIX));
					$this->tpl->setVariable('DD_TRIGGER', ilGlyphGUI::get(ilGlyphGUI::ADD));					
					$this->tpl->setVariable('URL_DD_NEW_APP', $new_app_url);					
					$this->tpl->setVariable('TXT_DD_NEW_APP', $this->lng->txt('cal_new_app'));					
					$this->tpl->setVariable('URL_DD_NEW_MS', $new_ms_url);					
					$this->tpl->setVariable('TXT_DD_NEW_MS', $this->lng->txt('cal_new_ms'));					
					$this->tpl->parseCurrentBlock();					
				}
				else
				{
					$this->tpl->setCurrentBlock("new_app");
					$this->tpl->setVariable('NEW_APP_LINK',$new_app_url);					
					$this->tpl->setVariable('NEW_APP_SRC',ilGlyphGUI::get(ilGlyphGUI::ADD, $this->lng->txt('cal_new_app')));
					// $this->tpl->setVariable('NEW_APP_ALT',$this->lng->txt('cal_new_app'));
					$this->tpl->parseCurrentBlock();
				}
								
				$this->ctrl->clearParametersByClass('ilcalendarappointmentgui');				
			}

			$dayname = ilCalendarUtil::_numericDayToString($date->get(IL_CAL_FKT_DATE,'w'),true);
			$daydate = $date_info['mday'].' '.ilCalendarUtil::_numericMonthToString($date_info['mon'],false);

			if(!$disable_empty || $num_apps[$date->get(IL_CAL_DATE)] > 0)
			{
				$link = $this->ctrl->getLinkTargetByClass('ilcalendardaygui','');
				$this->ctrl->clearParametersByClass('ilcalendardaygui');

				$this->tpl->setCurrentBlock("day_view1_link");
				$this->tpl->setVariable('HEADER_DATE',$daydate);
				$this->tpl->setVariable('DAY_VIEW_LINK',$link);
				$this->tpl->parseCurrentBlock();

				$this->tpl->setCurrentBlock("day_view2_link");
				$this->tpl->setVariable('DAYNAME',$dayname);
				$this->tpl->setVariable('DAY_VIEW_LINK',$link);
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock("day_view1_no_link");
				$this->tpl->setVariable('HEADER_DATE',$daydate);
				$this->tpl->parseCurrentBlock();

				$this->tpl->setCurrentBlock("day_view2_no_link");
				$this->tpl->setVariable('DAYNAME',$dayname);
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock('day_header_row');
			$this->tpl->setVariable('DAY_COLSPAN',max($colspans[$counter],1));
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
		$this->tpl->setCurrentBlock('fullday_apps');
		$this->tpl->setVariable('TXT_F_DAY', $lng->txt("cal_all_day"));
		$this->tpl->parseCurrentBlock();
		
		$new_link_counter = 0;
		foreach($hours as $num_hour => $hours_per_day)
		{		
			$first = true;
			foreach($hours_per_day as $num_day => $hour)
			{		
				if($first)
				{
					if(!($num_hour%60) || ($num_hour == $morning_aggr && $morning_aggr) || 
					($num_hour == $evening_aggr && $evening_aggr))
					{		
						$first = false;
						
						// aggregation rows 
						if(($num_hour == $morning_aggr && $morning_aggr) || 
							($num_hour == $evening_aggr && $evening_aggr))
						{
							$this->tpl->setVariable('TIME_ROWSPAN', 1);
						}
						// rastered hour
						else
						{
							$this->tpl->setVariable('TIME_ROWSPAN', 60/$raster);
						}

						$this->tpl->setCurrentBlock('time_txt');

						$this->tpl->setVariable('TIME',$hour['txt']);
						$this->tpl->parseCurrentBlock();			
					}
				}				
				
				foreach($hour['apps_start'] as $app)
				{
					$this->showAppointment($app);
				}
				
				// screen reader: appointments are divs, now output cell
				if ($ilUser->prefs["screen_reader_optimization"])
				{
					$this->tpl->setCurrentBlock('scrd_day_cell');
					$this->tpl->setVariable('TD_CLASS','calstd');
					$this->tpl->parseCurrentBlock();
				}

								
				#echo "NUMDAY: ".$num_day;
				#echo "COLAPANS: ".max($colspans[$num_day],1).'<br />';
				$num_apps = $hour['apps_num'];
				$colspan = max($colspans[$num_day],1);
				
				
				// Show new apointment link
				if(!$hour['apps_num'] && !$ilUser->prefs["screen_reader_optimization"] && !$no_add)
				{
					$this->tpl->setCurrentBlock('new_app_link');
					$this->ctrl->setParameterByClass('ilcalendarappointmentgui','idate',$this->weekdays[$num_day]->get(IL_CAL_DATE));
					$this->ctrl->setParameterByClass('ilcalendarappointmentgui','seed',$this->seed->get(IL_CAL_DATE));
					$this->ctrl->setParameterByClass('ilcalendarappointmentgui','hour',floor($num_hour/60));
					$this->tpl->setVariable('DAY_NEW_APP_LINK',$this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui','add'));
					$this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
				
					$this->tpl->setVariable('DAY_NEW_APP_SRC', ilGlyphGUI::get(ilGlyphGUI::ADD, $this->lng->txt('cal_new_app')));
					$this->tpl->setVariable('DAY_NEW_APP_ALT',$this->lng->txt('cal_new_app'));
					$this->tpl->setVariable('DAY_NEW_ID',++$new_link_counter);
					$this->tpl->parseCurrentBlock();
				}

				for($i = $colspan;$i > $hour['apps_num'];$i--)
				{
					if ($ilUser->prefs["screen_reader_optimization"])
					{
						continue;
					}
					$this->tpl->setCurrentBlock('day_cell');
					
					// last "slot" of hour needs border
					$empty_border = '';
					if($num_hour%60 == 60-$raster || 
						($num_hour == $morning_aggr && $morning_aggr) || 
						($num_hour == $evening_aggr && $evening_aggr))
					{
						$empty_border = ' calempty_border';						
					}

					if($i == ($hour['apps_num'] + 1))
					{
						$this->tpl->setVariable('TD_CLASS','calempty calrightborder'.$empty_border);
						#$this->tpl->setVariable('TD_STYLE',$add_style);
					}
					else
					{
						$this->tpl->setVariable('TD_CLASS','calempty'.$empty_border);
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
			
			$this->tpl->touchBlock('time_row');			
		}
		
		$this->tpl->setVariable("TXT_TIME", $lng->txt("time"));
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
		$f = $this->ui_factory;
		$r = $this->ui_renderer;
		
		// milestone icon
		if ($a_app['event']->isMilestone())
		{
			$this->tpl->setCurrentBlock('fullday_ms_icon');
			$this->tpl->setVariable('ALT_FD_MS', $this->lng->txt("cal_milestone"));
			$this->tpl->setVariable('SRC_FD_MS', ilUtil::getImagePath("icon_ms.svg"));
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock('fullday_app');
		
		$compl = ($a_app['event']->isMilestone() && $a_app['event']->getCompletion() > 0)
			? " (".$a_app['event']->getCompletion()."%)"
			: "";

		//plugins can change the modal title.
		$modal_title = $this->getModalTitleByPlugins();
		$shy = $this->getAppointmentShyButton($a_app['event'], $a_app['dstart'], "", $modal_title);

		$title = ($new_title = $this->getContentByPlugins($a_app['event'], $a_app['dstart'], $shy))? $new_title : $shy;

		$this->tpl->setVariable('F_APP_TITLE',$title.$compl);

		$color = $this->app_colors->getColorByAppointment($a_app['event']->getEntryId());
		$font_color = ilCalendarUtil::calculateFontColor($color);

		$this->tpl->setVariable('F_APP_BGCOLOR',$color);
		$this->tpl->setVariable('F_APP_FONTCOLOR',$font_color);
		
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
		$ilUser = $this->user;
		$f = $this->ui_factory;
		$r = $this->ui_renderer;
		
		if (!$ilUser->prefs["screen_reader_optimization"])
		{
			$this->tpl->setCurrentBLock('not_empty');
		}
		else
		{
			$this->tpl->setCurrentBLock('scrd_not_empty');
		}
		
		$this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
		$this->ctrl->setParameterByClass('ilcalendarappointmentgui','app_id',$a_app['event']->getEntryId());
		$this->tpl->setVariable('APP_EDIT_LINK',$this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui','edit'));

		$color = $this->app_colors->getColorByAppointment($a_app['event']->getEntryId());
		$style = 'background-color: '.$color.';';
		$style .= ('color:'.ilCalendarUtil::calculateFontColor($color));
		$td_style = $style;

		
		if(!$a_app['event']->isFullDay())
		{
			switch($this->user_settings->getTimeFormat())
			{
				case ilCalendarSettings::TIME_FORMAT_24:
					$time = $a_app['event']->getStart()->get(IL_CAL_FKT_DATE,'H:i',$this->timezone);
					break;
					
				case ilCalendarSettings::TIME_FORMAT_12:
					$time = $a_app['event']->getStart()->get(IL_CAL_FKT_DATE,'h:ia',$this->timezone);
					break;
			}
			// add end time for screen readers
			if ($ilUser->prefs["screen_reader_optimization"])
			{
				switch($this->user_settings->getTimeFormat())
				{
					case ilCalendarSettings::TIME_FORMAT_24:
						$time.= "-".$a_app['event']->getEnd()->get(IL_CAL_FKT_DATE,'H:i',$this->timezone);
						break;
						
					case ilCalendarSettings::TIME_FORMAT_12:
						$time.= "-".$a_app['event']->getEnd()->get(IL_CAL_FKT_DATE,'h:ia',$this->timezone);
						break;
				}
			}

			$td_style .= $a_app['event']->getPresentationStyle();
		}
		//plugins can change the modal title.
		$modal_title = $this->getModalTitleByPlugins();
		$shy = $this->getAppointmentShyButton($a_app['event'], $a_app['dstart'], "", $modal_title);

		$title = ($time != "")? $time." ".$shy : $shy;

		//calendar plugins
		$title = ($new_title = $this->getContentByPlugins($a_app['event'], $a_app['dstart'], $shy))? $new_title : $title;

		$this->tpl->setVariable('APP_TITLE', $title);
		$this->tpl->setVariable('LINK_NUM',$this->num_appointments);
		
		$this->tpl->setVariable('LINK_STYLE',$style);

		
		if (!$ilUser->prefs["screen_reader_optimization"])
		{
			// provide table cell attributes
			$this->tpl->parseCurrentBlock();
			
			$this->tpl->setCurrentBlock('day_cell');
		
			$this->tpl->setVariable('DAY_CELL_NUM',$this->num_appointments);
			$this->tpl->setVariable('TD_ROWSPAN',$a_app['rowspan']);
			$this->tpl->setVariable('TD_STYLE',$td_style);
			$this->tpl->setVariable('TD_CLASS','calevent');
		
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			// screen reader: work on div attributes
			$this->tpl->setVariable('DIV_STYLE',$style);
			$this->tpl->parseCurrentBlock();
		}
		
		$this->num_appointments++;

	}
	
	/**
	 * calculate overlapping hours 
	 *
	 * @access protected
	 * @return array hours
	 */
	protected function parseHourInfo($daily_apps,$date,$num_day,$hours = null,
		$morning_aggr, $evening_aggr, $raster)
	{
		global $ilUser;
		
		for($i = $morning_aggr;$i <= $evening_aggr;$i+=$raster)
		{
			$hours[$i][$num_day]['apps_start'] = array();
			$hours[$i][$num_day]['apps_num'] = 0;
			switch($this->user_settings->getTimeFormat())
			{
				case ilCalendarSettings::TIME_FORMAT_24:
					if ($morning_aggr > 0 && $i == $morning_aggr)
					{
						$hours[$i][$num_day]['txt'] = sprintf('%02d:00',0)."-".
							sprintf('%02d:00',ceil(($i+1)/60));
					}
					else
					{
						$hours[$i][$num_day]['txt'].= sprintf('%02d:%02d',floor($i/60),$i%60);
					}
					if ($evening_aggr < 23*60 && $i == $evening_aggr)
					{
						$hours[$i][$num_day]['txt'].= "-".sprintf('%02d:00',23);
					}
					break;
				
				case ilCalendarSettings::TIME_FORMAT_12:
					if ($morning_aggr > 0 && $i == $morning_aggr)
					{
						$hours[$i][$num_day]['txt'] = date('h a',mktime(0,0,0,1,1,2000))."-";
					}
					$hours[$i][$num_day]['txt'].= date('h a',mktime(floor($i/60),$i%60,0,1,1,2000));
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
				$start = $app['start_info']['hours']*60+$app['start_info']['minutes'];
			}
			// end hour for this day
			if($app['end_info']['mday'] != $date_info['mday'])
			{
				$end = 23*60;
			}
			elseif($app['start_info']['hours'] == $app['end_info']['hours'])
			{
				$end = $start+$raster;
			}
			
			else
			{
				$end = $app['end_info']['hours']*60+$app['end_info']['minutes'];
			}
			
			// set end to next hour for screen readers
			if ($ilUser->prefs["screen_reader_optimization"])
			{
				$end = $start+$raster;
			}
			
			if ($start < $morning_aggr)
			{
				$start = $morning_aggr;
			}
			if ($end <= $morning_aggr)
			{
				$end = $morning_aggr+$raster;
			}
			if ($start > $evening_aggr)
			{
				$start = $evening_aggr;
			}
			if ($end > $evening_aggr+$raster)
			{
				$end = $evening_aggr+$raster;
			}
			if ($end <= $start)
			{
				$end = $start+$raster;
			}
			
			// map start and end to raster
			$start = floor($start/$raster)*$raster;
			$end = ceil($end/$raster)*$raster;

			$first = true;
			for($i = $start;$i < $end;$i+=$raster)
			{
				if($first)
				{
					if (!$ilUser->prefs["screen_reader_optimization"])
					{
						$app['rowspan'] = ceil(($end - $start)/$raster);	
					}
					else  	// screen readers get always a rowspan of 1
					{
						$app['rowspan'] = 1;
					}
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
		global $ilUser;
		
		foreach($hours as $hour_num => $hours_per_day)
		{
			foreach($hours_per_day as $num_day => $hour)
			{
				$colspans[$num_day] = max($colspans[$num_day],$hour['apps_num']);
				
				// screen reader: always one col
				if ($ilUser->prefs["screen_reader_optimization"])
				{
					$colspans[$num_day] = 1;
				}
			}
		}
		return $colspans;
	}
	
}