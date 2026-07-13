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

declare(strict_types=1);

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\UI\Renderer as UiRenderer;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Forum\Notification\ForumNotificationTable;
use ILIAS\HTTP\Response\ResponseHeader;
use ILIAS\Forum\Notification\NotificationType;

/**
 * @ilCtrl_Calls ilForumSettingsGUI: ilObjectContentStyleSettingsGUI
 */
class ilForumSettingsGUI implements ilForumObjectConstants, ilCtrlSecurityInterface
{
    private readonly ilCtrlInterface $ctrl;
    private readonly ilGlobalTemplateInterface $tpl;
    private readonly ilLanguage $lng;
    private readonly ilSetting $settings;
    private readonly ilTabsGUI $tabs;
    private readonly ilAccessHandler $access;
    private readonly \ILIAS\HTTP\GlobalHttpState $http;
    private ilForumNotification $forumNotificationObj;
    private ?ilPropertyFormGUI $notificationSettingsForm = null;
    private readonly ilObjectService $obj_service;
    private readonly \ILIAS\DI\Container $dic;
    private readonly ilErrorHandling $error;
    private readonly \ILIAS\UI\Factory $ui_factory;
    private readonly UiRenderer $ui_renderer;
    private readonly ilUIService $ui_service;

    public function __construct(private readonly ilObjForumGUI $parent_obj, private readonly ilObjForum $forum)
    {
        global $DIC;

        $this->dic = $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();
        $this->settings = $DIC->settings();
        $this->tabs = $DIC->tabs();
        $this->access = $DIC->access();
        $this->obj_service = $this->dic->object();
        $this->http = $DIC->http();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->ui_service = $DIC->uiService();
        $this->error = $DIC['ilErr'];

        $this->lng->loadLanguageModule('style');
        $this->lng->loadLanguageModule('cont');
    }

    public function getRefId(): int
    {
        return $this->forum->getRefId();
    }

