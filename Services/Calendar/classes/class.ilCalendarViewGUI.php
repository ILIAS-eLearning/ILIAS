<?php declare(strict_types=1);

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\DI\UIServices;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\HTTP\Services as HttpServices;

/**
 * @author  Jesús López Reyes <lopez@leifos.com>
 * @version $Id$
 * @ingroup ServicesCalendar
 */
class ilCalendarViewGUI
{
    public const CAL_PRESENTATION_UNDEFINED = 0;
    public const CAL_PRESENTATION_DAY = 1;
    public const CAL_PRESENTATION_WEEK = 2;
    public const CAL_PRESENTATION_MONTH = 3;
    public const CAL_PRESENTATION_AGENDA_LIST = 9;

    protected int $presentation_type = self::CAL_PRESENTATION_UNDEFINED;
    protected bool $view_with_appointments = false;
    protected ilDate $seed;
    protected int $ch_user_id = 0;
    protected ?string $period_end_day = null;

    protected Factory $ui_factory;
    protected Renderer $ui_renderer;
    protected ilCtrlInterface $ctrl;
    protected ilToolbarGUI $toolbar;
    protected ilLogger $logger;
    protected \ILIAS\DI\UIServices $ui;
    protected ilLanguage $lng;
    protected ilObjUser $user;
    protected ?ilTemplate $tpl;
    protected ilGlobalTemplateInterface $main_tpl;
    protected ilComponentFactory $component_factory;
    protected ilTabsGUI $tabs_gui;
    protected RefineryFactory $refinery;
    protected HttpServices $http;

    public function __construct(ilDate $seed, int $presentation_type)
    {
        $this->seed = $seed;
        $this->initialize($presentation_type);
    }

    public function setConsulationHoursUserId(int $a_user_id) : void
    {
        $this->ch_user_id = $a_user_id;
    }

    public function getConsultationHoursUserId() : int
    {
        return $this->ch_user_id;
    }

    public function initialize(int $a_calendar_presentation_type) : void
    {
        global $DIC;

        $this->component_factory = $DIC['component.factory'];
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->ui = $DIC->ui();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->tabs_gui = $DIC->tabs();
        $this->toolbar = $DIC->toolbar();
        $this->presentation_type = $a_calendar_presentation_type;
        $this->logger = $DIC->logger()->cal();
        $this->view_with_appointments = false;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        if ($this->presentation_type == self::CAL_PRESENTATION_DAY ||
            $this->presentation_type == self::CAL_PRESENTATION_WEEK) {
            iljQueryUtil::initjQuery($this->main_tpl);
            $this->main_tpl->addJavaScript('./Services/Calendar/js/calendar_appointment.js');
        }
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
    }

