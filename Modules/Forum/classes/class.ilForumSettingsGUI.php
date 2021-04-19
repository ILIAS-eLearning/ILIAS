<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilForumSettingsGUI
 * @author Nadia Matuschek <nmatuschek@databay.de>
 * @ingroup ModulesForum
 */
class ilForumSettingsGUI
{
    private $ctrl;
    private $tpl;
    private $lng;
    private $settings;
    private $tabs;
    private $access;
    private $tree;
    private $parent_obj;
    
    /**
     * @var ilForumNotification
     */
    protected $forumNotificationObj = [];

    /**
     * @var ilPropertyFormGUI
     */
    protected $form;

    /**
     * @var ilPropertyFormGUI
     */
    private $notificationSettingsForm;

    /**
     * ilForumSettingsGUI constructor.
     * @param $parent_obj
     */
    public function __construct(ilObjForumGUI $parent_obj)
    {
        global $DIC;

        $this->parent_obj = $parent_obj;
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();
        $this->settings = $DIC->settings();
        $this->tabs = $DIC->tabs();
        $this->access = $DIC->access();
        $this->tree = $DIC->repositoryTree();
        $this->obj_service = $DIC->object();
    }

    private function initForcedForumNotification()
    {
        $this->forumNotificationObj = new ilForumNotification($this->parent_obj->object->getRefId());
        $this->forumNotificationObj->readAllForcedEvents();
    }
    
