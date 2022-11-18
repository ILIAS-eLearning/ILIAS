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


use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\HTTP\Services as HttpServices;

/**
 * @author       Stefan Meyer <meyer@leifos.com>
 * @version      $Id$
 * @ilCtrl_Calls ilCalendarPresentationGUI: ilCalendarMonthGUI, ilCalendarUserSettingsGUI, ilCalendarCategoryGUI, ilCalendarWeekGUI
 * @ilCtrl_Calls ilCalendarPresentationGUI: ilCalendarAppointmentGUI, ilCalendarDayGUI, ilCalendarInboxGUI, ilCalendarSubscriptionGUI
 * @ilCtrl_Calls ilCalendarPresentationGUI: ilConsultationHoursGUI, ilCalendarBlockGUI, ilPDCalendarBlockGUI, ilPublicUserProfileGUI
 * @ingroup      ServicesCalendar
 */
class ilCalendarPresentationGUI
{
    protected ilCtrlInterface $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilTabsGUI $tabs_gui;
    protected ilObjUser $user;
    protected ilHelpGUI $help;
    protected ilRbacSystem $rbacsystem;
    protected \ILIAS\DI\UIServices $ui;
    protected ilToolbarGUI $toolbar;
    protected ilAccessHandler $access;
    protected HttpServices $http;
    protected RefineryFactory $refinery;


    protected ilCalendarSettings $cal_settings;
    protected ilCalendarActions $actions;
    protected ilCalendarCategories $cats;
    protected bool $repository_mode = false;
    protected int $ref_id = 0;
    protected int $category_id = 0;
    protected ?ilDate $seed = null;

    protected int $cal_view = 0;
    protected int $cal_period = 0;

