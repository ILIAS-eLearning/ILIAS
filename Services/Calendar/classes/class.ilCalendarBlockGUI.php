<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Block/classes/class.ilBlockGUI.php");
include_once './Services/Calendar/classes/class.ilCalendarCategories.php';

/**
* Calendar blocks, displayed in different contexts, e.g. groups and courses
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_IsCalledBy ilCalendarBlockGUI: ilColumnGUI
* @ilCtrl_Calls ilCalendarBlockGUI: ilCalendarAppointmentGUI, ilCalendarMonthGUI, ilCalendarWeekGUI, ilCalendarDayGUI
* @ilCtrl_Calls ilCalendarBlockGUI: ilConsultationHoursGUI, ilCalendarAppointmentPresentationGUI
*
* @ingroup ServicesCalendar
*/
class ilCalendarBlockGUI extends ilBlockGUI
{
    /**
     * @var ilCtrl|null
     */
    public $ctrl = null;
    protected $mode;
    protected $display_mode;

    public static $block_type = "cal";
    public static $st_data;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var
     */
    protected $obj_data_cache;

    protected $parent_gui = "ilcolumngui";

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    protected $force_month_view = false;

    /**
     * Constructor
     * @param boolean        skip initialisation (is called by derived PDCalendarBlockGUI class)
     */
    public function __construct()
    {
        global $DIC;

        parent::__construct();

        $this->tabs           = $DIC->tabs();
        $this->obj_data_cache = $DIC["ilObjDataCache"];
        $this->ui             = $DIC->ui();

        $lng    = $this->lng;
        $ilCtrl = $this->ctrl;
        $tpl    = $this->main_tpl;
        $ilUser = $this->user;
        $ilHelp = $DIC["ilHelp"];

        $lng->loadLanguageModule("dateplaner");
        $ilHelp->addHelpSection("cal_block");

        include_once("./Services/News/classes/class.ilNewsItem.php");

        $ilCtrl->saveParameter($this, 'bkid');

        $this->setBlockId($ilCtrl->getContextObjId());

        $this->setLimit(5);            // @todo: needed?

        $this->setEnableNumInfo(false);

        $title = $lng->txt("calendar");

        $this->setTitle($title);
        $this->allow_moving = false;

        include_once('Services/Calendar/classes/class.ilDate.php');
        include_once('Services/Calendar/classes/class.ilCalendarUserSettings.php');

        $seed_str = "";
        if ((!isset($_GET["seed"]) || $_GET["seed"] == "") &&
            isset($_SESSION["il_cal_block_" . $this->getBlockType() . "_" . $this->getBlockId() . "_seed"])) {
            $seed_str = $_SESSION["il_cal_block_" . $this->getBlockType() . "_" . $this->getBlockId() . "_seed"];
        } elseif (isset($_GET["seed"])) {
            $seed_str = $_GET["seed"];
        }

        if (isset($_GET["seed"]) && $_GET["seed"] != "") {
            $_SESSION["il_cal_block_" . $this->getBlockType() . "_" . $this->getBlockId() . "_seed"]
                = $_GET["seed"];
        }

        if ($seed_str == "") {
            $now        = new \ilDate(time(), IL_CAL_UNIX);
            $this->seed = new \ilDate($now->get(IL_CAL_DATE), IL_CAL_DATE);
        } else {
            $this->seed = new ilDate($seed_str, IL_CAL_DATE);    // @todo: check this
        }

        $this->settings      = ilCalendarSettings::_getInstance();
        $this->user_settings = ilCalendarUserSettings::_getInstanceByUserId($ilUser->getId());

        $mode               = $ilUser->getPref("il_pd_cal_mode");
        $this->display_mode = $mode ? $mode : "mmon";

        if ($this->display_mode !== "mmon") {
            $this->setPresentation(self::PRES_SEC_LIST);
        }
    }

    /**
     * Show weeks column
     * @param
     * @return
     */
    public function getShowWeeksColumn()
    {
        return ($this->settings->getShowWeeks() && $this->user_settings->getShowWeeks());
    }

    /**
     * @inheritdoc
     */
    public function getBlockType() : string
    {
        return self::$block_type;
    }

    /**
     * @inheritdoc
     */
    protected function isRepositoryObject() : bool
    {
        return false;
    }

    /**
     * Set parent gui
     * @param  $a_val
     */
    public function setParentGUI($a_val)
    {
        $this->parent_gui = $a_val;
    }

