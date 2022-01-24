<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilForumSettingsGUI
 * @author  Nadia Matuschek <nmatuschek@databay.de>
 * @ilCtrl_Calls ilForumSettingsGUI: ilObjStyleSheetGUI
 */
class ilForumSettingsGUI implements ilForumObjectConstants
{
    private ilCtrl $ctrl;
    private ilGlobalTemplateInterface $tpl;
    private ilLanguage $lng;
    private ilSetting $settings;
    private ilTabsGUI $tabs;
    private ilAccessHandler $access;
    private ilTree $tree;
    private ilObjForumGUI $parent_obj;
    private \ILIAS\HTTP\Wrapper\WrapperFactory $http_wrapper;
    private \ILIAS\Refinery\Factory $refinery;
    private ilForumNotification $forumNotificationObj;
    private ilPropertyFormGUI $form;
    private ilPropertyFormGUI $notificationSettingsForm;
    private int $ref_id;
    private ilObjectService $obj_service;
    private \ILIAS\DI\Container $dic;
    private ilForumProperties $properties;

    public function __construct(ilObjForumGUI $parent_obj, ilForumProperties $properties)
    {
        global $DIC;

        $this->dic = $DIC;
        $this->parent_obj = $parent_obj;
        $this->properties = $properties;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();
        $this->settings = $DIC->settings();
        $this->tabs = $DIC->tabs();
        $this->access = $DIC->access();
        $this->tree = $DIC->repositoryTree();
        $this->obj_service = $this->dic->object();
        $this->ref_id = $this->parent_obj->object->getRefId();
        $this->http_wrapper = $DIC->http()->wrapper();
        $this->refinery = $DIC->refinery();

        $this->lng->loadLanguageModule('style');
        $this->lng->loadLanguageModule('cont');
    }

    private function initForcedForumNotification() : void
    {
        $this->forumNotificationObj = new ilForumNotification($this->parent_obj->object->getRefId());
        $this->forumNotificationObj->readAllForcedEvents();
    }

    public function executeCommand() : void
    {
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass();
        
        switch (strtolower($next_class)) {
            case strtolower(ilObjStyleSheetGUI::class):
                $this->tabs->clearTargets();
                
                $this->ctrl->setReturn($this, 'editStyleProperties');
                $style_gui = new ilObjStyleSheetGUI(
                    '',
                    $this->properties->getStyleSheetId(),
                    false,
                    false
                );

                $new_type = (string) ($this->dic->http()->request()->getQueryParams()['new_type'] ?? '');
                if ($cmd === 'create' || $new_type === 'sty') {
                    $style_gui->setCreationMode();
                }

                if ($cmd === 'confirmedDelete') {
                    $this->properties->setStyleSheetId(0);
                    $this->properties->update();
                }

                $ret = $this->ctrl->forwardCommand($style_gui);

                if ($cmd === 'save' || $cmd === 'copyStyle' || $cmd === 'importStyle') {
                    $styleId = $ret;
                    $this->properties->setStyleSheetId((int) $styleId);
                    $this->properties->update();
                    $this->ctrl->redirectByClass(ilObjStyleSheetGUI::class, 'edit');
                }
                break;

            default:
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
    }

    private function addAvailabilitySection(ilPropertyFormGUI $form) : void
    {
        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('rep_activation_availability'));
        $form->addItem($section);

        $online = new ilCheckboxInputGUI($this->lng->txt('rep_activation_online'), 'activation_online');
        $online->setInfo($this->lng->txt('frm_activation_online_info'));
        $form->addItem($online);
    }

