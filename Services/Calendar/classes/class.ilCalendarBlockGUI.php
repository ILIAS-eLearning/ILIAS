<?php

declare(strict_types=1);

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Component\ViewControl\Section as Section;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\HTTP\Services as HttpServices;

/**
 * Calendar blocks, displayed in different contexts, e.g. groups and courses
 * @author            Alex Killing <alex.killing@gmx.de>
 * @ilCtrl_IsCalledBy ilCalendarBlockGUI: ilColumnGUI
 * @ilCtrl_Calls      ilCalendarBlockGUI: ilCalendarAppointmentGUI, ilCalendarMonthGUI, ilCalendarWeekGUI, ilCalendarDayGUI
 * @ilCtrl_Calls      ilCalendarBlockGUI: ilConsultationHoursGUI, ilCalendarAppointmentPresentationGUI
 * @ingroup           ServicesCalendar
 */
class ilCalendarBlockGUI extends ilBlockGUI
{
    protected array $cal_footer = [];
    protected ?ilCalendarSchedule $scheduler = null;
    protected RefineryFactory $refinery;
    protected HttpServices $http;
    protected int $mode = ilCalendarCategories::MODE_UNDEFINED;
    protected string $display_mode = '';

    public static string $block_type = "cal";

    protected ilTabsGUI $tabs;
    protected ilObjectDataCache $obj_data_cache;
    protected ilHelpGUI $help;


    protected ilDate $seed;
    protected ilCalendarSettings $settings;
    protected ilCalendarUserSettings $user_settings;

    protected string $parent_gui = ilColumnGUI::class;
    protected bool $force_month_view = false;

    protected int $requested_cal_agenda_per;

    /**
     * Constructor
     * @todo check if setLimit is required
     */
    public function __construct()
    {
        global $DIC;

        parent::__construct();

        $this->tabs = $DIC->tabs();
        $this->obj_data_cache = $DIC["ilObjDataCache"];
        $this->ui = $DIC->ui();
        $this->help = $DIC->help();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        $this->lng->loadLanguageModule("dateplaner");
        $this->help->addHelpSection("cal_block");

        $this->ctrl->saveParameter($this, 'bkid');
        $this->setBlockId((string) $this->ctrl->getContextObjId());
        $this->setLimit(5);            // @todo: needed?
        $this->setEnableNumInfo(false);
        $title = $this->lng->txt("calendar");
        $this->setTitle($title);
        $this->allow_moving = false;

        $params = $DIC->http()->request()->getQueryParams();
        $this->requested_cal_agenda_per = (int) ($params['cal_agenda_per'] ?? null);

        $seed_str = $this->initSeedFromQuery();
        if (!strlen($seed_str) && ilSession::has("il_cal_block_" . $this->getBlockType() . "_" . $this->getBlockId() . "_seed")) {
            $seed_str = ilSession::get("il_cal_block_" . $this->getBlockType() . "_" . $this->getBlockId() . "_seed");
        } elseif (strlen($seed_str)) {
            ilSession::set("il_cal_block_" . $this->getBlockType() . "_" . $this->getBlockId() . "_seed", $seed_str);
        } else {
            $seed_str = date('Y-m-d', time());
        }
        $this->seed = new ilDate($seed_str, IL_CAL_DATE);
        $this->settings = ilCalendarSettings::_getInstance();
        $this->user_settings = ilCalendarUserSettings::_getInstanceByUserId($DIC->user()->getId());

        $mode = $this->user->getPref("il_pd_cal_mode");
        $this->display_mode = $mode ?: "mmon";

        if ($this->display_mode !== "mmon") {
            $this->setPresentation(self::PRES_SEC_LIST);
        }
    }

    protected function initBookingUserFromQuery(): int
    {
        if ($this->http->wrapper()->query()->has('bkid')) {
            return $this->http->wrapper()->query()->retrieve(
                'bkid',
                $this->refinery->kindlyTo()->int()
            );
        }
        return 0;
    }

    protected function initSeedFromQuery(): string
    {
        if ($this->http->wrapper()->query()->has('seed')) {
            return $this->http->wrapper()->query()->retrieve(
                'seed',
                $this->refinery->kindlyTo()->string()
            );
        }
        return '';
    }

    protected function initAppointmentIdFromQuery(): int
    {
        if ($this->http->wrapper()->query()->has('app_id')) {
            return $this->http->wrapper()->query()->retrieve(
                'app_id',
                $this->refinery->kindlyTo()->string()
            );
        }
        return 0;
    }