    protected function initAppointmentIdFromQuery() : int
    {
        if ($this->http->wrapper()->query()->has('app_id')) {
            return $this->http->wrapper()->query()->retrieve(
                'app_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        return 0;
    }

    protected function initInitialDateFromQuery() : int
    {
        if ($this->http->wrapper()->query()->has('dt')) {
            return $this->http->wrapper()->query()->retrieve(
                'dt',
                $this->refinery->kindlyTo()->int()
            );
        }
        return 0;
    }

    protected function initBookingUserFromQuery() : int
    {
        if ($this->http->wrapper()->query()->has('bkid')) {
            return $this->http->wrapper()->query()->retrieve(
                'bkid',
                $this->refinery->kindlyTo()->int()
            );
        }
        return 0;
    }

    /**
     * @todo refactor the GET usage
     */
    public function getCurrentApp() : ?array
    {
        // @todo: this needs optimization
        $events = $this->getEvents();
        foreach ($events as $item) {
            if ($item["event"]->getEntryId() == $this->initAppointmentIdFromQuery()) {
                return $item;
            }
        }
        return null;
    }

    public function getEvents() : array
    {
        $user = $this->user->getId();

        $schedule = null;
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
                        $schedule = new ilCalendarSchedule(
                            $this->seed,
                            ilCalendarSchedule::TYPE_HALF_YEAR,
                            $user,
                            true
                        );
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
        return $schedule->getScheduledEvents();
    }

    /**
     * @param $item
     * @return array<{start: ilDate|ilDateTime, end: ilDate|ilDateTime}>
     * @throws ilDateTimeException
     */
    public function getDatesForItem($item) : array
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
     * @todo fix envent initialisation
     */
    public function getModalForApp() : void
    {
        // set return class
        $this->ctrl->setReturn($this, '');

        // @todo: this needs optimization
        $events = $this->getEvents();

        //item => array containing ilcalendary object, dstart of the event , dend etc.
        foreach ($events as $item) {
            if ($item["event"]->getEntryId() == $this->initAppointmentIdFromQuery() && $item['dstart'] == $this->initInitialDateFromQuery()) {
                $dates = $this->getDatesForItem($item);
                // content of modal
                $next_gui = ilCalendarAppointmentPresentationGUI::_getInstance($this->seed, $item);
                $content = $this->ctrl->getHTML($next_gui);

                //plugins can change the modal title.
                $modal_title = ilDatePresentation::formatPeriod($dates["start"], $dates["end"]);
                $modal_title = $this->getModalTitleByPlugins($modal_title);
                $modal = $this->ui_factory->modal()->roundtrip(
                    $modal_title,
                    $this->ui_factory->legacy($content)
                )->withCancelButtonLabel("close");
                echo $this->ui_renderer->renderAsync($modal);
            }
        }
        exit();
    }

    /**
     * @param ilCalendarEntry $a_calendar_entry
     * @param string          $a_dstart
     * @param string          $a_title_forced //used in plugins to rename the shy button title.
     * @return string  shy button html
     */
    public function getAppointmentShyButton(
        ilCalendarEntry $a_calendar_entry,
        string $a_dstart,
        string $a_title_forced = ""
    ) : string {
        $this->ctrl->setParameter($this, "app_id", $a_calendar_entry->getEntryId());

        if ($this->getConsultationHoursUserId()) {
            $this->ctrl->setParameter($this, 'chuid', $this->getConsultationHoursUserId());
        }
        $this->ctrl->setParameter($this, 'dt', $a_dstart);
        $this->ctrl->setParameter($this, 'seed', $this->seed->get(IL_CAL_DATE));
        $url = $this->ctrl->getLinkTarget($this, "getModalForApp", "", true, false);
        $this->ctrl->setParameter($this, "app_id", $this->initAppointmentIdFromQuery());
        $this->ctrl->setParameter($this, "dt", $this->initInitialDateFromQuery());
        $this->ctrl->setParameter($this, 'seed', $this->seed->get(IL_CAL_DATE));

        $modal = $this->ui_factory->modal()->roundtrip('', [])->withAsyncRenderUrl($url);

        //Day view presents the titles with the full length.(agenda:class.ilCalendarAgendaListGUI.php)
        if ($this->presentation_type == self::CAL_PRESENTATION_DAY) {
            $title = ($a_title_forced == "") ? $a_calendar_entry->getPresentationTitle(false) : $a_title_forced;
        } else {
            $title = ($a_title_forced == "") ? $a_calendar_entry->getPresentationTitle() : $a_title_forced;
        }
        $comps = [$this->ui_factory->button()->shy($title, "#")->withOnClick($modal->getShowSignal()), $modal];
        return $this->ui_renderer->render($comps);
    }

    /**
     * @param string $a_slot_id
     * @return Iterator <ilPlugin>
     */
    public function getActivePlugins(string $a_slot_id) : Iterator
    {
        return $this->component_factory->getActivePluginsInSlot($a_slot_id);
    }

    public function getModalTitleByPlugins(string $a_current_title) : string
    {
        $modal_title = $a_current_title;
        //demo of plugin execution.
        //"capm" is the plugin slot id for Appointment presentations (modals)
        foreach ($this->getActivePlugins("capm") as $plugin) {
            $modal_title = ($new_title = $plugin->editModalTitle($a_current_title)) ? $new_title : $a_current_title;
        }
        return $modal_title;
    }

    /**
     * @param ilCalendarEntry $a_cal_entry
     * @param int             $a_start_date
     * @param string          $a_content
     * @param ilTemplate      $a_tpl needed to adding elements in the template like extra content inside the event container
     * @return string
     */
    public function getContentByPlugins(
        ilCalendarEntry $a_cal_entry,
        int $a_start_date,
        string $a_content,
        ilTemplate $a_tpl
    ) : string {
        $content = $a_content;

        //"capg" is the plugin slot id for AppointmentCustomGrid
        foreach ($this->getActivePlugins("capg") as $plugin) {
            $plugin->setAppointment($a_cal_entry, new ilDateTime($a_start_date, IL_CAL_UNIX));

            if ($new_title = $plugin->editShyButtonTitle()) {
                $a_tpl->setVariable(
                    'EVENT_CONTENT',
                    $this->getAppointmentShyButton($a_cal_entry, (string) $a_start_date, $new_title)
                );
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
            return '';
        }
        return $content;
    }

    /**
     * Add download link to toolbar
     */
    public function addToolbarFileDownload() : void
    {
        $settings = ilCalendarSettings::_getInstance();

        if ($settings->isBatchFileDownloadsEnabled()) {
            $num_events = 0;
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
                    $this->lng->txt("cal_download_files"),
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
    public function downloadFiles() : void
    {
        $download_job = new ilDownloadFilesBackgroundTask($this->user->getId());
        $download_job->setBucketTitle($this->getBucketTitle());
        $download_job->setEvents($this->getEvents());
        if ($download_job->run()) {
            $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('cal_download_files_started'), true);
        }
        $this->ctrl->redirect($this);
    }

    /**
     * get proper label to add in the background task popover
     */
    public function getBucketTitle() : string
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
                $get_list_option = intval($this->user->getPref('cal_list_view'));
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
    public function countEventsInView() : int
    {
        $start = $this->seed;
        $end = clone $start;
        $get_list_option = ilCalendarAgendaListGUI::getPeriod();
        switch ($get_list_option) {
            case ilCalendarAgendaListGUI::PERIOD_DAY:
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
