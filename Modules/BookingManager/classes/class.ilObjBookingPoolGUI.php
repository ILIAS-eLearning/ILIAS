<?php

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
 * @ilCtrl_Calls ilObjBookingPoolGUI: ilPermissionGUI, ilBookingObjectGUI, ilDidacticTemplateGUI
 * @ilCtrl_Calls ilObjBookingPoolGUI: ilBookingScheduleGUI, ilInfoScreenGUI, ilPublicUserProfileGUI
 * @ilCtrl_Calls ilObjBookingPoolGUI: ilCommonActionDispatcherGUI, ilObjectCopyGUI, ilObjectMetaDataGUI
 * @ilCtrl_Calls ilObjBookingPoolGUI: ilBookingParticipantGUI, ilBookingReservationsGUI, ilBookingPreferencesGUI
 * @ilCtrl_IsCalledBy ilObjBookingPoolGUI: ilRepositoryGUI, ilAdministrationGUI
 */
class ilObjBookingPoolGUI extends ilObjectGUI
{
    protected \ILIAS\BookingManager\StandardGUIRequest $book_request;
    protected \ILIAS\BookingManager\InternalService $service;
    protected ilTabsGUI $tabs;
    protected ilNavigationHistory $nav_history;
    protected ilBookingHelpAdapter $help;
    protected int $user_id_to_book;  // user who is getting the reservation
    protected int $user_id_assigner; // user who performs the reservation.(self/another)
    protected string $seed;
    protected string $sseed;
    protected int $profile_user_id;
    protected int $book_obj_id;
    protected string $reservation_id;

    public function __construct(
        $a_data,
        int $a_id,
        bool $a_call_by_reference,
        bool $a_prepare_output = true
    ) {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->tabs = $DIC->tabs();
        $this->nav_history = $DIC["ilNavigationHistory"];
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->type = "book";

        $this->book_request = $DIC->bookingManager()
                                  ->internal()
                                  ->gui()
                                  ->standardRequest();

        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
        $this->lng->loadLanguageModule("book");

        // not on creation
        if (is_object($this->object)) {
            /** @var ilObjBookingPool $pool */
            $pool = $this->object;
            $this->help = new ilBookingHelpAdapter($pool, $DIC["ilHelp"]);
            $DIC["ilHelp"]->setScreenIdComponent("book");
        }

        $this->book_obj_id = $this->book_request->getObjectId();
        $this->seed = $this->book_request->getSeed();
        $this->sseed = $this->book_request->getSSeed();
        $this->reservation_id = $this->book_request->getReservationId();
        $this->profile_user_id = $this->book_request->getUserId();

        $this->service = $DIC->bookingManager()->internal();

        $this->user_id_assigner = $this->user->getId();
        if ($this->book_request->getBookedUser() > 0) {
            $this->user_id_to_book = $this->book_request->getBookedUser();
        } else {
            $this->user_id_to_book = $this->user_id_assigner; // by default user books his own booking objects.
        }

        if ($this->book_request->getObjectId() > 0 &&
            ilBookingObject::lookupPoolId($this->book_request->getObjectId()) !== $this->object->getId()) {
            throw new ilException("Booking Object ID does not match Booking Pool.");
        }
    }

    /**
     * @throws ilCtrlException
     * @throws ilException
     * @throws ilObjectException
     */
    public function executeCommand(): void
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $ilNavigationHistory = $this->nav_history;
        $ilUser = $this->user;

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        if (!$next_class && $cmd === 'render') {
            if ($ilUser->getId() !== ANONYMOUS_USER_ID) {
                if ($this->object->getScheduleType() === ilObjBookingPool::TYPE_NO_SCHEDULE_PREFERENCES &&
                    !$this->checkPermissionBool('write')) {
                    $this->ctrl->setCmdClass('ilBookingPreferencesGUI');
                } else {
                    $this->ctrl->setCmdClass('ilBookingObjectGUI');
                }
                $next_class = $this->ctrl->getNextClass($this);
            } else {
                $this->ctrl->redirect($this, "infoscreen");
            }
        }