    /**
     *
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        switch (true) {
            case method_exists($this, $cmd):
                $this->settingsTabs();
                $this->{$cmd}();
                break;

            default:
                $this->ctrl->redirect($this->parent_obj);
                break;
        }
    }

    /**
     * @param ilPropertyFormGUI $a_form
     */
    public function getCustomForm(&$a_form)
    {
        $this->settingsTabs();
        $this->tabs->activateSubTab("basic_settings");
        $a_form->setTitle($this->lng->txt('frm_settings_form_header'));

        $presentationHeader = new ilFormSectionHeaderGUI();
        $presentationHeader->setTitle($this->lng->txt('frm_settings_presentation_header'));
        $a_form->addItem($presentationHeader);

        $this->obj_service->commonSettings()->legacyForm($a_form, $this->parent_obj->object)->addTileImage();

        $rg_pro = new ilRadioGroupInputGUI($this->lng->txt('frm_default_view'), 'default_view');
        $rg_pro->addOption(new ilRadioOption($this->lng->txt('sort_by_posts'), ilForumProperties::VIEW_TREE));
        $view_desc = new ilRadioOption(
            $this->lng->txt('sort_by_date') . ' (' . $this->lng->txt('descending_order') . ')',
            ilForumProperties::VIEW_DATE_DESC
        );
        $view_asc = new ilRadioOption(
            $this->lng->txt('sort_by_date') . ' (' . $this->lng->txt('ascending_order') . ')',
            ilForumProperties::VIEW_DATE_ASC
        );
        $rg_pro->addOption($view_desc);
        $rg_pro->addOption($view_asc);
        $a_form->addItem($rg_pro);

        $userFunctionsHeader = new \ilFormSectionHeaderGUI();
        $userFunctionsHeader->setTitle($this->lng->txt('frm_settings_user_functions_header'));
        $a_form->addItem($userFunctionsHeader);

        $frm_subject = new ilRadioGroupInputGUI($this->lng->txt('frm_subject_setting'), 'subject_setting');
        $frm_subject->addOption(new ilRadioOption($this->lng->txt('preset_subject'), 'preset_subject'));
        $frm_subject->addOption(new ilRadioOption($this->lng->txt('add_re_to_subject'), 'add_re_to_subject'));
        $frm_subject->addOption(new ilRadioOption($this->lng->txt('empty_subject'), 'empty_subject'));
        $a_form->addItem($frm_subject);

        $cb_prop = new ilCheckboxInputGUI($this->lng->txt('enable_thread_ratings'), 'thread_rating');
        $cb_prop->setValue(1);
        $cb_prop->setInfo($this->lng->txt('enable_thread_ratings_info'));
        $a_form->addItem($cb_prop);

        if (!ilForumProperties::isFileUploadGloballyAllowed()) {
            $frm_upload = new ilCheckboxInputGUI($this->lng->txt('file_upload_allowed'), 'file_upload_allowed');
            $frm_upload->setValue(1);
            $frm_upload->setInfo($this->lng->txt('allow_file_upload_desc'));
            $a_form->addItem($frm_upload);
        }

        $moderatorFunctionsHeader = new \ilFormSectionHeaderGUI();
        $moderatorFunctionsHeader->setTitle($this->lng->txt('frm_settings_mod_functions_header'));
        $a_form->addItem($moderatorFunctionsHeader);

        $cb_prop = new ilCheckboxInputGUI($this->lng->txt('activate_new_posts'), 'post_activation');
        $cb_prop->setValue(1);
        $cb_prop->setInfo($this->lng->txt('post_activation_desc'));
        $a_form->addItem($cb_prop);

        $cb_prop = new ilCheckboxInputGUI($this->lng->txt('mark_moderator_posts'), 'mark_mod_posts');
        $cb_prop->setValue(1);
        $cb_prop->setInfo($this->lng->txt('mark_moderator_posts_desc'));
        $a_form->addItem($cb_prop);


        $stickyThreadSorting = new ilRadioGroupInputGUI($this->lng->txt('sorting_manual_sticky'), 'thread_sorting');
        $latestAtTop = new ilRadioOption($this->lng->txt('frm_sticky_threads_latest_at_top'), 0);
        $latestAtTop->setInfo($this->lng->txt('frm_sticky_threads_latest_at_top_info'));
        $latestAtTop->setValue(0);
        $stickyThreadSorting->addOption($latestAtTop);
        $manualSorting = new ilRadioOption($this->lng->txt('frm_sticky_threads_manual_sorting'), 1);
        $manualSorting->setInfo($this->lng->txt('frm_sticky_threads_manual_sorting_info'));
        $stickyThreadSorting->addOption($manualSorting);
        $a_form->addItem($stickyThreadSorting);

        if ($this->settings->get('enable_anonymous_fora') || $this->settings->get('enable_fora_statistics', false)) {
            $privacyHeader = new \ilFormSectionHeaderGUI();
            $privacyHeader->setTitle($this->lng->txt('frm_settings_privacy_header'));
            $a_form->addItem($privacyHeader);
        }

        if ($this->settings->get('enable_fora_statistics', false)) {
            $cb_prop = new ilCheckboxInputGUI($this->lng->txt('frm_statistics_enabled'), 'statistics_enabled');
            $cb_prop->setValue(1);
            $cb_prop->setInfo($this->lng->txt('frm_statistics_enabled_desc'));
            $a_form->addItem($cb_prop);
        }

        if ($this->settings->get('enable_anonymous_fora') || $this->parent_obj->objProperties->isAnonymized()) {
            $cb_prop = new ilCheckboxInputGUI($this->lng->txt('frm_anonymous_posting'), 'anonymized');
            $cb_prop->setValue(1);
            $cb_prop->setInfo($this->lng->txt('frm_anonymous_posting_desc'));
            $a_form->addItem($cb_prop);
        }
    }

    /**
     * @return bool
     */
    public function settingsTabs()
    {
        $this->tabs->activateTab('settings');
        $this->tabs->addSubTabTarget(
            'basic_settings',
            $this->ctrl->getLinkTarget($this->parent_obj, 'edit'),
            '',
            ['ilobjforumgui', 'ilObjForumGUI']
        );

        if ($this->settings->get('forum_notification') > 0) {
            // check if there a parent-node is a grp or crs
            $grp_ref_id = $this->tree->checkForParentType($this->parent_obj->ref_id, 'grp');
            $crs_ref_id = $this->tree->checkForParentType($this->parent_obj->ref_id, 'crs');

            if ((int) $grp_ref_id > 0 || (int) $crs_ref_id > 0) {
                #show member-tab for notification if forum-notification is enabled in administration
                if ($this->access->checkAccess('write', '', $this->parent_obj->ref_id)) {
                    $mem_active = array('showMembers', 'forums_notification_settings');
                    (in_array($_GET['cmd'], $mem_active)) ? $force_mem_active = true : $force_mem_active = false;

                    $this->tabs->addSubTabTarget(
                        'notifications',
                        $this->ctrl->getLinkTarget($this, 'showMembers'),
                        '',
                        '',
                        '',
                        $force_mem_active
                    );
                }
            }
        }

        $this->lng->loadLanguageModule('cont');
        $this->tabs->addSubTabTarget(
            'cont_news_settings',
            $this->ctrl->getLinkTargetByClass('ilcontainernewssettingsgui'),
            '',
            'ilcontainernewssettingsgui'
        );

        return true;
    }