    public function __construct($a_ref_id = 0)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('dateplaner');

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tabs_gui = $DIC->tabs();
        $this->user = $DIC->user();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->access = $DIC->access();
        $this->help = $DIC["ilHelp"];
        $this->ui = $DIC->ui();
        $this->toolbar = $DIC->toolbar();
        $this->ref_id = $a_ref_id;
        $this->category_id = 0;
        if ($this->http->wrapper()->query()->has('category_id')) {
            $this->category_id = $this->http->wrapper()->query()->retrieve(
                'category_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        $this->ctrl->setParameter($this, 'category_id', $this->category_id);
        $this->cal_settings = ilCalendarSettings::_getInstance();

        // show back to pd
        $this->ctrl->saveParameter($this, 'backpd');

        $this->initCalendarView();

        $cats = ilCalendarCategories::_getInstance($this->user->getId());

        if ($a_ref_id > 0) {
            $this->repository_mode = true;
        }
        if ($this->category_id > 0) {        // single calendar view
            // ensure activation of this category
            $vis = ilCalendarVisibility::_getInstanceByUserId($this->user->getId(), $a_ref_id);
            $vis->forceVisibility($this->category_id);

            $cats->initialize(ilCalendarCategories::MODE_SINGLE_CALENDAR, 0, false, $this->category_id);
        } elseif ($a_ref_id > 0) {
            $cats->initialize(ilCalendarCategories::MODE_REPOSITORY, (int) $a_ref_id, true);
        } elseif (ilCalendarUserSettings::_getInstance()->getCalendarSelectionType() == ilCalendarUserSettings::CAL_SELECTION_MEMBERSHIP) {
            $cats->initialize(ilCalendarCategories::MODE_PERSONAL_DESKTOP_MEMBERSHIP);
        } else {
            $cats->initialize(ilCalendarCategories::MODE_PERSONAL_DESKTOP_ITEMS);
        }

        $this->actions = ilCalendarActions::getInstance();
        $this->cats = $cats;
    }

    public function getRepositoryMode(): bool
    {
        return $this->repository_mode;
    }

    protected function initAppointmentIdFromQuery(): int
    {
        if ($this->http->wrapper()->query()->has('app_id')) {
            return $this->http->wrapper()->query()->retrieve(
                'app_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        return 0;
    }

    protected function initCategoryIdFromQuery(): int
    {
        if ($this->http->wrapper()->query()->has('category_id')) {
            return $this->http->wrapper()->query()->retrieve(
                'category_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        return 0;
    }


    /**
     * Init and redirect to consultation hours
     */
    protected function initAndRedirectToConsultationHours(): void
    {
        $ch_user_id = 0;
        if ($this->http->wrapper()->query()->has('ch_user_id')) {
            $ch_user_id = $this->http->wrapper()->query()->retrieve(
                'ch_user_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        $visibility = ilCalendarVisibility::_getInstanceByUserId($this->user->getId(), $this->ref_id);
        foreach ($this->cats->getCategoriesInfo() as $info) {
            if (
                $info["type"] == ilCalendarCategory::TYPE_CH &&
                $info["obj_id"] == $ch_user_id
            ) {
                $v = $visibility->getVisible();
                if (!in_array($info["cat_id"], $v)) {
                    $v[] = $info["cat_id"];
                }
                $visibility->showSelected($v);
                $visibility->save();
                $this->ctrl->setParameterByClass(ilCalendarMonthGUI::class, 'category_id', $info['cat_id']);
                $this->ctrl->setParameterByClass(\ilCalendarMonthGUI::class, 'seed', $this->seed);
                $this->ctrl->redirectToURL(
                    $this->ctrl->getLinkTargetByClass(\ilCalendarMonthGUI::class, '')
                );
            }
        }
    }

    /**
     * Initialises calendar view according to given settings
     */
    protected function initCalendarView(): void
    {
        $this->cal_view = $this->cal_settings->getDefaultCal();
        if ($this->http->wrapper()->query()->has('cal_view')) {
            $this->cal_view = $this->http->wrapper()->query()->retrieve(
                'cal_view',
                $this->refinery->kindlyTo()->int()
            );
        }
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();
        // now next class is not empty, which breaks old consultation hour implementation
        $next_class = $this->getNextClass();

        if (!ilCalendarSettings::_getInstance()->isEnabled()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
            ilUtil::redirect('ilias.php?baseClass=ilDashboardGUI');
        }

        $this->initSeed();
        $this->prepareOutput();

        $this->help->setScreenIdComponent("cal");

        switch ($cmd) {
            case 'selectCHCalendarOfUser':
                $this->initAndRedirectToConsultationHours();
                break;
        }

        switch ($next_class) {
            case 'ilcalendarinboxgui':
                $this->tabs_gui->activateTab('cal_agenda');
                $inbox_gui = $this->forwardToClass('ilcalendarinboxgui');
                if ($this->showToolbarAndSidebar()) {
                    $this->showViewSelection("cal_list");
                    $this->showSideBlocks();
                    $inbox_gui->addToolbarFileDownload();
                }

                break;

            case 'ilconsultationhoursgui':
                $this->tabs_gui->activateTab('app_consultation_hours');
                $this->tabs_gui->clearTargets();

                // No side blocks
                $this->tabs_gui->setBackTarget(
                    $this->lng->txt('cal_back_to_cal'),
                    $this->ctrl->getLinkTargetByClass($this->readLastClass())
                );
                $this->ctrl->forwardCommand(new ilConsultationHoursGUI());
                if ($this->showToolbarAndSidebar()) {
                    $this->showSideBlocks();
                }
                return;

            case 'ilcalendarmonthgui':
                $this->tabs_gui->activateTab('cal_agenda');
                $month_gui = $this->forwardToClass('ilcalendarmonthgui');

                if ($this->showToolbarAndSidebar()) {
                    $this->showViewSelection("app_month");
                    $this->showSideBlocks();
                    $month_gui->addToolbarFileDownload();
                }
                break;

            case 'ilcalendarweekgui':
                $this->tabs_gui->activateTab('cal_agenda');
                $week_gui = $this->forwardToClass('ilcalendarweekgui');
                if ($this->showToolbarAndSidebar()) {
                    $this->showViewSelection("app_week");
                    $this->showSideBlocks();
                    $week_gui->addToolbarFileDownload();
                }

                break;

            case 'ilcalendardaygui':
                $this->tabs_gui->activateTab('cal_agenda');
                $day_gui = $this->forwardToClass('ilcalendardaygui');
                if ($this->showToolbarAndSidebar()) {
                    $this->showViewSelection("app_day");
                    $this->showSideBlocks();
                    $day_gui->addToolbarFileDownload();
                }
                break;

            case 'ilcalendarusersettingsgui':
                $this->ctrl->setReturn($this, '');
                $this->tabs_gui->activateTab('settings');
                $this->setCmdClass('ilcalendarusersettingsgui');

                $user_settings = new ilCalendarUserSettingsGUI();
                $this->ctrl->forwardCommand($user_settings);
                // No side blocks
                return;

            case 'ilcalendarappointmentgui':
                $this->ctrl->setReturn($this, '');
                $this->tabs_gui->activateTab((string) ilSession::get('cal_last_tab'));

                $app = new ilCalendarAppointmentGUI($this->seed, $this->seed, $this->initAppointmentIdFromQuery());
                $this->ctrl->forwardCommand($app);
                break;

            case 'ilcalendarsubscriptiongui':
                $this->ctrl->setReturn($this, '');
                $this->tabs_gui->activateTab("cal_agenda");

                $ref_id = 0;
                if ($this->http->wrapper()->query()->has('ref_id')) {
                    $ref_id = $this->http->wrapper()->query()->retrieve(
                        'ref_id',
                        $this->refinery->kindlyTo()->int()
                    );
                }
                $sub = new ilCalendarSubscriptionGUI($this->category_id, $ref_id);
                $this->ctrl->forwardCommand($sub);
                if ($this->showToolbarAndSidebar()) {
                    $this->showSideBlocks();
                }
                break;

            case 'ilcalendarcategorygui':
                $this->ctrl->setReturn($this, '');
                $this->tabs_gui->activateTab("cal_manage");
                $category = new ilCalendarCategoryGUI($this->user->getId(), $this->seed, $this->ref_id);
                if ($this->ctrl->forwardCommand($category)) {
                    return;
                } else {
                    $this->showSideBlocks();
                    break;
                }

            // no break
            case 'ilcalendarblockgui':
                $side_cal = new ilCalendarBlockGUI();
                $side_cal->setRepositoryMode($this->getRepositoryMode());
                $side_cal->setForceMonthView(true);
                $this->ctrl->forwardCommand($side_cal);
                $this->showSideBlocks();
                break;

            case 'ilpdcalendarblockgui':
                $side_cal = new ilPDCalendarBlockGUI();
                $side_cal->setRepositoryMode($this->getRepositoryMode());
                $side_cal->setForceMonthView(true);
                $this->ctrl->forwardCommand($side_cal);
                $this->showSideBlocks();
                break;

            case 'ilpublicuserprofilegui':
                $user_id = $this->user->getId();
                if ($this->http->wrapper()->query()->has('user_id')) {
                    $user_id = $this->http->wrapper()->query()->retrieve(
                        'user_id',
                        $this->refinery->kindlyTo()->int()
                    );
                }
                $user_profile = new ilPublicUserProfileGUI($user_id);
                $html = $this->ctrl->forwardCommand($user_profile);
                $this->tpl->setContent($html);
                break;

            default:
                $cmd = $this->ctrl->getCmd("show");
                $this->$cmd();
                $this->showSideBlocks();
                break;
        }
        // @todo add cron job feature
        $this->synchroniseExternalCalendars();
    }

    public function showViewSelection(string $a_active = "cal_list"): void
    {
        $ui = $this->ui;
        $ctrl = $this->ctrl;
        $lng = $this->lng;
        $toolbar = $this->toolbar;

        $f = $ui->factory();

        $actions = array(
            $this->lng->txt("app_day") => $ctrl->getLinkTargetByClass('ilCalendarDayGUI', ''),
            $this->lng->txt("app_week") => $ctrl->getLinkTargetByClass('ilCalendarWeekGUI', ''),
            $this->lng->txt("app_month") => $ctrl->getLinkTargetByClass('ilCalendarMonthGUI', ''),
            $this->lng->txt("cal_list") => $ctrl->getLinkTargetByClass('ilCalendarInboxGUI', '')
        );


        $view_control = $f->viewControl()->mode($actions, "cal_change_calendar_view")->withActive($this->lng->txt($a_active));
        $toolbar->addComponent($view_control);
        $ctrl->setParameterByClass("ilcalendarappointmentgui", "seed", $this->seed->get(IL_CAL_DATE, ''));
        $ctrl->setParameterByClass("ilcalendarappointmentgui", "app_id", "");
        $ctrl->setParameterByClass("ilcalendarappointmentgui", "dt", "");
        $ctrl->setParameterByClass("ilcalendarappointmentgui", "idate", (new ilDate(time(), IL_CAL_UNIX))->get(IL_CAL_DATE));

        $extra_button_added = false;
        // add appointment
        if ($this->category_id == 0 || $this->actions->checkAddEvent($this->category_id)) {
            $toolbar->addSeparator();
            $extra_button_added = true;
            $add_button = $f->button()->standard(
                $this->lng->txt("cal_add_appointment"),
                $ctrl->getLinkTargetByClass("ilcalendarappointmentgui", "add")
            );
            $toolbar->addComponent($add_button);
        }

        // import appointments
        if ($this->category_id > 0 && $this->actions->checkAddEvent($this->category_id)) {
            if (!$extra_button_added) {
                $toolbar->addSeparator();
            }
            $add_button = $f->button()->standard(
                $this->lng->txt("cal_import_appointments"),
                $ctrl->getLinkTargetByClass("ilcalendarcategorygui", "importAppointments")
            );
            $toolbar->addComponent($add_button);
        }
    }

    public function getNextClass(): string
    {
        if (strlen($next_class = $this->ctrl->getNextClass())) {
            return $next_class;
        }
        if (
            strcasecmp($this->ctrl->getCmdClass(), ilCalendarPresentationGUI::class) === 0 ||
            $this->ctrl->getCmdClass() == ''
        ) {
            $cmd_class = $this->readLastClass();
            $this->ctrl->setCmdClass($cmd_class);
            return $cmd_class;
        }
        return '';
    }

    /**
     * Read last class from history
     */
    public function readLastClass(): string
    {
        $ilUser = $this->user;

        switch ($this->cal_view) {
            case ilCalendarSettings::DEFAULT_CAL_DAY:
                $class = "ilcalendardaygui";
                break;
            case ilCalendarSettings::DEFAULT_CAL_WEEK:
                $class = "ilcalendarweekgui";
                break;
            case ilCalendarSettings::DEFAULT_CAL_MONTH:
                $class = "ilcalendarmonthgui";
                break;
            case ilCalendarSettings::DEFAULT_CAL_LIST:
            default:
                $class = "ilcalendarinboxgui";
                break;
        }

        return $this->user->getPref('cal_last_class') ? $this->user->getPref('cal_last_class') : $class;
    }

    public function setCmdClass($a_class): void
    {
        // If cmd class == 'ilcalendarpresentationgui' the cmd class is set to the the new forwarded class
        // otherwise e.g ilcalendarmonthgui tries to forward (back) to ilcalendargui.
        if ($this->ctrl->getCmdClass() == strtolower(get_class($this))) {
            $this->ctrl->setCmdClass(strtolower($a_class));
        }
    }

    protected function forwardToClass(string $a_class): ?ilCalendarViewGUI
    {
        $ilUser = $this->user;

        switch ($a_class) {
            case 'ilcalendarmonthgui':
                $this->user->writePref('cal_last_class', $a_class);
                ilSession::set('cal_last_tab', 'app_month');
                $this->setCmdClass('ilcalendarmonthgui');
                $month_gui = new ilCalendarMonthGUI($this->seed);
                $this->ctrl->forwardCommand($month_gui);
                return $month_gui;

            case 'ilcalendarweekgui':
                $this->user->writePref('cal_last_class', $a_class);
                ilSession::set('cal_last_tab', 'app_week');
                $this->setCmdClass('ilcalendarweekgui');
                $week_gui = new ilCalendarWeekGUI($this->seed);
                $this->ctrl->forwardCommand($week_gui);
                return $week_gui;

            case 'ilcalendardaygui':
                $this->user->writePref('cal_last_class', $a_class);
                ilSession::set('cal_last_tab', 'app_day');
                $this->setCmdClass('ilcalendardaygui');
                $day_gui = new ilCalendarDayGUI($this->seed);
                $this->ctrl->forwardCommand($day_gui);
                return $day_gui;

            case 'ilcalendarinboxgui':
                $this->user->writePref('cal_last_class', $a_class);
                ilSession::set('cal_last_tab', 'cal_upcoming_events_header');
                $this->setCmdClass('ilcalendarinboxgui');
                $inbox_gui = new ilCalendarInboxGUI($this->seed);
                $this->ctrl->forwardCommand($inbox_gui);
                return $inbox_gui;
        }
        return null;
    }

    protected function showSideBlocks(): void
    {
        $tpl = new ilTemplate('tpl.cal_side_block.html', true, true, 'Services/Calendar');
        if ($this->getRepositoryMode()) {
            $side_cal = new ilCalendarBlockGUI();
        } else {
            $side_cal = new ilPDCalendarBlockGUI();
        }
        $side_cal->setParentGUI("ilCalendarPresentationGUI");
        $side_cal->setForceMonthView(true);
        $side_cal->setRepositoryMode($this->getRepositoryMode());
        $tpl->setVariable('MINICAL', $this->ctrl->getHTML($side_cal));

        $cat = new ilCalendarCategoryGUI($this->user->getId(), $this->seed, $this->ref_id);
        $tpl->setVariable('CATEGORIES', $this->ctrl->getHTML($cat));

        $this->tpl->setRightContent($tpl->get());
    }

    /**
     * Add tabs for ilCategoryGUI context This cannot be done there since many views (Day Week Agenda)
     * are initiated from these view
     */
    protected function addCategoryTabs(): void
    {
        $ctrl = $this->ctrl;
        $this->tabs_gui->clearTargets();
        $ctrl->setParameterByClass(ilCalendarCategoryGUI::class, "category_id", $this->initCategoryIdFromQuery());
        if ($this->getRepositoryMode()) {
            if ($this->http->wrapper()->query()->has('backpd')) {
                $this->tabs_gui->setBack2Target(
                    $this->lng->txt('back_to_pd'),
                    $this->ctrl->getLinkTargetByClass(ilDashboardGUI::class, 'jumpToCalendar')
                );
            }
            $label = $this->lng->txt('back_to_' . ilObject::_lookupType($this->ref_id, true));
            $this->tabs_gui->setBackTarget(
                $label,
                $this->ctrl->getParentReturn($this)
            );
        } elseif ($this->http->wrapper()->query()->has('backvm')) {

            // no object calendar => back is back to manage view
            $this->tabs_gui->setBackTarget(
                $this->lng->txt("back"),
                $ctrl->getLinkTargetByClass(ilCalendarCategoryGUI::class, 'manage')
            );
        } else {
            $ctrl->clearParameterByClass(ilCalendarPresentationGUI::class, 'category_id');
            $this->tabs_gui->setBackTarget(
                $this->lng->txt("back"),
                $ctrl->getLinkTargetByClass('ilcalendarpresentationgui', '')
            );
            $ctrl->setParameterByClass(ilCalendarPresentationGUI::class, "category_id", $this->initCategoryIdFromQuery());
        }

        $this->tabs_gui->addTab(
            "cal_agenda",
            $this->lng->txt("cal_agenda"),
            $ctrl->getLinkTargetByClass(ilCalendarPresentationGUI::class, "")
        );

        if ($this->actions->checkShareCal($this->category_id)) {
            $this->tabs_gui->addTab(
                "share",
                $this->lng->txt("cal_share"),
                $ctrl->getLinkTargetByClass(ilCalendarCategoryGUI::class, "shareSearch")
            );
        }
        if ($this->actions->checkSettingsCal($this->category_id)) {
            $this->tabs_gui->addTab(
                "edit",
                $this->lng->txt("settings"),
                $ctrl->getLinkTargetByClass(ilCalendarCategoryGUI::class, "edit")
            );
        }
        $this->tabs_gui->activateTab('cal_agenda');
    }

    /**
     * add standard tabs
     */
    protected function addStandardTabs(): void
    {
        $access = $this->access;
        $rbacsystem = $this->rbacsystem;

        $this->tabs_gui->clearTargets();
        if ($this->getRepositoryMode()) {
            if ($this->http->wrapper()->query()->has('backpd')) {
                $this->tabs_gui->setBack2Target(
                    $this->lng->txt('back_to_pd'),
                    $this->ctrl->getLinkTargetByClass(ilDashboardGUI::class, 'jumpToCalendar')
                );
            }
            $label = $this->lng->txt('back_to_' . ilObject::_lookupType($this->ref_id, true));
            $this->tabs_gui->setBackTarget(
                $label,
                $this->ctrl->getParentReturn($this)
            );

            $obj_id = ilObject::_lookupObjId($this->ref_id);
            $category = ilCalendarCategory::_getInstanceByObjId($obj_id);
            $category_id = $category->getCategoryID();

            // agenda tab
            $this->tabs_gui->addTab(
                'cal_agenda',
                $this->lng->txt('cal_agenda'),
                $this->ctrl->getLinkTarget($this, '')
            );

            // settings tab
            if ($access->checkAccess('edit_event', '', $this->ref_id)) {
                $this->ctrl->setParameterByClass(ilCalendarCategoryGUI::class, 'category_id', $category_id);
                $this->tabs_gui->addTab(
                    'cal_manage',
                    $this->lng->txt('settings'),
                    $this->ctrl->getLinkTargetByClass(ilCalendarCategoryGUI::class, 'edit')
                );
                $this->ctrl->clearParameterByClass(ilCalendarCategoryGUI::class, 'category_id');
            }
        } else {
            $this->tabs_gui->addTab(
                'cal_agenda',
                $this->lng->txt("cal_agenda"),
                $this->ctrl->getLinkTarget($this, '')
            );

            if (
                $this->rbacsystem->checkAccess(
                    'add_consultation_hours',
                    ilCalendarSettings::_getInstance()->getCalendarSettingsId()
                ) &&
                ilCalendarSettings::_getInstance()->areConsultationHoursEnabled()
            ) {
                $this->tabs_gui->addTab(
                    'app_consultation_hours',
                    $this->lng->txt('app_consultation_hours'),
                    $this->ctrl->getLinkTargetByClass(ilConsultationHoursGUI::class, '')
                );
            }
            $this->tabs_gui->addTarget(
                'cal_manage',
                $this->ctrl->getLinkTargetByClass('ilCalendarCategoryGUI', 'manage')
            );
            $this->tabs_gui->addTarget('settings', $this->ctrl->getLinkTargetByClass('ilCalendarUserSettingsGUI', ''));
        }
    }

    protected function prepareOutput(): void
    {
        if ($this->category_id) {
            $this->addCategoryTabs();
        } else {
            $this->addStandardTabs();
        }

        // if we are in single calendar view
        if ($this->category_id > 0) {
            $tabs = $this->tabs_gui;
            $lng = $this->lng;
            $ctrl = $this->ctrl;
            $tpl = $this->tpl;

            $category = new ilCalendarCategory($this->category_id);

            // Set header
            $header = "";
            switch ($category->getType()) {
                case ilCalendarCategory::TYPE_USR:
                    $header = $this->lng->txt('cal_type_personal') . ": " . $category->getTitle();
                    break;

                case ilCalendarCategory::TYPE_GLOBAL:
                    $header = $this->lng->txt('cal_type_system') . ": " . $category->getTitle();
                    break;

                case ilCalendarCategory::TYPE_OBJ:
                    $header = $this->lng->txt('cal_type_' . $category->getObjType()) . ": " . $category->getTitle();
                    break;

                case ilCalendarCategory::TYPE_CH:
                    $header = str_replace(
                        "%1",
                        ilObjUser::_lookupFullname($category->getObjId()),
                        $this->lng->txt("cal_consultation_hours_for_user")
                    );
                    break;

                case ilCalendarCategory::TYPE_BOOK:
                    $header = $category->getTitle();
                    break;
            }
            $tpl->setTitleIcon(ilUtil::getImagePath("icon_cal.svg"));
            $tpl->setTitle($header);

            $action_menu = new ilAdvancedSelectionListGUI();
            $action_menu->setAsynch(false);
            $action_menu->setAsynchUrl('');
            $action_menu->setListTitle($this->lng->txt('actions'));
            $action_menu->setId('act_cal');
            $action_menu->setSelectionHeaderClass('small');
            $action_menu->setItemLinkClass('xsmall');
            $action_menu->setLinksMode('il_ContainerItemCommand2');
            $action_menu->setHeaderIcon(ilAdvancedSelectionListGUI::DOWN_ARROW_DARK);
            $action_menu->setUseImages(false);

            // iCal-Url
            $ctrl->setParameterByClass("ilcalendarsubscriptiongui", "category_id", $this->category_id);
            $action_menu->addItem(
                $this->lng->txt("cal_ical_url"),
                "",
                $ctrl->getLinkTargetByClass("ilcalendarsubscriptiongui", "")
            );

            // delete action
            if ($this->actions->checkDeleteCal($this->category_id)) {
                $ctrl->setParameterByClass("ilcalendarcategorygui", "category_id", $this->category_id);
                $action_menu->addItem(
                    $this->lng->txt("cal_delete_cal"),
                    "",
                    $ctrl->getLinkTargetByClass("ilcalendarcategorygui", "confirmDelete")
                );
            }
            $tpl->setHeaderActionMenu($action_menu->getHTML());
        }
    }

    /**
     * init the seed date for presentations (month view, minicalendar)
     */
    public function initSeed(): void
    {
        $seed = '';
        if ($this->http->wrapper()->query()->has('seed')) {
            $seed = $this->http->wrapper()->query()->retrieve(
                'seed',
                $this->refinery->kindlyTo()->string()
            );
        }

        // default to today
        $now = new \ilDate(time(), IL_CAL_UNIX);
        $this->seed = new \ilDate($now->get(IL_CAL_DATE), IL_CAL_DATE);
        if ($seed) {
            $this->seed = new ilDate($seed, IL_CAL_DATE);
        } elseif (!$this->getRepositoryMode()) {
            $session_seed = ilSession::get('cal_seed');
            if ($session_seed) {
                $this->seed = new ilDate($session_seed, IL_CAL_DATE);
            }
        }
        $this->ctrl->setParameter($this, 'seed', $this->seed->get(IL_CAL_DATE));
        ilSession::set('cal_seed', $this->seed->get(IL_CAL_DATE));
    }

    /**
     * Sync external calendars
     */
    protected function synchroniseExternalCalendars(): void
    {
        if (!ilCalendarSettings::_getInstance()->isWebCalSyncEnabled()) {
            return;
        }
        $limit = new ilDateTime(time(), IL_CAL_UNIX);
        $limit->increment(IL_CAL_HOUR, -1 * ilCalendarSettings::_getInstance()->getWebCalSyncHours());

        $cats = ilCalendarCategories::_getInstance($this->user->getId());
        foreach ($cats->getCategoriesInfo() as $cat_id => $info) {
            if ($info['remote'] ?? false) {
                // Check for execution
                $category = new ilCalendarCategory($cat_id);

                if (
                    $category->getRemoteSyncLastExecution()->isNull() ||
                    ilDateTime::_before($category->getRemoteSyncLastExecution(), $limit)
                ) {
                    // update in any case to avoid multiple updates of invalid calendar sources.
                    $category->setRemoteSyncLastExecution(new ilDateTime(time(), IL_CAL_UNIX));
                    $category->update();

                    $remote = new ilCalendarRemoteReader($category->getRemoteUrl());
                    $remote->setUser($category->getRemoteUser());
                    $remote->setPass($category->getRemotePass());
                    $remote->read();
                    $remote->import($category);
                    break;
                }
            }
        }
    }

    #21613
    public function showToolbarAndSidebar(): bool
    {
        #21783
        return !(
            $this->ctrl->getCmdClass() == "ilcalendarappointmentgui" ||
            $this->ctrl->getCmdClass() == 'ilconsultationhoursgui'
        );
    }
}