    protected function initInitialDateQuery(): int
    {
        if ($this->http->wrapper()->query()->has('dt')) {
            return $this->http->wrapper()->query()->retrieve(
                'dt',
                $this->refinery->kindlyTo()->string()
            );
        }
        return 0;
    }

    /**
     * Show weeks column
     */
    public function getShowWeeksColumn(): bool
    {
        return ($this->settings->getShowWeeks() && $this->user_settings->getShowWeeks());
    }

    /**
     * @inheritdoc
     */
    public function getBlockType(): string
    {
        return self::$block_type;
    }

    /**
     * @inheritdoc
     */
    protected function isRepositoryObject(): bool
    {
        return false;
    }

    public function setParentGUI(string $a_val): void
    {
        $this->parent_gui = $a_val;
    }

    public function getParentGUI(): string
    {
        return $this->parent_gui;
    }

    public function setForceMonthView(bool $a_val): void
    {
        $this->force_month_view = $a_val;
        if ($a_val) {
            $this->display_mode = "mmon";
            $this->setPresentation(self::PRES_SEC_LEG);
        }
    }

    public function getForceMonthView(): bool
    {
        return $this->force_month_view;
    }

    /**
     * Get Screen Mode for current command.
     */
    public static function getScreenMode(): string
    {
        global $DIC;

        $ilCtrl = $DIC->ctrl();
        $cmd_class = $ilCtrl->getCmdClass();

        $cmd = $ilCtrl->getCmd();

        if ($cmd_class == "ilcalendarappointmentgui" ||
            $cmd_class == "ilconsultationhoursgui" ||
            $cmd == 'showCalendarSubscription') {
            return IL_SCREEN_CENTER;
        }
        return '';
    }

    public function executeCommand(): string
    {
        $next_class = $this->ctrl->getNextClass();
        $cmd = $this->ctrl->getCmd("getHTML");

        $this->setSubTabs();

        switch ($next_class) {
            case "ilcalendarappointmentgui":
                $this->initCategories();
                $app_gui = new ilCalendarAppointmentGUI($this->seed, $this->seed);
                $this->ctrl->forwardCommand($app_gui);
                break;

            case "ilconsultationhoursgui":
                $hours = new ilConsultationHoursGUI();
                $this->ctrl->forwardCommand($hours);
                break;

            case "ilcalendarappointmentpresentationgui":
                $this->initCategories();
                $presentation = ilCalendarAppointmentPresentationGUI::_getInstance($this->seed, []);
                $this->ctrl->forwardCommand($presentation);
                break;

            case "ilcalendarmonthgui":
                $this->tabs->setSubTabActive('app_month');
                $month_gui = new ilCalendarMonthGUI($this->seed);
                $this->ctrl->forwardCommand($month_gui);
                break;

            default:
                return $this->$cmd();
        }
        return '';
    }

    public function fillDataSection(): void
    {
        if ($this->display_mode != "mmon") {
            $this->setRowTemplate("tpl.pd_event_list.html", "Services/Calendar");

            ilBlockGUI::fillDataSection();
        } else {
            $tpl = new ilTemplate(
                "tpl.calendar_block.html",
                true,
                true,
                "Services/Calendar"
            );

            $this->addMiniMonth($tpl, true);
            $this->setDataSection($tpl->get());
        }
    }

    public function getTargetGUIClassPath(): array
    {
        $target_class = array();
        if (!$this->getRepositoryMode()) {
            $target_class = array("ildashboardgui", "ilcalendarpresentationgui");
        } else {
            switch (ilObject::_lookupType((int) $this->requested_ref_id, true)) {
                case "crs":
                    $target_class = array("ilobjcoursegui", "ilcalendarpresentationgui");
                    break;

                case "grp":
                    $target_class = array("ilobjgroupgui", "ilcalendarpresentationgui");
                    break;
            }
        }
        return $target_class;
    }

