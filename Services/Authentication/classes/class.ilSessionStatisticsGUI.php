<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Authentication/classes/class.ilSessionStatistics.php";

/**
* Class ilSessionStatisticsGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilLPListOfObjectsGUI.php 27489 2011-01-19 16:58:09Z jluetzen $
*
* @ingroup ServicesAuthentication
*/
class ilSessionStatisticsGUI 
{
	const MODE_TODAY = 1;
	const MODE_DAY = 2; 
	const MODE_WEEK = 3;
	const MODE_MONTH = 4;
	
	function executeCommand()
	{
		global $ilCtrl;
		
		switch($ilCtrl->getNextClass())
		{
			default:
			    $cmd = $ilCtrl->getCmd("view");
				$this->$cmd();
		}

		return true;
	}

	protected function view()
	{
		global $tpl, $ilSetting, $lng, $ilToolbar, $ilCtrl;
		
		// current mode
		if(!$_REQUEST["smd"])
		{
			$_REQUEST["smd"] = self::MODE_TODAY;
		}
		$mode = (int)$_REQUEST["smd"];
		
		// current measure
		if(!$_REQUEST["smm"])
		{
			$_REQUEST["smm"] = "avg";
		}
		$measure = (string)$_REQUEST["smm"];
		
				
		// basic data - not time related
		
		include_once "Services/Authentication/classes/class.ilSessionControl.php";
		$active = (int)ilSessionControl::getExistingSessionCount(ilSessionControl::$session_types_controlled);
		
		$control_active = ($ilSetting->get('session_handling_type', 0) == 1);
		if($control_active)
		{
			$control_max_sessions = (int)$ilSetting->get('session_max_count', ilSessionControl::DEFAULT_MAX_COUNT);
			$control_min_idle = (int)$ilSetting->get('session_min_idle', ilSessionControl::DEFAULT_MIN_IDLE);
			$control_max_idle = (int)$ilSetting->get('session_max_idle', ilSessionControl::DEFAULT_MAX_IDLE);
			$control_max_idle_first = (int)$ilSetting->get('session_max_idle_after_first_request', ilSessionControl::DEFAULT_MAX_IDLE_AFTER_FIRST_REQUEST);
		}
		
		$last_maxed_out = new ilDateTime(ilSessionStatistics::getLastMaxedOut(), IL_CAL_UNIX);
		$last_aggr = new ilDateTime(ilSessionStatistics::getLastAggregation(), IL_CAL_UNIX);
		
		
		// build left column
		
		$left = new ilTemplate("tpl.session_statistics_left.html", true, true, "Services/Authentication");
		
		$left->setVariable("CAPTION_CURRENT", $lng->txt("users_online"));
		$left->setVariable("VALUE_CURRENT", $active);
		
		$left->setVariable("CAPTION_LAST_AGGR", $lng->txt("trac_last_aggregation"));
		$left->setVariable("VALUE_LAST_AGGR", ilDatePresentation::formatDate($last_aggr));
		
		$left->setVariable("CAPTION_LAST_MAX", $lng->txt("trac_last_maxed_out_sessions"));
		$left->setVariable("VALUE_LAST_MAX", ilDatePresentation::formatDate($last_maxed_out));
		
		$left->setVariable("CAPTION_SESSION_CONTROL", $lng->txt("sess_load_dependent_session_handling"));
		if(!$control_active)
		{			
			$left->setVariable("VALUE_SESSION_CONTROL", $lng->txt("no"));
		}
		else
		{
			$left->setVariable("VALUE_SESSION_CONTROL", $lng->txt("yes"));
			
			$left->setCurrentBlock("control_details");
			
			$left->setVariable("CAPTION_SESSION_CONTROL_LIMIT", $lng->txt("session_max_count"));
			$left->setVariable("VALUE_SESSION_CONTROL_LIMIT", $control_max_sessions);
			
			$left->setVariable("CAPTION_SESSION_CONTROL_IDLE_MIN", $lng->txt("session_min_idle"));
			$left->setVariable("VALUE_SESSION_CONTROL_IDLE_MIN", $control_min_idle);
			
			$left->setVariable("CAPTION_SESSION_CONTROL_IDLE_MAX", $lng->txt("session_max_idle"));
			$left->setVariable("VALUE_SESSION_CONTROL_IDLE_MAX", $control_max_idle);
			
			$left->setVariable("CAPTION_SESSION_CONTROL_IDLE_FIRST", $lng->txt("session_max_idle_after_first_request"));
			$left->setVariable("VALUE_SESSION_CONTROL_IDLE_FIRST", $control_max_idle_first);
						
			$left->parseCurrentBlock();
		}
		
		
		$tpl->setLeftContent($left->get());
		
		
		// basic data - time related
		
		switch($mode)
		{
			case self::MODE_TODAY:
				$time_from = strtotime("today"); 
				$time_to = strtotime("tomorrow")-1;
				break;
			
			case self::MODE_DAY:				
				$time_to = time();
				$time_from = $time_to-60*60*24;
				break;		
			
			case self::MODE_WEEK:				
				$time_to = time();
				$time_from = $time_to-60*60*24*7;
				break;		
			
			case self::MODE_MONTH:				
				$time_to = time();
				$time_from = $time_to-60*60*24*30;
				break;		
		}
			
		$maxed_out_duration = round(ilSessionStatistics::getMaxedOutDuration($time_from, $time_to)/60);	
		$counters = ilSessionStatistics::getNumberOfSessionsByType($time_from, $time_to);			
		$opened = (int)$counters["opened"];
		$closed_limit = (int)$counters["closed_limit"];
		unset($counters["opened"]);
		unset($counters["closed_limit"]);
		
		
		// build center column
		
		$center = new ilTemplate("tpl.session_statistics_center.html", true, true, "Services/Authentication");
		
			
		$ilToolbar->setFormAction($ilCtrl->getFormAction($this, "view"));			
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		
		$mode_options = array(
			self::MODE_TODAY => $lng->txt("trac_session_statistics_mode_today"),
			self::MODE_DAY => $lng->txt("trac_session_statistics_mode_day"),
			self::MODE_WEEK => $lng->txt("trac_session_statistics_mode_week"),
			self::MODE_MONTH => $lng->txt("trac_session_statistics_mode_month"));
		
		$mode_selector = new ilSelectInputGUI("", "smd");
		$mode_selector->setOptions($mode_options);		
		$mode_selector->setValue($mode);
		$ilToolbar->addInputItem($mode_selector);
		
		$measure_options = array(
			"avg" => $lng->txt("trac_session_active_avg"),
			"min" => $lng->txt("trac_session_active_min"),
			"max" => $lng->txt("trac_session_active_max"));
		
		$measure_selector = new ilSelectInputGUI("", "smm");
		$measure_selector->setOptions($measure_options);		
		$measure_selector->setValue($measure);
		$ilToolbar->addInputItem($measure_selector);
		
		$ilToolbar->addFormButton($lng->txt("ok"), "view");
		
		$ilToolbar->addSeparator();		
		$ilToolbar->addButton($lng->txt("trac_sync_session_stats"),
			$ilCtrl->getLinkTarget($this, "adminSync"));
		
		
		$center->setVariable("CAPTION_MAXED_OUT_TIME", $lng->txt("trac_maxed_out_time"));
		$center->setVariable("VALUE_MAXED_OUT_TIME", $maxed_out_duration);
		$center->setVariable("CAPTION_MAXED_OUT_COUNTER", $lng->txt("trac_maxed_out_counter"));
		$center->setVariable("VALUE_MAXED_OUT_COUNTER", $closed_limit);
		$center->setVariable("CAPTION_OPENED", $lng->txt("trac_sessions_opened"));
		$center->setVariable("VALUE_OPENED", $opened);
		$center->setVariable("CAPTION_CLOSED", $lng->txt("trac_sessions_closed"));
		$center->setVariable("VALUE_CLOSED", array_sum($counters));
		
		$center->setCurrentBlock("closed_details");
		foreach($counters as $type => $counter)
		{
			$center->setVariable("CAPTION_CLOSED_DETAILS", $lng->txt("trac_".$type));
			$center->setVariable("VALUE_CLOSED_DETAILS", (int)$counter);
			$center->parseCurrentBlock();
		}
		
		
		ilDatePresentation::setUseRelativeDates(false);
		$title = $lng->txt("session_statistics")." - ".$mode_options[$mode]." (".
			ilDatePresentation::formatPeriod(new ilDateTime($time_from, IL_CAL_UNIX),
			new ilDateTime($time_to, IL_CAL_UNIX)).")";
		
		$active = ilSessionStatistics::getActiveSessions($time_from, $time_to);
		if($active)
		{
			$center->setVariable("CHART", $this->getChart($active, $title, $mode, $measure));
		}
		else
		{
			ilUtil::sendInfo($lng->txt("trac_session_statistics_no_data"));
		}
				
		$tpl->setContent($center->get());
	}
	