        $ilNavigationHistory->addItem(
            $this->ref_id,
            "./goto.php?target=book_" . $this->ref_id,
            "book"
        );

        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->checkPermission('edit_permission');
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            case 'ilbookingobjectgui':
                if (!$this->checkPermissionBool('read') && $this->checkPermissionBool('visible')) {
                    $this->ctrl->redirect($this, "infoScreen");
                }
                $this->checkPermission('read');
                $this->tabs_gui->setTabActive('render');
                $object_gui = new ilBookingObjectGUI(
                    $this,
                    $this->seed,
                    $this->sseed,
                    $this->help
                );
                $this->ctrl->forwardCommand($object_gui);
                break;

            case 'ilbookingschedulegui':
                $this->checkPermission('write');
                $this->tabs_gui->setTabActive('schedules');
                $schedule_gui = new ilBookingScheduleGUI($this);
                $this->ctrl->forwardCommand($schedule_gui);
                break;

            case 'ilpublicuserprofilegui':
                $this->checkPermission('read');
                $ilTabs->clearTargets();
                $profile = new ilPublicUserProfileGUI($this->profile_user_id);
                $profile->setBackUrl($this->ctrl->getLinkTargetByClass("ilbookingreservationsgui", ''));
                $ret = $this->ctrl->forwardCommand($profile);
                $tpl->setContent($ret);
                break;

            case 'ilinfoscreengui':
                $this->checkPermission('visible');
                $this->infoScreen();
                break;

            case "ilcommonactiondispatchergui":
                $this->checkPermission('read');
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                if ($gui !== null) {
                    $this->ctrl->forwardCommand($gui);
                }
                break;

            case "ilobjectcopygui":
                $this->checkPermission('copy');
                $cp = new ilObjectCopyGUI($this);
                $cp->setType("book");
                $this->ctrl->forwardCommand($cp);
                break;

            case 'ilobjectmetadatagui':
                $this->checkPermission('write');
                $this->tabs_gui->setTabActive('meta_data');
                $md_gui = new ilObjectMetaDataGUI($this->object, 'bobj');
                $this->ctrl->forwardCommand($md_gui);
                break;

            case 'ilbookingparticipantgui':
                $this->checkPermission('write');
                $this->tabs_gui->setTabActive('participants');
                $object_gui = new ilBookingParticipantGUI($this);
                $this->ctrl->forwardCommand($object_gui);
                break;


            case "ilbookingreservationsgui":
                $this->tabs_gui->setTabActive('log');
                /** @var ilObjBookingPool $pool */
                $pool = $this->object;
                $res_gui = new ilBookingReservationsGUI($pool, $this->help);
                $this->ctrl->forwardCommand($res_gui);
                break;

            case 'ilbookingpreferencesgui':
                $this->tabs_gui->setTabActive('preferences');
                /** @var ilObjBookingPool $pool */
                $pool = $this->object;
                $gui = $this->service->gui()->preferences()->BookingPreferencesGUI($pool);
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ildidactictemplategui':
                $this->ctrl->setReturn($this, 'edit');
                $did = new ilDidacticTemplateGUI($this);
                $this->ctrl->forwardCommand($did);
                break;