    /**
     * @param array $a_values
     */
    public function getCustomValues(array &$a_values)
    {
        $a_values['anonymized'] = $this->parent_obj->objProperties->isAnonymized();
        $a_values['statistics_enabled'] = $this->parent_obj->objProperties->isStatisticEnabled();
        $a_values['post_activation'] = $this->parent_obj->objProperties->isPostActivationEnabled();
        $a_values['subject_setting'] = $this->parent_obj->objProperties->getSubjectSetting();
        $a_values['mark_mod_posts'] = $this->parent_obj->objProperties->getMarkModeratorPosts();
        $a_values['thread_sorting'] = $this->parent_obj->objProperties->getThreadSorting();
        $a_values['thread_rating'] = $this->parent_obj->objProperties->isIsThreadRatingEnabled();

        if (in_array((int)  $this->parent_obj->objProperties->getDefaultView(), array(
            ilForumProperties::VIEW_TREE,
            ilForumProperties::VIEW_DATE_ASC,
            ilForumProperties::VIEW_DATE_DESC
        ))) {
            $default_view = (int)  $this->parent_obj->objProperties->getDefaultView();
        } else {
            $default_view = ilForumProperties::VIEW_TREE;
        }
        
        $a_values['default_view'] = $default_view;
        $a_values['file_upload_allowed'] = (bool) $this->parent_obj->objProperties->getFileUploadAllowed();
    }

    /**
     * @param ilPropertyFormGUI $a_form
     */
    public function updateCustomValues(ilPropertyFormGUI $a_form)
    {
        if (in_array((int) $a_form->getInput('default_view'), array(
            ilForumProperties::VIEW_TREE,
            ilForumProperties::VIEW_DATE_ASC,
            ilForumProperties::VIEW_DATE_DESC
        ))) {
            $default_view = (int) $a_form->getInput('default_view');
        } else {
            $default_view = ilForumProperties::VIEW_TREE;
        }
        $this->parent_obj->objProperties->setDefaultView($default_view);

        // BUGFIX FOR 11271
        if (isset($_SESSION['viewmode'])) {
            $_SESSION['viewmode'] = $default_view;
        }

        if ($this->settings->get('enable_anonymous_fora') || $this->parent_obj->objProperties->isAnonymized()) {
            $this->parent_obj->objProperties->setAnonymisation((int) $a_form->getInput('anonymized'));
        }
        if ($this->settings->get('enable_fora_statistics', false)) {
            $this->parent_obj->objProperties->setStatisticsStatus((int) $a_form->getInput('statistics_enabled'));
        }
        $this->parent_obj->objProperties->setPostActivation((int) $a_form->getInput('post_activation'));
        $this->parent_obj->objProperties->setSubjectSetting($a_form->getInput('subject_setting'));
        $this->parent_obj->objProperties->setMarkModeratorPosts((int) $a_form->getInput('mark_mod_posts'));
        $this->parent_obj->objProperties->setThreadSorting((int) $a_form->getInput('thread_sorting'));
        $this->parent_obj->objProperties->setIsThreadRatingEnabled((bool) $a_form->getInput('thread_rating'));
        if (!ilForumProperties::isFileUploadGloballyAllowed()) {
            $this->parent_obj->objProperties->setFileUploadAllowed((bool) $a_form->getInput('file_upload_allowed'));
        }
        $this->parent_obj->objProperties->update();
        $this->obj_service->commonSettings()->legacyForm($a_form, $this->parent_obj->object)->saveTileImage();
    }