    /**
     * Add mini version of monthly overview
     * (Maybe extracted to another class, if used in pd calendar tab
     */
    public function addMiniMonth(ilTemplate $a_tpl, bool $a_include_view_ctrl = false): void
    {
        $lng = $this->lng;
        $ilUser = $this->user;
        $ui = $this->ui;

        // weekdays
        if ($this->getShowWeeksColumn()) {
            $a_tpl->setCurrentBlock('month_header_col');
            $a_tpl->setVariable('TXT_WEEKDAY', $this->lng->txt("cal_week_abbrev"));
            $a_tpl->parseCurrentBlock();
        }
        for ($i = $this->user_settings->getWeekStart(); $i < (7 + $this->user_settings->getWeekStart()); $i++) {
            $a_tpl->setCurrentBlock('month_header_col');
            $a_tpl->setVariable('TXT_WEEKDAY', ilCalendarUtil::_numericDayToString($i, false));
            $a_tpl->parseCurrentBlock();
        }

        $bkid = $this->initBookingUserFromQuery();
        if ($bkid) {
            $user_id = $bkid;
            $disable_empty = true;
        } else {
            $user_id = $this->user->getId();
            $disable_empty = false;
        }
        $this->scheduler = new ilCalendarSchedule($this->seed, ilCalendarSchedule::TYPE_MONTH, $user_id);
        $this->scheduler->addSubitemCalendars(true);
        $this->scheduler->calculate();

        $counter = 0;
        foreach (ilCalendarUtil::_buildMonthDayList(
            (int) $this->seed->get(IL_CAL_FKT_DATE, 'm'),
            (int) $this->seed->get(IL_CAL_FKT_DATE, 'Y'),
            $this->user_settings->getWeekStart()
        )->get() as $date) {
            $counter++;

            $events = $this->scheduler->getByDay($date, $this->user->getTimeZone());
            $has_events = (bool) count($events);
            if ($has_events || !$disable_empty) {
                $a_tpl->setCurrentBlock('month_col_link');
            } else {
                $a_tpl->setCurrentBlock('month_col_no_link');
            }

            if ($disable_empty) {
                if (!$has_events) {
                    $a_tpl->setVariable('DAY_CLASS', 'calminiinactive');
                } else {
                    $week_has_events = true;
                    foreach ($events as $event) {
                        $booking = new ilBookingEntry($event['event']->getContextId());
                        if ($booking->hasBooked($event['event']->getEntryId())) {
                            $a_tpl->setVariable('DAY_CLASS', 'calminiapp');
                            break;
                        }
                    }
                }
            } elseif ($has_events) {
                $week_has_events = true;
                $a_tpl->setVariable('DAY_CLASS', 'calminiapp');
            }

            $day = $date->get(IL_CAL_FKT_DATE, 'j');
            $month = $date->get(IL_CAL_FKT_DATE, 'n');

            $month_day = $day;

            $path = $this->getTargetGUIClassPath();
            $last_gui = end($path);
            $this->ctrl->setParameterByClass($last_gui, 'seed', $date->get(IL_CAL_DATE));
            if ($agenda_view_type = $this->requested_cal_agenda_per) {
                $this->ctrl->setParameterByClass($last_gui, "cal_agenda_per", $agenda_view_type);
            }
            $a_tpl->setVariable('OPEN_DAY_VIEW', $this->ctrl->getLinkTargetByClass($this->getTargetGUIClassPath(), ''));

            $a_tpl->setVariable('MONTH_DAY', $month_day);

            $a_tpl->parseCurrentBlock();

            $a_tpl->setCurrentBlock('month_col');

            if (ilCalendarUtil::_isToday($date)) {
                $a_tpl->setVariable('TD_CLASS', 'calminitoday');
            } elseif (ilDateTime::_equals($date, $this->seed, IL_CAL_MONTH)) {
                $a_tpl->setVariable('TD_CLASS', 'calministd');
            } elseif (ilDateTime::_before($date, $this->seed, IL_CAL_MONTH)) {
                $a_tpl->setVariable('TD_CLASS', 'calminiprev');
            } else {
                $a_tpl->setVariable('TD_CLASS', 'calmininext');
            }

            $a_tpl->parseCurrentBlock();

            if ($counter and !($counter % 7)) {
                if ($this->getShowWeeksColumn()) {
                    $a_tpl->setCurrentBlock('week');
                    $a_tpl->setVariable(
                        'WEEK',
                        $date->get(IL_CAL_FKT_DATE, 'W')
                    );
                    $a_tpl->parseCurrentBlock();
                }

                $a_tpl->setCurrentBlock('month_row');
                $a_tpl->parseCurrentBlock();

                $week_has_events = false;
            }
        }
        $a_tpl->setCurrentBlock('mini_month');
        if ($a_include_view_ctrl) {
            $a_tpl->setVariable("VIEW_CTRL_SECTION", $ui->renderer()->render($this->getViewControl()));
        }

        $a_tpl->parseCurrentBlock();
    }

