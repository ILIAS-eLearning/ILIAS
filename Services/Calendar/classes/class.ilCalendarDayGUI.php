<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Presentation day view
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilCalendarDayGUI: ilCalendarAppointmentGUI
* @ilCtrl_Calls ilCalendarDayGUI: ilCalendarAppointmentPresentationGUI
* @ingroup ServicesCalendar
*/

include_once('./Services/Calendar/classes/class.ilDate.php');
include_once('./Services/Calendar/classes/class.ilCalendarUtil.php');
include_once('./Services/Calendar/classes/class.ilCalendarHeaderNavigationGUI.php');
include_once('./Services/Calendar/classes/class.ilCalendarUserSettings.php');
include_once('./Services/Calendar/classes/class.ilCalendarAppointmentColors.php');
include_once './Services/Calendar/classes/class.ilCalendarViewGUI.php';


class ilCalendarDayGUI extends ilCalendarViewGUI
{
    protected $seed_info = array();
    protected $user_settings = null;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTabsGUI
     */
    protected $tabs_gui;

    /**
     * @var ilTemplate
     */
    protected $tpl;
    
    protected $num_appointments = 1;
    
    protected $timezone = 'UTC';

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * Constructor
     *
     * @access public
     * @param
     *
     * @todo make parent constructor (initialize) and init also seed and other common stuff
     */
    public function __construct(ilDate $seed_date)
    {
        parent::__construct($seed_date, ilCalendarViewGUI::CAL_PRESENTATION_DAY);

        $this->seed_info = $this->seed->get(IL_CAL_FKT_GETDATE);

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
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;

        $next_class = $ilCtrl->getNextClass();
        switch ($next_class) {
            case "ilcalendarappointmentpresentationgui":
                $this->ctrl->setReturn($this, "");
                include_once("./Services/Calendar/classes/class.ilCalendarAppointmentPresentationGUI.php");
                $this->logger->debug("-ExecCommand - representation of ilDate: this->seed->get(IL_CAL_DATE) = " . $this->seed->get(IL_CAL_DATE));
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
        /**
         * @var ILIAS\DI\Container $DIC
         */
        global $DIC;

        $ui_factory = $DIC->ui()->factory();
        $renderer = $DIC->ui()->renderer();

        $lng = $this->lng;
        $ilUser = $this->user;


        // config
        $raster = 15;
        if ($this->user_settings->getDayStart()) {
            // push starting point to last "slot" of hour BEFORE morning aggregation
            $morning_aggr = ($this->user_settings->getDayStart()-1)*60+(60-$raster);
        } else {
            $morning_aggr = 0;
        }
        $evening_aggr = $this->user_settings->getDayEnd()*60;
        
                        
        $this->tpl = new ilTemplate('tpl.day_view.html', true, true, 'Services/Calendar');
        
        include_once('./Services/YUI/classes/class.ilYuiUtil.php');
        ilYuiUtil::initDragDrop();

        if (isset($_GET["bkid"])) {
            $user_id = $_GET["bkid"];
            $no_add = true;
        } elseif ($ilUser->getId() == ANONYMOUS_USER_ID) {
            $user_id = $ilUser->getId();
            $no_add = true;
        } else {
            $user_id = $ilUser->getId();
            $no_add = false;
        }
        include_once('Services/Calendar/classes/class.ilCalendarSchedule.php');
        $this->scheduler = new ilCalendarSchedule($this->seed, ilCalendarSchedule::TYPE_DAY, $user_id);
        $this->scheduler->addSubitemCalendars(true);
        $this->scheduler->calculate();
        $daily_apps = $this->scheduler->getByDay($this->seed, $this->timezone);

        //display the download files button.
        if (count($daily_apps)) {
            $this->view_with_appointments = true;
        }

        $hours = $this->parseInfoIntoRaster(
            $daily_apps,
            $morning_aggr,
            $evening_aggr,
            $raster
        );
        
        $colspan = $this->calculateColspan($hours);
        
        $navigation = new ilCalendarHeaderNavigationGUI($this, $this->seed, ilDateTime::DAY);
        $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'seed', $this->seed->get(IL_CAL_DATE));
        
        // add milestone link
        include_once('Services/Calendar/classes/class.ilCalendarSettings.php');
        $settings = ilCalendarSettings::_getInstance();

        if (!$no_add) {
            $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'seed', $this->seed->get(IL_CAL_DATE));
            $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'idate', $this->seed->get(IL_CAL_DATE));
            $new_app_url = $this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui', 'add');
            