    public function showMembers()
    {
        if (!$this->access->checkAccess('write', '', $this->parent_obj->ref_id)) {
            $this->parent_obj->error->raiseError(
                $this->lng->txt('msg_no_perm_read'),
                $this->parent_obj->error->MESSAGE
            );
        }

        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.forums_members_list.html', 'Modules/Forum');

        // instantiate the property form
        if (!$this->initNotificationSettingsForm()) {
            // if the form was just created set the values fetched from database
            $interested_events = $this->parent_obj->objProperties->getInterestedEvents();
            
            $this->notificationSettingsForm->setValuesByArray(array(
                'notification_type' => $this->parent_obj->objProperties->getNotificationType(),
                'adm_force' => (bool) $this->parent_obj->objProperties->isAdminForceNoti(),
                'usr_toggle' => (bool) $this->parent_obj->objProperties->isUserToggleNoti(),
                'notify_modified' => $interested_events & \ilForumNotificationEvents::UPDATED,
                'notify_censored' => $interested_events & \ilForumNotificationEvents::CENSORED,
                'notify_uncensored' => $interested_events & \ilForumNotificationEvents::UNCENSORED,
                'notify_post_deleted' => $interested_events & \ilForumNotificationEvents::POST_DELETED,
                'notify_thread_deleted' => $interested_events & \ilForumNotificationEvents::THREAD_DELETED
            ));
        }

        // set form html into template
        $this->tpl->setVariable('NOTIFICATIONS_SETTINGS_FORM', $this->notificationSettingsForm->getHTML());

        $frm_noti = new ilForumNotification($this->parent_obj->object->getRefId());
        $oParticipants = $this->getParticipants();

        $moderator_ids = ilForum::_getModerators($this->parent_obj->object->getRefId());

        $admin_ids = $oParticipants->getAdmins();
        $member_ids = $oParticipants->getMembers();
        $tutor_ids = $oParticipants->getTutors();

        if ($this->parent_obj->objProperties->getNotificationType() == 'default') {
            // update forum_notification table
            $forum_noti = new ilForumNotification($this->parent_obj->object->getRefId());
            $forum_noti->setAdminForce($this->parent_obj->objProperties->isAdminForceNoti());
            $forum_noti->setUserToggle($this->parent_obj->objProperties->isUserToggleNoti());
            $forum_noti->setForumId($this->parent_obj->objProperties->getObjId());
            $forum_noti->setInterestedEvents($this->parent_obj->objProperties->getInterestedEvents());
            $forum_noti->update();
        } else {
            if ($this->parent_obj->objProperties->getNotificationType() == 'per_user') {
                $this->initForcedForumNotification();
                
                $moderators = $this->getUserNotificationTableData($moderator_ids);
                $admins = $this->getUserNotificationTableData($admin_ids);
                $members = $this->getUserNotificationTableData($member_ids);
                $tutors = $this->getUserNotificationTableData($tutor_ids);

                $this->__showMembersTable($moderators, $admins, $members, $tutors);
            }
        }
    }
    
    /**
     * @param $user_ids
     * @return array
     */
    private function getUserNotificationTableData($user_ids)
    {
        $counter = 0;
        $users = array();
        foreach ($user_ids as $user_id) {
            $forced_events = $this->forumNotificationObj->getForcedEventsObjectByUserId($user_id);

            $users[$counter]['user_id'] = ilUtil::formCheckbox(0, 'user_id[]', $user_id);
            $users[$counter]['login'] = ilObjUser::_lookupLogin($user_id);
            $name = ilObjUser::_lookupName($user_id);
            $users[$counter]['firstname'] = $name['firstname'];
            $users[$counter]['lastname'] = $name['lastname'];
            $users[$counter]['user_toggle_noti'] = $forced_events->getUserToggle();
            $users[$counter]['notification_id'] = $forced_events->getNotificationId();
            $users[$counter]['interested_events'] = $forced_events->getInterestedEvents();
            $users[$counter]['usr_id_events'] = $user_id;
            $users[$counter]['forum_id'] = $forced_events->getForumId();
            
            $counter++;
        }
        return $users;
    }

    private function __showMembersTable(array $moderators, array $admins, array $members, array $tutors)
    {
        foreach (array_filter([
            'moderators' => $moderators,
            'administrator' => $admins,
            'tutors' => $tutors,
            'members' => $members
        ]) as $type => $data) {
             $tbl = new ilForumNotificationTableGUI($this->parent_obj, 'showMembers');
             $tbl->setTitle($this->lng->txt(strtolower($type)));
             $tbl->setId('tbl_id_mod');
             $tbl->setData($data);
     
            $this->tpl->setCurrentBlock(strtolower($type) . '_table');
            $this->tpl->setVariable(strtoupper($type), $tbl->getHTML());
        }
    }

