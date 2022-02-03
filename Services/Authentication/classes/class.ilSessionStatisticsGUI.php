<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

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
    
    private ilCtrl $ilCtrl;
    private ilTabsGUI $ilTabs;
    private ilLanguage $lng;
    private ilGlobalTemplateInterface $tpl;
    private ilToolbarGUI $toolbar;
    private ilSetting $settings;
    private ilAccess $access;
    private ilIniFile $clientIniFile;
    private ilObjUser $user;
    
    public function executeCommand()
    {
        global $DIC;

        $this->ilCtrl = $DIC->ctrl();
        $this->ilTabs = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->toolbar = $DIC->toolbar();
        $this->settings = $DIC->settings();
        $this->access = $DIC->access();
        $this->clientIniFile = $DIC->clientIni();
        $this->user = $DIC->user();
        
        $this->setSubTabs();
        
        switch ($this->ilCtrl->getNextClass()) {
            default:
                $cmd = $this->ilCtrl->getCmd("current");
                $this->$cmd();
        }

        return true;
    }
    
    protected function setSubTabs()
    {
        $this->ilTabs->addSubTab(
            "current",
            $this->lng->txt("trac_current_system_load"),
            $this->ilCtrl->getLinkTarget($this, "current")
        );
        $this->ilTabs->addSubTab(
            "short",
            $this->lng->txt("trac_short_system_load"),
            $this->ilCtrl->getLinkTarget($this, "short")
        );
        $this->ilTabs->addSubTab(
            "long",
            $this->lng->txt("trac_long_system_load"),
            $this->ilCtrl->getLinkTarget($this, "long")
        );
        $this->ilTabs->addSubTab(
            "periodic",
            $this->lng->txt("trac_periodic_system_load"),
            $this->ilCtrl->getLinkTarget($this, "periodic")
        );
    }
    
    protected function current($a_export = false)
    {
        $this->ilTabs->activateSubTab("current");
        
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
            self::MODE_TODAY => $this->lng->txt("trac_session_statistics_mode_today"),
            self::MODE_LAST_DAY => $this->lng->txt("trac_session_statistics_mode_last_day"),
            self::MODE_LAST_WEEK => $this->lng->txt("trac_session_statistics_mode_last_week"),
            self::MODE_LAST_MONTH => $this->lng->txt("trac_session_statistics_mode_last_month"));
        
        $title = $this->lng->txt("trac_current_system_load") . " - " . $mode_options[$mode];
        $data = $this->buildData($time_from, $time_to, $title);
        
        if (!$a_export) {
            // toolbar
            $this->toolbar->setFormAction($this->ilCtrl->getFormAction($this, "current"));

            $mode_selector = new ilSelectInputGUI("&nbsp;" . $this->lng->txt("trac_scale"), "smd");
            $mode_selector->setOptions($mode_options);
            $mode_selector->setValue($mode);
            $this->toolbar->addInputItem($mode_selector, true);

            $measure_options = array(
                "avg" => $this->lng->txt("trac_session_active_avg"),
                "min" => $this->lng->txt("trac_session_active_min"),
                "max" => $this->lng->txt("trac_session_active_max"));

            $measure_selector = new ilSelectInputGUI("&nbsp;" . $this->lng->txt("trac_measure"), "smm");
            $measure_selector->setOptions($measure_options);
            $measure_selector->setValue($measure);
            $this->toolbar->addInputItem($measure_selector, true);

            $this->toolbar->addFormButton($this->lng->txt("ok"), "current");
            
            if (sizeof($data["active"])) {
                $this->toolbar->addSeparator();
                $this->toolbar->addFormButton($this->lng->txt("export"), "currentExport");
            }
                    
            $this->tpl->setContent($this->render($data, $scale, $measure));

            $this->tpl->setLeftContent($this->renderCurrentBasics());
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
        
        $parsed = ilCalendarUtil::parseIncomingDate($a_incoming);
        return $parsed
            ? $parsed->get(IL_CAL_UNIX)
            : $a_default;
    }
        
    protected function short($a_export = false)
    {
        $this->ilTabs->activateSubTab("short");
        
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
                self::MODE_DAY => $this->lng->txt("trac_session_statistics_mode_day"),
                self::MODE_WEEK => $this->lng->txt("trac_session_statistics_mode_week")
            );
            
        $title = $this->lng->txt("trac_short_system_load") . " - " . $mode_options[$mode];
        $data = $this->buildData($time_from, $time_to, $title);
        
        if (!$a_export) {
            // toolbar
            $this->toolbar->setFormAction($this->ilCtrl->getFormAction($this, "short"));
            
            $start_selector = new ilDateTimeInputGUI($this->lng->txt("trac_end_at"), "sst");
            $start_selector->setDate(new ilDate($time_to, IL_CAL_UNIX));
            $this->toolbar->addInputItem($start_selector, true);

            $mode_selector = new ilSelectInputGUI("&nbsp;" . $this->lng->txt("trac_scale"), "smd");
            $mode_selector->setOptions($mode_options);
            $mode_selector->setValue($mode);
            $this->toolbar->addInputItem($mode_selector, true);

            $measure_options = array(
                "avg" => $this->lng->txt("trac_session_active_avg"),
                "min" => $this->lng->txt("trac_session_active_min"),
                "max" => $this->lng->txt("trac_session_active_max"));

            $measure_selector = new ilSelectInputGUI("&nbsp;" . $this->lng->txt("trac_measure"), "smm");
            $measure_selector->setOptions($measure_options);
            $measure_selector->setValue($measure);
            $this->toolbar->addInputItem($measure_selector, true);

            $this->toolbar->addFormButton($this->lng->txt("ok"), "short");
            
            if (sizeof($data["active"])) {
                $this->toolbar->addSeparator();
                $this->toolbar->addFormButton($this->lng->txt("export"), "shortExport");
            }
                                                
            $this->tpl->setContent($this->render($data, $scale, $measure));
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
        $this->ilTabs->activateSubTab("long");
        
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
                self::MODE_WEEK => $this->lng->txt("trac_session_statistics_mode_week"),
                self::MODE_MONTH => $this->lng->txt("trac_session_statistics_mode_month"),
                self::MODE_YEAR => $this->lng->txt("trac_session_statistics_mode_year")
            );
        
        $title = $this->lng->txt("trac_long_system_load") . " - " . $mode_options[$mode];
        $data = $this->buildData($time_from, $time_to, $title);
        
        if (!$a_export) {
            // toolbar
            $this->toolbar->setFormAction($this->ilCtrl->getFormAction($this, "long"));

            $start_selector = new ilDateTimeInputGUI($this->lng->txt("trac_end_at"), "sst");
            $start_selector->setDate(new ilDate($time_to, IL_CAL_UNIX));
            $this->toolbar->addInputItem($start_selector, true);

            $mode_selector = new ilSelectInputGUI("&nbsp;" . $this->lng->txt("trac_scale"), "smd");
            $mode_selector->setOptions($mode_options);
            $mode_selector->setValue($mode);
            $this->toolbar->addInputItem($mode_selector, true);

            $this->toolbar->addFormButton($this->lng->txt("ok"), "long");
            
            if (sizeof($data["active"])) {
                $this->toolbar->addSeparator();
                $this->toolbar->addFormButton($this->lng->txt("export"), "longExport");
            }
                                                
            $this->tpl->setContent($this->render($data, $scale));
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
        $this->ilTabs->activateSubTab("periodic");
        
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
                                        
        $title = $this->lng->txt("trac_periodic_system_load");
        $data = $this->buildData($time_from, $time_to, $title);
                
        if (!$a_export) {
            // toolbar
            $this->toolbar->setFormAction($this->ilCtrl->getFormAction($this, "periodic"));
            
            $end_selector = new ilDateTimeInputGUI($this->lng->txt("trac_begin_at"), "sto");
            $end_selector->setDate(new ilDate($time_from, IL_CAL_UNIX));
            $this->toolbar->addInputItem($end_selector, true);

            $start_selector = new ilDateTimeInputGUI($this->lng->txt("trac_end_at"), "sst");
            $start_selector->setDate(new ilDate($time_to, IL_CAL_UNIX));
            $this->toolbar->addInputItem($start_selector, true);

            $this->toolbar->addFormButton($this->lng->txt("ok"), "periodic");
            
            if (sizeof($data["active"])) {
                $this->toolbar->addSeparator();
                $this->toolbar->addFormButton($this->lng->txt("export"), "periodicExport");
            }
            
            $this->tpl->setContent($this->render($data, self::SCALE_PERIODIC_WEEK));
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
        // basic data - not time related
        
        $active = (int) ilSessionControl::getExistingSessionCount(ilSessionControl::$session_types_controlled);
        
        $control_active = ($this->settings->get('session_handling_type', 0) == 1);
        if ($control_active) {
            $control_max_sessions = (int) $this->settings->get('session_max_count', ilSessionControl::DEFAULT_MAX_COUNT);
            $control_min_idle = (int) $this->settings->get('session_min_idle', ilSessionControl::DEFAULT_MIN_IDLE);
            $control_max_idle = (int) $this->settings->get('session_max_idle', ilSessionControl::DEFAULT_MAX_IDLE);
            $control_max_idle_first = (int) $this->settings->get('session_max_idle_after_first_request', ilSessionControl::DEFAULT_MAX_IDLE_AFTER_FIRST_REQUEST);
        }
        
        $last_maxed_out = new ilDateTime(ilSessionStatistics::getLastMaxedOut(), IL_CAL_UNIX);
        $last_aggr = new ilDateTime(ilSessionStatistics::getLastAggregation(), IL_CAL_UNIX);
                
        
        // build left column
        
        $left = new ilTemplate("tpl.session_statistics_left.html", true, true, "Services/Authentication");
        
        $left->setVariable("CAPTION_CURRENT", $this->lng->txt("users_online"));
        $left->setVariable("VALUE_CURRENT", $active);
        
        $left->setVariable("CAPTION_LAST_AGGR", $this->lng->txt("trac_last_aggregation"));
        $left->setVariable("VALUE_LAST_AGGR", ilDatePresentation::formatDate($last_aggr));
        
        $left->setVariable("CAPTION_LAST_MAX", $this->lng->txt("trac_last_maxed_out_sessions"));
        $left->setVariable("VALUE_LAST_MAX", ilDatePresentation::formatDate($last_maxed_out));
        
        $left->setVariable("CAPTION_SESSION_CONTROL", $this->lng->txt("sess_load_dependent_session_handling"));
        if (!$control_active) {
            $left->setVariable("VALUE_SESSION_CONTROL", $this->lng->txt("no"));
        } else {
            $left->setVariable("VALUE_SESSION_CONTROL", $this->lng->txt("yes"));
            
            $left->setCurrentBlock("control_details");
            
            $left->setVariable("CAPTION_SESSION_CONTROL_LIMIT", $this->lng->txt("session_max_count"));
            $left->setVariable("VALUE_SESSION_CONTROL_LIMIT", $control_max_sessions);
            
            $left->setVariable("CAPTION_SESSION_CONTROL_IDLE_MIN", $this->lng->txt("session_min_idle"));
            $left->setVariable("VALUE_SESSION_CONTROL_IDLE_MIN", $control_min_idle);
            
            $left->setVariable("CAPTION_SESSION_CONTROL_IDLE_MAX", $this->lng->txt("session_max_idle"));
            $left->setVariable("VALUE_SESSION_CONTROL_IDLE_MAX", $control_max_idle);
            
            $left->setVariable("CAPTION_SESSION_CONTROL_IDLE_FIRST", $this->lng->txt("session_max_idle_after_first_request"));
            $left->setVariable("VALUE_SESSION_CONTROL_IDLE_FIRST", $control_max_idle_first);
                        
            $left->parseCurrentBlock();
        }
        
        // sync button
        if ($this->access->checkAccess("write", "", (int) $_REQUEST["ref_id"])) {
            $left->setVariable("URL_SYNC", $this->ilCtrl->getFormAction($this, "adminSync"));
            $left->setVariable("CMD_SYNC", "adminSync");
            $left->setVariable("TXT_SYNC", $this->lng->txt("trac_sync_session_stats"));
        }
        
        return $left->get();
    }
    
    protected function buildData($a_time_from, $a_time_to, $a_title)
    {
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
            
        $data["maxed_out_time"] = array($this->lng->txt("trac_maxed_out_time"), $maxed_out_duration);
        $data["maxed_out_counter"] = array($this->lng->txt("trac_maxed_out_counter"), $closed_limit);
        $data["opened"] = array($this->lng->txt("trac_sessions_opened"), $opened);
        $data["closed"] = array($this->lng->txt("trac_sessions_closed"), array_sum($counters));
        foreach ($counters as $type => $counter) {
            $data["closed_details"][] = array($this->lng->txt("trac_" . $type), (int) $counter);
        }
                
        $data["active"] = ilSessionStatistics::getActiveSessions($a_time_from, $a_time_to);
        
        return $data;
    }
    
    protected function render($a_data, $a_scale, $a_measure = null)
    {
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
            ilUtil::sendInfo($this->lng->txt("trac_session_statistics_no_data"));
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
            $act_line[$measure]->setLabel($this->lng->txt("trac_session_active_" . $measure));
            $colors[] = $colors_map[$measure];
        }
        
        if ($a_scale != self::SCALE_PERIODIC_WEEK) {
            $max_line = $chart->getDataInstance(ilChartGrid::DATA_LINES);
            $max_line->setLabel($this->lng->txt("session_max_count"));
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
                            $labels[$date] = ilCalendarUtil::_numericDayToString((int) $day, false);
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
        // see ilSession::_writeData()
        $now = time();
        ilSession::_destroyExpiredSessions();
        ilSessionStatistics::aggretateRaw($now);
        
        ilUtil::sendSuccess($this->lng->txt("trac_sync_session_stats_success"), true);
        $this->ilCtrl->redirect($this);
    }
    
    protected function exportCSV(array $a_data, $a_scale)
    {
        ilDatePresentation::setUseRelativeDates(false);
        
        $csv = new ilCSVWriter();
        $csv->setSeparator(";");
        
        $now = time();
        
        // meta
        $meta = array(
            $this->lng->txt("trac_name_of_installation") => $this->clientIniFile->readVariable('client', 'name'),
            $this->lng->txt("trac_report_date") => ilDatePresentation::formatDate(new ilDateTime($now, IL_CAL_UNIX)),
            $this->lng->txt("trac_report_owner") => $this->user->getFullName(),
            );
        foreach ($a_data as $idx => $item) {
            switch ($idx) {
                case "title":
                    $meta[$this->lng->txt("title")] = $item;
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
                            $csv->addColumn(ilCalendarUtil::_numericDayToString((int) substr($value, 0, 1)));
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
