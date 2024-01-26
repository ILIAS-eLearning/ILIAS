<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * User privacy settings (currently located under "Profile and Privacy")
 * @author killing@leifos.de
 */
class ilUserPrivacySettingsGUI
{
    const PROP_ENABLE_OSC = 'chat_osc_accept_msg';
    const PROP_ENABLE_BROWSER_NOTIFICATIONS = 'chat_osc_browser_notifications';
    const PROP_ENABLE_SOUND = 'play_invitation_sound';

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $main_tpl;

    /**
     * @var ilUserSettingsConfig
     */
    protected $user_settings_config;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var \Psr\Http\Message\RequestInterface
     */
    protected $request;

    /**
     * @var ilProfileChecklistStatus
     */
    protected $checklist_status;

    /**
     * @var ilPersonalProfileMode
     */
    protected $profile_mode;

    /** @var \ILIAS\UI\Factory */
    private $uiFactory;

    /** @var \ILIAS\UI\Renderer */
    private $uiRenderer;

    /** @var \ILIAS\Refinery\Factory */
    private $refinery;

    /** @var array */
    protected $chatSettings = array();

    /** @var array */
    protected $notificationSettings = array();

    /** @var ilAppEventHandler */
    protected $event;

    /**
     * constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->lng->loadLanguageModule("user");
        $this->user = $DIC->user();
        $this->refinery = $DIC->refinery();
        $this->uiFactory = $DIC->ui()->factory();
        $this->uiRenderer = $DIC->ui()->renderer();
        $this->chatSettings = new ilSetting('chatroom');
        $this->notificationSettings = new ilSetting('notifications');
        $this->event = $DIC->event();

        $this->request = $DIC->http()->request();

        $this->user_settings_config = new ilUserSettingsConfig();
        $this->settings = $DIC->settings();
        $this->checklist_status = new ilProfileChecklistStatus();
        $this->profile_mode = new ilPersonalProfileMode($this->user, $this->settings);
    }

    /**
     * execute command
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass();

        switch ($next_class) {
            default:
                $cmd = $this->ctrl->getCmd("showPrivacySettings");
                $this->$cmd();
                break;
        }
        $this->main_tpl->printToStdout();
    }


    //
    //
    //	GENERAL SETTINGS FORM
    //
    //

    /**
     * @param string $setting
     * @return bool
     */
    public function workWithUserSetting(string $setting) : bool
    {
        return $this->user_settings_config->isVisibleAndChangeable($setting);
    }

    /**
     * @param string $setting
     * @return bool
     */
    public function userSettingVisible(string $setting) : bool
    {
        return $this->user_settings_config->isVisible($setting);
    }

    /**
     * General settings form.
     * @param null $form
     */
    public function showPrivacySettings($form = null)
    {
        $main_tpl = $this->main_tpl;
        $user = $this->user;
        $lng = $this->lng;

        $html = "";
        if ($this->checklist_status->anyVisibilitySettings()
            && ($this->isAwarnessSettingVisible()
                || $this->isContactSettingVisible()
                || $this->shouldDisplayChatSection())) {
            if (is_null($form)) {
                $form = $this->initPrivacySettingsForm();
            }
            $html = $this->uiRenderer->render([$form]);
        }

        $pub_profile = new ilPublicUserProfileGUI($user->getId());
        if ($this->profile_mode->isEnabled()) {
            $pub_profile_legacy = $this->uiFactory->legacy($pub_profile->getEmbeddable());
            $html .= $this->uiRenderer->render($this->uiFactory->panel()->standard(
                $this->lng->txt('user_profile_preview'),
                $pub_profile_legacy
            ));
        } else {
            if (!$this->checklist_status->anyVisibilitySettings()) {
                $html .= $this->uiRenderer->render(
                    [$this->uiFactory->messageBox()->info($lng->txt("usr_public_profile_disabled"))]
                );
            }
        }


        $chat_tpl = $this->appendChatJsToTemplate($main_tpl);

        $main_tpl->setContent($html . $chat_tpl->get());
    }