    protected function getViewControl(): Section
    {
        $ui = $this->ui;
        $lng = $this->lng;

        $first_of_month = substr($this->seed->get(IL_CAL_DATE), 0, 7) . "-01";
        $myseed = new ilDate($first_of_month, IL_CAL_DATE);

        $myseed->increment(ilDateTime::MONTH, -1);
        $this->ctrl->setParameter($this, 'seed', $myseed->get(IL_CAL_DATE));

        $prev_link = $this->ctrl->getLinkTarget($this, "setSeed", "", true);

        $myseed->increment(ilDateTime::MONTH, 2);
        $this->ctrl->setParameter($this, 'seed', $myseed->get(IL_CAL_DATE));
        $next_link = $this->ctrl->getLinkTarget($this, "setSeed", "", true);

        $this->ctrl->setParameter($this, 'seed', "");

        $blockgui = $this;

        // view control
        // ... previous button
        $b1 = $ui->factory()->button()->standard($this->lng->txt("previous"), "#")->withOnLoadCode(function ($id) use (
            $prev_link,
            $blockgui
        ) {
            return
                "$('#" . $id . "').click(function() { ilBlockJSHandler('block_" . $blockgui->getBlockType() .
                "_" . $blockgui->getBlockId() . "','" . $prev_link . "'); return false;});";
        });

        // ... month button
        $this->ctrl->clearParameterByClass("ilcalendarblockgui", 'seed');
        $month_link = $this->ctrl->getLinkTarget($this, "setSeed", "", true, false);
        $seed_parts = explode("-", $this->seed->get(IL_CAL_DATE));
        $b2 = $ui->factory()->button()->month($seed_parts[1] . "-" . $seed_parts[0])->withOnLoadCode(function ($id) use (
            $month_link,
            $blockgui
        ) {
            return "$('#" . $id . "').on('il.ui.button.month.changed', function(el, id, month) { var m = month.split('-'); ilBlockJSHandler('block_" . $blockgui->getBlockType() .
                "_" . $blockgui->getBlockId() . "','" . $month_link . "' + '&seed=' + m[1] + '-' + m[0] + '-01'); return false;});";
        });
        // ... next button
        $b3 = $ui->factory()->button()->standard($this->lng->txt("next"), "#")->withOnLoadCode(function ($id) use (
            $next_link,
            $blockgui
        ) {
            return
                "$('#" . $id . "').click(function() { ilBlockJSHandler('block_" . $blockgui->getBlockType() .
                "_" . $blockgui->getBlockId() . "','" . $next_link . "'); return false;});";
        });

        return $ui->factory()->viewControl()->section($b1, $b2, $b3);
    }