    public function getCustomForm(ilPropertyFormGUI $a_form) : void
    {
        $this->settingsTabs();
        $this->tabs->activateSubTab(self::UI_SUB_TAB_ID_BASIC_SETTINGS);
        $a_form->setTitle($this->lng->txt('frm_settings_form_header'));

        $this->addAvailabilitySection($a_form);

        $presentationHeader = new ilFormSectionHeaderGUI();
        $presentationHeader->setTitle($this->lng->txt('frm_settings_presentation_header'));
        $a_form->addItem($presentationHeader);

        $this->obj_service->commonSettings()->legacyForm($a_form, $this->parent_obj->object)->addTileImage();

        $rg_pro = new ilRadioGroupInputGUI($this->lng->txt('frm_default_view'), 'default_view');
        $rg_pro->addOption(new ilRadioOption($this->lng->txt('sort_by_posts'), (string) ilForumProperties::VIEW_TREE));
        $view_desc = new ilRadioOption(
            $this->lng->txt('sort_by_date') . ' (' . $this->lng->txt('descending_order') . ')',
            (string) ilForumProperties::VIEW_DATE_DESC
        );
        $view_asc = new ilRadioOption(
            $this->lng->txt('sort_by_date') . ' (' . $this->lng->txt('ascending_order') . ')',
            (string) ilForumProperties::VIEW_DATE_ASC
        );
        $rg_pro->addOption($view_desc);
        $rg_pro->addOption($view_asc);
        $a_form->addItem($rg_pro);

        $userFunctionsHeader = new ilFormSectionHeaderGUI();
        $userFunctionsHeader->setTitle($this->lng->txt('frm_settings_user_functions_header'));
        $a_form->addItem($userFunctionsHeader);

        $frm_subject = new ilRadioGroupInputGUI($this->lng->txt('frm_subject_setting'), 'subject_setting');
        $frm_subject->addOption(new ilRadioOption($this->lng->txt('preset_subject'), 'preset_subject'));
        $frm_subject->addOption(new ilRadioOption($this->lng->txt('add_re_to_subject'), 'add_re_to_subject'));
        $frm_subject->addOption(new ilRadioOption($this->lng->txt('empty_subject'), 'empty_subject'));
        $a_form->addItem($frm_subject);

        $cb_prop = new ilCheckboxInputGUI($this->lng->txt('enable_thread_ratings'), 'thread_rating');
        $cb_prop->setValue('1');
        $cb_prop->setInfo($this->lng->txt('enable_thread_ratings_info'));
        $a_form->addItem($cb_prop);

        if (!ilForumProperties::isFileUploadGloballyAllowed()) {
            $frm_upload = new ilCheckboxInputGUI($this->lng->txt('file_upload_allowed'), 'file_upload_allowed');
            $frm_upload->setValue('1');
            $frm_upload->setInfo($this->lng->txt('allow_file_upload_desc'));
            $a_form->addItem($frm_upload);
        }

        $moderatorFunctionsHeader = new ilFormSectionHeaderGUI();
        $moderatorFunctionsHeader->setTitle($this->lng->txt('frm_settings_mod_functions_header'));
        $a_form->addItem($moderatorFunctionsHeader);

        $cb_prop = new ilCheckboxInputGUI($this->lng->txt('activate_new_posts'), 'post_activation');
        $cb_prop->setValue('1');
        $cb_prop->setInfo($this->lng->txt('post_activation_desc'));
        $a_form->addItem($cb_prop);

        $cb_prop = new ilCheckboxInputGUI($this->lng->txt('mark_moderator_posts'), 'mark_mod_posts');
        $cb_prop->setValue('1');
        $cb_prop->setInfo($this->lng->txt('mark_moderator_posts_desc'));
        $a_form->addItem($cb_prop);

        $stickyThreadSorting = new ilRadioGroupInputGUI($this->lng->txt('sorting_manual_sticky'), 'thread_sorting');
        $latestAtTop = new ilRadioOption($this->lng->txt('frm_sticky_threads_latest_at_top'), '0');
        $latestAtTop->setInfo($this->lng->txt('frm_sticky_threads_latest_at_top_info'));
        $latestAtTop->setValue('1');
        $stickyThreadSorting->addOption($latestAtTop);
        $manualSorting = new ilRadioOption($this->lng->txt('frm_sticky_threads_manual_sorting'), '1');
        $manualSorting->setInfo($this->lng->txt('frm_sticky_threads_manual_sorting_info'));
        $stickyThreadSorting->addOption($manualSorting);
        $a_form->addItem($stickyThreadSorting);

        if ($this->settings->get('enable_anonymous_fora') || $this->settings->get('enable_fora_statistics')) {
            $privacyHeader = new ilFormSectionHeaderGUI();
            $privacyHeader->setTitle($this->lng->txt('frm_settings_privacy_header'));
            $a_form->addItem($privacyHeader);
        }

        if ($this->settings->get('enable_fora_statistics')) {
            $cb_prop = new ilCheckboxInputGUI($this->lng->txt('frm_statistics_enabled'), 'statistics_enabled');
            $cb_prop->setValue('1');
            $cb_prop->setInfo($this->lng->txt('frm_statistics_enabled_desc'));
            $a_form->addItem($cb_prop);
        }

        if ($this->settings->get('enable_anonymous_fora') || $this->parent_obj->objProperties->isAnonymized()) {
            $cb_prop = new ilCheckboxInputGUI($this->lng->txt('frm_anonymous_posting'), 'anonymized');
            $cb_prop->setValue('1');
            $cb_prop->setInfo($this->lng->txt('frm_anonymous_posting_desc'));
            $a_form->addItem($cb_prop);
        }
    }

