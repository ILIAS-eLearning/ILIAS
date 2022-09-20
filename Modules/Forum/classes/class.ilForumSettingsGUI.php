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

/**
 * Class ilForumSettingsGUI
 * @author  Nadia Matuschek <nmatuschek@databay.de>
 * @ilCtrl_Calls ilForumSettingsGUI: ilObjectContentStyleSettingsGUI
 */
class ilForumSettingsGUI implements ilForumObjectConstants
{
    private ilCtrlInterface $ctrl;
    private ilGlobalTemplateInterface $tpl;
    private ilLanguage $lng;
    private ilSetting $settings;
    private ilTabsGUI $tabs;
    private ilAccessHandler $access;
    private ilTree $tree;
    private ilObjForumGUI $parent_obj;
    private \ILIAS\HTTP\GlobalHttpState $http;
    private \ILIAS\Refinery\Factory $refinery;
    private ilForumNotification $forumNotificationObj;
    private ?ilPropertyFormGUI $notificationSettingsForm = null;
    private int $ref_id;
    private ilObjectService $obj_service;
    private \ILIAS\DI\Container $dic;
    private ilErrorHandling $error;
    private \ILIAS\UI\Factory $ui_factory;

    public function __construct(ilObjForumGUI $parent_obj)
    {
        global $DIC;

        $this->dic = $DIC;
        $this->parent_obj = $parent_obj;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();
        $this->settings = $DIC->settings();
        $this->tabs = $DIC->tabs();
        $this->access = $DIC->access();
        $this->tree = $DIC->repositoryTree();
        $this->obj_service = $this->dic->object();
        $this->ref_id = $this->parent_obj->getObject()->getRefId();
        $this->http = $DIC->http();
        $this->ui_factory = $DIC->ui()->factory();
        $this->refinery = $DIC->refinery();
        $this->error = $DIC['ilErr'];

        $this->lng->loadLanguageModule('style');
        $this->lng->loadLanguageModule('cont');
    }

    public function getRefId(): int
    {
        return $this->ref_id;
    }

    private function initForcedForumNotification(): void
    {
        $this->forumNotificationObj = new ilForumNotification($this->parent_obj->getObject()->getRefId());
        $this->forumNotificationObj->readAllForcedEvents();
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass();

        switch (strtolower($next_class)) {
            default:
                switch (true) {
                    case method_exists($this, $cmd):
                        $this->settingsTabs();
                        $this->{$cmd}();
                        break;

                    default:
                        $this->ctrl->redirect($this->parent_obj);
                }
        }
    }

    private function addAvailabilitySection(ilPropertyFormGUI $form): void
    {
        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('rep_activation_availability'));
        $form->addItem($section);