            default:
                if (!in_array($cmd, ["create", "save", "infoScreen"])) {
                    $this->checkPermission('read');
                }
                $cmd = $this->ctrl->getCmd();
                $cmd .= 'Object';
                $this->$cmd();
                break;
        }

        $this->addHeaderAction();
    }

    protected function initCreationForms(string $new_type): array
    {
        $forms = parent::initCreationForms($new_type);
        unset($forms[self::CFORM_IMPORT]);

        return $forms;
    }

    protected function afterSave(ilObject $new_object): void
    {
        $new_object->setOffline(true);
        $new_object->update();

        // always send a message
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("book_pool_added"), true);
        $this->ctrl->setParameter($this, "ref_id", $new_object->getRefId());
        $this->ctrl->redirect($this, "edit");
    }

    protected function afterUpdate(): void
    {
        // check if template is changed
        $current_tpl_id = ilDidacticTemplateObjSettings::lookupTemplateId(
            $this->object->getRefId()
        );
        $new_tpl_id = $this->getDidacticTemplateVar('dtpl');

        if ($new_tpl_id !== $current_tpl_id) {
            // redirect to didactic template confirmation
            $this->ctrl->setReturn($this, 'edit');
            $this->ctrl->setCmdClass('ildidactictemplategui');
            $this->ctrl->setCmd('confirmTemplateSwitch');
            $dtpl_gui = new ilDidacticTemplateGUI($this, $new_tpl_id);
            $this->ctrl->forwardCommand($dtpl_gui);
            return;
        }
        parent::afterUpdate();
    }

    public function editObject(): void
    {
        $this->showNoScheduleMessage();
        parent::editObject();
    }

    public function showNoScheduleMessage(): void
    {
        // if we have no schedules yet - show info
        if ($this->object->getScheduleType() === ilObjBookingPool::TYPE_FIX_SCHEDULE &&
            !count(ilBookingSchedule::getList($this->object->getId()))) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("book_schedule_warning_edit"));
        }
    }


    protected function initEditCustomForm(ilPropertyFormGUI $form): void
    {
        $obj_service = $this->getObjectService();

        // Show didactic template type
        $this->initDidacticTemplate($form);

        $type = new ilRadioGroupInputGUI($this->lng->txt("book_schedule_type"), "stype");
        $type->setRequired(true);
        $form->addItem($type);

        // #14478
        if (count(ilBookingObject::getList($this->object->getId()))) {
            $type->setDisabled(true);
        }

        $fixed = new ilRadioOption($this->lng->txt("book_schedule_type_fixed"), ilObjBookingPool::TYPE_FIX_SCHEDULE);
        $fixed->setInfo($this->lng->txt("book_schedule_type_fixed_info"));
        $type->addOption($fixed);

        #23637
        //period
        $period = new ilNumberInputGUI($this->lng->txt("book_reservation_filter_period"), "period");
        $period->setInfo($this->lng->txt("book_reservation_filter_period_info"));
        $period->setSuffix($this->lng->txt("days"));
        $period->setSize(3);
        $period->setMinValue(0);
        $fixed->addSubItem($period);

        // reminder
        $rmd = new ilCheckboxInputGUI($this->lng->txt("book_reminder_setting"), "rmd");
        $rmd->setChecked((bool) $this->object->getReminderStatus());
        $rmd->setInfo($this->lng->txt("book_reminder_day_info"));
        $fixed->addSubItem($rmd);

        $rmd_day = new ilNumberInputGUI($this->lng->txt("book_reminder_day"), "rmd_day");
        $rmd_day->setRequired(true);
        $rmd_day->setSize(3);
        $rmd_day->setSuffix($this->lng->txt("book_reminder_days"));
        $rmd_day->setValue(max($this->object->getReminderDay(), 1));
        $rmd_day->setMinValue(1);
        $rmd->addSubItem($rmd_day);

        // no schedule, direct booking
        $none = new ilRadioOption($this->lng->txt("book_schedule_type_none_direct"), ilObjBookingPool::TYPE_NO_SCHEDULE);
        $none->setInfo($this->lng->txt("book_schedule_type_none_direct_info"));
        $type->addOption($none);

        $limit = new ilNumberInputGUI($this->lng->txt("book_overall_limit"), "limit");
        $limit->setSize(4);
        $limit->setMinValue(1);
        $limit->setSuffix($this->lng->txt("book_bookings_per_user"));
        $none->addSubItem($limit);

        // no schedule, using preferences
        $pref = new ilRadioOption($this->lng->txt("book_schedule_type_none_preference"), ilObjBookingPool::TYPE_NO_SCHEDULE_PREFERENCES);
        $pref->setInfo($this->lng->txt("book_schedule_type_none_preference_info"));
        $type->addOption($pref);

        // number of preferences
        $pref_nr = new ilNumberInputGUI($this->lng->txt("book_nr_of_preferences"), "preference_nr");
        $pref_nr->setSize(4);
        $pref_nr->setMinValue(1);
        $pref_nr->setInfo($this->lng->txt("book_nr_of_preferences_info"));
        $pref_nr->setSuffix($this->lng->txt("book_nr_preferences"));
        $pref_nr->setRequired(true);
        $pref->addSubItem($pref_nr);

        // preference deadline
        $pref_deadline = new ilDateTimeInputGUI($this->lng->txt("book_pref_deadline"), "pref_deadline");
        $pref_deadline->setInfo($this->lng->txt("book_pref_deadline_info"));
        $pref_deadline->setShowTime(true);
        $pref_deadline->setRequired(true);
        $pref->addSubItem($pref_deadline);

        $public = new ilCheckboxInputGUI($this->lng->txt("book_public_log"), "public");
        $public->setInfo($this->lng->txt("book_public_log_info"));
        $form->addItem($public);

        $this->lng->loadLanguageModule("rep");
        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('rep_activation_availability'));
        $form->addItem($section);

        $online = new ilCheckboxInputGUI($this->lng->txt("online"), "online");
        $form->addItem($online);

        // presentation
        $pres = new ilFormSectionHeaderGUI();
        $pres->setTitle($this->lng->txt('obj_presentation'));
        $form->addItem($pres);

        // tile image
        $obj_service->commonSettings()->legacyForm($form, $this->object)->addTileImage();

        // additional features
        $feat = new ilFormSectionHeaderGUI();
        $feat->setTitle($this->lng->txt('obj_features'));
        $form->addItem($feat);
    }

    protected function getEditFormCustomValues(
        array &$a_values
    ): void {
        $a_values["online"] = !$this->object->isOffline();
        $a_values["public"] = $this->object->hasPublicLog();
        $a_values["stype"] = $this->object->getScheduleType();
        $a_values["limit"] = $this->object->getOverallLimit();
        $a_values["period"] = $this->object->getReservationFilterPeriod();
        $a_values["rmd"] = $this->object->getReminderStatus();
        $a_values["rmd_day"] = $this->object->getReminderDay();
        $a_values["preference_nr"] = $this->object->getPreferenceNumber();
        if ($this->object->getPreferenceDeadline() > 0) {
            $a_values["pref_deadline"] = new ilDateTime($this->object->getPreferenceDeadline(), IL_CAL_UNIX);
        }
    }

    protected function updateCustom(ilPropertyFormGUI $form): void
    {
        $obj_service = $this->getObjectService();

        $pref_deadline = $form->getItemByPostVar("pref_deadline");
        if ($pref_deadline !== null) {
            $pref_deadline = $pref_deadline->getDate();
            $pref_deadline = $pref_deadline
                ? $pref_deadline->get(IL_CAL_UNIX)
                : 0;

            $this->object->setOffline(!$form->getInput('online'));
            $this->object->setReminderStatus((int) $form->getInput('rmd'));
            $this->object->setReminderDay($form->getInput('rmd_day'));
            $this->object->setPublicLog($form->getInput('public'));
            $this->object->setScheduleType($form->getInput('stype'));
            $this->object->setOverallLimit($form->getInput('limit') ?: null);
            $this->object->setReservationFilterPeriod($form->getInput('period') != '' ? (int) $form->getInput('period') : null);
            $this->object->setPreferenceDeadline($pref_deadline);
            $this->object->setPreferenceNumber($form->getInput('preference_nr'));

            // tile image
            $obj_service->commonSettings()->legacyForm($form, $this->object)->saveTileImage();

            ilObjectServiceSettingsGUI::updateServiceSettingsForm(
                $this->object->getId(),
                $form,
                array(ilObjectServiceSettingsGUI::CUSTOM_METADATA)
            );
        }
    }

    public function addExternalEditFormCustom(ilPropertyFormGUI $form): void
    {
        ilObjectServiceSettingsGUI::initServiceSettingsForm(
            $this->object->getId(),
            $form,
            array(ilObjectServiceSettingsGUI::CUSTOM_METADATA)
        );
    }

    /**
     * For tab order discussion see
     * https://mantis.ilias.de/view.php?id=32268
     * @throws ilCtrlException
     */
    protected function setTabs(): void
    {
        $ilUser = $this->user;

        /** @var ilObjBookingPool $pool */
        $pool = $this->object;

        if (!$this->ctrl->getNextClass() && in_array($this->ctrl->getCmd(), array("create", "save"))) {
            return;
        }

        if ($this->checkPermissionBool('read')) {
            if ($ilUser->getId() !== ANONYMOUS_USER_ID) {
                if ($pool->getScheduleType() === ilObjBookingPool::TYPE_NO_SCHEDULE_PREFERENCES) {
                    $this->tabs_gui->addTab(
                        "preferences",
                        $this->lng->txt("book_pref_overview"),
                        $this->ctrl->getLinkTargetByClass("ilbookingpreferencesgui", "")
                    );
                }

                if ($pool->getScheduleType() !== ilObjBookingPool::TYPE_NO_SCHEDULE_PREFERENCES ||
                    $this->checkPermissionBool('write')) {
                    $this->tabs_gui->addTab(
                        "render",
                        $this->lng->txt("book_booking_objects"),
                        $this->ctrl->getLinkTarget($this, "render")
                    );
                }
            }

            if ($ilUser->getId() !== ANONYMOUS_USER_ID || $this->object->hasPublicLog()) {
                $this->tabs_gui->addTab(
                    "log",
                    $this->lng->txt("book_log"),
                    $this->ctrl->getLinkTargetByClass("ilbookingreservationsgui", "")
                );
            }

            $this->tabs_gui->addTab(
                "info",
                $this->lng->txt("info_short"),
                $this->ctrl->getLinkTarget($this, "infoscreen")
            );
        }

        if ($this->checkPermissionBool('write')) {
            $this->tabs_gui->addTab(
                "settings",
                $this->lng->txt("settings"),
                $this->ctrl->getLinkTarget($this, "edit")
            );

            if ($this->object->getScheduleType() === ilObjBookingPool::TYPE_FIX_SCHEDULE) {
                $this->tabs_gui->addTab(
                    "schedules",
                    $this->lng->txt("book_schedules"),
                    $this->ctrl->getLinkTargetByClass("ilbookingschedulegui", "render")
                );
            }

            $this->tabs_gui->addTab(
                "participants",
                $this->lng->txt("participants"),
                $this->ctrl->getLinkTargetByClass("ilbookingparticipantgui", "render")
            );

            // meta data
            $mdgui = new ilObjectMetaDataGUI($this->object, "bobj");
            $mdtab = $mdgui->getTab();
            if ($mdtab) {
                $this->tabs_gui->addTarget(
                    "meta_data",
                    $mdtab,
                    "",
                    "ilobjectmetadatagui"
                );
            }
        }


        if ($this->checkPermissionBool('edit_permission')) {
            $this->tabs_gui->addTab(
                "perm_settings",
                $this->lng->txt("perm_settings"),
                $this->ctrl->getLinkTargetByClass("ilpermissiongui", "perm")
            );
        }
    }

    public static function _goto(string $a_target): void
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        $ilAccess = $DIC->access();
        $lng = $DIC->language();

        if ($ilAccess->checkAccess("read", "", $a_target)) {
            ilObjectGUI::_gotoRepositoryNode($a_target, "render");
        } elseif ($ilAccess->checkAccess("visible", "", $a_target)) {
            ilObjectGUI::_gotoRepositoryNode($a_target, "infoScreen");
        } elseif ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
            $main_tpl->setOnScreenMessage('failure', sprintf(
                $lng->txt("msg_no_perm_read_item"),
                ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
            ), true);
            ilObjectGUI::_gotoRepositoryRoot();
        }
    }

    /**
     * this one is called from the info button in the repository
     */
    public function infoScreenObject(): void
    {
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->infoScreen();
    }

    public function infoScreen(): string
    {
        $ilCtrl = $this->ctrl;

        $this->tabs_gui->setTabActive('info');

        $this->checkPermission("visible");

        $info = new ilInfoScreenGUI($this);

        $info->enablePrivateNotes();

        if ($this->checkPermissionBool("read")) {
            $info->enableNews();
        }

        // no news editing for files, just notifications
        $info->enableNewsEditing(false);
        if ($this->checkPermissionBool("write")) {
            $news_set = new ilSetting("news");
            $enable_internal_rss = $news_set->get("enable_rss_for_internal");

            if ($enable_internal_rss) {
                $info->setBlockProperty("news", "settings", true);
                $info->setBlockProperty("news", "public_notifications_option", true);
            }
        }

        // forward the command
        if ($ilCtrl->getNextClass() === "ilinfoscreengui") {
            $ilCtrl->forwardCommand($info);
        } else {
            return $ilCtrl->getHTML($info);
        }
        return "";
    }


    public function showProfileObject(): void
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;

        $this->tabs_gui->clearTargets();

        $user_id = $this->profile_user_id;

        $profile = new ilPublicUserProfileGUI($user_id);
        $profile->setBackUrl($this->ctrl->getLinkTarget($this, 'log'));
        $tpl->setContent($ilCtrl->getHTML($profile));
    }

    protected function addLocatorItems(): void
    {
        $ilLocator = $this->locator;

        if (is_object($this->object)) {
            $ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, "render"), "", $this->object->getRefId());
        }
    }

    protected function initHeaderAction(?string $sub_type = null, ?int $sub_id = null): ?ilObjectListGUI
    {
        $access = $this->access;
        $user = $this->user;
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $lng->loadLanguageModule("noti");

        $lg = parent::initHeaderAction($sub_type, $sub_id);

        if ($lg && $access->checkAccess("read", "", $this->ref_id)) {
            // notification
            if (!ilNotification::hasNotification(ilNotification::TYPE_BOOK, $user->getId(), $this->object->getId())) {
                $lg->addHeaderIcon(
                    "not_icon",
                    ilUtil::getImagePath("notification_off.svg"),
                    $lng->txt("noti_notification_deactivated")
                );

                $ctrl->setParameter($this, "ntf", 1);
                $caption = "noti_activate_notification";
            } else {
                $lg->addHeaderIcon(
                    "not_icon",
                    ilUtil::getImagePath("notification_on.svg"),
                    $lng->txt("noti_notification_activated")
                );

                $ctrl->setParameter($this, "ntf", 0);
                $caption = "noti_deactivate_notification";
            }

            $lg->addCustomCommand(
                $ctrl->getLinkTarget($this, "saveNotification"),
                $caption
            );

            $ctrl->setParameter($this, "ntf", "");
        }

        return $lg;
    }

    public function saveNotificationObject(): void
    {
        $ctrl = $this->ctrl;
        $user = $this->user;


        switch ($this->book_request->getNotification()) {
            case 0:
                ilNotification::setNotification(ilNotification::TYPE_BOOK, $user->getId(), $this->object->getId(), false);
                break;

            case 1:
                ilNotification::setNotification(ilNotification::TYPE_BOOK, $user->getId(), $this->object->getId(), true);
                break;
        }
        $ctrl->redirect($this, "render");
    }
}
