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
class ilCalendarWeekGUI extends ilCalendarViewGUI
{
    protected $num_appointments = 1;
    protected $user_settings = null;
    protected $weekdays = array();

    protected $lng;
    protected $ctrl;
    protected $tabs_gui;
    protected $tpl;
    
    protected $timezone = 'UTC';

    protected $user;
    protected $cal_settings;
    protected $colspans;

    // config
    protected $raster = 15;
    //setup_calendar
    protected $user_id;
    protected $disable_empty;
    protected $no_add;

    /**
     * Constructor
     *
     * @access public
     * @param
     * @todo make parent constructor (initialize) and init also seed and other common stuff
     */
    public function __construct(ilDate $seed_date)
    {
        parent::__construct($seed_date, ilCalendarViewGUI::CAL_PRESENTATION_WEEK);

        $this->seed_info = $this->seed->get(IL_CAL_FKT_GETDATE, '', 'UTC');
        
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
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];

        $this->ctrl->saveParameter($this, 'seed');

        $next_class = $ilCtrl->getNextClass();
        switch ($next_class) {
            case "ilcalendarappointmentpresentationgui":
                $this->ctrl->setReturn($this, "");
                include_once("./Services/Calendar/classes/class.ilCalendarAppointmentPresentationGUI.php");
                $gui = ilCalendarAppointmentPresentationGUI::_getInstance($this->seed, $this->getCurrentApp());
                $this->ctrl->forwardCommand($gui);
                break;
            case 'ilcalendarappointmentgui':
                $this->ctrl->setReturn($this, '');
                $this->tabs_gui->setSubTabActive($_SESSION['cal_last_tab']);
                
                // initial date for new calendar appointments
                $idate = new ilDate($_REQUEST['idate'], IL_CAL_DATE);

                include_once('./Services/Calendar/classes/class.ilCalendarAppointmentGUI.php');
                $app = new ilCalendarAppointmentGUI($this->seed, $idate, (int) $_GET['app_id']);
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
        $morning_aggr = $this->getMorningAggr();
        $evening_aggr = $this->user_settings->getDayEnd()*60;

        $this->tpl = new ilTemplate('tpl.week_view.html', true, true, 'Services/Calendar');
        
        include_once('./Services/YUI/classes/class.ilYuiUtil.php');
        ilYuiUtil::initDragDrop();
        
        $navigation = new ilCalendarHeaderNavigationGUI($this, $this->seed, ilDateTime::WEEK);
        $this->tpl->setVariable('NAVIGATION', $navigation->getHTML());

        $this->setUpCalendar();

        include_once('Services/Calendar/classes/class.ilCalendarSchedule.php');
        $this->scheduler = new ilCalendarSchedule($this->seed, ilCalendarSchedule::TYPE_WEEK, $this->user_id, $this->disable_empty);
        $this->scheduler->addSubitemCalendars(true);
        $this->scheduler->calculate();
        
        $counter = 0;
        $hours = null;
        $all_fullday = array();
        foreach (ilCalendarUtil::_buildWeekDayList($this->seed, $this->user_settings->getWeekStart())->get() as $date) {
            $daily_apps = $this->scheduler->getByDay($date, $this->timezone);
            if (!$this->view_with_appointments && count($daily_apps)) {
                $this->view_with_appointments = true;
            }
            $hours = $this->parseHourInfo(
                $daily_apps,
                $date,
                $counter,
                $hours,
                $morning_aggr,
                $evening_aggr,
                $this->raster
            );
            $this->weekdays[] = $date;

            $num_apps[$date->get(IL_CAL_DATE)] = count($daily_apps);
            
            $all_fullday[] = $daily_apps;
            $counter++;
        }

        $this->calculateColspans($hours);

        include_once('Services/Calendar/classes/class.ilCalendarSettings.php');
        $this->cal_settings = ilCalendarSettings::_getInstance();

        // Table header
        $counter = 0;
        foreach (ilCalendarUtil::_buildWeekDayList($this->seed, $this->user_settings->getWeekStart())->get() as $date) {
            $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'seed', $date->get(IL_CAL_DATE));
            $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'idate', $date->get(IL_CAL_DATE));
            $this->ctrl->setParameterByClass('ilcalendardaygui', 'seed', $date->get(IL_CAL_DATE));

            if (!$this->no_add) {
                $this->addAppointmentLink($date);
            }

            $this->addHeaderDate($date, $num_apps);

            $this->tpl->setCurrentBlock('day_header_row');
            $this->tpl->setVariable('DAY_COLSPAN', max($this->colspans[$counter], 1));
            $this->tpl->parseCurrentBlock();
            