    public function settingsTabs() : bool
    {
        $this->tabs->addSubTabTarget(
            self::UI_SUB_TAB_ID_BASIC_SETTINGS,
            $this->ctrl->getLinkTarget($this->parent_obj, 'edit'),
            '',
            [strtolower(ilObjForumGUI::class)]
        );

        if ($this->settings->get('forum_notification') > 0) {
            // check if there a parent-node is a grp or crs
            $grp_ref_id = $this->tree->checkForParentType($this->parent_obj->ref_id, 'grp');
            $crs_ref_id = $this->tree->checkForParentType($this->parent_obj->ref_id, 'crs');

            if ((int) $grp_ref_id > 0 || (int) $crs_ref_id > 0) {
                #show member-tab for notification if forum-notification is enabled in administration
                if ($this->access->checkAccess('write', '', $this->parent_obj->ref_id)) {
                    $cmd = '';
                    if ($this->dic->http()->wrapper()->query()->has('cmd')) {
                        $cmd = $this->dic->http()->wrapper()->query()->retrieve(
                            'cmd',
                            $this->dic->refinery()->kindlyTo()->string()
                        );
                    }

                    $mem_active = ['showMembers', 'forums_notification_settings'];
                    $force_mem_active = false;
                    if (in_array($cmd, $mem_active, true)) {
                        $force_mem_active = true;
                    }

                    $this->tabs->addSubTabTarget(
                        self::UI_SUB_TAB_ID_NOTIFICATIONS,
                        $this->ctrl->getLinkTarget($this, 'showMembers'),
                        '',
                        [strtolower(self::class)],
                        '',
                        $force_mem_active
                    );
                }
            }
        }

        $this->tabs->addSubTabTarget(
            self::UI_SUB_TAB_ID_NEWS,
            $this->ctrl->getLinkTargetByClass(ilContainerNewsSettingsGUI::class),
            '',
            [strtolower(ilContainerNewsSettingsGUI::class)]
        );

        $this->tabs->addSubTabTarget(
            self::UI_SUB_TAB_ID_STYLE,
            $this->ctrl->getLinkTarget($this, 'editStyleProperties'),
            '',
            [strtolower(ilObjStyleSheetGUI::class)]
        );

        $this->tabs->activateTab(self::UI_TAB_ID_SETTINGS);

        return true;
    }

    public function getCustomValues(array &$a_values) : void
    {
        $a_values['anonymized'] = $this->parent_obj->objProperties->isAnonymized();
        $a_values['statistics_enabled'] = $this->parent_obj->objProperties->isStatisticEnabled();
        $a_values['post_activation'] = $this->parent_obj->objProperties->isPostActivationEnabled();
        $a_values['subject_setting'] = $this->parent_obj->objProperties->getSubjectSetting();
        $a_values['mark_mod_posts'] = $this->parent_obj->objProperties->getMarkModeratorPosts();
        $a_values['thread_sorting'] = $this->parent_obj->objProperties->getThreadSorting();
        $a_values['thread_rating'] = $this->parent_obj->objProperties->isIsThreadRatingEnabled();

        if (in_array($this->parent_obj->objProperties->getDefaultView(), [
            ilForumProperties::VIEW_TREE,
            ilForumProperties::VIEW_DATE_ASC,
            ilForumProperties::VIEW_DATE_DESC
        ], true)) {
            $default_view = $this->parent_obj->objProperties->getDefaultView();
        } else {
            $default_view = ilForumProperties::VIEW_TREE;
        }

        $a_values['default_view'] = $default_view;
        $a_values['file_upload_allowed'] = $this->parent_obj->objProperties->getFileUploadAllowed();

        $object = $this->parent_obj->object;
        $a_values['activation_online'] = !($object->getOfflineStatus() === null) && !$object->getOfflineStatus();
    }