    /**
     * Get  parent gui
     * @return
     */
    public function getParentGUI()
    {
        return $this->parent_gui;
    }

    /**
     * Set force month view
     * @param bool $a_val force month view
     */
    public function setForceMonthView($a_val)
    {
        $this->force_month_view = $a_val;
        if ($a_val) {
            $this->display_mode = "mmon";
            $this->setPresentation(self::PRES_SEC_LEG);
        }
    }

    /**
     * Get force month view
     * @return bool force month view
     */
    public function getForceMonthView()
    {
        return $this->force_month_view;
    }

    /**
     * Get Screen Mode for current command.
     */
    public static function getScreenMode()
    {
        global $DIC;

        $ilCtrl = $DIC->ctrl();

        $cmd_class = $ilCtrl->getCmdClass();

        if ($cmd_class == "ilcalendarappointmentgui" ||
            $cmd_class == "ilconsultationhoursgui" ||
            $_GET['cmd'] == 'showCalendarSubscription') {
            return IL_SCREEN_CENTER;
        }

        switch ($ilCtrl->getCmd()) {
            case "kkk":
                // return IL_SCREEN_CENTER;
                // return IL_SCREEN_FULL;

            default:
                //return IL_SCREEN_SIDE;
                break;
        }
    }

    /**
     * execute command
     */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;

        $next_class = $ilCtrl->getNextClass();
        $cmd        = $ilCtrl->getCmd("getHTML");

        $this->setSubTabs();

