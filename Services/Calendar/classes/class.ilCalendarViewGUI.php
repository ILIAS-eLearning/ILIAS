<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Jesús López Reyes <lopez@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesCalendar
 */
class ilCalendarViewGUI
{
    const CAL_PRESENTATION_DAY = 1;
    const CAL_PRESENTATION_WEEK = 2;
    const CAL_PRESENTATION_MONTH = 3;
    const CAL_PRESENTATION_AGENDA_LIST = 9;

    /**
     * @var \ILIAS\UI\Factory
     */
    protected $ui_factory;

    /**
     * @var \ILIAS\UI\Renderer
     */
    protected $ui_renderer;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var integer
     */
    protected $presentation_type;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilLogger
     */
    protected $logger;

    /**
     * @var \ILIAS\UI
     */
    protected $ui;

    /**
     * @var bool true if the displayed view contains appointments.
     */
    protected $view_with_appointments;

    /**
     * @var ilLanguage
     */
    protected $lng;
    
    /**
     * @var ilObjUser
     */
    protected $user;
    
    /**
     * @var string
     */
    protected $seed;
    
    /**
     * @var int
     */
    protected $ch_user_id = 0;

    

    /**
     *
     * @param ilDate $seed
     * @param int $presentation_type
     */
    public function __construct(ilDate $seed, $presentation_type)
    {
        $this->seed = $seed;
        $this->initialize($presentation_type);
    }
    
    
    public function setConsulationHoursUserId($a_user_id)
    {
        $this->ch_user_id = $a_user_id;
    }
    
    /**
     *
     */
    public function getConsultationHoursUserId()
    {
        return $this->ch_user_id;
    }


    
    

    /**
     * View initialization
     * @param integer $a_calendar_presentation_type
     */
    public function initialize($a_calendar_presentation_type)
    {
        global $DIC;
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->ui = $DIC->ui();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->tabs_gui = $DIC->tabs();
        $this->tpl = $DIC["tpl"];
        $this->toolbar = $DIC->toolbar();
        $this->presentation_type = $a_calendar_presentation_type;
        $this->logger = $GLOBALS['DIC']->logger()->cal();
        //by default "download files" button is not displayed.
        $this->view_with_appointments = false;

        if ($this->presentation_type == self::CAL_PRESENTATION_DAY ||
            $this->presentation_type == self::CAL_PRESENTATION_WEEK) {
            iljQueryUtil::initjQuery($this->tpl);
            $this->tpl->addJavaScript('./Services/Calendar/js/calendar_appointment.js');
        }
    }

    /**
     * Get app for id
     *
     * @param
     * @return
     */
    public function getCurrentApp()
    {
        // @todo: this needs optimization
        $events = $this->getEvents();
        foreach ($events as $item) {
            if ($item["event"]->getEntryId() == (int) $_GET["app_id"]) {
                return $item;
            }
        }
        return null;
    }