            $counter++;
        }
    
        // show fullday events
        $this->addFullDayEvents($all_fullday);

        //show timed events
        $this->addTimedEvents($hours, $morning_aggr, $evening_aggr);
        
        $this->tpl->setVariable("TXT_TIME", $this->lng->txt("time"));
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
        $event_tpl = new ilTemplate('tpl.day_event_view.html', true, true, 'Services/Calendar');
        
        // milestone icon
        if ($a_app['event']->isMilestone()) {
            $event_tpl->setCurrentBlock('fullday_ms_icon');
            $event_tpl->setVariable('ALT_FD_MS', $this->lng->txt("cal_milestone"));
            $event_tpl->setVariable('SRC_FD_MS', ilUtil::getImagePath("icon_ms.svg"));
            $event_tpl->parseCurrentBlock();
        }

        $event_tpl->setCurrentBlock('fullday_app');
        
        $compl = ($a_app['event']->isMilestone() && $a_app['event']->getCompletion() > 0)
            ? " (" . $a_app['event']->getCompletion() . "%)"
            : "";

        $shy = $this->getAppointmentShyButton($a_app['event'], $a_app['dstart'], "");

        $title = $shy . $compl;

        $event_tpl->setVariable('EVENT_CONTENT', $title);

        $color = $this->app_colors->getColorByAppointment($a_app['event']->getEntryId());
        $font_color = ilCalendarUtil::calculateFontColor($color);

        $event_tpl->setVariable('F_APP_BGCOLOR', $color);
        $event_tpl->setVariable('F_APP_FONTCOLOR', $font_color);
        
        $this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
        $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'app_id', $a_app['event']->getEntryId());
        $event_tpl->setVariable('F_APP_EDIT_LINK', $this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui', 'edit'));

        if ($event_html_by_plugin = $this->getContentByPlugins($a_app['event'], $a_app['dstart'], $title, $event_tpl)) {
            $event_html = $event_html_by_plugin;
        } else {
            $event_tpl->parseCurrentBlock();
            $event_html = $event_tpl->get();
        }

        $this->tpl->setCurrentBlock("content_fd");
        $this->tpl->setVariable("CONTENT_EVENT_FD", $event_html);
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
        $event_tpl = new ilTemplate('tpl.week_event_view.html', true, true, 'Services/Calendar');

        $ilUser = $this->user;

        if (!$ilUser->prefs["screen_reader_optimization"]) {
            $this->tpl->setCurrentBlock('not_empty');
        } else {
            $this->tpl->setCurrentBlock('scrd_not_empty');
        }
        
        $this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
        $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'app_id', $a_app['event']->getEntryId());

        $color = $this->app_colors->getColorByAppointment($a_app['event']->getEntryId());
        $style = 'background-color: ' . $color . ';';
        $style .= ('color:' . ilCalendarUtil::calculateFontColor($color));
        $td_style = $style;

        
        if (!$a_app['event']->isFullDay()) {
            $time = $this->getAppointmentTimeString($a_app['event']);

            $td_style .= $a_app['event']->getPresentationStyle();
        }

        $shy = $this->getAppointmentShyButton($a_app['event'], $a_app['dstart'], "");

        $title = ($time != "")? $time . " " . $shy : $shy;

        $event_tpl->setCurrentBlock('event_cell_content');
        if (!$ilUser->prefs["screen_reader_optimization"]) {
            $event_tpl->setVariable("STYLE", $style);
        }
        $event_tpl->setVariable('EVENT_CONTENT', $title);

        if ($event_html_by_plugin = $this->getContentByPlugins($a_app['event'], $a_app['dstart'], $title, $event_tpl)) {
            $event_html = $event_html_by_plugin;
        } else {
            $event_tpl->parseCurrentBlock();
            $event_html = $event_tpl->get();
        }

        $this->tpl->setVariable('GRID_CONTENT', $event_html);

        if (!$ilUser->prefs["screen_reader_optimization"]) {
            // provide table cell attributes
            $this->tpl->parseCurrentBlock();

            $this->tpl->setCurrentBlock('day_cell');

            $this->tpl->setVariable('DAY_CELL_NUM', $this->num_appointments);
            $this->tpl->setVariable('TD_ROWSPAN', $a_app['rowspan']);
            //$event_tpl->setVariable('TD_STYLE',$td_style);
            $this->tpl->setVariable('TD_CLASS', 'calevent il_calevent');

            $this->tpl->parseCurrentBlock();
        } else {
            // screen reader: work on div attributes
            $this->tpl->setVariable('DIV_STYLE', $style);
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
    protected function parseHourInfo($daily_apps, $date, $num_day, $hours = null, $morning_aggr, $evening_aggr)
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        
        for ($i = $morning_aggr;$i <= $evening_aggr;$i+=$this->raster) {
            $hours[$i][$num_day]['apps_start'] = array();
            $hours[$i][$num_day]['apps_num'] = 0;
            switch ($this->user_settings->getTimeFormat()) {
                case ilCalendarSettings::TIME_FORMAT_24:
                    if ($morning_aggr > 0 && $i == $morning_aggr) {
                        $hours[$i][$num_day]['txt'] = sprintf('%02d:00', 0) . "-" .
                            sprintf('%02d:00', ceil(($i+1)/60));
                    } else {
                        $hours[$i][$num_day]['txt'].= sprintf('%02d:%02d', floor($i/60), $i%60);
                    }
                    if ($evening_aggr < 23*60 && $i == $evening_aggr) {
                        $hours[$i][$num_day]['txt'].= "-" . sprintf('%02d:00', 23);
                    }
                    break;
                
                case ilCalendarSettings::TIME_FORMAT_12:
                    if ($morning_aggr > 0 && $i == $morning_aggr) {
                        $hours[$i][$num_day]['txt'] = date('h a', mktime(0, 0, 0, 1, 1, 2000)) . "-";
                    }
                    $hours[$i][$num_day]['txt'].= date('h a', mktime(floor($i/60), $i%60, 0, 1, 1, 2000));
                    if ($evening_aggr < 23 && $i == $evening_aggr) {
                        $hours[$i][$num_day]['txt'].= "-" . date('h a', mktime(23, 0, 0, 1, 1, 2000));
                    }
                    break;
            }
        }
        
        $date_info = $date->get(IL_CAL_FKT_GETDATE, '', 'UTC');
        
        
        foreach ($daily_apps as $app) {
            // fullday appointment are not relavant
            if ($app['fullday']) {
                continue;
            }
            // start hour for this day
            #21636
            if ($app['start_info']['mday'] != $date_info['mday']) {
                $start = 0;
            } else {
                $start = $app['start_info']['hours']*60+$app['start_info']['minutes'];
            }
            #21132 #21636
            //$start = $app['start_info']['hours']*60+$app['start_info']['minutes'];

            // end hour for this day
            if ($app['end_info']['mday'] != $date_info['mday']) {
                $end = 23*60;
            } elseif ($app['start_info']['hours'] == $app['end_info']['hours']) {
                $end = $start+$raster;
            } else {
                $end = $app['end_info']['hours']*60+$app['end_info']['minutes'];
            }
            #21132 #21636
            //$end = $app['end_info']['hours']*60+$app['end_info']['minutes'];
            
            // set end to next hour for screen readers
            if ($ilUser->prefs["screen_reader_optimization"]) {
                $end = $start+$this->raster;
            }
            
            if ($start < $morning_aggr) {
                $start = $morning_aggr;
            }
            if ($end <= $morning_aggr) {
                $end = $morning_aggr+$this->raster;
            }
            if ($start > $evening_aggr) {
                $start = $evening_aggr;
            }
            if ($end > $evening_aggr+$this->raster) {
                $end = $evening_aggr+$this->raster;
            }
            if ($end <= $start) {
                $end = $start+$this->raster;
            }
            
            // map start and end to raster
            $start = floor($start/$this->raster)*$this->raster;
            $end = ceil($end/$this->raster)*$this->raster;

            $first = true;
            for ($i = $start;$i < $end;$i+=$this->raster) {
                if ($first) {
                    if (!$ilUser->prefs["screen_reader_optimization"]) {
                        $app['rowspan'] = ceil(($end - $start)/$this->raster);
                    } else {  	// screen readers get always a rowspan of 1
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
        global $DIC;

        $ilUser = $DIC['ilUser'];
        
        foreach ($hours as $hour_num => $hours_per_day) {
            foreach ($hours_per_day as $num_day => $hour) {
                $this->colspans[$num_day] = max($this->colspans[$num_day], $hour['apps_num']);
                
                // screen reader: always one col
                if ($ilUser->prefs["screen_reader_optimization"]) {
                    $this->colspans[$num_day] = 1;
                }
            }
        }
    }

    /**
     * @return int morning aggregated hours.
     */
    protected function getMorningAggr()
    {
        if ($this->user_settings->getDayStart()) {
            // push starting point to last "slot" of hour BEFORE morning aggregation
            $morning_aggr = ($this->user_settings->getDayStart()-1)*60+(60-$this->raster);
        } else {
            $morning_aggr = 0;
        }

        return $morning_aggr;
    }

    /**
     * Add the links to create an appointment or milestone.
     * @param $date
     */
    protected function addAppointmentLink($date)
    {
        $new_app_url = $this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui', 'add');

        if ($this->cal_settings->getEnableGroupMilestones()) {
            $new_ms_url = $this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui', 'addMilestone');

            $this->tpl->setCurrentBlock("new_ms");
            $this->tpl->setVariable('DD_ID', $date->get(IL_CAL_UNIX));
            $this->tpl->setVariable('DD_TRIGGER', $this->ui_renderer->render($this->ui_factory->glyph()->add()));
            $this->tpl->setVariable('URL_DD_NEW_APP', $new_app_url);
            $this->tpl->setVariable('TXT_DD_NEW_APP', $this->lng->txt('cal_new_app'));
            $this->tpl->setVariable('URL_DD_NEW_MS', $new_ms_url);
            $this->tpl->setVariable('TXT_DD_NEW_MS', $this->lng->txt('cal_new_ms'));
            $this->tpl->parseCurrentBlock();
        } else {
            $this->tpl->setCurrentBlock("new_app");
            //$this->tpl->setVariable('NEW_APP_LINK',$new_app_url);
            $this->tpl->setVariable('NEW_APP_GLYPH', $this->ui_renderer->render(
                $this->ui_factory->glyph()->add($new_app_url)
            ));
            // $this->tpl->setVariable('NEW_APP_ALT',$this->lng->txt('cal_new_app'));
            $this->tpl->parseCurrentBlock();
        }

        $this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
    }

    /**
     * Set values for: user_id, disable_empty, no_add
     */
    protected function setUpCalendar()
    {
        if (isset($_GET["bkid"])) {
            $this->user_id = $_GET["bkid"];
            $this->disable_empty = true;
            $this->no_add = true;
        } elseif ($this->user->getId() == ANONYMOUS_USER_ID) {
            //$this->user_id = $ilUser->getId();
            $this->disable_empty = false;
            $this->no_add = true;
        } else {
            //$this->user_id = $ilUser->getId();
            $this->disable_empty = false;
            $this->no_add = false;
        }
    }

    /**
     * @param $date
     * @param $num_apps
     */
    protected function addHeaderDate($date, $num_apps)
    {
        $date_info = $date->get(IL_CAL_FKT_GETDATE, '', 'UTC');
        $dayname = ilCalendarUtil::_numericDayToString($date->get(IL_CAL_FKT_DATE, 'w'), false);
        $daydate = $dayname . ' ' . $date_info['mday'] . '.';

        if (!$this->disable_empty || $num_apps[$date->get(IL_CAL_DATE)] > 0) {
            $link = $this->ctrl->getLinkTargetByClass('ilcalendardaygui', '');
            $this->ctrl->clearParametersByClass('ilcalendardaygui');

            $this->tpl->setCurrentBlock("day_view_link");
            $this->tpl->setVariable('HEADER_DATE', $daydate);
            $this->tpl->setVariable('DAY_VIEW_LINK', $link);
            $this->tpl->parseCurrentBlock();
        } else {
            $this->tpl->setCurrentBlock("day_view_no_link");
            $this->tpl->setVariable('HEADER_DATE', $daydate);
            $this->tpl->parseCurrentBlock();
        }
    }

    /**
     * @param array $all_fullday  array with all full day events
     */
    protected function addFullDayEvents($all_fullday)
    {
        $counter = 0;
        foreach ($all_fullday as $daily_apps) {
            foreach ($daily_apps as $event) {
                if ($event['fullday']) {
                    $this->showFulldayAppointment($event);
                }
            }
            $this->tpl->setCurrentBlock('f_day_row');
            $this->tpl->setVariable('COLSPAN', max($this->colspans[$counter], 1));
            $this->tpl->parseCurrentBlock();
            $counter++;
        }
        $this->tpl->setCurrentBlock('fullday_apps');
        $this->tpl->setVariable('TXT_F_DAY', $this->lng->txt("cal_all_day"));
        $this->tpl->parseCurrentBlock();
    }

    /**
     * @param $hours
     * @param $morning_aggr
     * @param $evening_aggr
     */
    protected function addTimedEvents($hours, $morning_aggr, $evening_aggr)
    {
        global $DIC;

        $ui_factory = $DIC->ui()->factory();
        $renderer = $DIC->ui()->renderer();

        $new_link_counter = 0;
        foreach ($hours as $num_hour => $hours_per_day) {
            $first = true;
            foreach ($hours_per_day as $num_day => $hour) {

                #ADD the hours in the left side of the grid.
                if ($first) {
                    if (!($num_hour%60) || ($num_hour == $morning_aggr && $morning_aggr) ||
                        ($num_hour == $evening_aggr && $evening_aggr)) {
                        $first = false;

                        // aggregation rows
                        if (($num_hour == $morning_aggr && $morning_aggr) ||
                            ($num_hour == $evening_aggr && $evening_aggr)) {
                            $this->tpl->setVariable('TIME_ROWSPAN', 1);
                        }
                        // rastered hour
                        else {
                            $this->tpl->setVariable('TIME_ROWSPAN', 60/$this->raster);
                        }

                        $this->tpl->setCurrentBlock('time_txt');

                        $this->tpl->setVariable('TIME', $hour['txt']);
                        $this->tpl->parseCurrentBlock();
                    }
                }

                foreach ($hour['apps_start'] as $app) {
                    $this->showAppointment($app);
                }

                // screen reader: appointments are divs, now output cell
                if ($this->user->prefs["screen_reader_optimization"]) {
                    $this->tpl->setCurrentBlock('scrd_day_cell');
                    $this->tpl->setVariable('TD_CLASS', 'calstd');
                    $this->tpl->parseCurrentBlock();
                }

                #echo "NUMDAY: ".$num_day;
                #echo "COLAPANS: ".max($colspans[$num_day],1).'<br />';
                $num_apps = $hour['apps_num'];
                $colspan = max($this->colspans[$num_day], 1);

                // Show new apointment link
                if (!$hour['apps_num'] && !$this->user->prefs["screen_reader_optimization"] && !$this->no_add) {
                    $this->tpl->setCurrentBlock('new_app_link');

                    $this->ctrl->clearParameterByClass('ilcalendarappointmentgui', 'app_id');

                    $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'idate', $this->weekdays[$num_day]->get(IL_CAL_DATE));
                    $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'seed', $this->seed->get(IL_CAL_DATE));
                    $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'hour', floor($num_hour/60));

                    //todo:it could be nice use also ranges of 15 min to create events.
                    $new_app_url = $this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui', 'add');
                    $this->tpl->setVariable("DAY_NEW_APP_LINK", $renderer->render($ui_factory->glyph()->add($new_app_url)));


                    $this->tpl->setVariable('DAY_NEW_ID', ++$new_link_counter);
                    $this->tpl->parseCurrentBlock();
                }

                for ($i = $colspan;$i > $hour['apps_num'];$i--) {
                    if ($this->user->prefs["screen_reader_optimization"]) {
                        continue;
                    }
                    $this->tpl->setCurrentBlock('day_cell');

                    // last "slot" of hour needs border
                    $empty_border = '';
                    if ($num_hour%60 == 60-$this->raster ||
                        ($num_hour == $morning_aggr && $morning_aggr) ||
                        ($num_hour == $evening_aggr && $evening_aggr)) {
                        $empty_border = ' calempty_border';
                    }

                    $this->tpl->setVariable('TD_CLASS', 'calempty createhover' . $empty_border);

                    if (!$hour['apps_num']) {
                        $this->tpl->setVariable('DAY_ID', $new_link_counter);
                    }
                    $this->tpl->setVariable('TD_ROWSPAN', 1);
                    $this->tpl->parseCurrentBlock();
                }
            }

            $this->tpl->touchBlock('time_row');
        }
    }

    /**
     * @param ilCalendarEntry $a_event
     * @return string
     */
    protected function getAppointmentTimeString(ilCalendarEntry $a_event)
    {
        $time = "";
        switch ($this->user_settings->getTimeFormat()) {
            case ilCalendarSettings::TIME_FORMAT_24:
                $time = $a_event->getStart()->get(IL_CAL_FKT_DATE, 'H:i', $this->timezone);
                break;

            case ilCalendarSettings::TIME_FORMAT_12:
                $time = $a_event->getStart()->get(IL_CAL_FKT_DATE, 'h:ia', $this->timezone);
                break;
        }
        // add end time for screen readers
        if ($this->user->prefs["screen_reader_optimization"]) {
            switch ($this->user_settings->getTimeFormat()) {
                case ilCalendarSettings::TIME_FORMAT_24:
                    $time.= "-" . $a_event->getEnd()->get(IL_CAL_FKT_DATE, 'H:i', $this->timezone);
                    break;

                case ilCalendarSettings::TIME_FORMAT_12:
                    $time.= "-" . $a_event->getEnd()->get(IL_CAL_FKT_DATE, 'h:ia', $this->timezone);
                    break;
            }
        }

        return $time;
    }
}
