<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjBookingPoolGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*
* @ilCtrl_Calls ilObjBookingPoolGUI: ilPermissionGUI, ilBookingObjectGUI
* @ilCtrl_Calls ilObjBookingPoolGUI: ilBookingScheduleGUI, ilInfoScreenGUI, ilPublicUserProfileGUI
* @ilCtrl_Calls ilObjBookingPoolGUI: ilCommonActionDispatcherGUI, ilObjectCopyGUI, ilObjectMetaDataGUI
* @ilCtrl_Calls ilObjBookingPoolGUI: ilBookingParticipantGUI, ilBookingReservationsGUI, ilBookingPreferencesGUI
* @ilCtrl_IsCalledBy ilObjBookingPoolGUI: ilRepositoryGUI, ilAdministrationGUI
*/
class ilObjBookingPoolGUI extends ilObjectGUI
{
    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilNavigationHistory
     */
    protected $nav_history;

    /**
     * @var ilBookingHelpAdapter
     */
    protected $help;

    /**
     * @var int
     */
    protected $user_id_to_book;  // user who is getting the reservation

    /**
     * @var int
     */
    protected $user_id_assigner; // user who performs the reservation.(self/another)

    /**
     * @var ilBookingManagerInternalService
     */
    protected $service;

    /**
     * @var string
     */
    protected $seed;

    /**
     * @var string
     */
    protected $sseed;