    public function updateCustomValues(ilPropertyFormGUI $a_form) : void
    {
        if (in_array((int) $a_form->getInput('default_view'), [
            ilForumProperties::VIEW_TREE,
            ilForumProperties::VIEW_DATE_ASC,
            ilForumProperties::VIEW_DATE_DESC
        ], true)) {
            $default_view = (int) $a_form->getInput('default_view');
        } else {
            $default_view = ilForumProperties::VIEW_TREE;
        }
        $this->parent_obj->objProperties->setDefaultView($default_view);

        // BUGFIX FOR 11271

        if (ilSession::get('viewmode')) {
            ilSession::set('viewmode', $default_view);
        }

        if ($this->settings->get('enable_anonymous_fora') || $this->parent_obj->objProperties->isAnonymized()) {
            $this->parent_obj->objProperties->setAnonymisation((bool) $a_form->getInput('anonymized'));
        }
        if ($this->settings->get('enable_fora_statistics')) {
            $this->parent_obj->objProperties->setStatisticsStatus((bool) $a_form->getInput('statistics_enabled'));
        }
        $this->parent_obj->objProperties->setPostActivation((bool) $a_form->getInput('post_activation'));
        $this->parent_obj->objProperties->setSubjectSetting($a_form->getInput('subject_setting'));
        $this->parent_obj->objProperties->setMarkModeratorPosts((bool) $a_form->getInput('mark_mod_posts'));
        $this->parent_obj->objProperties->setThreadSorting((int) $a_form->getInput('thread_sorting'));
        $this->parent_obj->objProperties->setIsThreadRatingEnabled((bool) $a_form->getInput('thread_rating'));
        if (!ilForumProperties::isFileUploadGloballyAllowed()) {
            $this->parent_obj->objProperties->setFileUploadAllowed((bool) $a_form->getInput('file_upload_allowed'));
        }
        $this->parent_obj->objProperties->update();
        $this->obj_service->commonSettings()->legacyForm($a_form, $this->parent_obj->object)->saveTileImage();

        $object = $this->parent_obj->object;
        $object->setOfflineStatus(!(bool) $a_form->getInput('activation_online'));
        $object->update();
    }

    public function showMembers() : void
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

            $form_events = [];
            if ($interested_events & ilForumNotificationEvents::UPDATED) {
                $form_events[] = ilForumNotificationEvents::UPDATED;
            }

            if ($interested_events & ilForumNotificationEvents::CENSORED) {
                $form_events[] = ilForumNotificationEvents::CENSORED;
            }

            if ($interested_events & ilForumNotificationEvents::UNCENSORED) {
                $form_events[] = ilForumNotificationEvents::UNCENSORED;
            }

            if ($interested_events & ilForumNotificationEvents::POST_DELETED) {
                $form_events[] = ilForumNotificationEvents::POST_DELETED;
            }

            if ($interested_events & ilForumNotificationEvents::THREAD_DELETED) {
                $form_events[] = ilForumNotificationEvents::THREAD_DELETED;
            }

