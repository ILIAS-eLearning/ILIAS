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
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

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
        $this->ui = $DIC->ui();
        $this->user = $DIC->user();
        $this->refinery = $DIC->refinery();
        $this->uiFactory = $DIC->ui()->factory();
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
        $ui = $this->ui;
        $user = $this->user;
        $lng = $this->lng;

        $html = "";
        if ($this->checklist_status->anyVisibilitySettings()) {
            if (is_null($form)) {
                $form = $this->initPrivacySettingsForm();
            }
            $html = $ui->renderer()->render([$form]);
        }

        $pub_profile = new ilPublicUserProfileGUI($user->getId());
        if ($this->profile_mode->isEnabled()) {
            $html .= $pub_profile->getEmbeddable();
        } else {
            if (!$this->checklist_status->anyVisibilitySettings()) {
                $html .= $ui->renderer()->render(
                    [$ui->factory()->messageBox()->info($lng->txt("usr_public_profile_disabled"))]
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
        $ui = $this->ui;
        $f = $ui->factory();
        $ctrl = $this->ctrl;
        $lng = $this->lng;
        $user = $this->user;
        $settings = $this->settings;

        $fields = [];

        // hide_own_online_status
        if ($this->isAwarnessSettingVisible()) {
            $lng->loadLanguageModule("awrn");

            $default = ($this->settings->get('hide_own_online_status') == "n")
                ? $this->lng->txt("user_awrn_show")
                : $this->lng->txt("user_awrn_hide");

            $options = array(
                "x" => $this->lng->txt("user_awrn_default") . " (" . $default . ")",
                "n" => $this->lng->txt("user_awrn_show"),
                "y" => $this->lng->txt("user_awrn_hide")
            );
            $val = $user->prefs["hide_own_online_status"];
            if ($val == "") {
                $val = "x";
            }
            $fields["hide_own_online_status"] = $f->input()->field()->select(
                $lng->txt("awrn_user_show"),
                $options,
                $lng->txt("awrn_hide_from_awareness_info")
            )
                                                  ->withValue($val)
                                                  ->withRequired(true)
                                                  ->withDisabled(
                                                      $settings->get("usr_settings_disable_hide_own_online_status")
                                                  );
        }

        // allow to contact me
        if ($this->isContactSettingVisible()) {
            $lng->loadLanguageModule('buddysystem');
            $fields["bs_allow_to_contact_me"] = $f->input()->field()->checkbox(
                $lng->txt("buddy_allow_to_contact_me"),
                $lng->txt("buddy_allow_to_contact_me_info")
            )
                                                  ->withValue($user->prefs['bs_allow_to_contact_me'] == 'y')
                                                  ->withDisabled(
                                                      $settings->get('usr_settings_disable_bs_allow_to_contact_me')
                                                  );
        }
        // section
        $section1 = $f->input()->field()->section($fields, $lng->txt("user_visibility_settings"));

        $chat_fields = $this->initChatForm();
        $section_chat = $f->input()->field()->section($chat_fields, $lng->txt("chat_settings"));

        $form_action = $ctrl->getLinkTarget($this, "savePrivacySettings");
        $sections = ['sec' => $section1];
        if(count($chat_fields) > 0) {
            array_push($sections, $section_chat);
        }
        return $f->input()->container()->form()->standard($form_action, $sections);
    }

    /**
     * @return array
     */
    protected function initChatForm(){
        $fieldFactory = $this->uiFactory->input()->field();
        $chat_fields = [];
        if ($this->shouldShowOnScreenChatOptions()) {
            $this->lng->loadLanguageModule("chatroom");
            $checkboxStateToBooleanTrafo = $this->refinery->custom()->transformation(function ($v) {
                if (is_array($v)) {
                    return $v;
                }

                if (is_bool($v)) {
                    return $v;
                }

                return $v === 'checked';
            });

            if ($this->shouldShowOnScreenChatOptions()) {
                $oscAvailable = (bool) $this->settings->get('usr_settings_disable_chat_osc_accept_msg', false);
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

                $chat_fields[self::PROP_ENABLE_OSC] = $enabledOsc;
            }

            if ($this->shouldShowNotificationOptions()) {
                $chat_fields[self::PROP_ENABLE_SOUND] = $fieldFactory
                    ->checkbox($this->lng->txt('play_invitation_sound'), $this->lng->txt('play_invitation_sound_info'))
                    ->withAdditionalTransformation($checkboxStateToBooleanTrafo)
                    ->withValue((bool) $this->user->getPref('chat_play_invitation_sound'));
            }
        }
        return $chat_fields;
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
            $data = $form->getData();
            if (is_array($data["sec"])) {
                if ($this->isAwarnessSettingVisible() && $this->workWithUserSetting("hide_own_online_status")) {
                    $val = $data["sec"]["hide_own_online_status"];
                    if ($val == "x") {
                        $val = "";
                    }
                    $user->setPref(
                        "hide_own_online_status",
                        $val
                    );
                }
                if ($this->isContactSettingVisible() && $this->workWithUserSetting("bs_allow_to_contact_me")) {
                    if ($data["sec"]["bs_allow_to_contact_me"]) {
                        $user->setPref("bs_allow_to_contact_me", "y");
                    } else {
                        $user->setPref("bs_allow_to_contact_me", "n");
                    }
                }
                $user->update();

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
                    $enableOsc = $data[0][self::PROP_ENABLE_OSC] ?? null;
                    if (!is_bool($enableOsc)) {
                        $enableOsc = is_array($enableOsc);
                    }

                    if (!(bool) $this->settings->get('usr_settings_disable_chat_osc_accept_msg', false)) {
                        $preferencesUpdated = true;
                        if ($oldEnableOscValue !== $enableOsc) {
                            $this->user->setPref('chat_osc_accept_msg', ilUtil::tf2yn($enableOsc));
                            $preferencesUpdated = true;
                        }
                    }

                    if ($this->chatSettings->get('enable_browser_notifications', false) && $enableOsc) {
                        $oldBrowserNotificationValue = ilUtil::yn2tf($this->user->getPref('chat_osc_browser_notifications'));

                        $sendBrowserNotifications = false;
                        if (is_array($data[0][self::PROP_ENABLE_OSC])) {
                            if (true === $data[0][self::PROP_ENABLE_OSC][self::PROP_ENABLE_BROWSER_NOTIFICATIONS]) {
                                $sendBrowserNotifications = true;
                            }
                        }

                        if ($oldBrowserNotificationValue !== $sendBrowserNotifications) {
                            $this->user->setPref('chat_osc_browser_notifications', ilUtil::tf2yn($sendBrowserNotifications));
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
                $this->checklist_status->saveStepSucess(ilProfileChecklistStatus::STEP_VISIBILITY_OPTIONS);
                ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
                $ctrl->redirect($this, "");
            }
        }

        $this->showPrivacySettings($form);
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
     * @param ilGlobalPageTemplate|ilTemplate $main_tpl
     * @return ilTemplate
     */
    protected function appendChatJsToTemplate(ilGlobalPageTemplate $main_tpl): ilTemplate
    {
        $tpl = new ilTemplate('tpl.personal_chat_settings_form.html', true, true, 'Modules/Chatroom');
        if ($this->shouldShowOnScreenChatOptions() && $this->chatSettings->get('enable_browser_notifications', false)) {
            $main_tpl->addJavascript('./Services/Notifications/js/browser_notifications.js');

            $tpl->setVariable('ALERT_IMAGE_SRC', ilUtil::getImagePath('icon_alert.svg'));

            $this->lng->toJSMap([
                'osc_browser_noti_no_permission_error' => $this->lng->txt('osc_browser_noti_no_permission_error'),
                'osc_browser_noti_no_support_error' => $this->lng->txt('osc_browser_noti_no_support_error'),
                'osc_browser_noti_req_permission_error' => $this->lng->txt('osc_browser_noti_req_permission_error'),
            ], $main_tpl);
        }
        return $tpl;
    }
}