        switch ($next_class) {
            case "ilcalendarappointmentgui":
                include_once('./Services/Calendar/classes/class.ilCalendarAppointmentGUI.php');
                $app_gui = new ilCalendarAppointmentGUI($this->seed, $this->seed);
                $ilCtrl->forwardCommand($app_gui);
                break;

            case "ilconsultationhoursgui":
                include_once('./Services/Calendar/classes/ConsultationHours/class.ilConsultationHoursGUI.php');
                $hours = new ilConsultationHoursGUI($this->seed);
                $ilCtrl->forwardCommand($hours);
                break;

            case "ilcalendarappointmentpresentationgui":
                $this->initCategories();
                $presentation = ilCalendarAppointmentPresentationGUI::_getInstance($this->seed, $this->appointment);
                $ilCtrl->forwardCommand($presentation);
                break;

            case "ilcalendarmonthgui":
                $ilTabs->setSubTabActive('app_month');
                include_once('./Services/Calendar/classes/class.ilCalendarMonthGUI.php');
                $month_gui = new ilCalendarMonthGUI($this->seed);
                $ilCtrl->forwardCommand($month_gui);
                break;

            default:
                return $this->$cmd();
        }
    }

    /**
     * Set EnableEdit.
     * @param boolean $a_enable_edit Edit mode on/off
     */
    public function setEnableEdit($a_enable_edit = 0)
    {
        $this->enable_edit = $a_enable_edit;
    }

    /**
     * Get EnableEdit.
     * @return    boolean    Edit mode on/off
     */
    public function getEnableEdit()
    {
        return $this->enable_edit;
    }

    /**
     * Fill data section
     */
    public function fillDataSection()
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

    /**
     * Get target gui class path (for presenting the calendar)
     * @param
     * @return
     */
    public function getTargetGUIClassPath()
    {
        $target_class = array();
        if (!$this->getRepositoryMode()) {
            $target_class = array("ildashboardgui", "ilcalendarpresentationgui");
        } else {
            switch (ilObject::_lookupType((int) $_GET["ref_id"], true)) {
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
    public function addMiniMonth($a_tpl, $a_include_view_ctrl = false)
    {
        $lng    = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $ui     = $this->ui;

        // weekdays
        include_once('Services/Calendar/classes/class.ilCalendarUtil.php');
        if ($this->getShowWeeksColumn()) {
            $a_tpl->setCurrentBlock('month_header_col');
            $a_tpl->setVariable('TXT_WEEKDAY', $lng->txt("cal_week_abbrev"));
            $a_tpl->parseCurrentBlock();
        }
        for ($i = (int) $this->user_settings->getWeekStart(); $i < (7 + (int) $this->user_settings->getWeekStart()); $i++) {
            $a_tpl->setCurrentBlock('month_header_col');
            $a_tpl->setVariable('TXT_WEEKDAY', ilCalendarUtil::_numericDayToString($i, false));
            $a_tpl->parseCurrentBlock();
        }

        if (isset($_GET["bkid"])) {
            $user_id       = $_GET["bkid"];
            $disable_empty = true;
        } else {
            $user_id       = $ilUser->getId();
            $disable_empty = false;
        }
        include_once('Services/Calendar/classes/class.ilCalendarSchedule.php');
        $this->scheduler = new ilCalendarSchedule($this->seed, ilCalendarSchedule::TYPE_MONTH, $user_id);
        $this->scheduler->addSubitemCalendars(true);
        $this->scheduler->calculate();

        $counter = 0;
        foreach (ilCalendarUtil::_buildMonthDayList(
            $this->seed->get(IL_CAL_FKT_DATE, 'm'),
            $this->seed->get(IL_CAL_FKT_DATE, 'Y'),
            $this->user_settings->getWeekStart()
        )->get() as $date) {
            $counter++;

            $events     = $this->scheduler->getByDay($date, $ilUser->getTimeZone());
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
                    include_once 'Services/Booking/classes/class.ilBookingEntry.php';
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

            $day   = $date->get(IL_CAL_FKT_DATE, 'j');
            $month = $date->get(IL_CAL_FKT_DATE, 'n');

            $month_day = $day;

            $ilCtrl->setParameterByClass(end($this->getTargetGUIClassPath()), 'seed', $date->get(IL_CAL_DATE));
            if ($agenda_view_type = (int) $_GET['cal_agenda_per']) {
                $ilCtrl->setParameterByClass(end($this->getTargetGUIClassPath()), "cal_agenda_per", $agenda_view_type);
            }
            $a_tpl->setVariable('OPEN_DAY_VIEW', $ilCtrl->getLinkTargetByClass($this->getTargetGUIClassPath(), ''));

            $a_tpl->setVariable('MONTH_DAY', $month_day);

            $a_tpl->parseCurrentBlock();

            $a_tpl->setCurrentBlock('month_col');

            include_once('./Services/Calendar/classes/class.ilCalendarUtil.php');
            if (ilCalendarUtil::_isToday($date)) {
                $a_tpl->setVariable('TD_CLASS', 'calminitoday');
            }
            #elseif(ilDateTime::_equals($date,$this->seed,IL_CAL_DAY))
            #{
            #	$a_tpl->setVariable('TD_CLASS','calmininow');
            #}
            elseif (ilDateTime::_equals($date, $this->seed, IL_CAL_MONTH)) {
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
                //$a_tpl->setVariable('TD_CLASS','calminiweek');
                $a_tpl->parseCurrentBlock();

                $week_has_events = false;
            }
        }
        $a_tpl->setCurrentBlock('mini_month');
        //$a_tpl->setVariable('TXT_MONTH_OVERVIEW', $lng->txt("cal_month_overview"));

        if ($a_include_view_ctrl) {
            $a_tpl->setVariable("VIEW_CTRL_SECTION", $ui->renderer()->render($this->getViewControl()));
        }

        $a_tpl->parseCurrentBlock();
    }

    /**
     * Get view control
     * @return \ILIAS\UI\Component\ViewControl\Section
     */
    protected function getViewControl() : \ILIAS\UI\Component\ViewControl\Section
    {
        $ui     = $this->ui;
        $lng    = $this->lng;
        $ilCtrl = $this->ctrl;

        $first_of_month = substr($this->seed->get(IL_CAL_DATE), 0, 7) . "-01";
        $myseed         = new ilDate($first_of_month, IL_CAL_DATE);

        $myseed->increment(ilDateTime::MONTH, -1);
        $ilCtrl->setParameter($this, 'seed', $myseed->get(IL_CAL_DATE));

        $prev_link = $ilCtrl->getLinkTarget($this, "setSeed", "", true);

        $myseed->increment(ilDateTime::MONTH, 2);
        $ilCtrl->setParameter($this, 'seed', $myseed->get(IL_CAL_DATE));
        $next_link = $ilCtrl->getLinkTarget($this, "setSeed", "", true);

        $ilCtrl->setParameter($this, 'seed', "");

        $blockgui = $this;

        // view control
        // ... previous button
        $b1 = $ui->factory()->button()->standard($lng->txt("previous"), "#")->withOnLoadCode(function ($id) use ($prev_link, $blockgui) {
            return
                "$('#" . $id . "').click(function() { ilBlockJSHandler('block_" . $blockgui->getBlockType() .
                "_" . $blockgui->getBlockId() . "','" . $prev_link . "'); return false;});";
        });

        // ... month button
        $ilCtrl->clearParameterByClass("ilcalendarblockgui", 'seed');
        $month_link = $ilCtrl->getLinkTarget($this, "setSeed", "", true, false);
        $seed_parts = explode("-", $this->seed->get(IL_CAL_DATE));
        $b2         = $ui->factory()->button()->month($seed_parts[1] . "-" . $seed_parts[0])->withOnLoadCode(function ($id) use ($month_link, $blockgui) {
            return "$('#" . $id . "').on('il.ui.button.month.changed', function(el, id, month) { var m = month.split('-'); ilBlockJSHandler('block_" . $blockgui->getBlockType() .
                "_" . $blockgui->getBlockId() . "','" . $month_link . "' + '&seed=' + m[1] + '-' + m[0] + '-01'); return false;});";
        });
        // ... next button
        $b3 = $ui->factory()->button()->standard($lng->txt("next"), "#")->withOnLoadCode(function ($id) use ($next_link, $blockgui) {
            return
                "$('#" . $id . "').click(function() { ilBlockJSHandler('block_" . $blockgui->getBlockType() .
                "_" . $blockgui->getBlockId() . "','" . $next_link . "'); return false;});";
        });

        return $ui->factory()->viewControl()->section($b1, $b2, $b3);
    }

    /**
     * Get bloch HTML code.
     */
    public function getHTML()
    {
        $this->initCategories();
        $lng            = $this->lng;
        $ilCtrl         = $this->ctrl;
        $ilObjDataCache = $this->obj_data_cache;
        $user           = $this->user;

        if ($this->mode == ilCalendarCategories::MODE_REPOSITORY) {
            if (!isset($_GET["bkid"])) {
                include_once "Modules/Course/classes/class.ilCourseParticipants.php";
                $obj_id       = $ilObjDataCache->lookupObjId((int) $_GET['ref_id']);
                $participants = ilCourseParticipants::_getInstanceByObjId($obj_id);
                $users        = array_unique(array_merge($participants->getTutors(), $participants->getAdmins()));
                //$users = $participants->getParticipants();
                include_once 'Services/Booking/classes/class.ilBookingEntry.php';
                $users = ilBookingEntry::lookupBookableUsersForObject($obj_id, $users);
                foreach ($users as $user_id) {
                    if (!isset($_GET["bkid"])) {
                        include_once './Services/Calendar/classes/ConsultationHours/class.ilConsultationHourAppointments.php';
                        $now = new ilDateTime(time(), IL_CAL_UNIX);

                        // default to last booking entry
                        $appointments = ilConsultationHourAppointments::getAppointments($user_id);
                        $next_app     = end($appointments);
                        reset($appointments);

                        foreach ($appointments as $entry) {
                            // find next entry
                            if (ilDateTime::_before($entry->getStart(), $now, IL_CAL_DAY)) {
                                continue;
                            }
                            include_once 'Services/Booking/classes/class.ilBookingEntry.php';
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

                        /*
                        $ilCtrl->setParameter($this, "bkid", $user_id);
                        if($next_app)
                        {
                            $ilCtrl->setParameter(
                                $this,
                                'seed',
                                (string) $next_app->getStart()->get(IL_CAL_DATE)
                            );
                        }*/

                        //$ilCtrl->setParameterByClass(end($this->getTargetGUIClassPath()), "bkid", $user_id);

                        $ilCtrl->setParameterByClass(end($this->getTargetGUIClassPath()), "ch_user_id", $user_id);

                        if ($next_app) {
                            // this does not work correctly
                            /*$ilCtrl->setParameterByClass(
                                end($this->getTargetGUIClassPath()),
                                'seed',
                                (string) $next_app->getStart()->get(IL_CAL_DATE)
                            );*/
                        }

                        if (!$this->getForceMonthView()) {
                            $this->cal_footer[] = array(
                                'link' => $ilCtrl->getLinkTargetByClass($this->getTargetGUIClassPath(), 'selectCHCalendarOfUser'),
                                'txt'  => str_replace("%1", ilObjUser::_lookupFullname($user_id), $lng->txt("cal_consultation_hours_for_user"))
                            );
                        }
                        $ilCtrl->setParameterByClass(end($this->getTargetGUIClassPath()), "ch_user_id", "");
                        $ilCtrl->setParameterByClass(end($this->getTargetGUIClassPath()), "bkid", $_GET["bkid"]);
                        $ilCtrl->setParameterByClass(end($this->getTargetGUIClassPath()), "seed", $_GET["seed"]);
                    }
                }
                $ilCtrl->setParameter($this, "bkid", "");
                $ilCtrl->setParameter($this, 'seed', '');
            } else {
                $ilCtrl->setParameter($this, "bkid", "");
                $this->addBlockCommand(
                    $ilCtrl->getLinkTarget($this),
                    $lng->txt("back")
                );
                $ilCtrl->setParameter($this, "bkid", (int) $_GET["bkid"]);
            }
        }

        if ($this->getProperty("settings") == true) {
            $this->addBlockCommand(
                $ilCtrl->getLinkTarget($this, "editSettings"),
                $lng->txt("settings")
            );
        }

        $ilCtrl->setParameterByClass($this->getParentGUI(), "seed", isset($_GET["seed"]) ? $_GET["seed"] : "");
        $ret = parent::getHTML();
        $ilCtrl->setParameterByClass($this->getParentGUI(), "seed", "");

        // workaround to include asynch code from ui only one time, see #20853
        if ($ilCtrl->isAsynch()) {
            global $DIC;
            $f   = $DIC->ui()->factory()->legacy("");
            $ret .= $DIC->ui()->renderer()->renderAsync($f);
        }

        return $ret;
    }

    /**
     * Get overview.
     */
    public function getOverview()
    {
        $lng    = $this->lng;
        $ilCtrl = $this->ctrl;

        include_once('./Services/Calendar/classes/class.ilCalendarSchedule.php');
        $schedule = new ilCalendarSchedule($this->seed, ilCalendarSchedule::TYPE_INBOX);
        $events   = $schedule->getChangedEvents(true);

        $ilCtrl->setParameterByClass('ilcalendarinboxgui', 'changed', 1);
        $link = '<a href=' . $ilCtrl->getLinkTargetByClass('ilcalendarinboxgui', '') . '>';
        $ilCtrl->setParameterByClass('ilcalendarinboxgui', 'changed', '');
        $text     = '<div class="small">' . ((int) count($events)) . " " . $lng->txt("cal_changed_events_header") . "</div>";
        $end_link = '</a>';

        return $link . $text . $end_link;
    }

    /**
     * init categories
     * @access protected
     * @param
     * @return
     */
    protected function initCategories()
    {
        $this->mode = ilCalendarCategories::MODE_REPOSITORY;
        $cats       = \ilCalendarCategories::_getInstance();
        if ($this->getForceMonthView()) {
            // old comment: in full container calendar presentation (allows selection of other calendars)
        } elseif (!$cats->getMode()) {
            $cats->initialize(
                \ilCalendarCategories::MODE_REPOSITORY_CONTAINER_ONLY,
                (int) $_GET['ref_id'],
                true
            );
        }
    }

    /**
     * @param
     * @return
     */
    protected function setSubTabs()
    {
        $ilTabs = $this->tabs;

        $ilTabs->clearSubTabs();
    }

    /**
     * Set seed
     */
    public function setSeed()
    {
        $ilCtrl = $this->ctrl;

        //$ilUser->writePref("il_pd_bkm_mode", 'flat');
        $_SESSION["il_cal_block_" . $this->getBlockType() . "_" . $this->getBlockId() . "_seed"] =
            $_GET["seed"];
        if ($ilCtrl->isAsynch()) {
            echo $this->getHTML();
            exit;
        } else {
            $this->returnToUpperContext();
        }
    }

    /**
     * Return to upper context
     */
    public function returnToUpperContext()
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->returnToParent($this);
    }

    public function fillFooter()
    {
        // @todo: check this
        return;

        // begin-patch ch
        foreach ((array) $this->cal_footer as $link_info) {
            $this->tpl->setCurrentBlock('data_section');
            $this->tpl->setVariable(
                'DATA',
                sprintf('<a href="%s">%s</a>', $link_info['link'], $link_info['txt'])

            );
            $this->tpl->parseCurrentBlock();
        }
        // end-patch ch

        if ($this->tpl->blockExists("block_footer")) {
            $this->tpl->setCurrentBlock("block_footer");
            $this->tpl->parseCurrentBlock();
        }
    }

    public function initCommands()
    {
        $ilCtrl = $this->ctrl;
        $lng    = $this->lng;

        if (!$this->getForceMonthView()) {
            // @todo: set checked on ($this->display_mode != 'mmon')
            $this->addBlockCommand(
                $ilCtrl->getLinkTarget($this, "setPdModeEvents"),
                $lng->txt("cal_upcoming_events_header"),
                $ilCtrl->getLinkTarget($this, "setPdModeEvents", "", true)
            );

            // @todo: set checked on ($this->display_mode == 'mmon')
            $this->addBlockCommand(
                $ilCtrl->getLinkTarget($this, "setPdModeMonth"),
                $lng->txt("app_month"),
                $ilCtrl->getLinkTarget($this, "setPdModeMonth", "", true)
            );

            if ($this->getRepositoryMode()) {
                #23921
                $ilCtrl->setParameterByClass('ilcalendarpresentationgui', 'seed', '');
                $this->addBlockCommand(
                    $ilCtrl->getLinkTargetByClass($this->getTargetGUIClassPath(), ""),
                    $lng->txt("cal_open_calendar")
                );

                $ilCtrl->setParameter($this, "add_mode", "");
                $this->addBlockCommand(
                    $ilCtrl->getLinkTargetByClass("ilCalendarAppointmentGUI", "add"),
                    $lng->txt("add_appointment")
                );
                $ilCtrl->setParameter($this, "add_mode", "");
            }
        }
    }

    public function setPdModeEvents()
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;

        $ilUser->writePref("il_pd_cal_mode", "evt");
        $this->display_mode = "evt";
        $this->setPresentation(self::PRES_SEC_LIST);
        if ($ilCtrl->isAsynch()) {
            echo $this->getHTML();
            exit;
        } else {
            $ilCtrl->redirectByClass("ildashboardgui", "show");
        }
    }

    public function setPdModeMonth()
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;

        $ilUser->writePref("il_pd_cal_mode", "mmon");
        $this->display_mode = "mmon";
        $this->setPresentation(self::PRES_SEC_LEG);
        if ($ilCtrl->isAsynch()) {
            echo $this->getHTML();
            exit;
        } else {
            $ilCtrl->redirectByClass("ildashboardgui", "show");
        }
    }

    /**
     * Get events
     * @param
     * @return
     */
    public function getEvents()
    {
        $seed = new ilDate(date('Y-m-d', time()), IL_CAL_DATE);

        include_once('./Services/Calendar/classes/class.ilCalendarSchedule.php');
        $schedule = new ilCalendarSchedule($seed, ilCalendarSchedule::TYPE_PD_UPCOMING);
        $schedule->addSubitemCalendars(true); // #12007
        $schedule->setEventsLimit(20);
        $schedule->calculate();
        $ev = $schedule->getScheduledEvents(); // #13809
        return ($ev);
    }

    public function getData()
    {
        $lng = $this->lng;
        $ui  = $this->ui;

        $f = $ui->factory();

        $events = $this->getEvents();

        $data = array();
        if (sizeof($events)) {
            foreach ($events as $item) {
                $this->ctrl->setParameter($this, "app_id", $item["event"]->getEntryId());
                $this->ctrl->setParameter($this, 'dt', $item['dstart']);
                $url = $this->ctrl->getLinkTarget($this, "getModalForApp", "", true, false);
                $this->ctrl->setParameter($this, "app_id", $_GET["app_id"]);
                $this->ctrl->setParameter($this, "dt", $_GET["dt"]);
                $modal = $f->modal()->roundtrip('', [])->withAsyncRenderUrl($url);

                $dates = $this->getDatesForItem($item);

                $comps    = [$f->button()->shy($item["event"]->getPresentationTitle(), "")->withOnClick($modal->getShowSignal()), $modal];
                $renderer = $ui->renderer();
                $shy      = $renderer->render($comps);

                $data[] = array(
                    "date"       => ilDatePresentation::formatPeriod($dates["start"], $dates["end"]),
                    "title"      => $item["event"]->getPresentationTitle(),
                    "url"        => "#",
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
    public function getDatesForItem($item)
    {
        $start = $item["dstart"];
        $end   = $item["dend"];
        if ($item["fullday"]) {
            $start = new ilDate($start, IL_CAL_UNIX);
            $end   = new ilDate($end, IL_CAL_UNIX);
        } else {
            $start = new ilDateTime($start, IL_CAL_UNIX);
            $end   = new ilDateTime($end, IL_CAL_UNIX);
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
        $ui     = $this->ui;
        $ilCtrl = $this->ctrl;

        $f = $ui->factory();
        $r = $ui->renderer();

        // @todo: this needs optimization
        $events = $this->getEvents();
        foreach ($events as $item) {
            if ($item["event"]->getEntryId() == (int) $_GET["app_id"] && $item['dstart'] == (int) $_GET['dt']) {
                $dates = $this->getDatesForItem($item);

                // content of modal
                include_once("./Services/Calendar/classes/class.ilCalendarAppointmentPresentationGUI.php");
                $next_gui = ilCalendarAppointmentPresentationGUI::_getInstance($this->seed, $item);
                $content  = $ilCtrl->getHTML($next_gui);

                $modal = $f->modal()->roundtrip(ilDatePresentation::formatPeriod($dates["start"], $dates["end"]), $f->legacy($content));
                echo $r->renderAsync($modal);
            }
        }
        exit();
    }

    //
    // New rendering
    //

    protected $new_rendering = true;

    /**
     * @inheritdoc
     */
    protected function getViewControls() : array
    {
        if ($this->getPresentation() == self::PRES_SEC_LEG) {
            return [$this->getViewControl()];
        }
        return parent::getViewControls();
    }

    /**
     * @inheritdoc
     */
    protected function getLegacyContent() : string
    {
        $tpl = new ilTemplate(
            "tpl.calendar_block.html",
            true,
            true,
            "Services/Calendar"
        );

        $this->addMiniMonth($tpl);

        return $tpl->get();
    }

    /**
     * @inheritdoc
     */
    protected function getListItemForData(array $data) : \ILIAS\UI\Component\Item\Item
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
    protected function getNoItemFoundContent() : string
    {
        return $this->lng->txt("cal_no_events_block");
    }

    /**
     * overwrites base implementation for adding subscription and consultation hour links
     * @return string
     */
    public function getHTMLNew()
    {
        global $DIC;

        $ui_factory = $DIC->ui()->factory();
        $ui_renderer = $DIC->ui()->renderer();

        $block_html = parent::getHTMLNew();



        $panel_tpl = new \ilTemplate(
            'tpl.cal_block_panel.html',
            true,
            true,
            'Services/Calendar'
        );

        $this->addConsultationHourButtons($panel_tpl);
        $this->addSubscriptionButton($panel_tpl);

        $panel = $ui_factory->panel()
                     ->secondary()
                     ->legacy(
                         '',
                         $ui_factory->legacy($panel_tpl->get())
                     );

        return $block_html . $ui_renderer->render([$panel]);
    }

    /**
     * Add consultation hour buttons
     */
    protected function addConsultationHourButtons(ilTemplate $panel_template) : void
    {
        global $DIC;

        $user = $DIC->user();

        if (!$this->getRepositoryMode()) {
            return;
        }

        $links = \ilConsultationHourUtils::getConsultationHourLinksForRepositoryObject(
            (int) $_GET['ref_id'],
            (int) $user->getId(),
            $this->getTargetGUIClassPath()
        );
        $counter = 0;
        foreach ($links as $link) {

            $ui_factory  = $DIC->ui()->factory();
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
    protected function addSubscriptionButton(ilTemplate $panel_template) : void
    {
        global $DIC;

        $lng = $DIC->language();

        $ui_factory  = $DIC->ui()->factory();
        $ui_renderer = $DIC->ui()->renderer();

        $gui_path   = $this->getTargetGUIClassPath();
        $gui_path[] = strtolower(\ilCalendarSubscriptionGUI::class);
        $url        = $this->ctrl->getLinkTargetByClass($gui_path, 'getModalForSubscription', "", true, false);

        $roundtrip_modal = $ui_factory->modal()->roundtrip('', [])->withAsyncRenderUrl($url);

        $standard_button = $ui_factory->button()->standard($lng->txt('btn_ical'), '')->withOnClick(
            $roundtrip_modal->getShowSignal()
        );
        $components      = [
            $roundtrip_modal,
            $standard_button
        ];

        $presentation = $ui_renderer->render($components);

        $panel_template->setCurrentBlock('subscription_buttons');
        $panel_template->setVariable('SUBSCRIPTION_BUTTON', $presentation);
        $panel_template->parseCurrentBlock();
    }
}