    /**
     * Get bloch HTML code.
     */
    public function getHTML(): string
    {
        $this->initCategories();
        $lng = $this->lng;
        $ilObjDataCache = $this->obj_data_cache;
        $user = $this->user;

        if ($this->mode == ilCalendarCategories::MODE_REPOSITORY) {
            $bkid = $this->initBookingUserFromQuery();
            if (!$bkid) {
                $obj_id = $ilObjDataCache->lookupObjId((int) $this->requested_ref_id);
                $participants = ilCourseParticipants::_getInstanceByObjId($obj_id);
                $users = array_unique(array_merge($participants->getTutors(), $participants->getAdmins()));
                //$users = $participants->getParticipants();
                $users = ilBookingEntry::lookupBookableUsersForObject([$obj_id], $users);
                foreach ($users as $user_id) {
                    $now = new ilDateTime(time(), IL_CAL_UNIX);

                    // default to last booking entry
                    $appointments = ilConsultationHourAppointments::getAppointments($user_id);
                    $next_app = end($appointments);
                    reset($appointments);

                    foreach ($appointments as $entry) {
                        // find next entry
                        if (ilDateTime::_before($entry->getStart(), $now, IL_CAL_DAY)) {
                            continue;
                        }
                        $booking_entry = new ilBookingEntry($entry->getContextId());
                        if (!in_array($obj_id, $booking_entry->getTargetObjIds())) {
                            continue;
                        }

                        if (!$booking_entry->isAppointmentBookableForUser($entry->getEntryId(), $user->getId())) {
                            continue;
                        }
                        $next_app = $entry;
                        break;
                    }

                    $path = $this->getTargetGUIClassPath();
                    $this->ctrl->setParameterByClass(end($path), "ch_user_id", $user_id);

                    if (!$this->getForceMonthView()) {
                        $this->cal_footer[] = array(
                            'link' => $this->ctrl->getLinkTargetByClass(
                                $this->getTargetGUIClassPath(),
                                'selectCHCalendarOfUser'
                            ),
                            'txt' => str_replace(
                                "%1",
                                ilObjUser::_lookupFullname($user_id),
                                $this->lng->txt("cal_consultation_hours_for_user")
                            )
                        );
                    }
                    $path = $this->getTargetGUIClassPath();
                    $last_gui = end($path);
                    $this->ctrl->setParameterByClass($last_gui, "ch_user_id", "");
                    $this->ctrl->setParameterByClass($last_gui, "bkid", $bkid);
                    $this->ctrl->setParameterByClass($last_gui, "seed", $this->seed->get(IL_CAL_DATE));
                }
                $this->ctrl->setParameter($this, "bkid", "");
                $this->ctrl->setParameter($this, 'seed', '');
            } else {
                $this->ctrl->setParameter($this, "bkid", "");
                $this->addBlockCommand(
                    $this->ctrl->getLinkTarget($this),
                    $this->lng->txt("back")
                );
                $this->ctrl->setParameter($this, "bkid", $this->initBookingUserFromQuery());
            }
        }

        if ($this->getProperty("settings")) {
            $this->addBlockCommand(
                $this->ctrl->getLinkTarget($this, "editSettings"),
                $this->lng->txt("settings")
            );
        }

        $this->ctrl->setParameterByClass($this->getParentGUI(), "seed", $this->seed->get(IL_CAL_DATE));
        $ret = parent::getHTML();
        $this->ctrl->setParameterByClass($this->getParentGUI(), "seed", "");

        // workaround to include asynch code from ui only one time, see #20853
        if ($this->ctrl->isAsynch()) {
            global $DIC;
            $f = $DIC->ui()->factory()->legacy("");
            $ret .= $DIC->ui()->renderer()->renderAsync($f);
        }
        return $ret;
    }

    public function getOverview(): string
    {
        $lng = $this->lng;

        $schedule = new ilCalendarSchedule($this->seed, ilCalendarSchedule::TYPE_INBOX);
        $events = $schedule->getChangedEvents(true);

        $this->ctrl->setParameterByClass('ilcalendarinboxgui', 'changed', 1);
        $link = '<a href=' . $this->ctrl->getLinkTargetByClass('ilcalendarinboxgui', '') . '>';
        $this->ctrl->setParameterByClass('ilcalendarinboxgui', 'changed', '');
        $text = '<div class="small">' . (count($events)) . " " . $this->lng->txt("cal_changed_events_header") . "</div>";
        $end_link = '</a>';

        return $link . $text . $end_link;
    }

    protected function initCategories(): void
    {
        $this->mode = ilCalendarCategories::MODE_REPOSITORY;
        $cats = \ilCalendarCategories::_getInstance();
        if ($this->getForceMonthView()) {
            // old comment: in full container calendar presentation (allows selection of other calendars)
        } elseif (!$cats->getMode()) {
            $cats->initialize(
                \ilCalendarCategories::MODE_REPOSITORY_CONTAINER_ONLY,
                (int) $this->requested_ref_id,
                true
            );
        }
    }

    protected function setSubTabs(): void
    {
        $this->tabs->clearSubTabs();
    }

    public function setSeed(): void
    {
        ilSession::set(
            "il_cal_block_" . $this->getBlockType() . "_" . $this->getBlockId() . "_seed",
            $this->initSeedFromQuery()
        );
        if ($this->ctrl->isAsynch()) {
            echo $this->getHTML();
            exit;
        } else {
            $this->returnToUpperContext();
        }
    }

    public function returnToUpperContext(): void
    {
        $this->ctrl->returnToParent($this);
    }