    public function saveEventsForUser()
    {
        $hidden_value = json_decode($_POST['hidden_value']);
        $interested_events = 0;
    
        $interested_events += (int)$_POST['notify_modified'];
        $interested_events += (int)$_POST['notify_censored'];
        $interested_events += (int)$_POST['notify_uncensored'];
        $interested_events += (int)$_POST['notify_post_deleted'];
        $interested_events += (int)$_POST['notify_thread_deleted'];
    
        $frm_noti = new ilForumNotification($hidden_value->ref_id);
        $frm_noti->setUserId($hidden_value->usr_id_events);
        $frm_noti->setForumId($hidden_value->forum_id);
//        $frm_noti->setNotificationId($hidden_value->notification_id);
        $frm_noti->setInterestedEvents($interested_events);
        $frm_noti->updateInterestedEvents();
        
        $this->showMembers();
    }
    
    public function enableAdminForceNoti()
    {
        if (!$this->access->checkAccess('write', '', $this->parent_obj->ref_id)) {
            $this->parent_obj->error->raiseError(
                $this->lng->txt('msg_no_perm_read'),
                $this->parent_obj->error->MESSAGE
            );
        }

        if (!isset($_POST['user_id']) || !is_array($_POST['user_id'])) {
            ilUtil::sendInfo($this->lng->txt('time_limit_no_users_selected'), true);
        } else {
            $frm_noti = new ilForumNotification($this->parent_obj->object->getRefId());

            foreach ($_POST['user_id'] as $user_id) {
                $frm_noti->setUserId((int) $user_id);
                $is_enabled = $frm_noti->isAdminForceNotification();

                $frm_noti->setUserToggle(0);
                if (!$is_enabled) {
                    $frm_noti->setAdminForce(1);
                    $frm_noti->insertAdminForce();
                }
            }

            ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
        }

        $this->showMembers();
    }

    public function disableAdminForceNoti()
    {
        if (!$this->access->checkAccess('write', '', $this->parent_obj->ref_id)) {
            $this->parent_obj->error->raiseError(
                $this->lng->txt('msg_no_perm_read'),
                $this->parent_obj->error->MESSAGE
            );
        }

        if (!isset($_POST['user_id']) || !is_array($_POST['user_id'])) {
            ilUtil::sendInfo($this->lng->txt('time_limit_no_users_selected'));
        } else {
            $frm_noti = new ilForumNotification($this->parent_obj->object->getRefId());

            foreach ($_POST['user_id'] as $user_id) {
                $frm_noti->setUserId((int) $user_id);
                $is_enabled = $frm_noti->isAdminForceNotification();

                if ($is_enabled) {
                    $frm_noti->deleteAdminForce();
                }
            }

            ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
        }

        $this->showMembers();
    }

    public function enableHideUserToggleNoti()
    {
        if (!$this->access->checkAccess('write', '', $this->parent_obj->ref_id)) {
            $this->parent_obj->error->raiseError(
                $this->lng->txt('msg_no_perm_read'),
                $this->parent_obj->error->MESSAGE
            );
        }
        if (!isset($_POST['user_id']) || !is_array($_POST['user_id'])) {
            ilUtil::sendInfo($this->lng->txt('time_limit_no_users_selected'));
        } else {
            $frm_noti = new ilForumNotification($this->parent_obj->object->getRefId());

            foreach ($_POST['user_id'] as $user_id) {
                $frm_noti->setUserId((int) $user_id);
                $is_enabled = $frm_noti->isAdminForceNotification();
                $frm_noti->setUserToggle(1);

                if (!$is_enabled) {
                    $frm_noti->setAdminForce(1);
                    $frm_noti->insertAdminForce();
                } else {
                    $frm_noti->updateUserToggle();
                }
            }

            ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
        }

        $this->showMembers();
    }