    /**
     * Get events
     * @todo public or protected
     * @param
     * @return
     */
    public function getEvents()
    {
        $user = $this->user->getId();
        
        switch ($this->presentation_type) {
            case self::CAL_PRESENTATION_AGENDA_LIST:

                //if($this->period_end_day == "")
                //{
                    $this->period = ilCalendarAgendaListGUI::getPeriod();

                    $end_date = clone $this->seed;
                    switch ($this->period) {
                        case ilCalendarAgendaListGUI::PERIOD_DAY:
                            $schedule = new ilCalendarSchedule($this->seed, ilCalendarSchedule::TYPE_DAY, $user, true);
                            $end_date->increment(IL_CAL_DAY, 1);
                            break;

                        case ilCalendarAgendaListGUI::PERIOD_WEEK:
                            $schedule = new ilCalendarSchedule($this->seed, ilCalendarSchedule::TYPE_WEEK, $user, true);
                            $end_date->increment(IL_CAL_WEEK, 1);
                            break;

                        case ilCalendarAgendaListGUI::PERIOD_MONTH:
                            $schedule = new ilCalendarSchedule($this->seed, ilCalendarSchedule::TYPE_MONTH, $user, true);
                            $end_date->increment(IL_CAL_MONTH, 1);
                            break;

                        case ilCalendarAgendaListGUI::PERIOD_HALF_YEAR:
                            $schedule = new ilCalendarSchedule($this->seed, ilCalendarSchedule::TYPE_HALF_YEAR, $user, true);
                            $end_date->increment(IL_CAL_MONTH, 6);
                            break;
                        default:
                            // default is week ?!
                            $schedule = new ilCalendarSchedule($this->seed, ilCalendarSchedule::TYPE_WEEK, $user, true);
                            $end_date->increment(IL_CAL_WEEK, 1);
                            break;
                    }
                    $this->period_end_day = $end_date->get(IL_CAL_DATE);
                    $schedule->setPeriod($this->seed, $end_date);

                //}
                /*else
                {
                    $schedule = new ilCalendarSchedule($this->seed, ilCalendarSchedule::TYPE_PD_UPCOMING);
                }*/

                break;
            case self::CAL_PRESENTATION_DAY:
                $schedule = new ilCalendarSchedule($this->seed, ilCalendarSchedule::TYPE_DAY, $user, true);
                break;
            case self::CAL_PRESENTATION_WEEK:
                $schedule = new ilCalendarSchedule($this->seed, ilCalendarSchedule::TYPE_WEEK, $user, true);
                break;
            case self::CAL_PRESENTATION_MONTH:
                // if we put the user and true in the call method we will get only events and
                // files from the current month. Not from 6 days before and after.
                $schedule = new ilCalendarSchedule($this->seed, ilCalendarSchedule::TYPE_MONTH);
                break;
        }

        $schedule->addSubitemCalendars(true);
        $schedule->calculate();
        $ev = $schedule->getScheduledEvents();
        return $ev;
    }


    /**
     * Get start/end date for item
     *
     * @param array $item item
     * @return array
     */
    public function getDatesForItem($item)
    {
        $start = $item["dstart"];
        $end = $item["dend"];
        if ($item["fullday"]) {
            $start = new ilDate($start, IL_CAL_UNIX);
            $end = new ilDate($end, IL_CAL_UNIX);
        } else {
            $start = new ilDateTime($start, IL_CAL_UNIX);
            $end = new ilDateTime($end, IL_CAL_UNIX);
        }
        return array("start" => $start, "end" => $end);
    }

    /**
     * Get modal for appointment (see similar code in ilCalendarBlockGUI)
     */
    public function getModalForApp()
    {
        $f = $this->ui_factory;
        $r = $this->ui_renderer;
        $ctrl = $this->ctrl;
        
        // set return class
        $this->ctrl->setReturn($this, '');

        // @todo: this needs optimization
        $events = $this->getEvents();

        //item => array containing ilcalendary object, dstart of the event , dend etc.
        foreach ($events as $item) {
            if ($item["event"]->getEntryId() == (int) $_GET["app_id"] && $item['dstart'] == (int) $_GET['dt']) {
                $dates = $this->getDatesForItem($item);
                // content of modal
                include_once("./Services/Calendar/classes/class.ilCalendarAppointmentPresentationGUI.php");
                $next_gui = ilCalendarAppointmentPresentationGUI::_getInstance($this->seed, $item);
                $content = $ctrl->getHTML($next_gui);

                //plugins can change the modal title.

                $modal_title = ilDatePresentation::formatPeriod($dates["start"], $dates["end"]);
                $modal_title = $this->getModalTitleByPlugins($modal_title);
                $modal = $f->modal()->roundtrip($modal_title, $f->legacy($content))->withCancelButtonLabel("close");

                echo $r->renderAsync($modal);
            }
        }
        exit();
    }