    private function initForcedForumNotification(): void
    {
        $this->forumNotificationObj = new ilForumNotification($this->forum->getRefId());
        $this->forumNotificationObj->readAllForcedEvents();
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd() ?? '';
        switch (true) {
            case method_exists($this, $cmd . 'Command'):
                $this->settingsTabs();
                $this->{$cmd . 'Command'}();
                break;

            default:
                $this->ctrl->redirect($this->parent_obj);
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

        if ($this->settings->get('forum_notification') > 0 &&
            $this->forum->isParentMembershipEnabledContainer() &&
            $this->access->checkAccess('write', '', $this->parent_obj->getRefId())) {

            $this->tabs->addSubTabTarget(
                self::UI_SUB_TAB_ID_NOTIFICATIONS,
                $this->ctrl->getLinkTarget($this, 'showMembers'),
                '',
                [strtolower(self::class)]
            );
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
        $a_values['activation_online'] = !$object->getOfflineStatus();
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

        $view_mode = 'viewmode_' . $this->forum->getId();
        if (ilSession::get($view_mode)) {
            ilSession::set($view_mode, $default_view);
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
        $this->parent_obj->objProperties->setIsThreadRatingEnabled((bool) $a_form->getInput('thread_rating'));
        if (!ilForumProperties::isFileUploadGloballyAllowed()) {
            $this->parent_obj->objProperties->setFileUploadAllowed((bool) $a_form->getInput('file_upload_allowed'));
        }
        $this->parent_obj->objProperties->update();
        $this->obj_service->commonSettings()->legacyForm($a_form, $this->parent_obj->getObject())->saveTileImage();

        $object = $this->parent_obj->getObject();
        $object->setOfflineStatus(!$a_form->getInput('activation_online'));
        $object->update();
    }

    private function showMembersCommand(): void
    {
        if (!$this->access->checkAccess('write', '', $this->parent_obj->getRefId())) {
            $this->error->raiseError(
                $this->lng->txt('msg_no_perm_read'),
                $this->error->MESSAGE
            );
        }

        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.forums_members_list.html', 'components/ILIAS/Forum');

        // instantiate the property form
        if (!$this->initNotificationSettingsForm()) {
            // if the form was just created set the values fetched from database
            $interested_events = $this->parent_obj->objProperties->getInterestedEvents();

            $form_events = [];
            if (($interested_events & ilForumNotificationEvents::UPDATED) !== 0) {
                $form_events[] = ilForumNotificationEvents::UPDATED;
            }

            if (($interested_events & ilForumNotificationEvents::CENSORED) !== 0) {
                $form_events[] = ilForumNotificationEvents::CENSORED;
            }

            if (($interested_events & ilForumNotificationEvents::UNCENSORED) !== 0) {
                $form_events[] = ilForumNotificationEvents::UNCENSORED;
            }

            if (($interested_events & ilForumNotificationEvents::POST_DELETED) !== 0) {
                $form_events[] = ilForumNotificationEvents::POST_DELETED;
            }

            if (($interested_events & ilForumNotificationEvents::THREAD_DELETED) !== 0) {
                $form_events[] = ilForumNotificationEvents::THREAD_DELETED;
            }

            $this->notificationSettingsForm->setValuesByArray([
                'notification_type' => $this->parent_obj->objProperties->getNotificationType()->value,
                'adm_force' => $this->parent_obj->objProperties->isAdminForceNoti(),
                'usr_toggle' => $this->parent_obj->objProperties->isUserToggleNoti(),
                'notification_events' => $form_events
            ]);
        }

        $this->tpl->setVariable('NOTIFICATIONS_SETTINGS_FORM', $this->notificationSettingsForm->getHTML());

        if ($this->parent_obj->objProperties->getNotificationType() === NotificationType::DEFAULT) {
            $forum_noti = new ilForumNotification($this->forum->getRefId());
            $forum_noti->setAdminForce($this->parent_obj->objProperties->isAdminForceNoti());
            $forum_noti->setUserToggle($this->parent_obj->objProperties->isUserToggleNoti());
            $forum_noti->setForumId($this->parent_obj->objProperties->getObjId());
            $forum_noti->setInterestedEvents($this->parent_obj->objProperties->getInterestedEvents());
            $forum_noti->update();
        } elseif ($this->parent_obj->objProperties->getNotificationType() === NotificationType::PER_USER) {
            $this->tpl->setVariable('TABLE', $this->ui_renderer->render($this->getForumNotificationTable()->getComponents()));
        }
    }

    private function getForumNotificationTable(): ForumNotificationTable
    {
        $this->initForcedForumNotification();
        return new ForumNotificationTable(
            $this->http->request(),
            $this->lng,
            $this->ui_factory,
            new DataFactory(),
            $this->parent_obj->getObject()->getRefId(),
            $this->forum->parentParticipants(),
            $this->forumNotificationObj,
            $this->ui_service,
            $this->ctrl->getLinkTarget($this, 'handleNotificationActions')
        );
    }

    private function notificationSettings(): never
    {
        $send_bad_request = function () {
            $this->http->saveResponse(
                $this->http->response()
                           ->withStatus(400)
            );
            $this->http->sendResponse();
            $this->http->close();
        };

        if (!$this->http->wrapper()->query()->has('frm_notifications_table_usr_ids')) {
            $send_bad_request();
        }

        $user_ids = $this->http->wrapper()->query()->retrieve(
            'frm_notifications_table_usr_ids',
            $this->dic->refinery()->kindlyTo()->listOf($this->dic->refinery()->kindlyTo()->int())
        );
        if (count($user_ids) !== 1) {
            $send_bad_request();
        }

        $user_id = current($user_ids);
        $this->initForcedForumNotification();
        $forced_events = $this->forumNotificationObj->getForcedEventsObjectByUserId($user_id);
        $interested_events = $forced_events->getInterestedEvents();
        $form_gui = new ilForumNotificationEventsFormGUI(
            $this->ctrl->getFormAction($this->parent_obj, 'saveEventsForUser'),
            [
                'hidden_value' => json_encode([
                    'usr_id' => $user_id
                ], JSON_THROW_ON_ERROR),
                'notify_modified' => (bool) ($interested_events & ilForumNotificationEvents::UPDATED),
                'notify_censored' => (bool) ($interested_events & ilForumNotificationEvents::CENSORED),
                'notify_uncensored' => (bool) ($interested_events & ilForumNotificationEvents::UNCENSORED),
                'notify_post_deleted' => (bool) ($interested_events & ilForumNotificationEvents::POST_DELETED),
                'notify_thread_deleted' => (bool) ($interested_events & ilForumNotificationEvents::THREAD_DELETED),
            ],
            $this->ui_factory,
            $this->lng
        );

        $modal = $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('notification_settings'),
            [],
            $form_gui->getInputs(),
            $this->ctrl->getFormAction($this, 'saveEventsForUser')
        );

        $this->http->saveResponse($this->http->response()->withBody(
            Streams::ofString($this->ui_renderer->renderAsync($modal))
        )->withHeader(ResponseHeader::CONTENT_TYPE, 'text/html'));
        $this->http->sendResponse();
        $this->http->close();
    }

    public function saveEventsForUserCommand(): void
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
                $valid_usr_ids = $this->forum->getAllForumParticipants();

                if (in_array($hidden_value->usr_id, $valid_usr_ids)) {
                    $frm_noti = new ilForumNotification($this->parent_obj->getRefId());
                    $frm_noti->setUserId($hidden_value->usr_id);
                    $frm_noti->setForumId($this->forum->getId());
                    $frm_noti->setInterestedEvents($interested_events);
                    $frm_noti->updateInterestedEvents();
                }
            }
        }

