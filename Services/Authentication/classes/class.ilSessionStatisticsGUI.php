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
    const MODE_LAST_DAY = 2;
    const MODE_LAST_WEEK = 3;
    const MODE_LAST_MONTH = 4;
    const MODE_DAY = 5;
    const MODE_WEEK = 6;
    const MODE_MONTH = 7;
    const MODE_YEAR = 8;
    
    const SCALE_DAY = 1;
    const SCALE_WEEK = 2;
    const SCALE_MONTH = 3;
    const SCALE_YEAR = 4;
    const SCALE_PERIODIC_WEEK = 5;
    
    public function executeCommand()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        
        $this->setSubTabs();
        
        switch ($ilCtrl->getNextClass()) {
            default:
                $cmd = $ilCtrl->getCmd("current");
                $this->$cmd();
        }

        return true;
    }
    
    protected function setSubTabs()
    {
        global $DIC;

        $ilTabs = $DIC['ilTabs'];
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        
        $ilTabs->addSubTab(
            "current",
            $lng->txt("trac_current_system_load"),
            $ilCtrl->getLinkTarget($this, "current")
        );
        $ilTabs->addSubTab(
            "short",
            $lng->txt("trac_short_system_load"),
            $ilCtrl->getLinkTarget($this, "short")
        );
        $ilTabs->addSubTab(
            "long",
            $lng->txt("trac_long_system_load"),
            $ilCtrl->getLinkTarget($this, "long")
        );
        $ilTabs->addSubTab(
            "periodic",
            $lng->txt("trac_periodic_system_load"),
            $ilCtrl->getLinkTarget($this, "periodic")
        );
    }
    
    protected function current($a_export = false)
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        $ilToolbar = $DIC['ilToolbar'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilTabs = $DIC['ilTabs'];
        $lng = $DIC['lng'];
        
        $ilTabs->activateSubTab("current");
        
        // current mode
        if (!$_REQUEST["smd"]) {
            $_REQUEST["smd"] = self::MODE_TODAY;
        }
        $mode = (int) $_REQUEST["smd"];
        
        // current measure
        if (!$_REQUEST["smm"]) {
            $_REQUEST["smm"] = "avg";
        }
        $measure = (string) $_REQUEST["smm"];
        
                
        switch ($mode) {
            case self::MODE_TODAY:
                $time_from = strtotime("today");
                $time_to = strtotime("tomorrow") - 1;
                $scale = self::SCALE_DAY;
                break;
            
            case self::MODE_LAST_DAY:
                $time_to = time();
                $time_from = $time_to - 60 * 60 * 24;
                $scale = self::SCALE_DAY;
                break;
            
            case self::MODE_LAST_WEEK:
                $time_to = time();
                $time_from = $time_to - 60 * 60 * 24 * 7;
                $scale = self::SCALE_WEEK;
                break;
            
            case self::MODE_LAST_MONTH:
                $time_to = time();
                $time_from = $time_to - 60 * 60 * 24 * 30;
                $scale = self::SCALE_MONTH;
                break;
        }
        
        $mode_options = array(
            self::MODE_TODAY => $lng->txt("trac_session_statistics_mode_today"),
            self::MODE_LAST_DAY => $lng->txt("trac_session_statistics_mode_last_day"),
            self::MODE_LAST_WEEK => $lng->txt("trac_session_statistics_mode_last_week"),
            self::MODE_LAST_MONTH => $lng->txt("trac_session_statistics_mode_last_month"));
        
        $title = $lng->txt("trac_current_system_load") . " - " . $mode_options[$mode];
        $data = $this->buildData($time_from, $time_to, $title);
        
        if (!$a_export) {
            // toolbar
            include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
            $ilToolbar->setFormAction($ilCtrl->getFormAction($this, "current"));

            $mode_selector = new ilSelectInputGUI("&nbsp;" . $lng->txt("trac_scale"), "smd");
            $mode_selector->setOptions($mode_options);
            $mode_selector->setValue($mode);
            $ilToolbar->addInputItem($mode_selector, true);

            $measure_options = array(
                "avg" => $lng->txt("trac_session_active_avg"),
                "min" => $lng->txt("trac_session_active_min"),
                "max" => $lng->txt("trac_session_active_max"));

            $measure_selector = new ilSelectInputGUI("&nbsp;" . $lng->txt("trac_measure"), "smm");
            $measure_selector->setOptions($measure_options);
            $measure_selector->setValue($measure);
            $ilToolbar->addInputItem($measure_selector, true);

            $ilToolbar->addFormButton($lng->txt("ok"), "current");
            
            if (sizeof($data["active"])) {
                $ilToolbar->addSeparator();
                $ilToolbar->addFormButton($lng->txt("export"), "currentExport");
            }
                    
            $tpl->setContent($this->render($data, $scale, $measure));

            $tpl->setLeftContent($this->renderCurrentBasics());
        } else {
            $this->exportCSV($data, $scale);
        }
    }
    
    protected function currentExport()
    {
        $this->current(true);
    }
        
    protected function importDate($a_incoming, $a_default = null)
    {
        if (!$a_default) {
            $a_default = time();
        }
        
        include_once "Services/Calendar/classes/class.ilCalendarUtil.php";
        $parsed = ilCalendarUtil::parseIncomingDate($a_incoming);
        return $parsed
            ? $parsed->get(IL_CAL_UNIX)
            : $a_default;
    }
        
    protected function short($a_export = false)
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        $ilToolbar = $DIC['ilToolbar'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilTabs = $DIC['ilTabs'];
        $lng = $DIC['lng'];
        
        $ilTabs->activateSubTab("short");
        
        // current start
        $time_to = $this->importDate($_REQUEST["sst"]);
        
        // current mode
        if (!$_REQUEST["smd"]) {
            $_REQUEST["smd"] = self::MODE_DAY;
        }
        $mode = (int) $_REQUEST["smd"];
        
        // current measure
        if (!$_REQUEST["smm"]) {
            $_REQUEST["smm"] = "avg";
        }
        $measure = (string) $_REQUEST["smm"];
                                
        switch ($mode) {
            case self::MODE_DAY:
                $time_from = $time_to - 60 * 60 * 24;
                $scale = self::SCALE_DAY;
                break;
            
            case self::MODE_WEEK:
                $time_from = $time_to - 60 * 60 * 24 * 7;
                $scale = self::SCALE_WEEK;
                break;
        }
                
        $mode_options = array(
                self::MODE_DAY => $lng->txt("trac_session_statistics_mode_day"),
                self::MODE_WEEK => $lng->txt("trac_session_statistics_mode_week")
            );
            
        $title = $lng->txt("trac_short_system_load") . " - " . $mode_options[$mode];
        $data = $this->buildData($time_from, $time_to, $title);
        
        if (!$a_export) {
            // toolbar
            include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
            $ilToolbar->setFormAction($ilCtrl->getFormAction($this, "short"));
            
            $start_selector = new ilDateTimeInputGUI($lng->txt("trac_end_at"), "sst");
            $start_selector->setDate(new ilDate($time_to, IL_CAL_UNIX));
            $ilToolbar->addInputItem($start_selector, true);

            $mode_selector = new ilSelectInputGUI("&nbsp;" . $lng->txt("trac_scale"), "smd");
            $mode_selector->setOptions($mode_options);
            $mode_selector->setValue($mode);
            $ilToolbar->addInputItem($mode_selector, true);

            $measure_options = array(
                "avg" => $lng->txt("trac_session_active_avg"),
                "min" => $lng->txt("trac_session_active_min"),
                "max" => $lng->txt("trac_session_active_max"));

            $measure_selector = new ilSelectInputGUI("&nbsp;" . $lng->txt("trac_measure"), "smm");
            $measure_selector->setOptions($measure_options);
            $measure_selector->setValue($measure);
            $ilToolbar->addInputItem($measure_selector, true);

            $ilToolbar->addFormButton($lng->txt("ok"), "short");
            
            if (sizeof($data["active"])) {
                $ilToolbar->addSeparator();
                $ilToolbar->addFormButton($lng->txt("export"), "shortExport");
            }
                                                
            $tpl->setContent($this->render($data, $scale, $measure));
        } else {
            $this->exportCSV($data, $scale);
        }
    }
    
    protected function shortExport()
    {
        $this->short(true);
    }
    
    protected function long($a_export = false)
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        $ilToolbar = $DIC['ilToolbar'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilTabs = $DIC['ilTabs'];
        $lng = $DIC['lng'];
        
        $ilTabs->activateSubTab("long");
        
        // current start
        $time_to = $this->importDate($_REQUEST["sst"]);
        
        // current mode
        if (!$_REQUEST["smd"]) {
            $_REQUEST["smd"] = self::MODE_WEEK;
        }
        $mode = (int) $_REQUEST["smd"];
        
        switch ($mode) {
            case self::MODE_WEEK:
                $time_from = $time_to - 60 * 60 * 24 * 7;
                $scale = self::SCALE_WEEK;
                break;
            
            case self::MODE_MONTH:
                $time_from = $time_to - 60 * 60 * 24 * 30;
                $scale = self::SCALE_MONTH;
                break;
            
            case self::MODE_YEAR:
                $time_from = $time_to - 60 * 60 * 24 * 365;
                $scale = self::SCALE_YEAR;
                break;
        }
        
        $mode_options = array(
                self::MODE_WEEK => $lng->txt("trac_session_statistics_mode_week"),
                self::MODE_MONTH => $lng->txt("trac_session_statistics_mode_month"),
                self::MODE_YEAR => $lng->txt("trac_session_statistics_mode_year")
            );
        
        $title = $lng->txt("trac_long_system_load") . " - " . $mode_options[$mode];
        $data = $this->buildData($time_from, $time_to, $title);
        
        if (!$a_export) {
            // toolbar
            include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
            $ilToolbar->setFormAction($ilCtrl->getFormAction($this, "long"));

            $start_selector = new ilDateTimeInputGUI($lng->txt("trac_end_at"), "sst");
            $start_selector->setDate(new ilDate($time_to, IL_CAL_UNIX));
            $ilToolbar->addInputItem($start_selector, true);

            $mode_selector = new ilSelectInputGUI("&nbsp;" . $lng->txt("trac_scale"), "smd");
            $mode_selector->setOptions($mode_options);
            $mode_selector->setValue($mode);
            $ilToolbar->addInputItem($mode_selector, true);

            $ilToolbar->addFormButton($lng->txt("ok"), "long");
            
            if (sizeof($data["active"])) {
                $ilToolbar->addSeparator();
                $ilToolbar->addFormButton($lng->txt("export"), "longExport");
            }
                                                
            $tpl->setContent($this->render($data, $scale));
        } else {
            $this->exportCSV($data, $scale);
        }
    }
    
    protected function longExport()
    {
        $this->long(true);
    }
    
    protected function periodic($a_export = false)
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        $ilToolbar = $DIC['ilToolbar'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilTabs = $DIC['ilTabs'];
        $lng = $DIC['lng'];
        
        $ilTabs->activateSubTab("periodic");
        
        // current start
        $time_to = $this->importDate($_REQUEST["sst"]);
        
        // current end
        $time_from = $this->importDate($_REQUEST["sto"], strtotime("-7 days"));
        
        // mixed up dates?
        if ($time_to < $time_from) {
            $tmp = $time_to;
            $time_to = $time_from;
            $time_from = $tmp;
        }
                                        
        $title = $lng->txt("trac_periodic_system_load");
        $data = $this->buildData($time_from, $time_to, $title);
                
        if (!$a_export) {
            // toolbar
            include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
            $ilToolbar->setFormAction($ilCtrl->getFormAction($this, "periodic"));
            
            $end_selector = new ilDateTimeInputGUI($lng->txt("trac_begin_at"), "sto");
            $end_selector->setDate(new ilDate($time_from, IL_CAL_UNIX));
            $ilToolbar->addInputItem($end_selector, true);

            $start_selector = new ilDateTimeInputGUI($lng->txt("trac_end_at"), "sst");
            $start_selector->setDate(new ilDate($time_to, IL_CAL_UNIX));
            $ilToolbar->addInputItem($start_selector, true);

            $ilToolbar->addFormButton($lng->txt("ok"), "periodic");
            
            if (sizeof($data["active"])) {
                $ilToolbar->addSeparator();
                $ilToolbar->addFormButton($lng->txt("export"), "periodicExport");
            }
            
            $tpl->setContent($this->render($data, self::SCALE_PERIODIC_WEEK));
        } else {
            $this->exportCSV($data, self::SCALE_PERIODIC_WEEK);
        }
    }
    
    protected function periodicExport()
    {
        $this->periodic(true);
    }
    
    protected function renderCurrentBasics()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilAccess = $DIC['ilAccess'];
        
        // basic data - not time related
        
        include_once "Services/Authentication/classes/class.ilSessionControl.php";
        $active = (int) ilSessionControl::getExistingSessionCount(ilSessionControl::$session_types_controlled);
        
        $control_active = ($ilSetting->get('session_handling_type', 0) == 1);
        if ($control_active) {
            $control_max_sessions = (int) $ilSetting->get('session_max_count', ilSessionControl::DEFAULT_MAX_COUNT);
            $control_min_idle = (int) $ilSetting->get('session_min_idle', ilSessionControl::DEFAULT_MIN_IDLE);
            $control_max_idle = (int) $ilSetting->get('session_max_idle', ilSessionControl::DEFAULT_MAX_IDLE);
            $control_max_idle_first = (int) $ilSetting->get('session_max_idle_after_first_request', ilSessionControl::DEFAULT_MAX_IDLE_AFTER_FIRST_REQUEST);
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
        if (!$control_active) {
            $left->setVariable("VALUE_SESSION_CONTROL", $lng->txt("no"));
        } else {
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
        
        // sync button
        if ($ilAccess->checkAccess("write", "", (int) $_REQUEST["ref_id"])) {
            $left->setVariable("URL_SYNC", $ilCtrl->getFormAction($this, "adminSync"));
            $left->setVariable("CMD_SYNC", "adminSync");
            $left->setVariable("TXT_SYNC", $lng->txt("trac_sync_session_stats"));
        }
        
        return $left->get();
    }
    
    protected function buildData($a_time_from, $a_time_to, $a_title)
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        // basic data - time related
        
        $maxed_out_duration = round(ilSessionStatistics::getMaxedOutDuration($a_time_from, $a_time_to) / 60);
        $counters = ilSessionStatistics::getNumberOfSessionsByType($a_time_from, $a_time_to);
        $opened = (int) $counters["opened"];
        $closed_limit = (int) $counters["closed_limit"];
        unset($counters["opened"]);
        unset($counters["closed_limit"]);
        
        
        // build center column
        
        $data = array();
            
        ilDatePresentation::setUseRelativeDates(false);
        $data["title"] = $a_title . " (" .
            ilDatePresentation::formatPeriod(
                new ilDateTime($a_time_from, IL_CAL_UNIX),
                new ilDateTime($a_time_to, IL_CAL_UNIX)
            ) . ")";
            
        $data["maxed_out_time"] = array($lng->txt("trac_maxed_out_time"), $maxed_out_duration);
        $data["maxed_out_counter"] = array($lng->txt("trac_maxed_out_counter"), $closed_limit);
        $data["opened"] = array($lng->txt("trac_sessions_opened"), $opened);
        $data["closed"] = array($lng->txt("trac_sessions_closed"), array_sum($counters));
        foreach ($counters as $type => $counter) {
            $data["closed_details"][] = array($lng->txt("trac_" . $type), (int) $counter);
        }
                
        $data["active"] = ilSessionStatistics::getActiveSessions($a_time_from, $a_time_to);
        
        return $data;
    }
    
    protected function render($a_data, $a_scale, $a_measure = null)
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        $center = new ilTemplate("tpl.session_statistics_center.html", true, true, "Services/Authentication");
        
        foreach ($a_data as $idx => $item) {
            switch ($idx) {
                case "active":
                case "title":
                    // nothing to do
                    break;
                
                case "closed_details":
                    $center->setCurrentBlock("closed_details");
                    foreach ($item as $detail) {
                        $center->setVariable("CAPTION_CLOSED_DETAILS", $detail[0]);
                        $center->setVariable("VALUE_CLOSED_DETAILS", $detail[1]);
                        $center->parseCurrentBlock();
                    }
                    break;
                
                default:
                    $tpl_var = strtoupper($idx);
                    $center->setVariable("CAPTION_" . $tpl_var, $item[0]);
                    $center->setVariable("VALUE_" . $tpl_var, $item[1]);
                    break;
            }
        }
        
        if ($a_data["active"]) {
            $center->setVariable("CHART", $this->getChart($a_data["active"], $a_data["title"], $a_scale, $a_measure));
        } else {
            ilUtil::sendInfo($lng->txt("trac_session_statistics_no_data"));
        }
                
        return $center->get();
    }
            
    /**
     * Build chart for active sessions
     *
     * @param array $a_data
     * @param string $a_title
     * @param int $a_scale
     * @param array $a_measure
     * @return string
     */
    protected function getChart($a_data, $a_title, $a_scale = self::SCALE_DAY, $a_measure = null)
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        include_once "Services/Chart/classes/class.ilChart.php";
        $chart = ilChart::getInstanceByType(ilChart::TYPE_GRID, "objstacc");
        $chart->setsize(700, 500);
        $chart->setYAxisToInteger(true);
        
        $legend = new ilChartLegend();
        $chart->setLegend($legend);

        if (!$a_measure) {
            $a_measure = array("min", "avg", "max");
        } elseif (!is_array($a_measure)) {
            $a_measure = array($a_measure);
        }

        $colors_map = array("min" => "#00cc00",
            "avg" => "#0000cc",
            "max" => "#cc00cc");
        
        $colors = $act_line = array();
        foreach ($a_measure as $measure) {
            $act_line[$measure] = $chart->getDataInstance(ilChartGrid::DATA_LINES);
            $act_line[$measure]->setLineSteps(true);
            $act_line[$measure]->setLabel($lng->txt("trac_session_active_" . $measure));
            $colors[] = $colors_map[$measure];
        }
        
        if ($a_scale != self::SCALE_PERIODIC_WEEK) {
            $max_line = $chart->getDataInstance(ilChartGrid::DATA_LINES);
            $max_line->setLabel($lng->txt("session_max_count"));
            $colors[] = "#cc0000";
        }
    
        $chart->setColors($colors);
        
        $chart_data = $this->adaptDataToScale($a_scale, $a_data, 700);
        unset($a_data);
        
        $scale = ceil(sizeof($chart_data) / 5);
        $labels = array();
        foreach ($chart_data as $idx => $item) {
            $date = $item["slot_begin"];
            
            if ($a_scale == self::SCALE_PERIODIC_WEEK || !($idx % ceil($scale))) {
                switch ($a_scale) {
                    case self::SCALE_DAY:
                        $labels[$date] = date("H:i", $date);
                        break;
                    
                    case self::SCALE_WEEK:
                        $labels[$date] = date("d.m. H", $date) . "h";
                        break;
                    
                    case self::SCALE_MONTH:
                        $labels[$date] = date("d.m.", $date);
                        break;
                    
                    case self::SCALE_YEAR:
                        $labels[$date] = date("Y-m", $date);
                        break;
                    
                    case self::SCALE_PERIODIC_WEEK:
                        $day = substr($date, 0, 1);
                        $hour = substr($date, 1, 2);
                        $min = substr($date, 3, 2);
                        
                        // build ascending scale from day values
                        $day_value = ($day - 1) * 60 * 60 * 24;
                        $date = $day_value + $hour * 60 * 60 + $min * 60;
                        
                        // 6-hour interval labels
                        if ($hour != $old_hour && $hour && $hour % 6 == 0) {
                            $labels[$date] = $hour;
                            $old_hour = $hour;
                        }
                        // day label
                        if ($day != $old_day) {
                            $labels[$date] = ilCalendarUtil::_numericDayToString($day, false);
                            $old_day = $day;
                        }
                        break;
                }
            }
            
            foreach ($a_measure as $measure) {
                $value = (int) $item["active_" . $measure];
                $act_line[$measure]->addPoint($date, $value);
            }
            
            if ($a_scale != self::SCALE_PERIODIC_WEEK) {
                $max_line->addPoint($date, (int) $item["max_sessions"]);
            }
        }
        
        foreach ($act_line as $line) {
            $chart->addData($line);
        }
        if ($a_scale != self::SCALE_PERIODIC_WEEK) {
            $chart->addData($max_line);
        }
        
        $chart->setTicks($labels, null, true);

        return $chart->getHTML();
    }
    
    protected function adaptDataToScale($a_scale, array $a_data)
    {
        // can we use original data?
        switch ($a_scale) {
            case self::SCALE_DAY:
                // 96 values => ok
                // fallthrough
                
            case self::SCALE_WEEK:
                // 672 values => ok
                return $a_data;
        }
        
        $tmp = array();
        foreach ($a_data as $item) {
            $date_parts = getdate($item["slot_begin"]);
            
            // aggregate slots for scale
            switch ($a_scale) {
                case self::SCALE_MONTH:
                    // aggregate to hours => 720 values
                    $slot = mktime($date_parts["hours"], 0, 0, $date_parts["mon"], $date_parts["mday"], $date_parts["year"]);
                    break;
                    
                case self::SCALE_YEAR:
                    // aggregate to days => 365 values
                    $slot = mktime(0, 0, 1, $date_parts["mon"], $date_parts["mday"], $date_parts["year"]);
                    break;

                case self::SCALE_PERIODIC_WEEK:
                    // aggregate to weekdays => 672 values
                    $day = $date_parts["wday"];
                    if (!$day) {
                        $day = 7;
                    }
                    $slot = $day . date("His", $item["slot_begin"]);
                    break;
            }
                        
            // process minx/max, prepare avg
            foreach ($item as $id => $value) {
                switch (substr($id, -3)) {
                    case "min":
                        if (!$tmp[$slot][$id] || $value < $tmp[$slot][$id]) {
                            $tmp[$slot][$id] = $value;
                        }
                        break;
                        
                    case "max":
                        if (!$tmp[$slot][$id] || $value > $tmp[$slot][$id]) {
                            $tmp[$slot][$id] = $value;
                        }
                        break;
                        
                    case "avg":
                        $tmp[$slot][$id][] = $value;
                        break;
                }
            }
        }
        
        foreach ($tmp as $slot => $attr) {
            $tmp[$slot]["active_avg"] = (int) round(array_sum($attr["active_avg"]) / sizeof($attr["active_avg"]));
            $tmp[$slot]["slot_begin"] = $slot;
        }
        ksort($tmp);
        return array_values($tmp);
    }
    
    protected function adminSync()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        
        // see ilSession::_writeData()
        $now = time();
        ilSession::_destroyExpiredSessions();
        ilSessionStatistics::aggretateRaw($now);
        
        ilUtil::sendSuccess($lng->txt("trac_sync_session_stats_success"), true);
        $ilCtrl->redirect($this);
    }
    
    protected function exportCSV(array $a_data, $a_scale)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilClientIniFile = $DIC['ilClientIniFile'];
        $ilUser = $DIC['ilUser'];
        
        ilDatePresentation::setUseRelativeDates(false);
        include_once './Services/Link/classes/class.ilLink.php';
        
        include_once "./Services/Utilities/classes/class.ilCSVWriter.php";
        $csv = new ilCSVWriter();
        $csv->setSeparator(";");
        
        $now = time();
        
        // meta
        $meta = array(
            $lng->txt("trac_name_of_installation") => $ilClientIniFile->readVariable('client', 'name'),
            $lng->txt("trac_report_date") => ilDatePresentation::formatDate(new ilDateTime($now, IL_CAL_UNIX)),
            $lng->txt("trac_report_owner") => $ilUser->getFullName(),
            );
        foreach ($a_data as $idx => $item) {
            switch ($idx) {
                case "title":
                    $meta[$lng->txt("title")] = $item;
                    break;
                
                case "active":
                    // nothing to do
                    break;
                
                case "closed_details":
                    foreach ($item as $detail) {
                        $meta[$a_data["closed"][0] . " - " . $detail[0]] = $detail[1];
                    }
                    break;
                
                default:
                    $meta[$item[0]] = $item[1];
                    break;
            }
        }
        foreach ($meta as  $caption => $value) {
            $csv->addColumn(strip_tags($caption));
            $csv->addColumn(strip_tags($value));
            $csv->addRow();
        }
        $csv->addRow();
        
        // aggregate data
        $aggr_data = $this->adaptDataToScale($a_scale, $a_data["active"], 700);
        unset($a_data);
        
        // header
        $first = $aggr_data;
        $first = array_keys(array_shift($first));
        foreach ($first as $column) {
            // split weekday and time slot again
            if ($a_scale == self::SCALE_PERIODIC_WEEK && $column == "slot_begin") {
                $csv->addColumn("weekday");
                $csv->addColumn("time");
            } else {
                $csv->addColumn(strip_tags($column));
            }
        }
        $csv->addRow();
        
        // data
        foreach ($aggr_data as $row) {
            foreach ($row as $column => $value) {
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                switch ($column) {
                    case "slot_begin":
                        // split weekday and time slot again
                        if ($a_scale == self::SCALE_PERIODIC_WEEK) {
                            $csv->addColumn(ilCalendarUtil::_numericDayToString(substr($value, 0, 1)));
                            $value = substr($value, 1, 2) . ":" . substr($value, 3, 2);
                            break;
                        }
                        // fallthrough
                        
                        // no break
                    case "slot_end":
                        $value = date("d.m.Y H:i", $value);
                        break;
                }
                $csv->addColumn(strip_tags($value));
            }
            $csv->addRow();
        }
        
        // send
        $filename .= "session_statistics_" . date("Ymd", $now) . ".csv";
        header("Content-type: text/comma-separated-values");
        header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
        header("Pragma: public");
        echo $csv->getCSVString();
        exit();
    }
}