    /**
     * @param $a_calendar_entry
     * @param $a_dstart
     * @param string $a_title_forced  //used in plugins to rename the shy button title.
     * @return string  shy button html
     */
    public function getAppointmentShyButton($a_calendar_entry, $a_dstart, $a_title_forced = "")
    {
        $f = $this->ui_factory;
        $r = $this->ui_renderer;
        
        $this->ctrl->setParameter($this, "app_id", $a_calendar_entry->getEntryId());
        
        if ($this->getConsultationHoursUserId()) {
            $this->ctrl->setParameter($this, 'chuid', $this->getConsultationHoursUserId());
        }
        $this->ctrl->setParameter($this, 'dt', $a_dstart);
        $this->ctrl->setParameter($this, 'seed', $this->seed->get(IL_CAL_DATE));
        $url = $this->ctrl->getLinkTarget($this, "getModalForApp", "", true, false);
        $this->ctrl->setParameter($this, "app_id", $_GET["app_id"]);
        $this->ctrl->setParameter($this, "dt", $_GET["dt"]);
        $this->ctrl->setParameter($this, 'seed', $_GET["seed"]);

        $modal = $f->modal()->roundtrip('', [])->withAsyncRenderUrl($url);

        //Day view presents the titles with the full length.(agenda:class.ilCalendarAgendaListGUI.php)
        if ($this->presentation_type == self::CAL_PRESENTATION_DAY) {
            $title = ($a_title_forced == "")? $a_calendar_entry->getPresentationTitle(false) : $a_title_forced;
        } else {
            $title = ($a_title_forced == "")? $a_calendar_entry->getPresentationTitle() : $a_title_forced;
        }


        $comps = [$f->button()->shy($title, "#")->withOnClick($modal->getShowSignal()), $modal];

        return $r->render($comps);
    }

    //get active plugins.
    public function getActivePlugins($a_slot_id)
    {
        global $DIC;

        $ilPluginAdmin = $DIC['ilPluginAdmin'];

        $res = array();

        foreach ($ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "Calendar", $a_slot_id) as $plugin_name) {
            $res[] = $ilPluginAdmin->getPluginObject(
                IL_COMP_SERVICE,
                "Calendar",
                $a_slot_id,
                $plugin_name
            );
        }