    protected function initCommands(): void
    {
        $lng = $this->lng;

        if (!$this->getForceMonthView()) {
            // @todo: set checked on ($this->display_mode != 'mmon')
            $this->addBlockCommand(
                $this->ctrl->getLinkTarget($this, "setPdModeEvents"),
                $this->lng->txt("cal_upcoming_events_header"),
                $this->ctrl->getLinkTarget($this, "setPdModeEvents", "", true)
            );

            // @todo: set checked on ($this->display_mode == 'mmon')
            $this->addBlockCommand(
                $this->ctrl->getLinkTarget($this, "setPdModeMonth"),
                $this->lng->txt("app_month"),
                $this->ctrl->getLinkTarget($this, "setPdModeMonth", "", true)
            );

            if ($this->getRepositoryMode()) {
                #23921
                $this->ctrl->setParameterByClass('ilcalendarpresentationgui', 'seed', '');
                $this->addBlockCommand(
                    $this->ctrl->getLinkTargetByClass($this->getTargetGUIClassPath(), ""),
                    $this->lng->txt("cal_open_calendar")
                );

                $this->ctrl->setParameter($this, "add_mode", "");
                $this->addBlockCommand(
                    $this->ctrl->getLinkTargetByClass("ilCalendarAppointmentGUI", "add"),
                    $this->lng->txt("add_appointment")
                );
                $this->ctrl->setParameter($this, "add_mode", "");
            }
        }
    }

    public function setPdModeEvents(): void
    {
        $ilUser = $this->user;

        $this->user->writePref("il_pd_cal_mode", "evt");
        $this->display_mode = "evt";
        $this->setPresentation(self::PRES_SEC_LIST);
        if ($this->ctrl->isAsynch()) {
            echo $this->getHTML();
            exit;
        } else {
            $this->ctrl->redirectByClass("ildashboardgui", "show");
        }
    }

    public function setPdModeMonth(): void
    {
        $ilUser = $this->user;

        $this->user->writePref("il_pd_cal_mode", "mmon");
        $this->display_mode = "mmon";
        $this->setPresentation(self::PRES_SEC_LEG);
        if ($this->ctrl->isAsynch()) {
            echo $this->getHTML();
            exit;
        } else {
            $this->ctrl->redirectByClass("ildashboardgui", "show");
        }
    }

    public function getEvents(): array
    {
        $seed = new ilDate(date('Y-m-d', time()), IL_CAL_DATE);

        $schedule = new ilCalendarSchedule($seed, ilCalendarSchedule::TYPE_PD_UPCOMING);
        $schedule->addSubitemCalendars(true); // #12007
        $schedule->setEventsLimit(20);
        $schedule->calculate();
        // #13809
        return $schedule->getScheduledEvents();
    }

    public function getData(): array
    {
        $lng = $this->lng;
        $ui = $this->ui;

        $f = $ui->factory();

        $events = $this->getEvents();

        $data = array();
        if (sizeof($events)) {
            foreach ($events as $item) {
                $this->ctrl->setParameter($this, "app_id", $item["event"]->getEntryId());
                $this->ctrl->setParameter($this, 'dt', $item['dstart']);
                $url = $this->ctrl->getLinkTarget($this, "getModalForApp", "", true, false);
                $this->ctrl->setParameter($this, "app_id", $this->initAppointmentIdFromQuery());
                $this->ctrl->setParameter($this, "dt", $this->initInitialDateQuery());
                $modal = $f->modal()->roundtrip('', [])->withAsyncRenderUrl($url);

                $dates = $this->getDatesForItem($item);

                $comps = [$f->button()->shy(
                    $item["event"]->getPresentationTitle(),
                    ""
                )->withOnClick($modal->getShowSignal()),
                          $modal
                ];
                $renderer = $ui->renderer();
                $shy = $renderer->render($comps);

                $data[] = array(
                    "date" => ilDatePresentation::formatPeriod($dates["start"], $dates["end"]),
                    "title" => $item["event"]->getPresentationTitle(),
                    "url" => "#",
                    "shy_button" => $shy
                );
            }
            $this->setEnableNumInfo(true);
        } else {
            $data = [];
            /*$data[] = array(
                    "date" => $lng->txt("msg_no_search_result"),
                    "title" => "",
                    "url" => ""
                    );		*/

            $this->setEnableNumInfo(false);
        }

        return $data;
    }