    public function disableHideUserToggleNoti()
    {
        if (!$this->access->checkAccess('write', '', $this->parent_obj->ref_id)) {
            $this->parent_obj->error->raiseError(
                $this->lng->txt('msg_no_perm_read'),
                $this->parent_obj->error->MESSAGE
            );
        }

        if (!isset($_POST['user_id']) || !is_array($_POST['user_id'])) {
            ilUtil::sendInfo($this->lng->txt('time_limit_no_users_selected'));
        } else {
            $frm_noti = new ilForumNotification($this->parent_obj->object->getRefId());

            foreach ($_POST['user_id'] as $user_id) {
                $frm_noti->setUserId((int) $user_id);
                $is_enabled = $frm_noti->isAdminForceNotification();
                $frm_noti->setUserToggle(0);
                if ($is_enabled) {
                    $frm_noti->updateUserToggle();
                } else {
                    $frm_noti->setAdminForce(1);
                    $frm_noti->insertAdminForce();
                }
            }

            ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
        }

        $this->showMembers();
    }


    /**
     * @return ilParticipants for course or group
     */
    public function getParticipants()
    {
        if ($this->parent_obj->isParentObjectCrsOrGrp() == false) {
            $this->parent_obj->error->raiseError(
                $this->lng->txt('msg_no_perm_read'),
                $this->parent_obj->error->MESSAGE
            );
        }

        /**
         * @var $oParticipants ilParticipants
         */
        $oParticipants = null;

        $grp_ref_id = $this->tree->checkForParentType($this->parent_obj->object->getRefId(), 'grp');
        $crs_ref_id = $this->tree->checkForParentType($this->parent_obj->object->getRefId(), 'crs');
        if ($grp_ref_id > 0) {
            $parent_obj = ilObjectFactory::getInstanceByRefId($grp_ref_id);
            $oParticipants = ilGroupParticipants::_getInstanceByObjId($parent_obj->getId());
            return $oParticipants;
        } else {
            if ($crs_ref_id > 0) {
                $parent_obj = ilObjectFactory::getInstanceByRefId($crs_ref_id);

                $oParticipants = ilCourseParticipants::_getInstanceByObjId($parent_obj->getId());
                return $oParticipants;
            }
        }

        return $oParticipants;
    }


    private function updateUserNotifications($update_all_users = false)
    {
        $oParticipants = $this->getParticipants();

        $frm_noti = new ilForumNotification($this->parent_obj->object->getRefId());
        $moderator_ids = ilForum::_getModerators($this->parent_obj->object->getRefId());

        $admin_ids = $oParticipants->getAdmins();
        $member_ids = $oParticipants->getMembers();
        $tutor_ids = $oParticipants->getTutors();

        $all_forum_users = array_merge($moderator_ids, $admin_ids, $member_ids, $tutor_ids);
        $all_forum_users = array_unique($all_forum_users);

        $all_notis = $frm_noti->read();

        foreach ($all_forum_users as $user_id) {
            $frm_noti->setUserId($user_id);

            $frm_noti->setAdminForce(1);
            $frm_noti->setUserToggle($this->parent_obj->objProperties->isUserToggleNoti());
            $frm_noti->setInterestedEvents($this->parent_obj->objProperties->getInterestedEvents());

            if (array_key_exists($user_id, $all_notis) && $update_all_users) {
                $frm_noti->update();
            } else {
                if ($frm_noti->existsNotification() == false) {
                    $frm_noti->insertAdminForce();
                }
            }
        }
    }