        $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('saved_successfully'), true);
        $this->ctrl->redirect($this, 'showMembers');
    }

    public function enableAdminForceNotiCommand(): void
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
            $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_INFO, $this->lng->txt('time_limit_no_users_selected'), true);
        } else {
            $frm_noti = new ilForumNotification($this->forum->getRefId());

            foreach ($user_ids as $user_id) {
                $frm_noti->setUserId((int) $user_id);
                $frm_noti->setUserToggle(false);
                $is_enabled = $frm_noti->isAdminForceNotification();

                if (!$is_enabled) {
                    $frm_noti->setAdminForce(true);
                    $frm_noti->insertAdminForce();
                }
            }

            $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('saved_successfully'), true);
        }

        $this->ctrl->redirect($this, 'showMembers');
    }

    public function disableAdminForceNotiCommand(): void
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
            $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_INFO, $this->lng->txt('time_limit_no_users_selected'), true);
        } else {
            $frm_noti = new ilForumNotification($this->forum->getRefId());

            foreach ($user_ids as $user_id) {
                $frm_noti->setUserId((int) $user_id);
                $is_enabled = $frm_noti->isAdminForceNotification();

                if ($is_enabled) {
                    $frm_noti->deleteAdminForce();
                }
            }

            $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('saved_successfully'), true);
        }

        $this->ctrl->redirect($this, 'showMembers');
    }

    private function enableHideUserToggleNoti(): void
    {
        if (!$this->access->checkAccess('write', '', $this->parent_obj->getRefId())) {
            $this->error->raiseError(
                $this->lng->txt('msg_no_perm_read'),
                $this->error->MESSAGE
            );
        }

        $user_ids = [];
        if ($this->dic->http()->wrapper()->query()->has('frm_notifications_table_usr_ids')) {
            $user_ids = $this->dic->http()->wrapper()->query()->retrieve(
                'frm_notifications_table_usr_ids',
                $this->dic->refinery()->kindlyTo()->listOf($this->dic->refinery()->kindlyTo()->string())
            );
            if ($user_ids === ['ALL_OBJECTS']) {
                $table = $this->getForumNotificationTable();
                $user_ids = $table->getFilteredUserIds($this->ui_service->filter()->getData($table->getFilterComponent()));
            }
        }

        if (count($user_ids) === 0) {
            $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_INFO, $this->lng->txt('time_limit_no_users_selected'), true);
        } else {
            $frm_noti = new ilForumNotification($this->forum->getRefId());

            foreach ($user_ids as $user_id) {
                $frm_noti->setUserId((int) $user_id);
                $frm_noti->setUserToggle(true);
                $is_enabled = $frm_noti->isAdminForceNotification();

                if ($is_enabled) {
                    $frm_noti->updateUserToggle();
                } else {
                    $frm_noti->setAdminForce(true);
                    $frm_noti->insertAdminForce();
                }
            }

            $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('saved_successfully'), true);
        }

        $this->ctrl->redirect($this, 'showMembers');
    }

    private function disableHideUserToggleNoti(): void
    {
        if (!$this->access->checkAccess('write', '', $this->parent_obj->getRefId())) {
            $this->error->raiseError(
                $this->lng->txt('msg_no_perm_read'),
                $this->error->MESSAGE
            );
        }

        $user_ids = [];
        if ($this->dic->http()->wrapper()->query()->has('frm_notifications_table_usr_ids')) {
            $user_ids = $this->dic->http()->wrapper()->query()->retrieve(
                'frm_notifications_table_usr_ids',
                $this->dic->refinery()->kindlyTo()->listOf($this->dic->refinery()->kindlyTo()->string())
            );
            if ($user_ids === ['ALL_OBJECTS']) {
                $table = $this->getForumNotificationTable();
                $user_ids = $table->getFilteredUserIds($this->ui_service->filter()->getData($table->getFilterComponent()));
            }
        }

        if (count($user_ids) === 0) {
            $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_INFO, $this->lng->txt('time_limit_no_users_selected'), true);
        } else {
            $frm_noti = new ilForumNotification($this->forum->getRefId());

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

            $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('saved_successfully'), true);
        }

        $this->ctrl->redirect($this, 'showMembers');
    }

    private function initNotificationSettingsForm(): bool
    {
        if ($this->notificationSettingsForm === null) {
            $form = new ilPropertyFormGUI();
            $form->setFormAction($this->ctrl->getFormAction($this, 'updateNotificationSettings'));
            $form->setTitle($this->lng->txt('forums_notification_settings'));

            $radio_grp = new ilRadioGroupInputGUI('', 'notification_type');
            $radio_grp->setValue('default');

            $opt_default = new ilRadioOption(
                $this->lng->txt('user_decides_notification'),
                NotificationType::DEFAULT->value
            );
            $opt_0 = new ilRadioOption($this->lng->txt('settings_for_all_members'), NotificationType::ALL_USERS->value);
            $opt_1 = new ilRadioOption($this->lng->txt('settings_per_users'), NotificationType::PER_USER->value);

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

    private function updateNotificationSettingsCommand(): void
    {
        if (!$this->access->checkAccess('write', '', $this->parent_obj->getRefId())) {
            $this->error->raiseError(
                $this->lng->txt('msg_no_perm_read'),
                $this->error->MESSAGE
            );
        }

        $this->initNotificationSettingsForm();

        $frm_noti = new ilForumNotification($this->forum->getRefId());

        $former_properties = clone $this->parent_obj->objProperties;

        if ($this->notificationSettingsForm->checkInput()) {
            $notification_type = NotificationType::tryFrom($this->dic->http()->wrapper()->post()->retrieve(
                'notification_type',
                $this->dic->refinery()->byTrying([
                    $this->dic->refinery()->kindlyTo()->string(),
                    $this->dic->refinery()->always(NotificationType::DEFAULT->value)
                ])
            )) ?? NotificationType::DEFAULT;

            if ($notification_type === NotificationType::ALL_USERS) {
                $notification_events = $this->notificationSettingsForm->getInput('notification_events');
                $interested_events = 0;

                if (is_array($notification_events)) {
                    foreach ($notification_events as $activated_event) {
                        $interested_events += (int) $activated_event;
                    }
                }

                $this->parent_obj->objProperties->setAdminForceNoti(true);
                $this->parent_obj->objProperties->setUserToggleNoti(
                    (bool) $this->notificationSettingsForm->getInput('usr_toggle')
                );
                $this->parent_obj->objProperties->setNotificationType(NotificationType::ALL_USERS);
                $this->parent_obj->objProperties->setInterestedEvents($interested_events);
            } elseif ($notification_type === NotificationType::PER_USER) {
                $this->parent_obj->objProperties->setNotificationType(NotificationType::PER_USER);
                $this->parent_obj->objProperties->setAdminForceNoti(true);
                $this->parent_obj->objProperties->setUserToggleNoti(false);
            } else {
                $this->parent_obj->objProperties->setNotificationType(NotificationType::DEFAULT);
                $this->parent_obj->objProperties->setAdminForceNoti(false);
                $this->parent_obj->objProperties->setUserToggleNoti(false);
            }

            $frm_noti->applyTypeConfigurationFor(
                $this->forum->getAllForumParticipants(),
                $this->parent_obj->objProperties,
                $former_properties
            );

            $this->parent_obj->objProperties->update();

            $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('saved_successfully'), true);
            $this->ctrl->redirect($this, 'showMembers');
        }
        $this->notificationSettingsForm->setValuesByPost();

        $this->ctrl->redirect($this, 'showMembers');
    }

    public function getUnsafeGetCommands(): array
    {
        return [
            'handleNotificationActions',
        ];
    }

    public function getSafePostCommands(): array
    {
        return [];
    }

    private function handleNotificationActionsCommand(): void
    {
        $action = $this->http->wrapper()->query()->retrieve(
            'frm_notifications_table_action',
            $this->dic->refinery()->byTrying([
                $this->dic->refinery()->kindlyTo()->string(),
                $this->dic->refinery()->always(null)
            ])
        );

        match ($action) {
            'enableHideUserToggleNoti' => $this->enableHideUserToggleNoti(),
            'disableHideUserToggleNoti' => $this->disableHideUserToggleNoti(),
            'notificationSettings' => $this->notificationSettings(),
            default => $this->showMembersCommand()
        };
    }
}