    /**
     * ilObjBookingPoolGUI constructor.
     * @param array $a_data
     * @param int $a_id
     * @param bool $a_call_by_reference
     * @param bool $a_prepare_output
     * @throws ilException
     */
    public function __construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output = true)
    {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->tabs = $DIC->tabs();
        $this->nav_history = $DIC["ilNavigationHistory"];
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->type = "book";
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
        $this->lng->loadLanguageModule("book");

        // not on creation
        if (is_object($this->object)) {
            $this->help = new ilBookingHelpAdapter($this->object, $DIC["ilHelp"]);
            $DIC["ilHelp"]->setScreenIdComponent("book");
        }

        $this->user_profile_id = (int) $_GET["user_id"];
        $this->book_obj_id = (int) $_REQUEST['object_id'];
        $this->seed = ilUtil::stripSlashes($_GET['seed']);
        $this->sseed = ilUtil::stripSlashes($_GET['sseed']);
        $this->reservation_id = ilUtil::stripSlashes($_GET["reservation_id"]);
        $this->profile_user_id = (int) $_GET['user_id'];

        $this->service = $DIC->bookingManager()->internal();

        $this->user_id_assigner = $this->user->getId();
        if ($_GET['bkusr']) {
            $this->user_id_to_book = (int) $_GET['bkusr'];
        } else {
            $this->user_id_to_book = $this->user_id_assigner; // by default user books his own booking objects.
        }

        if ((int) $_REQUEST['object_id'] > 0 && ilBookingObject::lookupPoolId((int) $_REQUEST['object_id']) != $this->object->getId()) {
            throw new ilException("Booking Object ID does not match Booking Pool.");
        }
    }

    /**
     * @return bool|void
     * @throws ilCtrlException
     * @throws ilObjectException
     */
    public function executeCommand()
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $ilNavigationHistory = $this->nav_history;
        $ilUser = $this->user;

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        
        if (!$next_class && $cmd == 'render') {
            if ($ilUser->getId() != ANONYMOUS_USER_ID) {
                if ($this->object->getScheduleType() == ilObjBookingPool::TYPE_NO_SCHEDULE_PREFERENCES &&
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

        /*		if(substr($cmd, 0, 4) == 'book')
                {
                    $next_class = '';
                }*/

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
                $profile = new ilPublicUserProfileGUI($this->user_profile_id);
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
                $this->ctrl->forwardCommand($gui);
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
                $res_gui = new ilBookingReservationsGUI($this->object, $this->help);
                $this->ctrl->forwardCommand($res_gui);
                break;

            case 'ilbookingpreferencesgui':
                $this->tabs_gui->setTabActive('preferences');
                $gui = $this->service->ui()->getPreferencesGUI($this->object);
                $this->ctrl->forwardCommand($gui);
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
        return true;
    }

    protected function initCreationForms($a_new_type)
    {
        $forms = parent::initCreationForms($a_new_type);
        unset($forms[self::CFORM_IMPORT]);
        
        return $forms;
    }

    protected function afterSave(ilObject $a_new_object)
    {
        $a_new_object->setOffline(true);
        $a_new_object->update();

        // always send a message
        ilUtil::sendSuccess($this->lng->txt("book_pool_added"), true);
        $this->ctrl->setParameter($this, "ref_id", $a_new_object->getRefId());
        $this->ctrl->redirect($this, "edit");
    }
    
    public function editObject()
    {
        $this->showNoScheduleMessage();
        return parent::editObject();
    }

    /**
     * Show no schedule message
     *
     * @param
     * @return
     */
    public function showNoScheduleMessage()
    {
        // if we have no schedules yet - show info
        if ($this->object->getScheduleType() == ilObjBookingPool::TYPE_FIX_SCHEDULE &&
            !sizeof(ilBookingSchedule::getList($this->object->getId()))) {
            ilUtil::sendInfo($this->lng->txt("book_schedule_warning_edit"));
        }
    }

    
    protected function initEditCustomForm(ilPropertyFormGUI $a_form)
    {
        $obj_service = $this->getObjectService();

        $online = new ilCheckboxInputGUI($this->lng->txt("online"), "online");
        $a_form->addItem($online);

        $type = new ilRadioGroupInputGUI($this->lng->txt("book_schedule_type"), "stype");
        $type->setRequired(true);
        $a_form->addItem($type);
        
        // #14478
        if (sizeof(ilBookingObject::getList($this->object->getId()))) {
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
        $rmd->setChecked($this->object->getReminderStatus());
        if (!ilCronManager::isJobActive('book_notification')) {
            $rmd->setInfo($this->lng->txt("book_notification_cron_not_active"));
        }
        $fixed->addSubItem($rmd);

        $rmd_day = new ilNumberInputGUI($this->lng->txt("book_reminder_day"), "rmd_day");
        $rmd_day->setRequired(true);
        $rmd_day->setInfo($this->lng->txt("book_reminder_day_info"));
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
        $limit->setInfo($this->lng->txt("book_overall_limit_info"));
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
        $pref_nr->setRequired(true);
        $pref->addSubItem($pref_nr);

        // preference deadline
        $pref_deadline = new ilDateTimeInputGUI($this->lng->txt("book_pref_deadline"), "pref_deadline");
        $pref_deadline->setShowTime(true);
        $pref_deadline->setRequired(true);
        $pref->addSubItem($pref_deadline);

        $public = new ilCheckboxInputGUI($this->lng->txt("book_public_log"), "public");
        $public->setInfo($this->lng->txt("book_public_log_info"));
        $a_form->addItem($public);

        // presentation
        $pres = new ilFormSectionHeaderGUI();
        $pres->setTitle($this->lng->txt('obj_presentation'));
        $a_form->addItem($pres);

        // tile image
        $obj_service->commonSettings()->legacyForm($a_form, $this->object)->addTileImage();

        // additional features
        $feat = new ilFormSectionHeaderGUI();
        $feat->setTitle($this->lng->txt('obj_features'));
        $a_form->addItem($feat);
    }

    protected function getEditFormCustomValues(array &$a_values)
    {
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

    protected function updateCustom(ilPropertyFormGUI $a_form)
    {
        $obj_service = $this->getObjectService();

        $pref_deadline = $a_form->getItemByPostVar("pref_deadline")->getDate();
        $pref_deadline = $pref_deadline
            ? $pref_deadline->get(IL_CAL_UNIX)
            : 0;

        $this->object->setOffline(!$a_form->getInput('online'));
        $this->object->setReminderStatus($a_form->getInput('rmd'));
        $this->object->setReminderDay($a_form->getInput('rmd_day'));
        $this->object->setPublicLog($a_form->getInput('public'));
        $this->object->setScheduleType($a_form->getInput('stype'));
        $this->object->setOverallLimit($a_form->getInput('limit') ? $a_form->getInput('limit') : null);
        $this->object->setReservationFilterPeriod(strlen($a_form->getInput('period')) ? (int) $a_form->getInput('period') : null);
        $this->object->setPreferenceDeadline($pref_deadline);
        $this->object->setPreferenceNumber($a_form->getInput('preference_nr'));

        // tile image
        $obj_service->commonSettings()->legacyForm($a_form, $this->object)->saveTileImage();

        ilObjectServiceSettingsGUI::updateServiceSettingsForm(
            $this->object->getId(),
            $a_form,
            array(ilObjectServiceSettingsGUI::CUSTOM_METADATA)
        );
    }
    
    public function addExternalEditFormCustom(ilPropertyFormGUI $a_form)
    {
        ilObjectServiceSettingsGUI::initServiceSettingsForm(
            $this->object->getId(),
            $a_form,
            array(ilObjectServiceSettingsGUI::CUSTOM_METADATA)
        );
    }
    
    /**
    * get tabs
    */
    public function setTabs()
    {
        $ilUser = $this->user;

        /** @var ilObjBookingPool $pool */
        $pool = $this->object;
        
        if (in_array($this->ctrl->getCmd(), array("create", "save")) && !$this->ctrl->getNextClass()) {
            return;
        }

        if ($this->checkPermissionBool('read')) {
            if ($ilUser->getId() != ANONYMOUS_USER_ID) {
                if ($pool->getScheduleType() == ilObjBookingPool::TYPE_NO_SCHEDULE_PREFERENCES) {
                    $this->tabs_gui->addTab(
                        "preferences",
                        $this->lng->txt("book_pref_overview"),
                        $this->ctrl->getLinkTargetByClass("ilbookingpreferencesgui", "")
                    );
                }

                if ($pool->getScheduleType() != ilObjBookingPool::TYPE_NO_SCHEDULE_PREFERENCES ||
                    $this->checkPermissionBool('write')) {
                    $this->tabs_gui->addTab(
                        "render",
                        $this->lng->txt("book_booking_types"),
                        $this->ctrl->getLinkTarget($this, "render")
                    );
                }
            }
        
            $this->tabs_gui->addTab(
                "info",
                $this->lng->txt("info_short"),
                $this->ctrl->getLinkTarget($this, "infoscreen")
            );

            if ($ilUser->getId() != ANONYMOUS_USER_ID || $this->object->hasPublicLog()) {
                $this->tabs_gui->addTab(
                    "log",
                    $this->lng->txt("book_log"),
                    $this->ctrl->getLinkTargetByClass("ilbookingreservationsgui", "")
                );
            }
        }
        
        if ($this->checkPermissionBool('write')) {
            if ($this->object->getScheduleType() == ilObjBookingPool::TYPE_FIX_SCHEDULE) {
                $this->tabs_gui->addTab(
                    "schedules",
                    $this->lng->txt("book_schedules"),
                    $this->ctrl->getLinkTargetByClass("ilbookingschedulegui", "render")
                );
            }
            
            $this->tabs_gui->addTab(
                "settings",
                $this->lng->txt("settings"),
                $this->ctrl->getLinkTarget($this, "edit")
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

        if ($this->checkPermissionBool('write')) {
            $this->tabs_gui->addTab(
                "participants",
                $this->lng->txt("participants"),
                $this->ctrl->getLinkTargetByClass("ilbookingparticipantgui", "render")
            );
        }

        if ($this->checkPermissionBool('edit_permission')) {
            $this->tabs_gui->addTab(
                "perm_settings",
                $this->lng->txt("perm_settings"),
                $this->ctrl->getLinkTargetByClass("ilpermissiongui", "perm")
            );
        }
    }
    
    protected function setHelpId($a_id)
    {
        $this->help->setHelpId($a_id);

        $ilHelp = $this->help;

        switch ($this->object->getScheduleType()) {
            case ilObjBookingPool::TYPE_FIX_SCHEDULE: $object_subtype = "-schedule"; break;
            case ilObjBookingPool::TYPE_NO_SCHEDULE: $object_subtype = "-noschedule"; break;
            case ilObjBookingPool::TYPE_NO_SCHEDULE_PREFERENCES: $object_subtype = "-noschedulepref"; break;
        }

        $ilHelp->setScreenIdComponent('book');
        $ilHelp->setScreenId('object' . $object_subtype);
        $ilHelp->setSubScreenId($a_id);
    }

    public static function _goto($a_target)
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $lng = $DIC->language();

        if ($ilAccess->checkAccess("read", "", $a_target)) {
            ilObjectGUI::_gotoRepositoryNode($a_target, "render");
        } elseif ($ilAccess->checkAccess("visible", "", $a_target)) {
            ilObjectGUI::_gotoRepositoryNode($a_target, "infoScreen");
        } elseif ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
            ilUtil::sendFailure(sprintf(
                $lng->txt("msg_no_perm_read_item"),
                ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
            ), true);
            ilObjectGUI::_gotoRepositoryRoot();
        }
    }

    /**
    * this one is called from the info button in the repository
    * not very nice to set cmdClass/Cmd manually, if everything
    * works through ilCtrl in the future this may be changed
    */
    public function infoScreenObject()
    {
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->infoScreen();
    }

    public function infoScreen()
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
        if ($ilCtrl->getNextClass() == "ilinfoscreengui") {
            $ilCtrl->forwardCommand($info);
        } else {
            return $ilCtrl->getHTML($info);
        }
    }
    

    public function showProfileObject()
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        
        $this->tabs_gui->clearTargets();
        
        $user_id = $this->profile_user_id;
        
        $profile = new ilPublicUserProfileGUI($user_id);
        $profile->setBackUrl($this->ctrl->getLinkTarget($this, 'log'));
        $tpl->setContent($ilCtrl->getHTML($profile));
    }
    
    public function addLocatorItems()
    {
        $ilLocator = $this->locator;
        
        if (is_object($this->object)) {
            $ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, "render"), "", $this->object->getRefId());
        }
    }

    /**
     * @inheritdoc
     */
    protected function initHeaderAction($a_sub_type = null, $a_sub_id = null)
    {
        $access = $this->access;
        $user = $this->user;
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $lng->loadLanguageModule("noti");

        $lg = parent::initHeaderAction($a_sub_type, $a_sub_id);

        if ($lg && $access->checkAccess("read", "", $this->ref_id)) {
            // notification
            if ($this->object->getScheduleType() === ilObjBookingPool::TYPE_FIX_SCHEDULE &&
                $this->object->getReminderStatus()) {
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
        }

        return $lg;
    }

    /**
     * Save notification
     */
    public function saveNotificationObject()
    {
        $ctrl = $this->ctrl;
        $user = $this->user;


        switch ($_GET["ntf"]) {
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
