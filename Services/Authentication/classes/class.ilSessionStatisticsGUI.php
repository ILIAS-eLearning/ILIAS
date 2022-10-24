<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilSessionStatisticsGUI
{
    private const MODE_TODAY = 1;
    private const MODE_LAST_DAY = 2;
    private const MODE_LAST_WEEK = 3;
    private const MODE_LAST_MONTH = 4;
    private const MODE_DAY = 5;
    private const MODE_WEEK = 6;
    private const MODE_MONTH = 7;
    private const MODE_YEAR = 8;

    private const SCALE_DAY = 1;
    private const SCALE_WEEK = 2;
    private const SCALE_MONTH = 3;
    private const SCALE_YEAR = 4;
    private const SCALE_PERIODIC_WEEK = 5;

    private const REQUEST_SMD = "smd";
    private const REQUEST_SMM = "smm";
    private const REQUEST_SST = "sst";
    private const REQUEST_STO = "sto";
    private const REQUEST_REF = "ref_id";

    private ilCtrl $ilCtrl;
    private ilTabsGUI $ilTabs;
    private ilLanguage $lng;
    private ilGlobalTemplateInterface $tpl;
    private ilToolbarGUI $toolbar;
    private ilSetting $settings;
    private ilAccess $access;
    private ilIniFile $clientIniFile;
    private ilObjUser $user;
    private ilLogger $logger;

    private int $ref_id = -1;
    private ?int $smd = null;
    private ?string $smm = null;
    private ?string $sst = null;
    private ?string $sto = null;

    public function __construct()
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
        $this->logger = $DIC->logger()->auth();

        $http = $DIC->http();
        $kindlyTo = $DIC->refinery()->kindlyTo();
        if ($http->request()->getMethod() === "POST") {
            if ($http->wrapper()->post()->has(self::REQUEST_SMD)) {
                $this->smd = $http->wrapper()->post()->retrieve(self::REQUEST_SMD, $kindlyTo->int());
            }
            if ($http->wrapper()->post()->has(self::REQUEST_SMM)) {
                $this->smm = $http->wrapper()->post()->retrieve(self::REQUEST_SMM, $kindlyTo->string());
            }
            if ($http->wrapper()->post()->has(self::REQUEST_STO)) {
                $this->sto = $http->wrapper()->post()->retrieve(self::REQUEST_STO, $kindlyTo->string());
            }
            if ($http->wrapper()->post()->has(self::REQUEST_SST)) {
                $this->sst = $http->wrapper()->post()->retrieve(self::REQUEST_SST, $kindlyTo->string());
            }
        } else {
            if ($http->wrapper()->query()->has(self::REQUEST_SMD)) {
                $this->smd = $http->wrapper()->query()->retrieve(self::REQUEST_SMD, $kindlyTo->int());
            }
            if ($http->wrapper()->query()->has(self::REQUEST_SMM)) {
                $this->smm = $http->wrapper()->query()->retrieve(self::REQUEST_SMM, $kindlyTo->string());
            }
            if ($http->wrapper()->query()->has(self::REQUEST_STO)) {
                $this->sto = $http->wrapper()->query()->retrieve(self::REQUEST_STO, $kindlyTo->string());
            }
            if ($http->wrapper()->query()->has(self::REQUEST_SST)) {
                $this->sst = $http->wrapper()->query()->retrieve(self::REQUEST_SST, $kindlyTo->string());
            }
        }
        if ($http->wrapper()->query()->has(self::REQUEST_REF)) {
            $this->ref_id = $http->wrapper()->query()->retrieve(self::REQUEST_REF, $kindlyTo->int());
        }
    }

    public function executeCommand(): bool
    {
        $this->setSubTabs();

        switch ($this->ilCtrl->getNextClass()) {
            default:
                $cmd = $this->ilCtrl->getCmd("current");
                $this->$cmd();
        }

        return true;
    }

    protected function setSubTabs(): void
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

    protected function current(bool $a_export = false): void
    {
        $this->ilTabs->activateSubTab("current");

        // current mode
        if (!$this->smd) {
            $mode = self::MODE_TODAY;
        } else {
            $mode = $this->smd;
        }

        // current measure
        if (!$this->smm) {
            $measure = "avg";
        } else {
            $measure = $this->smm;
        }

        switch ($mode) {
            default:
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

            if (count($data["active"])) {
                $this->toolbar->addSeparator();
                $this->toolbar->addFormButton($this->lng->txt("export"), "currentExport");
            }

            $this->tpl->setContent($this->render($data, $scale, $measure));

            $this->tpl->setLeftContent($this->renderCurrentBasics());
        } else {
            $this->exportCSV($data, $scale);
        }
    }

    protected function currentExport(): void
    {
        $this->current(true);
    }

    /**
     * @return array|int|string|null
     */
    protected function importDate(string $a_incoming, int $a_default = null)
    {
        if (!$a_default) {
            $a_default = time();
        }

        $parsed = ilCalendarUtil::parseIncomingDate($a_incoming);
        return $parsed
            ? $parsed->get(IL_CAL_UNIX)
            : $a_default;
    }

    protected function short(bool $a_export = false): void
    {
        $this->ilTabs->activateSubTab("short");

        //TODO validate input
        // current start
        $time_to = $this->importDate((string) $this->sst);

        // current mode
        if (!$this->smd) {
            $mode = self::MODE_DAY;
        } else {
            $mode = $this->smd;
        }

        // current measure
        if (!$this->smm) {
            $measure = "avg";
        } else {
            $measure = $this->smm;
        }

        switch ($mode) {
            default:
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

            if (count($data["active"])) {
                $this->toolbar->addSeparator();
                $this->toolbar->addFormButton($this->lng->txt("export"), "shortExport");
            }

            $this->tpl->setContent($this->render($data, $scale, $measure));
        } else {
            $this->exportCSV($data, $scale);
        }
    }

    protected function shortExport(): void
    {
        $this->short(true);
    }

    protected function long($a_export = false): void
    {
        $this->ilTabs->activateSubTab("long");

        // current start
        //TODO validate input
        $time_to = $this->importDate((string) $this->sst);

        // current mode
        if (!$this->smd) {
            $mode = self::MODE_WEEK;
        } else {
            $mode = $this->smd;
        }

        switch ($mode) {
            default:
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

            if (count($data["active"])) {
                $this->toolbar->addSeparator();
                $this->toolbar->addFormButton($this->lng->txt("export"), "longExport");
            }

            $this->tpl->setContent($this->render($data, $scale));
        } else {
            $this->exportCSV($data, $scale);
        }
    }

    protected function longExport(): void
    {
        $this->long(true);
    }

    protected function periodic($a_export = false): void
    {
        $this->ilTabs->activateSubTab("periodic");

        //TODO validate input
        // current start
        $time_to = $this->importDate((string) $this->sst);

        // current end
        $time_from = $this->importDate((string) $this->sto, strtotime("-7 days"));

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

            if (count($data["active"])) {
                $this->toolbar->addSeparator();
                $this->toolbar->addFormButton($this->lng->txt("export"), "periodicExport");
            }

            $this->tpl->setContent($this->render($data, self::SCALE_PERIODIC_WEEK));
        } else {
            $this->exportCSV($data, self::SCALE_PERIODIC_WEEK);
        }
    }

    protected function periodicExport(): void
    {
        $this->periodic(true);
    }

    protected function renderCurrentBasics(): string
    {
        // basic data - not time related

        $active = ilSessionControl::getExistingSessionCount(ilSessionControl::$session_types_controlled);

        $control_active = ((int) $this->settings->get('session_handling_type', "0") === 1);
        if ($control_active) {
            $control_max_sessions = (int) $this->settings->get('session_max_count', (string) ilSessionControl::DEFAULT_MAX_COUNT);
            $control_min_idle = (int) $this->settings->get('session_min_idle', (string) ilSessionControl::DEFAULT_MIN_IDLE);
            $control_max_idle = (int) $this->settings->get('session_max_idle', (string) ilSessionControl::DEFAULT_MAX_IDLE);
            $control_max_idle_first = (int) $this->settings->get('session_max_idle_after_first_request', (string) ilSessionControl::DEFAULT_MAX_IDLE_AFTER_FIRST_REQUEST);
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
        if ($this->access->checkAccess("write", "", $this->ref_id)) {
            $left->setVariable("URL_SYNC", $this->ilCtrl->getFormAction($this, "adminSync"));
            $left->setVariable("CMD_SYNC", "adminSync");
            $left->setVariable("TXT_SYNC", $this->lng->txt("trac_sync_session_stats"));
        }

        return $left->get();
    }

    protected function buildData(int $a_time_from, int $a_time_to, string $a_title): array
    {
        // basic data - time related

        $maxed_out_duration = round(ilSessionStatistics::getMaxedOutDuration($a_time_from, $a_time_to) / 60);
        $counters = ilSessionStatistics::getNumberOfSessionsByType($a_time_from, $a_time_to);
        $opened = (int) $counters["opened"];
        $closed_limit = (int) $counters["closed_limit"];
        unset($counters["opened"], $counters["closed_limit"]);


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
        $this->logger->debug("Data to plot: " . var_export($data, true));
        return $data;
    }

    protected function render(array $a_data, int $a_scale, string $a_measure = null): string
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
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("trac_session_statistics_no_data"));
        }

        return $center->get();
    }

    /**
     * Build chart for active sessions
     */
    protected function getChart(array $a_data, string $a_title, int $a_scale = self::SCALE_DAY, string $a_measure = null): string
    {
        $chart = ilChart::getInstanceByType(ilChart::TYPE_GRID, "objstacc");
        $chart->setSize("700", "500");
        $chart->setYAxisToInteger(true);

        $legend = new ilChartLegend();
        $chart->setLegend($legend);

        if (!$a_measure) {
            $measures = ["min", "avg", "max"];
        } else {
            $measures = [$a_measure];
        }

        $colors_map = array("min" => "#00cc00",
            "avg" => "#0000cc",
            "max" => "#cc00cc");

        $colors = $act_line = array();
        foreach ($measures as $measure) {
            $act_line[$measure] = $chart->getDataInstance(ilChartGrid::DATA_LINES);
            $act_line[$measure]->setLineSteps(true);
            $act_line[$measure]->setLabel($this->lng->txt("trac_session_active_" . $measure));
            $colors[] = $colors_map[$measure];
        }

        if ($a_scale !== self::SCALE_PERIODIC_WEEK) {
            $max_line = $chart->getDataInstance(ilChartGrid::DATA_LINES);
            $max_line->setLabel($this->lng->txt("session_max_count"));
            $colors[] = "#cc0000";
        }

        $chart->setColors($colors);

        $chart_data = $this->adaptDataToScale($a_scale, $a_data);

        $scale = ceil(count($chart_data) / 5);
        $labels = array();
        foreach ($chart_data as $idx => $item) {
            $date = $item["slot_begin"];

            if ($a_scale === self::SCALE_PERIODIC_WEEK || !($idx % ceil($scale))) {
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
                        $day = substr((string) $date, 0, 1);
                        $hour = substr((string) $date, 1, 2);
                        $min = substr((string) $date, 3, 2);

                        // build ascending scale from day values
                        $day_value = ($day - 1) * 60 * 60 * 24;
                        $date = $day_value + $hour * 60 * 60 + $min * 60;

                        // 6-hour interval labels
                        if ((!isset($old_hour) || $hour != $old_hour) && $hour && $hour % 6 == 0) {
                            $labels[$date] = $hour;
                            $old_hour = $hour;
                        }
                        // day label
                        if (!isset($old_day) || $day != $old_day) {
                            $labels[$date] = ilCalendarUtil::_numericDayToString((int) $day, false);
                            $old_day = $day;
                        }
                        break;
                }
            }

            foreach ($measures as $measure) {
                $value = (int) $item["active_" . $measure];
                $act_line[$measure]->addPoint($date, $value);
            }

            if (isset($max_line) && $a_scale !== self::SCALE_PERIODIC_WEEK) {
                $max_line->addPoint($date, (int) $item["max_sessions"]);
            }
        }

        foreach ($act_line as $line) {
            $chart->addData($line);
        }
        if (isset($max_line) && $a_scale !== self::SCALE_PERIODIC_WEEK) {
            $chart->addData($max_line);
        }

        $chart->setTicks($labels, null, true);

        return $chart->getHTML();
    }

    protected function adaptDataToScale(int $a_scale, array $a_data): array
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

        $tmp = [];
        foreach ($a_data as $item) {
            $date_parts = getdate($item["slot_begin"]);

            // aggregate slots for scale
            switch ($a_scale) {
                default:
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
                switch (substr((string) $id, -3)) {
                    case "min":
                        if (!isset($tmp[$slot][$id]) || $value < $tmp[$slot][$id]) {
                            $tmp[$slot][$id] = $value;
                        }
                        break;

                    case "max":
                        if (!isset($tmp[$slot][$id]) || $value > $tmp[$slot][$id]) {
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
            $tmp[$slot]["active_avg"] = (int) round(array_sum($attr["active_avg"]) / count($attr["active_avg"]));
            $tmp[$slot]["slot_begin"] = $slot;
        }
        ksort($tmp);
        return array_values($tmp);
    }

    protected function adminSync(): void
    {
        // see ilSession::_writeData()
        $now = time();
        ilSession::_destroyExpiredSessions();
        ilSessionStatistics::aggretateRaw($now);

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("trac_sync_session_stats_success"), true);
        $this->ilCtrl->redirect($this);
    }

    protected function exportCSV(array $a_data, $a_scale): void
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
            $csv->addColumn(strip_tags((string) $caption));
            $csv->addColumn(strip_tags((string) $value));
            $csv->addRow();
        }
        $csv->addRow();

        // aggregate data
        $aggr_data = $this->adaptDataToScale($a_scale, $a_data["active"]);

        // header
        $first = $aggr_data;
        $first = array_keys(array_shift($first));
        foreach ($first as $column) {
            // split weekday and time slot again
            if ($a_scale === self::SCALE_PERIODIC_WEEK && $column === "slot_begin") {
                $csv->addColumn("weekday");
                $csv->addColumn("time");
            } else {
                $csv->addColumn(strip_tags((string) $column));
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
                        if ($a_scale === self::SCALE_PERIODIC_WEEK) {
                            $csv->addColumn(ilCalendarUtil::_numericDayToString((int) substr((string) $value, 0, 1)));
                            $value = substr((string) $value, 1, 2) . ":" . substr((string) $value, 3, 2);
                            break;
                        }
                        // fallthrough

                        // no break
                    case "slot_end":
                        $value = date("d.m.Y H:i", $value);
                        break;
                }
                $csv->addColumn(strip_tags((string) $value));
            }
            $csv->addRow();
        }

        // send
        $filename = "session_statistics_" . date("Ymd", $now) . ".csv";
        header("Content-type: text/comma-separated-values");
        header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
        header("Pragma: public");
        echo $csv->getCSVString();
        exit();
    }
}