    /**
     * Is awareness tool setting visible
     * @return bool
     */
    protected function isAwarnessSettingVisible() : bool
    {
        $awrn_set = new ilSetting("awrn");
        if ($awrn_set->get("awrn_enabled", false) && $this->userSettingVisible("hide_own_online_status")) {
            return true;
        }
        return false;
    }

    /**
     * Is contact setting visible
     * @return bool
     */
    protected function isContactSettingVisible() : bool
    {
        if (ilBuddySystem::getInstance()->isEnabled() && $this->userSettingVisible('bs_allow_to_contact_me')) {
            return true;
        }
        return false;
    }

    /**
     * Init  form.
     * @return \ILIAS\UI\Component\Input\Container\Form\Standard
     */
    public function initPrivacySettingsForm()
    {
        $sections = [];

        $this->populateWithAwarenessSettingsSection($sections);
        $this->populateWithContactsSettingsSection($sections);
        $this->populateWithChatSettingsSection($sections);

        $form_action = $this->ctrl->getLinkTarget($this, "savePrivacySettings");

        return $this->uiFactory->input()
            ->container()
            ->form()
            ->standard($form_action, $sections)
            ->withAdditionalTransformation($this->refinery->custom()->transformation(static function ($values) : array {
                return call_user_func_array('array_merge', $values);
            }));
    }

    /**
     * @return bool
     */
    public function shouldDisplayChatSection() : bool
    {
        return (
            $this->chatSettings->get('chat_enabled', false) && (
                $this->shouldShowNotificationOptions() || $this->shouldShowOnScreenChatOptions()
            )
        );
    }

    /**
     * @return bool
     */
    private function shouldShowNotificationOptions() : bool
    {
        return (
            $this->notificationSettings->get('enable_osd', false) &&
            $this->chatSettings->get('play_invitation_sound', false)
        );
    }

    /**
     * @return bool
     */
    private function shouldShowOnScreenChatOptions() : bool
    {
        return (
            $this->chatSettings->get('enable_osc', false) &&
            !(bool) $this->settings->get('usr_settings_hide_chat_osc_accept_msg', false)
        );
    }

    /**
     * @param array $formSections
     */
    protected function populateWithAwarenessSettingsSection(array &$formSections) : void
    {
        if (!$this->isAwarnessSettingVisible()) {
            return;
        }

        $this->lng->loadLanguageModule("awrn");

        $default = ($this->settings->get('hide_own_online_status') == "n")
            ? $this->lng->txt("user_awrn_show")
            : $this->lng->txt("user_awrn_hide");

        $options = array(
            "x" => $this->lng->txt("user_awrn_default") . " (" . $default . ")",
            "n" => $this->lng->txt("user_awrn_show"),
            "y" => $this->lng->txt("user_awrn_hide")
        );
        $val = $this->user->prefs["hide_own_online_status"];
        if ($val == "") {
            $val = "x";
        }

        $fields["hide_own_online_status"] = $this->uiFactory->input()
            ->field()
            ->select(
                $this->lng->txt("awrn_user_show"),
                $options,
                $this->lng->txt("awrn_hide_from_awareness_info")
            )
            ->withValue($val)
            ->withRequired(true)
            ->withDisabled(
                $this->settings->get('usr_settings_disable_hide_own_online_status', '0') === '1' ? true : false
            );

        $formSections['awrn_sec'] = $this->uiFactory->input()->field()->section($fields, $this->lng->txt('obj_awra'));
    }

    /**
     * @param array $formSections
     */
    protected function populateWithContactsSettingsSection(array &$formSections) : void
    {
        if (!$this->isContactSettingVisible()) {
            return;
        }

        $this->lng->loadLanguageModule('buddysystem');
        $fields["bs_allow_to_contact_me"] = $this->uiFactory->input()
            ->field()
            ->checkbox(
                $this->lng->txt("buddy_allow_to_contact_me"),
                $this->lng->txt("buddy_allow_to_contact_me_info")
            )
            ->withValue($this->user->prefs['bs_allow_to_contact_me'] == 'y')
            ->withDisabled(
                $this->settings->get('usr_settings_disable_bs_allow_to_contact_me', '0') === '1' ? true : false
            );

        $formSections['contacts_sec'] = $this->uiFactory->input()->field()->section($fields, $this->lng->txt('mm_contacts'));
    }