            if ($settings->getEnableGroupMilestones()) {
                $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'seed', $this->seed->get(IL_CAL_DATE));
                $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'idate', $this->seed->get(IL_CAL_DATE));
                $new_ms_url = $this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui', 'addMilestone');
                
                $this->tpl->setCurrentBlock("new_ms");
                $this->tpl->setVariable('DD_ID', $this->seed->get(IL_CAL_UNIX));
                $this->tpl->setVariable('DD_TRIGGER', $renderer->render($ui_factory->glyph()->add()));
                $this->tpl->setVariable('URL_DD_NEW_APP', $new_app_url);
                $this->tpl->setVariable('TXT_DD_NEW_APP', $this->lng->txt('cal_new_app'));
                $this->tpl->setVariable('URL_DD_NEW_MS', $new_ms_url);
                $this->tpl->setVariable('TXT_DD_NEW_MS', $this->lng->txt('cal_new_ms'));
                $this->tpl->parseCurrentBlock();
            } else {
                $this->tpl->setCurrentBlock("new_app1");
                $this->tpl->setVariable('H_NEW_APP_GLYPH', $renderer->render($ui_factory->glyph()->add($new_app_url)));
                $this->tpl->parseCurrentBlock();
            }
            
            $this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
        }
        
        $this->tpl->setVariable('NAVIGATION', $navigation->getHTML());
        
        $this->tpl->setVariable('HEADER_DATE', $this->seed_info['mday'] . ' ' . ilCalendarUtil::_numericMonthToString($this->seed_info['mon'], false));
        $this->tpl->setVariable('HEADER_DAY', ilCalendarUtil::_numericDayToString($this->seed_info['wday'], true));
        $this->tpl->setVariable('HCOLSPAN', $colspan - 1);
        
        $this->tpl->setVariable('TXT_TIME', $lng->txt("time"));

        // show fullday events
        foreach ($daily_apps as $event) {
            if ($event['fullday']) {
                $this->showFulldayAppointment($event);
            }
        }
        $this->tpl->setCurrentBlock('fullday_apps');
        $this->tpl->setVariable('TXT_F_DAY', $lng->txt("cal_all_day"));
        $this->tpl->setVariable('COLSPAN', $colspan - 1);
        $this->tpl->parseCurrentBlock();
        
        // parse the hour rows
        foreach ($hours as $numeric => $hour) {
            if (!($numeric%60) || ($numeric == $morning_aggr && $morning_aggr) ||
                ($numeric == $evening_aggr && $evening_aggr)) {
                if (!$no_add) {
                    $this->tpl->setCurrentBlock("new_app2");
                    $this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
                    $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'seed', $this->seed->get(IL_CAL_DATE));
                    $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'idate', $this->seed->get(IL_CAL_DATE));
                    $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'hour', floor($numeric/60));
                    $this->tpl->setVariable('NEW_APP_GLYPH', $renderer->render(
                        $ui_factory->glyph()->add(
                                $this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui', 'add')
                            )
                    ));
                    $this->tpl->parseCurrentBlock();
                }
                
                // aggregation rows
                if (($numeric == $morning_aggr && $morning_aggr) ||
                    ($numeric == $evening_aggr && $evening_aggr)) {
                    $this->tpl->setVariable('TIME_ROWSPAN', 1);
                }
                // rastered hour
                else {
                    $this->tpl->setVariable('TIME_ROWSPAN', 60/$raster);
                }
                
                $this->tpl->setCurrentBlock('time_txt');
                
                $this->tpl->setVariable('TIME', $hour['txt']);
                $this->tpl->parseCurrentBlock();
            }
            
            foreach ($hour['apps_start'] as $app) {
                $this->showAppointment($app);
            }
            
            if ($ilUser->prefs["screen_reader_optimization"]) {
                // see #0022492
                //$this->tpl->touchBlock('scrd_app_cell');
            }
            
            for ($i = ($colspan - 1);$i > $hour['apps_num'];$i--) {
                $this->tpl->setCurrentBlock('empty_cell');
                $this->tpl->setVariable('EMPTY_WIDTH', (100 / (int) ($colspan - 1)) . '%');
                
                // last "slot" of hour needs border
                if ($numeric%60 == 60-$raster ||
                    ($numeric == $morning_aggr && $morning_aggr) ||
                    ($numeric == $evening_aggr && $evening_aggr)) {
                    $this->tpl->setVariable('EMPTY_STYLE', ' calempty_border');
                }
                
                $this->tpl->parseCurrentBlock();
            }
            
            $this->tpl->touchBlock('time_row');
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
        $f = $this->ui_factory;
        $r = $this->ui_renderer;

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

        //$title = ($new_title = $this->getContentByPlugins($a_app['event'], $a_app['dstart'], $shy))? $new_title : $shy;

        $content = $shy . $compl;

        $event_tpl->setVariable('EVENT_CONTENT', $content);

        $color = $this->app_colors->getColorByAppointment($a_app['event']->getEntryId());
        $event_tpl->setVariable('F_APP_BGCOLOR', $color);
        $event_tpl->setVariable('F_APP_FONTCOLOR', ilCalendarUtil::calculateFontColor($color));

        $this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
        $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'seed', $this->seed->get(IL_CAL_DATE));
        $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'app_id', $a_app['event']->getEntryId());
        $event_tpl->setVariable('F_APP_EDIT_LINK', $this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui', 'edit'));

        if ($event_html_by_plugin = $this->getContentByPlugins($a_app['event'], $a_app['dstart'], $content, $event_tpl)) {
            $body_html = $event_html_by_plugin;
        } else {
            $event_tpl->parseCurrentBlock();
            $body_html = $event_tpl->get();
        }

        $this->tpl->setCurrentBlock("content_fd");
        $this->tpl->setVariable("CONTENT_EVENT", $body_html);
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

        $event_tpl = new ilTemplate('tpl.day_event_view.html', true, true, 'Services/Calendar');

        if (!$ilUser->prefs["screen_reader_optimization"]) {
            $event_tpl->setCurrentBlock('app');
        } else {
            $event_tpl->setCurrentBlock('scrd_app');
        }

        $this->tpl->setVariable('APP_ROWSPAN', $a_app['rowspan']);
        //$event_tpl->setVariable('APP_TITLE',$a_app['event']->getPresentationTitle(false));

        switch ($this->user_settings->getTimeFormat()) {
            case ilCalendarSettings::TIME_FORMAT_24:
                $time = $a_app['event']->getStart()->get(IL_CAL_FKT_DATE, 'H:i', $this->timezone);
                break;
                
            case ilCalendarSettings::TIME_FORMAT_12:
                $time = $a_app['event']->getStart()->get(IL_CAL_FKT_DATE, 'h:ia', $this->timezone);
                break;
        }
        
        // add end time for screen readers
        if ($ilUser->prefs["screen_reader_optimization"]) {
            switch ($this->user_settings->getTimeFormat()) {
                case ilCalendarSettings::TIME_FORMAT_24:
                    $time.= "-" . $a_app['event']->getEnd()->get(IL_CAL_FKT_DATE, 'H:i', $this->timezone);
                    break;
                    
                case ilCalendarSettings::TIME_FORMAT_12:
                    $time.= "-" . $a_app['event']->getEnd()->get(IL_CAL_FKT_DATE, 'h:ia', $this->timezone);
                    break;
            }
        }

        $shy = $this->getAppointmentShyButton($a_app['event'], $a_app['dstart'], "");

        $title = $shy;
        $content = ($time != "")? $time . " " . $title : $title;

        $event_tpl->setVariable('EVENT_CONTENT', $content);

        $color = $this->app_colors->getColorByAppointment($a_app['event']->getEntryId());
        $event_tpl->setVariable('APP_BGCOLOR', $color);
        //$this->tpl->setVariable('APP_BGCOLOR',$color);
        $event_tpl->setVariable('APP_COLOR', ilCalendarUtil::calculateFontColor($color));
        //$this->tpl->setVariable('APP_COLOR',ilCalendarUtil::calculateFontColor($color));
        $event_tpl->setVariable('APP_ADD_STYLES', $a_app['event']->getPresentationStyle());
        //$this->tpl->setVariable('APP_ADD_STYLES',$a_app['event']->getPresentationStyle());

        $this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
        $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'seed', $this->seed->get(IL_CAL_DATE));
        $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'app_id', $a_app['event']->getEntryId());
        $event_tpl->setVariable('APP_EDIT_LINK', $this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui', 'edit'));

        if ($event_html_by_plugin = $this->getContentByPlugins($a_app['event'], $a_app['dstart'], $content, $event_tpl)) {
            $event_html = $event_html_by_plugin;
        } else {
            $event_tpl->parseCurrentBlock();
            $event_html = $event_tpl->get();
        }

        $this->tpl->setCurrentBlock("event_nfd");
        $this->tpl->setVariable("CONTENT_EVENT_NFD", $event_html);
        $this->tpl->parseCurrentBlock();

        $this->num_appointments++;
    }
    
    /**
     * calculate overlapping hours
     *
     * @access protected
     * @return array hours
     */
    protected function parseInfoIntoRaster($daily_apps, $morning_aggr, $evening_aggr, $raster)
    {
        $ilUser = $this->user;

        $hours = array();
        for ($i = $morning_aggr;$i <= $evening_aggr;$i+=$raster) {
            $hours[$i]['apps_start'] = array();
            $hours[$i]['apps_num'] = 0;
    
            switch ($this->user_settings->getTimeFormat()) {
                case ilCalendarSettings::TIME_FORMAT_24:
                    if ($morning_aggr > 0 && $i == $morning_aggr) {
                        $hours[$i]['txt'] = sprintf('%02d:00', 0) . ' - ' .
                            sprintf('%02d:00', ceil(($i+1)/60));
                    } else {
                        $hours[$i]['txt'].= sprintf('%02d:%02d', floor($i/60), $i%60);
                    }
                    if ($evening_aggr < 23*60 && $i == $evening_aggr) {
                        $hours[$i]['txt'].= ' - ' . sprintf('%02d:00', 0);
                    }
                    break;
                
                case ilCalendarSettings::TIME_FORMAT_12:

                    $this->logger->notice('Morning: ' . $morning_aggr . ' and $i:' . $i);

                    if ($morning_aggr > 0 && $i == $morning_aggr) {
                        $hours[$i]['txt'] =
                            date('h a', mktime(0,0,0,1,1,2000)) . ' - ' .
                            date('h a', mktime($this->user_settings->getDayStart(), 0, 0, 1, 1, 2000));
                    } else {
                        $hours[$i]['txt'] = date('h a', mktime(floor($i/60), $i%60, 0, 1, 1, 2000));
                    }
                    if ($evening_aggr < 23*60 && $i == $evening_aggr) {
                        $hours[$i]['txt'].= ' - ' . date('h a', mktime(0, 0, 0, 1, 1, 2000));
                    }
                    break;
            }
        }
        
        
        foreach ($daily_apps as $app) {
            // fullday appointment are not relavant
            if ($app['fullday']) {
                continue;
            }
            // start hour for this day
            #21132 #21636
            if ($app['start_info']['mday'] != $this->seed_info['mday']) {
                $start = 0;
            } else {
                $start = $app['start_info']['hours']*60+$app['start_info']['minutes'];
            }
            #21636
            //$start = $app['start_info']['hours']*60+$app['start_info']['minutes'];

            // end hour for this day
            #21132
            if ($app['end_info']['mday'] != $this->seed_info['mday']) {
                $end = 23*60;
            } elseif ($app['start_info']['hours'] == $app['end_info']['hours']) {
                $end = $start+$raster;
            } else {
                $end = $app['end_info']['hours']*60+$app['end_info']['minutes'];
            }
            //$end = $app['end_info']['hours']*60+$app['end_info']['minutes'];

            // set end to next hour for screen readers
            if ($ilUser->prefs["screen_reader_optimization"]) {
                $end = $start+$raster;
            }
            
            if ($start < $morning_aggr) {
                $start = $morning_aggr;
            }
            if ($end <= $morning_aggr) {
                $end = $morning_aggr+$raster;
            }
            if ($start > $evening_aggr) {
                $start = $evening_aggr;
            }
            if ($end > $evening_aggr+$raster) {
                $end = $evening_aggr+$raster;
            }
            if ($end <= $start) {
                $end = $start+$raster;
            }
            
            // map start and end to raster
            $start = floor($start/$raster)*$raster;
            $end = ceil($end/$raster)*$raster;
                        
            $first = true;
            for ($i = $start;$i < $end;$i+=$raster) {
                if ($first) {
                    if (!$ilUser->prefs["screen_reader_optimization"]) {
                        $app['rowspan'] = ceil(($end - $start)/$raster);
                    } else {  	// screen readers get always a rowspan of 1
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
        $ilUser = $this->user;


        $colspan = 1;
        foreach ($hours as $hour) {
            $colspan = max($colspan, $hour['apps_num'] + 1);
        }
        
        // screen reader: always two cols (time and event col)
        if ($ilUser->prefs["screen_reader_optimization"]) {
            $colspan = 2;
        }
        
        return max($colspan, 2);
    }
}