        $online = new ilCheckboxInputGUI($this->lng->txt('rep_activation_online'), 'activation_online');
        $online->setInfo($this->lng->txt('frm_activation_online_info'));
        $form->addItem($online);
    }

    public function getCustomForm(ilPropertyFormGUI $a_form): void
    {
        $this->settingsTabs();
        $this->tabs->activateSubTab(self::UI_SUB_TAB_ID_BASIC_SETTINGS);
        $a_form->setTitle($this->lng->txt('frm_settings_form_header'));

        $this->addAvailabilitySection($a_form);

        $presentationHeader = new ilFormSectionHeaderGUI();
        $presentationHeader->setTitle($this->lng->txt('settings_presentation_header'));
        $a_form->addItem($presentationHeader);

        $this->obj_service->commonSettings()->legacyForm($a_form, $this->parent_obj->getObject())->addTileImage();

        $rg_pro = new ilRadioGroupInputGUI($this->lng->txt('frm_default_view'), 'default_view');
        $option_view_by_posts = new ilRadioOption($this->lng->txt('sort_by_posts'), (string) ilForumProperties::VIEW_TREE);
        $option_view_by_posts->setInfo($this->lng->txt('sort_by_posts_desc'));
        $rg_pro->addOption($option_view_by_posts);
        $option_view_by_date = new ilRadioOption($this->lng->txt('sort_by_date'), (string) ilForumProperties::VIEW_DATE);
        $option_view_by_date->setInfo($this->lng->txt('sort_by_date_desc'));
        $sub_group = new ilRadioGroupInputGUI('', 'default_view_by_date');
        $sub_group->addOption(new ilRadioOption($this->lng->txt('ascending_order'), (string) ilForumProperties::VIEW_DATE_ASC));
        $sub_group->addOption(new ilRadioOption($this->lng->txt('descending_order'), (string) ilForumProperties::VIEW_DATE_DESC));

        $option_view_by_date->addSubItem($sub_group);
        $rg_pro->addOption($option_view_by_date);
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

    public function settingsTabs(): bool
    {
        $this->tabs->addSubTabTarget(
            self::UI_SUB_TAB_ID_BASIC_SETTINGS,
            $this->ctrl->getLinkTarget($this->parent_obj, 'edit'),
            '',
            [strtolower(ilObjForumGUI::class)]
        );

        if ($this->settings->get('forum_notification') > 0) {
            // check if there a parent-node is a grp or crs
            $grp_ref_id = $this->tree->checkForParentType($this->parent_obj->getRefId(), 'grp');
            $crs_ref_id = $this->tree->checkForParentType($this->parent_obj->getRefId(), 'crs');

            if ($grp_ref_id > 0 || $crs_ref_id > 0) {
                #show member-tab for notification if forum-notification is enabled in administration
                if ($this->access->checkAccess('write', '', $this->parent_obj->getRefId())) {
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
            $this->ctrl->getLinkTargetByClass(strtolower(ilObjectContentStyleSettingsGUI::class), ""),
            '',
            [strtolower(ilObjectContentStyleSettingsGUI::class)]
        );

        $this->tabs->activateTab(self::UI_TAB_ID_SETTINGS);

        return true;
    }

    public function getCustomValues(array &$a_values): void
    {
        $a_values['anonymized'] = $this->parent_obj->objProperties->isAnonymized();
        $a_values['statistics_enabled'] = $this->parent_obj->objProperties->isStatisticEnabled();
        $a_values['post_activation'] = $this->parent_obj->objProperties->isPostActivationEnabled();
        $a_values['subject_setting'] = $this->parent_obj->objProperties->getSubjectSetting();
        $a_values['mark_mod_posts'] = $this->parent_obj->objProperties->getMarkModeratorPosts();
        $a_values['thread_sorting'] = $this->parent_obj->objProperties->getThreadSorting();
        $a_values['thread_rating'] = $this->parent_obj->objProperties->isIsThreadRatingEnabled();

        $default_view_value = $this->parent_obj->objProperties->getDefaultView();
        if (in_array($default_view_value, [
            ilForumProperties::VIEW_TREE,
            ilForumProperties::VIEW_DATE,
            ilForumProperties::VIEW_DATE_ASC,
            ilForumProperties::VIEW_DATE_DESC
        ], true)) {
            if (in_array($default_view_value, [
                ilForumProperties::VIEW_DATE_ASC,
                ilForumProperties::VIEW_DATE_DESC
            ], true)) {
                $default_view_by_date = $default_view_value;
                $default_view = ilForumProperties::VIEW_DATE;
            } else {
                $default_view = $default_view_value;
            }
        } else {
            $default_view = ilForumProperties::VIEW_TREE;
        }

        $a_values['default_view'] = $default_view;
        if (isset($default_view_by_date)) {
            $a_values['default_view_by_date'] = $default_view_by_date;
        }
        $a_values['file_upload_allowed'] = $this->parent_obj->objProperties->getFileUploadAllowed();

        $object = $this->parent_obj->getObject();
        $a_values['activation_online'] = $object->getOfflineStatus() === false;
    }

    public function updateCustomValues(ilPropertyFormGUI $a_form): void
    {
        $default_view_input_value = (int) $a_form->getInput('default_view');
        if (in_array($default_view_input_value, [
            ilForumProperties::VIEW_TREE,
            ilForumProperties::VIEW_DATE,
            ilForumProperties::VIEW_DATE_ASC,
            ilForumProperties::VIEW_DATE_DESC
        ], true)) {
            if ($default_view_input_value === ilForumProperties::VIEW_DATE) {
                $default_view_order_by_date_value = (int) $a_form->getInput('default_view_by_date');
                if (in_array($default_view_order_by_date_value, [
                    ilForumProperties::VIEW_DATE_ASC,
                    ilForumProperties::VIEW_DATE_DESC
                ], true)) {
                    $default_view_input_value = $default_view_order_by_date_value;
                }
            }
            $default_view = $default_view_input_value;
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
        $this->obj_service->commonSettings()->legacyForm($a_form, $this->parent_obj->getObject())->saveTileImage();

        $object = $this->parent_obj->getObject();
        $object->setOfflineStatus(!(bool) $a_form->getInput('activation_online'));
        $object->update();
    }

    public function showMembers(): void
    {
        if (!$this->access->checkAccess('write', '', $this->parent_obj->getRefId())) {
            $this->error->raiseError(
                $this->lng->txt('msg_no_perm_read'),
                $this->error->MESSAGE
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

        $frm_noti = new ilForumNotification($this->parent_obj->getObject()->getRefId());
        $oParticipants = $this->getParticipants();

        $moderator_ids = ilForum::_getModerators($this->parent_obj->getObject()->getRefId());

        $admin_ids = $oParticipants->getAdmins();
        $member_ids = $oParticipants->getMembers();
        $tutor_ids = $oParticipants->getTutors();

        if ($this->parent_obj->objProperties->getNotificationType() === 'default') {
            // update forum_notification table
            $forum_noti = new ilForumNotification($this->parent_obj->getObject()->getRefId());
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

    private function getUserNotificationTableData($user_ids): array
    {
        $counter = 0;
        $users = [];
        foreach ($user_ids as $user_id) {
            $forced_events = $this->forumNotificationObj->getForcedEventsObjectByUserId($user_id);

            $users[$counter]['user_id'] = ilLegacyFormElementsUtil::formCheckbox(false, 'user_id[]', (string) $user_id);
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

    private function showMembersTable(array $moderators, array $admins, array $members, array $tutors): void
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

    public function saveEventsForUser(): void
    {
        if (!$this->access->checkAccess('write', '', $this->parent_obj->getRefId())) {
            $this->error->raiseError(
                $this->lng->txt('msg_no_perm_read'),
                $this->error->MESSAGE
            );
        }

        $events_form_builder = new ilForumNotificationEventsFormGUI(
            $this->ctrl->getFormAction($this, 'saveEventsForUser'),
            null,
            $this->ui_factory,
            $this->lng
        );

        if ($this->http->request()->getMethod() === 'POST') {
            $form = $events_form_builder->build()->withRequest($this->http->request());
            $formData = $form->getData();

            $interested_events = ilForumNotificationEvents::DEACTIVATED;

            foreach ($events_form_builder->getValidEvents() as $event) {
                $interested_events += isset($formData[$event]) && $formData[$event] ? $events_form_builder->getValueForEvent(
                    $event
                ) : 0;
            }

            if (isset($formData['hidden_value']) && $formData['hidden_value']) {
                $hidden_value = json_decode($formData['hidden_value'], false, 512, JSON_THROW_ON_ERROR);

                $oParticipants = $this->getParticipants();
                $moderator_ids = ilForum::_getModerators($this->parent_obj->getObject()->getRefId());
                $admin_ids = $oParticipants->getAdmins();
                $member_ids = $oParticipants->getMembers();
                $tutor_ids = $oParticipants->getTutors();

                $valid_usr_ids = array_unique(array_merge($moderator_ids, $admin_ids, $member_ids, $tutor_ids));

                if (in_array($hidden_value->usr_id, $valid_usr_ids)) {
                    $frm_noti = new ilForumNotification($this->parent_obj->getRefId());
                    $frm_noti->setUserId($hidden_value->usr_id);
                    $frm_noti->setForumId($this->parent_obj->getObject()->getId());
                    $frm_noti->setInterestedEvents($interested_events);
                    $frm_noti->updateInterestedEvents();
                }
            }
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);

        $this->showMembers();
    }

    public function enableAdminForceNoti(): void
    {
        if (!$this->access->checkAccess('write', '', $this->parent_obj->getRefId())) {
            $this->error->raiseError(
                $this->lng->txt('msg_no_perm_read'),
                $this->error->MESSAGE
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
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('time_limit_no_users_selected'), true);
        } else {
            $frm_noti = new ilForumNotification($this->parent_obj->getObject()->getRefId());

            foreach ($user_ids as $user_id) {
                $frm_noti->setUserId((int) $user_id);
                $frm_noti->setUserToggle(false);
                $is_enabled = $frm_noti->isAdminForceNotification();

                if (!$is_enabled) {
                    $frm_noti->setAdminForce(true);
                    $frm_noti->insertAdminForce();
                }
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'));
        }

        $this->showMembers();
    }

    public function disableAdminForceNoti(): void
    {
        if (!$this->access->checkAccess('write', '', $this->parent_obj->getRefId())) {
            $this->error->raiseError(
                $this->lng->txt('msg_no_perm_read'),
                $this->error->MESSAGE
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
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('time_limit_no_users_selected'));
        } else {
            $frm_noti = new ilForumNotification($this->parent_obj->getObject()->getRefId());

            foreach ($user_ids as $user_id) {
                $frm_noti->setUserId((int) $user_id);
                $is_enabled = $frm_noti->isAdminForceNotification();

                if ($is_enabled) {
                    $frm_noti->deleteAdminForce();
                }
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'));
        }

        $this->showMembers();
    }

    public function enableHideUserToggleNoti(): void
    {
        if (!$this->access->checkAccess('write', '', $this->parent_obj->getRefId())) {
            $this->error->raiseError(
                $this->lng->txt('msg_no_perm_read'),
                $this->error->MESSAGE
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
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('time_limit_no_users_selected'));
        } else {
            $frm_noti = new ilForumNotification($this->parent_obj->getObject()->getRefId());

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

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'));
        }

        $this->showMembers();
    }

    public function disableHideUserToggleNoti(): void
    {
        if (!$this->access->checkAccess('write', '', $this->parent_obj->getRefId())) {
            $this->error->raiseError(
                $this->lng->txt('msg_no_perm_read'),
                $this->error->MESSAGE
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
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('time_limit_no_users_selected'));
        } else {
            $frm_noti = new ilForumNotification($this->parent_obj->getObject()->getRefId());

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

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'));
        }

        $this->showMembers();
    }

    public function getParticipants(): ilParticipants
    {
        if (!$this->parent_obj->isParentObjectCrsOrGrp()) {
            $this->error->raiseError(
                $this->lng->txt('msg_no_perm_read'),
                $this->error->MESSAGE
            );
        }

        $grp_ref_id = $this->tree->checkForParentType($this->parent_obj->getObject()->getRefId(), 'grp');
        if ($grp_ref_id > 0) {
            $parent_obj = ilObjectFactory::getInstanceByRefId($grp_ref_id);
            return ilGroupParticipants::_getInstanceByObjId($parent_obj->getId());
        }

        $crs_ref_id = $this->tree->checkForParentType($this->parent_obj->getObject()->getRefId(), 'crs');
        $parent_obj = ilObjectFactory::getInstanceByRefId($crs_ref_id);

        return ilCourseParticipants::_getInstanceByObjId($parent_obj->getId());
    }

    private function updateUserNotifications(bool $update_all_users = false): void
    {
        $oParticipants = $this->getParticipants();

        $frm_noti = new ilForumNotification($this->parent_obj->getObject()->getRefId());
        $moderator_ids = ilForum::_getModerators($this->parent_obj->getObject()->getRefId());

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

    private function initNotificationSettingsForm(): bool
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

            $notify_modified = new ilCheckboxOption($this->lng->txt('notify_modified'), (string) ilForumNotificationEvents::UPDATED);
            $cb_grp->addOption($notify_modified);

            $notify_censored = new ilCheckboxOption($this->lng->txt('notify_censored'), (string) ilForumNotificationEvents::CENSORED);
            $cb_grp->addOption($notify_censored);

            $notify_uncensored = new ilCheckboxOption($this->lng->txt('notify_uncensored'), (string) ilForumNotificationEvents::UNCENSORED);
            $cb_grp->addOption($notify_uncensored);

            $notify_post_deleted = new ilCheckboxOption($this->lng->txt('notify_post_deleted'), (string) ilForumNotificationEvents::POST_DELETED);
            $cb_grp->addOption($notify_post_deleted);

            $notify_thread_deleted = new ilCheckboxOption($this->lng->txt('notify_thread_deleted'), (string) ilForumNotificationEvents::THREAD_DELETED);
            $cb_grp->addOption($notify_thread_deleted);
            $opt_0->addSubItem($cb_grp);

            $form->addItem($radio_grp);

            $form->addCommandButton('updateNotificationSettings', $this->lng->txt('save'));

            $this->notificationSettingsForm = $form;

            return false;
        }

        return true;
    }

    public function updateNotificationSettings(): void
    {
        if (!$this->access->checkAccess('write', '', $this->parent_obj->getRefId())) {
            $this->error->raiseError(
                $this->lng->txt('msg_no_perm_read'),
                $this->error->MESSAGE
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
                $frm_noti = new ilForumNotification($this->parent_obj->getObject()->getRefId());
                $frm_noti->deleteNotificationAllUsers();
            }

            $this->parent_obj->objProperties->update();

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'));
        }
        $this->notificationSettingsForm->setValuesByPost();

        $this->showMembers();
    }
}