    /**
     * Get start/end date for item
     * @param array $item item
     * @return array
     */
    public function getDatesForItem(array $item): array
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
     * Get modal for appointment (see similar code in ilCalendarAgendaListGUI)
     * todo use all this methods from ilcalendarviewgui.php
     */
    public function getModalForApp()
    {
        $this->initCategories();
        $ui = $this->ui;

        $f = $ui->factory();
        $r = $ui->renderer();

        // @todo: this needs optimization
        $events = $this->getEvents();
        foreach ($events as $item) {
            if ($item["event"]->getEntryId() == $this->initAppointmentIdFromQuery() && $item['dstart'] == $this->initInitialDateQuery()) {
                $dates = $this->getDatesForItem($item);

                // content of modal
                $next_gui = ilCalendarAppointmentPresentationGUI::_getInstance($this->seed, $item);
                $content = $this->ctrl->getHTML($next_gui);

                $modal = $f->modal()->roundtrip(
                    ilDatePresentation::formatPeriod($dates["start"], $dates["end"]),
                    $f->legacy($content)
                );
                echo $r->renderAsync($modal);
            }
        }
        exit();
    }

    //
    // New rendering
    //

    protected bool $new_rendering = true;

    /**
     * @inheritdoc
     */
    protected function getViewControls(): array
    {
        if ($this->getPresentation() == self::PRES_SEC_LEG) {
            return [$this->getViewControl()];
        }
        return parent::getViewControls();
    }

    /**
     * @inheritdoc
     */
    protected function getLegacyContent(): string
    {
        $tpl = new ilTemplate(
            "tpl.calendar_block.html",
            true,
            true,
            "Services/Calendar"
        );

        $this->addMiniMonth($tpl);

        $panel_tpl = new \ilTemplate(
            'tpl.cal_block_panel.html',
            true,
            true,
            'Services/Calendar'
        );

        $this->addConsultationHourButtons($panel_tpl);
        $this->addSubscriptionButton($panel_tpl);

        return $tpl->get() . $panel_tpl->get();
    }

    /**
     * @inheritdoc
     */
    protected function getListItemForData(array $data): ?\ILIAS\UI\Component\Item\Item
    {
        $factory = $this->ui->factory();
        if (isset($data["shy_button"])) {
            return $factory->item()->standard($data["shy_button"])->withDescription($data["date"]);
        } else {
            return $factory->item()->standard($data["date"]);
        }
    }

    /**
     * No item entry
     * @return string
     */
    public function getNoItemFoundContent(): string
    {
        return $this->lng->txt("cal_no_events_block");
    }

    /**
     * Add consultation hour buttons
     */
    protected function addConsultationHourButtons(ilTemplate $panel_template): void
    {
        global $DIC;

        $user = $DIC->user();

        if (!$this->getRepositoryMode()) {
            return;
        }

        $links = \ilConsultationHourUtils::getConsultationHourLinksForRepositoryObject(
            (int) $this->requested_ref_id,
            $user->getId(),
            $this->getTargetGUIClassPath()
        );
        $counter = 0;
        foreach ($links as $link) {
            $ui_factory = $DIC->ui()->factory();
            $ui_renderer = $DIC->ui()->renderer();

            $link_button = $ui_factory->button()->shy(
                $link['txt'],
                $link['link']
            );
            if ($counter) {
                $panel_template->touchBlock('consultation_hour_buttons_multi');
            }
            $panel_template->setCurrentBlock('consultation_hour_buttons');
            $panel_template->setVariable('SHY_BUTTON', $ui_renderer->render([$link_button]));
            $panel_template->parseCurrentBlock();
            $counter++;
        }
    }

    /**
     * Add subscription button
     */
    protected function addSubscriptionButton(ilTemplate $panel_template): void
    {
        global $DIC;

        $ui_factory = $DIC->ui()->factory();
        $ui_renderer = $DIC->ui()->renderer();

        $gui_path = $this->getTargetGUIClassPath();
        $gui_path[] = strtolower(\ilCalendarSubscriptionGUI::class);
        $url = $this->ctrl->getLinkTargetByClass($gui_path, 'getModalForSubscription', "", true, false);

        $roundtrip_modal = $ui_factory->modal()->roundtrip('', [])->withAsyncRenderUrl($url);

        $standard_button = $ui_factory->button()->standard($this->lng->txt('btn_ical'), '')->withOnClick(
            $roundtrip_modal->getShowSignal()
        );
        $components = [
            $roundtrip_modal,
            $standard_button
        ];

        $presentation = $ui_renderer->render($components);

        $panel_template->setCurrentBlock('subscription_buttons');
        $panel_template->setVariable('SUBSCRIPTION_BUTTON', $presentation);
        $panel_template->parseCurrentBlock();
    }
}