            $this->notificationSettingsForm->setValuesByArray([
                'notification_type' => $this->parent_obj->objProperties->getNotificationType(),
                'adm_force' => $this->parent_obj->objProperties->isAdminForceNoti(),
                'usr_toggle' => $this->parent_obj->objProperties->isUserToggleNoti(),
                'notification_events' => $form_events
            ]);
        }

        // set form html into template
        $this->tpl->setVariable('NOTIFICATIONS_SETTINGS_FORM', $this->notificationSettingsForm->getHTML());

        $frm_noti = new ilForumNotification($this->parent_obj->object->getRefId());
        $oParticipants = $this->getParticipants();

        $moderator_ids = ilForum::_getModerators($this->parent_obj->object->getRefId());

        $admin_ids = $oParticipants->getAdmins();
        $member_ids = $oParticipants->getMembers();
        $tutor_ids = $oParticipants->getTutors();

        if ($this->parent_obj->objProperties->getNotificationType() === 'default') {
            // update forum_notification table
            $forum_noti = new ilForumNotification($this->parent_obj->object->getRefId());
            $forum_noti->setAdminForce($this->parent_obj->objProperties->isAdminForceNoti());
            $forum_noti->setUserToggle($this->parent_obj->objProperties->isUserToggleNoti());
            $forum_noti->setForumId($this->parent_obj->objProperties->getObjId());
            $forum_noti->setInterestedEvents($this->parent_obj->objProperties->getInterestedEvents());
            $forum_noti->update();
        } elseif ($this->parent_obj->objProperties->getNotificationType() === 'per_user') {
            $this->initForcedForumNotification();

            $moderators = $this->getUserNotificationTableData($moderator_ids);
            $admins = $this->getUserNotificationTableData($admin_ids);
            $members = $this->getUserNotificationTableData($member_ids);
            $tutors = $this->getUserNotificationTableData($tutor_ids);

            $this->showMembersTable($moderators, $admins, $members, $tutors);
        }
    }

    private function getUserNotificationTableData($user_ids) : array
    {
        $counter = 0;
        $users = [];
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

    private function showMembersTable(array $moderators, array $admins, array $members, array $tutors) : void
    {
        foreach (array_filter([
            'moderators' => $moderators,
            'administrator' => $admins,
            'tutors' => $tutors,
            'members' => $members
        ]) as $type => $data) {
            $tbl = new ilForumNotificationTableGUI($this, 'showMembers', $type);
            $tbl->setData($data);

            $this->tpl->setCurrentBlock(strtolower($type) . '_table');
            $this->tpl->setVariable(strtoupper($type), $tbl->getHTML());
        }
    }

    public function saveEventsForUser() : void
    {
        $hidden_value = [];
        if ($this->http_wrapper->post()->has('hidden_value')) {
            $hidden_value = $this->http_wrapper->post()->retrieve(
                'hidden_value',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string())
            );
        }

        $notify_modified = 0;
        if ($this->http_wrapper->post()->has('notify_modified')) {
            $notify_modified = $this->http_wrapper->post()->retrieve(
                'notify_modified',
                $this->refinery->kindlyTo()->int()
            );
        }
        $notify_censored = 0;
        if ($this->http_wrapper->post()->has('notify_censored')) {
            $notify_censored = $this->http_wrapper->post()->retrieve(
                'notify_censored',
                $this->refinery->kindlyTo()->int()
            );
        }
        $notify_uncensored = 0;
        if ($this->http_wrapper->post()->has('notify_uncensored')) {
            $notify_uncensored = $this->http_wrapper->post()->retrieve(
                'notify_uncensored',
                $this->refinery->kindlyTo()->int()
            );
        }
        $notify_post_deleted = 0;
        if ($this->http_wrapper->post()->has('notify_post_deleted')) {
            $notify_post_deleted = $this->http_wrapper->post()->retrieve(
                'notify_post_deleted',
                $this->refinery->kindlyTo()->int()
            );
        }

        $notify_thread_deleted = 0;
        if ($this->http_wrapper->post()->has('notify_thread_deleted')) {
            $notify_thread_deleted = $this->http_wrapper->post()->retrieve(
                'notify_thread_deleted',
                $this->refinery->kindlyTo()->int()
            );
        }

        $hidden_value = json_decode($hidden_value, null, 512, JSON_THROW_ON_ERROR);
        $interested_events = 0;

        $interested_events += (int) $notify_modified;
        $interested_events += (int) $notify_censored;
        $interested_events += (int) $notify_uncensored;
        $interested_events += (int) $notify_post_deleted;
        $interested_events += (int) $notify_thread_deleted;

        $frm_noti = new ilForumNotification($hidden_value->ref_id);
        $frm_noti->setUserId($hidden_value->usr_id_events);
        $frm_noti->setForumId($hidden_value->forum_id);
        $frm_noti->setInterestedEvents($interested_events);
        $frm_noti->updateInterestedEvents();

        $this->showMembers();
    }

    public function enableAdminForceNoti() : void
    {
        if (!$this->access->checkAccess('write', '', $this->parent_obj->ref_id)) {
            $this->parent_obj->error->raiseError(
                $this->lng->txt('msg_no_perm_read'),
                $this->parent_obj->error->MESSAGE
            );
        }

        $user_ids = [];
        if ($this->dic->http()->wrapper()->post()->has('user_id')) {
            $user_ids = $this->dic->http()->wrapper()->post()->retrieve(
                'user_id',
                $this->dic->refinery()->kindlyTo()->listOf($this->dic->refinery()->kindlyTo()->int())
            );
        }

        if (count($user_ids) === 0) {
            ilUtil::sendInfo($this->lng->txt('time_limit_no_users_selected'), true);
        } else {
            $frm_noti = new ilForumNotification($this->parent_obj->object->getRefId());

            foreach ($user_ids as $user_id) {
                $frm_noti->setUserId((int) $user_id);
                $frm_noti->setUserToggle(false);
                $is_enabled = $frm_noti->isAdminForceNotification();

                if (!$is_enabled) {
                    $frm_noti->setAdminForce(true);
                    $frm_noti->insertAdminForce();
                }
            }

            ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
        }

        $this->showMembers();
    }

    public function disableAdminForceNoti() : void
    {
        if (!$this->access->checkAccess('write', '', $this->parent_obj->ref_id)) {
            $this->parent_obj->error->raiseError(
                $this->lng->txt('msg_no_perm_read'),
                $this->parent_obj->error->MESSAGE
            );
        }

        $user_ids = [];
        if ($this->dic->http()->wrapper()->post()->has('user_id')) {
            $user_ids = $this->dic->http()->wrapper()->post()->retrieve(
                'user_id',
                $this->dic->refinery()->kindlyTo()->listOf($this->dic->refinery()->kindlyTo()->int())
            );
        }

        if (count($user_ids) === 0) {
            ilUtil::sendInfo($this->lng->txt('time_limit_no_users_selected'));
        } else {
            $frm_noti = new ilForumNotification($this->parent_obj->object->getRefId());

            foreach ($user_ids as $user_id) {
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

    public function enableHideUserToggleNoti() : void
    {
        if (!$this->access->checkAccess('write', '', $this->parent_obj->ref_id)) {
            $this->parent_obj->error->raiseError(
                $this->lng->txt('msg_no_perm_read'),
                $this->parent_obj->error->MESSAGE
            );
        }

        $user_ids = [];
        if ($this->dic->http()->wrapper()->post()->has('user_id')) {
            $user_ids = $this->dic->http()->wrapper()->post()->retrieve(
                'user_id',
                $this->dic->refinery()->kindlyTo()->listOf($this->dic->refinery()->kindlyTo()->int())
            );
        }

        if (count($user_ids) === 0) {
            ilUtil::sendInfo($this->lng->txt('time_limit_no_users_selected'));
        } else {
            $frm_noti = new ilForumNotification($this->parent_obj->object->getRefId());

            foreach ($user_ids as $user_id) {
                $frm_noti->setUserId((int) $user_id);
                $frm_noti->setUserToggle(true);
                $is_enabled = $frm_noti->isAdminForceNotification();

                if (!$is_enabled) {
                    $frm_noti->setAdminForce(true);
                    $frm_noti->insertAdminForce();
                } else {
                    $frm_noti->updateUserToggle();
                }
            }

            ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
        }

        $this->showMembers();
    }

    public function disableHideUserToggleNoti() : void
    {
        if (!$this->access->checkAccess('write', '', $this->parent_obj->ref_id)) {
            $this->parent_obj->error->raiseError(
                $this->lng->txt('msg_no_perm_read'),
                $this->parent_obj->error->MESSAGE
            );
        }

        $user_ids = [];
        if ($this->dic->http()->wrapper()->post()->has('user_id')) {
            $user_ids = $this->dic->http()->wrapper()->post()->retrieve(
                'user_id',
                $this->dic->refinery()->kindlyTo()->listOf($this->dic->refinery()->kindlyTo()->int())
            );
        }

        if (count($user_ids) === 0) {
            ilUtil::sendInfo($this->lng->txt('time_limit_no_users_selected'));
        } else {
            $frm_noti = new ilForumNotification($this->parent_obj->object->getRefId());

            foreach ($user_ids as $user_id) {
                $frm_noti->setUserId((int) $user_id);
                $frm_noti->setUserToggle(false);
                $is_enabled = $frm_noti->isAdminForceNotification();
                if ($is_enabled) {
                    $frm_noti->updateUserToggle();
                } else {
                    $frm_noti->setAdminForce(true);
                    $frm_noti->insertAdminForce();
                }
            }

            ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
        }

        $this->showMembers();
    }

    public function getParticipants() : ilParticipants
    {
        if ($this->parent_obj->isParentObjectCrsOrGrp()) {
            $this->parent_obj->error->raiseError(
                $this->lng->txt('msg_no_perm_read'),
                $this->parent_obj->error->MESSAGE
            );
        }

        /** @var $oParticipants ilParticipants */
        $oParticipants = null;

        $grp_ref_id = $this->tree->checkForParentType($this->parent_obj->object->getRefId(), 'grp');
        if ($grp_ref_id > 0) {
            $parent_obj = ilObjectFactory::getInstanceByRefId($grp_ref_id);
            $oParticipants = ilGroupParticipants::_getInstanceByObjId($parent_obj->getId());
            return $oParticipants;
        }

        $crs_ref_id = $this->tree->checkForParentType($this->parent_obj->object->getRefId(), 'crs');
        $parent_obj = ilObjectFactory::getInstanceByRefId($crs_ref_id);

        return ilCourseParticipants::_getInstanceByObjId($parent_obj->getId());
    }

    private function updateUserNotifications($update_all_users = false) : void
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

            $frm_noti->setAdminForce(true);
            $frm_noti->setUserToggle($this->parent_obj->objProperties->isUserToggleNoti());
            $frm_noti->setInterestedEvents($this->parent_obj->objProperties->getInterestedEvents());

            if (array_key_exists($user_id, $all_notis) && $update_all_users) {
                $frm_noti->update();
            } elseif ($frm_noti->existsNotification() === false) {
                $frm_noti->insertAdminForce();
            }
        }
    }

    private function initNotificationSettingsForm() : bool
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
            $chb_2->setValue('1');

            $opt_0->addSubItem($chb_2);

            $cb_grp = new ilCheckboxGroupInputGUI($this->lng->txt('notification_settings'), 'notification_events');

            $notify_modified = new ilCheckboxInputGUI($this->lng->txt('notify_modified'), 'notify_modified');
            $notify_modified->setValue((string) ilForumNotificationEvents::UPDATED);
            $cb_grp->addOption($notify_modified);

            $notify_censored = new ilCheckboxInputGUI($this->lng->txt('notify_censored'), 'notify_censored');
            $notify_censored->setValue((string) ilForumNotificationEvents::CENSORED);
            $cb_grp->addOption($notify_censored);

            $notify_uncensored = new ilCheckboxInputGUI($this->lng->txt('notify_uncensored'), 'notify_uncensored');
            $notify_uncensored->setValue((string) ilForumNotificationEvents::UNCENSORED);
            $cb_grp->addOption($notify_uncensored);

            $notify_post_deleted = new ilCheckboxInputGUI(
                $this->lng->txt('notify_post_deleted'),
                'notify_post_deleted'
            );
            $notify_post_deleted->setValue((string) ilForumNotificationEvents::POST_DELETED);
            $cb_grp->addOption($notify_post_deleted);

            $notify_thread_deleted = new ilCheckboxInputGUI(
                $this->lng->txt('notify_thread_deleted'),
                'notify_thread_deleted'
            );
            $notify_thread_deleted->setValue((string) ilForumNotificationEvents::THREAD_DELETED);
            $cb_grp->addOption($notify_thread_deleted);
            $opt_0->addSubItem($cb_grp);

            $form->addItem($radio_grp);

            $form->addCommandButton('updateNotificationSettings', $this->lng->txt('save'));

            $this->notificationSettingsForm = $form;

            return false;
        }

        return true;
    }

    public function updateNotificationSettings() : void
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
            $notification_type = '';
            if ($this->dic->http()->wrapper()->post()->has('notification_type')) {
                $notification_type = $this->dic->http()->wrapper()->post()->retrieve(
                    'notification_type',
                    $this->dic->refinery()->kindlyTo()->string()
                );
            }

            if ($notification_type === 'all_users') {
                // set values and call update
                $notification_events = $this->notificationSettingsForm->getInput('notification_events');
                $interested_events = 0;

                if (is_array($notification_events)) {
                    foreach ($notification_events as $activated_event) {
                        $interested_events += (int) $activated_event;
                    }
                }

                $this->parent_obj->objProperties->setAdminForceNoti(true);
                $this->parent_obj->objProperties->setUserToggleNoti((bool) $this->notificationSettingsForm->getInput('usr_toggle'));
                $this->parent_obj->objProperties->setNotificationType('all_users');
                $this->parent_obj->objProperties->setInterestedEvents($interested_events);
                $this->updateUserNotifications(true);
            } elseif ($notification_type === 'per_user') {
                $this->parent_obj->objProperties->setNotificationType('per_user');
                $this->parent_obj->objProperties->setAdminForceNoti(true);
                $this->parent_obj->objProperties->setUserToggleNoti(false);
                $this->updateUserNotifications();
            } else { //  if($notification_type] == 'default')
                $this->parent_obj->objProperties->setNotificationType('default');
                $this->parent_obj->objProperties->setAdminForceNoti(false);
                $this->parent_obj->objProperties->setUserToggleNoti(false);
                $frm_noti = new ilForumNotification($this->parent_obj->object->getRefId());
                $frm_noti->deleteNotificationAllUsers();
            }

            $this->parent_obj->objProperties->update();

            ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
        }
        $this->notificationSettingsForm->setValuesByPost();

        $this->showMembers();
    }

    protected function editStyleProperties() : void
    {
        $this->tabs->activateSubTab(self::UI_SUB_TAB_ID_STYLE);
        
        $form = $this->buildStylePropertiesForm();
        $this->tpl->setContent($form->getHTML());
    }

    protected function buildStylePropertiesForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();

        $fixedStyle = (int) $this->settings->get('fixed_content_style_id', '0');
        $defaultStyle = (int) $this->settings->get('default_content_style_id', '0');
        $styleId = $this->properties->getStyleSheetId();

        if ($fixedStyle > 0) {
            $st = new ilNonEditableValueGUI($this->lng->txt('cont_current_style'));
            $st->setValue(
                ilObject::_lookupTitle($fixedStyle) . ' (' . $this->lng->txt('global_fixed') . ')'
            );
            $form->addItem($st);
        } else {
            $st_styles = ilObjStyleSheet::_getStandardStyles(
                true,
                false,
                $this->parent_obj->object->getRefId()
            );

            if ($defaultStyle > 0) {
                $st_styles[0] = ilObject::_lookupTitle($defaultStyle) . ' (' . $this->lng->txt('default') . ')';
            } else {
                $st_styles[0] = $this->lng->txt('default');
            }
            ksort($st_styles);

            if ($styleId > 0 && !ilObjStyleSheet::_lookupStandard($styleId)) {
                $st = new ilNonEditableValueGUI($this->lng->txt('cont_current_style'));
                $st->setValue(ilObject::_lookupTitle($styleId));
                $form->addItem($st);

                $form->addCommandButton('editStyle', $this->lng->txt('cont_edit_style'));
                $form->addCommandButton('deleteStyle', $this->lng->txt('cont_delete_style'));
            }

            if ($styleId <= 0 || ilObjStyleSheet::_lookupStandard($styleId)) {
                $style_sel = new ilSelectInputGUI($this->lng->txt('cont_current_style'), 'style_id');
                $style_sel->setOptions($st_styles);
                $style_sel->setValue($styleId);
                $form->addItem($style_sel);
                $form->addCommandButton('saveStyleSettings', $this->lng->txt('save'));
                $form->addCommandButton('createStyle', $this->lng->txt('sty_create_ind_style'));
            }
        }

        $form->setTitle($this->lng->txt('cont_style'));
        $form->setFormAction($this->ctrl->getFormAction($this));

        return $form;
    }

    protected function createStyle() : void
    {
        $this->ctrl->redirectByClass(ilObjStyleSheetGUI::class, 'create');
    }

    protected function editStyle() : void
    {
        $this->ctrl->redirectByClass(ilObjStyleSheetGUI::class, 'edit');
    }

    protected function deleteStyle() : void
    {
        $this->ctrl->redirectByClass(ilObjStyleSheetGUI::class, 'delete');
    }

    protected function saveStyleSettings() : void
    {
        if (
            (int) $this->settings->get('fixed_content_style_id', '0') <= 0 &&
            (
                ilObjStyleSheet::_lookupStandard(
                    $this->properties->getStyleSheetId()
                ) ||
                $this->properties->getStyleSheetId() === 0
            )
        ) {
            $this->properties->setStyleSheetId(
                (int) ($this->dic->http()->request()->getQueryParams()['style_id'] ?? 0)
            );
            $this->properties->update();
            ilUtil::sendSuccess($this->lng->txt('msg_obj_modified'), true);
        }

        $this->ctrl->redirect($this, 'editStyleProperties');
    }
}