	/**
	 * Build chart for active sessions
	 * 
	 * @param array $a_data
	 * @param string $a_title
	 * @param int $a_mode
	 * @param string $a_measure
	 * @return string 
	 */
	function getChart($a_data, $a_title, $a_mode = self::MODE_TODAY, $a_measure = "avg")
	{
		global $lng;
		
		include_once "Services/Chart/classes/class.ilChart.php";
		$chart = new ilChart("objstacc", 700, 500);
		$chart->setYAxisToInteger(true);
		$chart->setColors(array("#3377ff", "#ff0000"));
		
		$legend = new ilChartLegend();
		$chart->setLegend($legend);

		$act_line = new ilChartData("lines");
		$act_line->setLineSteps(true);
		$act_line->setLabel($lng->txt("trac_session_active_".$a_measure));
		
		$max_line = new ilChartData("lines");
		$max_line->setLabel($lng->txt("session_max_count"));
	
		$scale = ceil(sizeof($a_data)/5);
		$labels = array();
		foreach($a_data as $idx => $item)
		{
		    $date = $item["slot_begin"];
			$value = (int)$item["active_".$a_measure];
			
			if(!($idx % ceil($scale)))
			{
				switch($a_mode)
				{
					case self::MODE_TODAY:
					case self::MODE_DAY:
						$labels[$date] = date("H:i", $date);
						break;
					
					case self::MODE_WEEK:
						$labels[$date] = date("d.m. H", $date)."h";
						break;
					
					case self::MODE_MONTH:
						$labels[$date] = date("d.m.", $date);
						break;
				}
			}
			
			$act_line->addPoint($date, $value);			
			$max_line->addPoint($date, (int)$item["max_sessions"]);
		}
		
		$chart->addData($act_line);
		$chart->addData($max_line);
		
		$chart->setTicks($labels, null, true);

		return $chart->getHTML();
	}
	
	function adminSync()
	{
		global $ilCtrl, $lng;
		
		ilSessionStatistics::aggretateRaw(time());
		
		ilUtil::sendSuccess($lng->txt("trac_sync_session_stats_success"), true);
		$ilCtrl->redirect($this);
	}
}

?>