    private function initNotificationSettingsForm()
    {
        if (null === $this->notificationSettingsForm) {
            $form = new ilPropertyFormGUI();
            $form->setFormAction($this->ctrl->getFormAction($this, 'updateNotificationSettings'));
            $form->setTitle($this->lng->txt('forums_notification_settings'));

            $radio_grp = new ilRadioGroupInputGUI('', 'notification_type');
            $radio_grp->setValue('default');

            $opt_default = new ilRadioOption($this->lng->txt("user_decides_notification"), 'default');
            $opt_0 = new ilRadioOption($this->lng->txt("settings_for_all_members"), 'all_users');
            $opt_1 = new ilRadioOption($this->lng->txt("settings_per_users"), 'per_user');

            $radio_grp->addOption($opt_default);
            $radio_grp->addOption($opt_0);
            $radio_grp->addOption($opt_1);

            $chb_2 = new ilCheckboxInputGUI($this->lng->txt('user_toggle_noti'), 'usr_toggle');
            $chb_2->setValue(1);

            $opt_0->addSubItem($chb_2);
            
            $cb_grp = new ilCheckboxGroupInputGUI($this->lng->txt('notification_events'), 'notification_events');
            
            $notify_modified = new ilCheckboxInputGUI($this->lng->txt('notify_modified'), 'notify_modified');
            $notify_modified->setValue(\ilForumNotificationEvents::UPDATED);
            $cb_grp->addOption($notify_modified);
    
            $notify_censored = new ilCheckboxInputGUI($this->lng->txt('notify_censored'), 'notify_censored');
            $notify_censored->setValue(\ilForumNotificationEvents::CENSORED);
            $cb_grp->addOption($notify_censored);
    
            $notify_uncensored = new ilCheckboxInputGUI($this->lng->txt('notify_uncensored'), 'notify_uncensored');
            $notify_uncensored->setValue(\ilForumNotificationEvents::UNCENSORED);
            $cb_grp->addOption($notify_uncensored);
    
            $notify_post_deleted = new ilCheckboxInputGUI($this->lng->txt('notify_post_deleted'), 'notify_post_deleted');
            $notify_post_deleted->setValue(\ilForumNotificationEvents::POST_DELETED);
            $cb_grp->addOption($notify_post_deleted);
    
            $notify_thread_deleted = new ilCheckboxInputGUI($this->lng->txt('notify_thread_deleted'), 'notify_thread_deleted');
            $notify_thread_deleted->setValue(\ilForumNotificationEvents::THREAD_DELETED);
            $cb_grp->addOption($notify_thread_deleted);
            $opt_0->addSubItem($cb_grp);
            
            $form->addItem($radio_grp);

            $form->addCommandButton('updateNotificationSettings', $this->lng->txt('save'));

            $this->notificationSettingsForm = $form;

            return false;
        }

        return true;
    }

    public function updateNotificationSettings()
    {
        if (!$this->access->checkAccess('write', '', $this->parent_obj->ref_id)) {
            $this->parent_obj->error->raiseError(
                $this->lng->txt('msg_no_perm_read'),
                $this->parent_obj->error->MESSAGE
            );
        }

        // instantiate the property form
        $this->initNotificationSettingsForm();

        // check input
        if ($this->notificationSettingsForm->checkInput()) {
            if (isset($_POST['notification_type']) && $_POST['notification_type'] == 'all_users') {
                // set values and call update
                $interested_events = 0;
    
                $interested_events += (int)$this->notificationSettingsForm->getInput('notify_modified');
                $interested_events += (int)$this->notificationSettingsForm->getInput('notify_censored');
                $interested_events += (int)$this->notificationSettingsForm->getInput('notify_uncensored');
                $interested_events += (int)$this->notificationSettingsForm->getInput('notify_post_deleted');
                $interested_events += (int)$this->notificationSettingsForm->getInput('notify_thread_deleted');
    
                $this->parent_obj->objProperties->setAdminForceNoti(1);
                $this->parent_obj->objProperties->setUserToggleNoti((int) $this->notificationSettingsForm->getInput('usr_toggle'));
                $this->parent_obj->objProperties->setNotificationType('all_users');
                $this->parent_obj->objProperties->setInterestedEvents($interested_events);
                $this->updateUserNotifications(true);
            } else {
                if ($_POST['notification_type'] == 'per_user') {
                    $this->parent_obj->objProperties->setNotificationType('per_user');
                    $this->parent_obj->objProperties->setAdminForceNoti(1);
                    $this->parent_obj->objProperties->setUserToggleNoti(0);
                    $this->updateUserNotifications();
                } else { //  if($_POST['notification_type'] == 'default')
                    $this->parent_obj->objProperties->setNotificationType('default');
                    $this->parent_obj->objProperties->setAdminForceNoti(0);
                    $this->parent_obj->objProperties->setUserToggleNoti(0);
                    $frm_noti = new ilForumNotification($this->parent_obj->object->getRefId());
                    $frm_noti->deleteNotificationAllUsers();
                }
            }

            $this->parent_obj->objProperties->update();

            // print success message
            ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
        }
        $this->notificationSettingsForm->setValuesByPost();

        $this->showMembers();
        return;
    }
}