    /**
     * @param array $formSections
     */
    protected function populateWithChatSettingsSection(array &$formSections) : void
    {
        if (!$this->shouldDisplayChatSection()) {
            return;
        }

        $fieldFactory = $this->uiFactory->input()->field();
        $fields = [];

        $this->lng->loadLanguageModule('chatroom_adm');
        $checkboxStateToBooleanTrafo = $this->refinery->custom()->transformation(static function ($v) {
            if (is_array($v)) {
                return $v;
            }

            if (is_bool($v)) {
                return $v;
            }

            return $v === 'checked';
        });

        if ($this->shouldShowOnScreenChatOptions()) {
            $oscAvailable = $this->settings->get('usr_settings_disable_chat_osc_accept_msg', '0') === '1' ? true : false;
            $oscSubFormGroup = [];

            if ($this->chatSettings->get('enable_browser_notifications', false)) {
                $enabledBrowserNotifications = $fieldFactory
                    ->checkbox(
                        $this->lng->txt('osc_enable_browser_notifications_label'),
                        sprintf(
                            $this->lng->txt('osc_enable_browser_notifications_info'),
                            (int) $this->chatSettings->get('conversation_idle_state_in_minutes')
                        )
                    )
                    ->withAdditionalTransformation($checkboxStateToBooleanTrafo)
                    ->withDisabled($oscAvailable);

                $oscSubFormGroup[self::PROP_ENABLE_BROWSER_NOTIFICATIONS] = $enabledBrowserNotifications;

                $groupValue = null;
                if (ilUtil::yn2tf($this->user->getPref('chat_osc_accept_msg'))) {
                    $groupValue = [
                        self::PROP_ENABLE_BROWSER_NOTIFICATIONS => ilUtil::yn2tf($this->user->getPref('chat_osc_browser_notifications')),
                    ];
                }
                $enabledOsc = $fieldFactory
                    ->optionalGroup(
                        $oscSubFormGroup,
                        $this->lng->txt('chat_osc_accept_msg'),
                        $this->lng->txt('chat_osc_accept_msg_info')
                    )
                    ->withAdditionalTransformation($checkboxStateToBooleanTrafo)
                    ->withDisabled($oscAvailable)
                    ->withValue($groupValue);
            } else {
                $enabledOsc = $fieldFactory
                    ->checkbox(
                        $this->lng->txt('chat_osc_accept_msg'),
                        $this->lng->txt('chat_osc_accept_msg_info')
                    )
                    ->withAdditionalTransformation($checkboxStateToBooleanTrafo)
                    ->withDisabled($oscAvailable)
                    ->withValue(ilUtil::yn2tf($this->user->getPref('chat_osc_accept_msg')));
            }

            $fields[self::PROP_ENABLE_OSC] = $enabledOsc;
        }

        if ($this->shouldShowNotificationOptions()) {
            $fields[self::PROP_ENABLE_SOUND] = $fieldFactory
                ->checkbox($this->lng->txt('play_invitation_sound'), $this->lng->txt('play_invitation_sound_info'))
                ->withAdditionalTransformation($checkboxStateToBooleanTrafo)
                ->withValue((bool) $this->user->getPref('chat_play_invitation_sound'));
        }

        $formSections['chat_sec'] = $this->uiFactory->input()->field()->section($fields, $this->lng->txt('chat_settings'));
    }