        return $res;
    }

    public function getModalTitleByPlugins($a_current_title)
    {
        $modal_title = $a_current_title;
        //demo of plugin execution.
        //"capm" is the plugin slot id for Appointment presentations (modals)
        foreach ($this->getActivePlugins("capm") as $plugin) {
            $modal_title = ($new_title = $plugin->editModalTitle($a_current_title))? $new_title : $a_current_title;
        }
        return $modal_title;
    }

    /**
     * @param $a_cal_entry
     * @param $a_start_date
     * @param $a_content
     * @param $a_tpl needed to adding elements in the template like extra content inside the event container
     * @return string
     */
    public function getContentByPlugins($a_cal_entry, $a_start_date, $a_content, $a_tpl)
    {
        $content = $a_content;

        //"capg" is the plugin slot id for AppointmentCustomGrid
        foreach ($this->getActivePlugins("capg") as $plugin) {
            $plugin->setAppointment($a_cal_entry, new ilDateTime($a_start_date));

            if ($new_title = $plugin->editShyButtonTitle()) {
                $a_tpl->setVariable('EVENT_CONTENT', $this->getAppointmentShyButton($a_cal_entry, $a_start_date, $new_title));
            }

            if ($glyph = $plugin->addGlyph()) {
                $a_tpl->setVariable('EXTRA_GLYPH_BY_PLUGIN', $glyph);
            }

            if ($more_content = $plugin->addExtraContent()) {
                $a_tpl->setVariable('EXTRA_CONTENT_BY_PLUGIN', $more_content);
            }

            $a_tpl->parseCurrentBlock();
            $html_content = $a_tpl->get();

            if ($new_content = $plugin->replaceContent($html_content)) {
                $content = $new_content;
            }
        }
        if ($content == $a_content) {
            return false;
        }

        return $content;
    }

    /**
     * Add download link to toolbar
     *
     * //TODO rename this method to something like addToolbarDonwloadFiles
     * @param
     * @return
     */
    public function addToolbarActions()
    {
        $settings = ilCalendarSettings::_getInstance();

        if ($settings->isBatchFileDownloadsEnabled()) {
            if ($this->presentation_type == self::CAL_PRESENTATION_AGENDA_LIST) {
                $num_events = $this->countEventsInView();
            }
            if ($this->view_with_appointments || $num_events) {
                $toolbar = $this->toolbar;
                $f = $this->ui_factory;
                $lng = $this->lng;
                $ctrl = $this->ctrl;

                // file download
                $add_button = $f->button()->standard(
                    $lng->txt("cal_download_files"),
                    $ctrl->getLinkTarget($this, "downloadFiles")
                );
                $toolbar->addSeparator();
                $toolbar->addComponent($add_button);
            }
        }
    }

    /**
     * Download files related to the appointments showed in the current calendar view (day,week,month,list). Not modals
     */
    public function downloadFiles()
    {
        include_once './Services/Calendar/classes/BackgroundTasks/class.ilDownloadFilesBackgroundTask.php';
        $download_job = new ilDownloadFilesBackgroundTask($GLOBALS['DIC']->user()->getId());

        $download_job->setBucketTitle($this->getBucketTitle());
        $download_job->setEvents($this->getEvents());
        if ($download_job->run()) {
            ilUtil::sendSuccess($this->lng->txt('cal_download_files_started'), true);
        }
        $GLOBALS['DIC']->ctrl()->redirect($this);
    }

    /**
     * get proper label to add in the background task popover
     * @return string
     */
    public function getBucketTitle()
    {
        //definition of bucket titles here: 21365
        $user_settings = ilCalendarUserSettings::_getInstanceByUserId($this->user->getId());
        $bucket_title = $this->lng->txt("cal_calendar_download");

        switch ($this->presentation_type) {
            case self::CAL_PRESENTATION_DAY:
                $bucket_title .= " " . $this->seed->get(IL_CAL_DATE);
                break;
            case self::CAL_PRESENTATION_WEEK:
                $weekday_list = ilCalendarUtil::_buildWeekDayList($this->seed, $user_settings->getWeekStart())->get();
                $start = current($weekday_list);
                $char = strtolower(mb_substr($this->lng->txt("week"), 0, 1));
                $bucket_title .= " " . $start->get(IL_CAL_DATE) . " 1$char";
                break;
            case self::CAL_PRESENTATION_MONTH:
                $year_month = $this->seed->get(IL_CAL_FKT_DATE, 'Y-m', 'UTC');
                $char = strtolower(mb_substr($this->lng->txt("month"), 0, 1));
                $bucket_title .= " " . $year_month . " 1" . $char;
                break;
            case self::CAL_PRESENTATION_AGENDA_LIST:
                $bucket_title .= " " . $this->seed->get(IL_CAL_DATE);
                $get_list_option = ilSession::get('cal_list_view');
                switch ($get_list_option) {
                    case ilCalendarAgendaListGUI::PERIOD_DAY:
                        break;
                    case ilCalendarAgendaListGUI::PERIOD_MONTH:
                        $char = strtolower(mb_substr($this->lng->txt("month"), 0, 1));
                        $bucket_title .= " 1$char";
                        break;
                    case ilCalendarAgendaListGUI::PERIOD_HALF_YEAR:
                        $char = strtolower(mb_substr($this->lng->txt("month"), 0, 1));
                        $bucket_title .= " 6$char";
                        break;
                    case ilCalendarAgendaListGUI::PERIOD_WEEK:
                    default:
                        $char = strtolower(mb_substr($this->lng->txt("week"), 0, 1));
                        $bucket_title .= " 1$char";
                        break;
                }
        }

        return $bucket_title;
    }

    /**
     * get the events starting between 2 dates based in seed + view options.
     * @return int number of events in the calendar list view.
     */
    public function countEventsInView()
    {
        $start = $this->seed;
        $end = clone $start;
        $get_list_option = ilCalendarAgendaListGUI::getPeriod();
        switch ($get_list_option) {
            case ilCalendarAgendaListGUI::PERIOD_DAY:
                //$end->increment(IL_CAL_DAY,1);
                break;
            case ilCalendarAgendaListGUI::PERIOD_MONTH:
                $end->increment(IL_CAL_MONTH, 1);
                break;
            case ilCalendarAgendaListGUI::PERIOD_HALF_YEAR:
                $end->increment(IL_CAL_MONTH, 6);
                break;
            case ilCalendarAgendaListGUI::PERIOD_WEEK:
            default:
                $end->increment(IL_CAL_DAY, 7);
                break;
        }
        $events = $this->getEvents();
        $num_events = 0;
        foreach ($events as $event) {
            $event_start = $event['event']->getStart()->get(IL_CAL_DATE);

            if ($event_start >= $start->get(IL_CAL_DATE) && $event_start <= $end->get(IL_CAL_DATE)) {
                $num_events++;
            }
        }
        return $num_events;
    }
}