    /**
     * Save privacy settings
     */
    public function savePrivacySettings()
    {
        $request = $this->request;
        $form = $this->initPrivacySettingsForm();
        $lng = $this->lng;
        $user = $this->user;
        $ctrl = $this->ctrl;

        if ($request->getMethod() == "POST") {
            $form = $form->withRequest($request);
            $formData = $form->getData();

            if ($this->isAwarnessSettingVisible() && $this->workWithUserSetting("hide_own_online_status")) {
                $val = $formData["hide_own_online_status"] ?? 'x';
                if ($val == "x") {
                    $val = "";
                }
                $user->setPref(
                    "hide_own_online_status",
                    $val
                );
            }
            if ($this->isContactSettingVisible() && $this->workWithUserSetting("bs_allow_to_contact_me")) {
                if ($formData["bs_allow_to_contact_me"]) {
                    $user->setPref("bs_allow_to_contact_me", "y");
                } else {
                    $user->setPref("bs_allow_to_contact_me", "n");
                }
            }

            $user->update();

            if ($this->shouldDisplayChatSection()) {
                $preferencesUpdated = false;

                if ($this->shouldShowNotificationOptions()) {
                    $oldPlaySoundValue = (int) $this->user->getPref('chat_play_invitation_sound');
                    $playASound = (int) ($formData[self::PROP_ENABLE_SOUND] ?? 0);

                    if ($oldPlaySoundValue !== $playASound) {
                        $this->user->setPref('chat_play_invitation_sound', $playASound);
                        $preferencesUpdated = true;
                    }
                }

                if ($this->shouldShowOnScreenChatOptions()) {
                    $oldEnableOscValue = ilUtil::yn2tf($this->user->getPref('chat_osc_accept_msg'));
                    $enableOsc = $formData[self::PROP_ENABLE_OSC] ?? null;
                    if (!is_bool($enableOsc)) {
                        $enableOsc = is_array($enableOsc);
                    }

                    if ($this->settings->get('usr_settings_disable_chat_osc_accept_msg', '0') !== '1') {
                        $preferencesUpdated = true;
                        if ($oldEnableOscValue !== $enableOsc) {
                            $this->user->setPref('chat_osc_accept_msg', ilUtil::tf2yn($enableOsc));
                            $preferencesUpdated = true;
                        }
                    }

                    if ($this->chatSettings->get('enable_browser_notifications', false) && $enableOsc) {
                        $oldBrowserNotificationValue = ilUtil::yn2tf($this->user->getPref('chat_osc_browser_notifications'));

                        $sendBrowserNotifications = false;
                        if (is_array($formData[self::PROP_ENABLE_OSC])) {
                            if (true === $formData[self::PROP_ENABLE_OSC][self::PROP_ENABLE_BROWSER_NOTIFICATIONS]) {
                                $sendBrowserNotifications = true;
                            }
                        }

                        if ($oldBrowserNotificationValue !== $sendBrowserNotifications) {
                            $this->user->setPref(
                                'chat_osc_browser_notifications',
                                ilUtil::tf2yn($sendBrowserNotifications)
                            );
                            $preferencesUpdated = true;
                        }
                    }
                }

                if ($preferencesUpdated) {
                    $this->user->writePrefs();

                    $this->event->raise(
                        'Modules/Chatroom',
                        'chatSettingsChanged',
                        [
                            'user' => $this->user
                        ]
                    );
                }
            }

            $this->checklist_status->saveStepSucess(ilProfileChecklistStatus::STEP_VISIBILITY_OPTIONS);
            ilUtil::sendSuccess($lng->txt('msg_obj_modified'), true);
            $ctrl->redirect($this, '');
        }

        $this->showPrivacySettings($form);
    }

    /**
     * @param ilGlobalPageTemplate $main_tpl
     * @return ilTemplate
     */
    protected function appendChatJsToTemplate(ilGlobalPageTemplate $pageTemplate) : ilTemplate
    {
        $tpl = new ilTemplate('tpl.personal_chat_settings_form.html', true, true, 'Modules/Chatroom');
        if ($this->shouldShowOnScreenChatOptions() && $this->chatSettings->get('enable_browser_notifications', false)) {
            $pageTemplate->addJavascript('./Services/Notifications/js/browser_notifications.js');

            $tpl->setVariable('ALERT_IMAGE_SRC', ilUtil::getImagePath('icon_alert.svg'));
            $tpl->setVariable('BROWSER_NOTIFICATION_TOGGLE_LABEL', $this->lng->txt('osc_enable_browser_notifications_label'));

            $this->lng->toJSMap([
                'osc_browser_noti_no_permission_error' => $this->lng->txt('osc_browser_noti_no_permission_error'),
                'osc_browser_noti_no_support_error' => $this->lng->txt('osc_browser_noti_no_support_error'),
                'osc_browser_noti_req_permission_error' => $this->lng->txt('osc_browser_noti_req_permission_error'),
            ], $pageTemplate);
        }

        return $tpl;
    }
}
