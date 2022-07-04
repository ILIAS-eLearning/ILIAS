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

use ILIAS\UI\Component\Input\Field\Section;

/**
 * User privacy settings (currently located under "Profile and Privacy")
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUserPrivacySettingsGUI
{
    private const PROP_ENABLE_OSC = 'chat_osc_accept_msg';
    private const PROP_ENABLE_BROWSER_NOTIFICATIONS = 'chat_osc_browser_notifications';
    private const PROP_ENABLE_SOUND = 'play_sound';
    private const PROP_ENABLE_BROADCAST_TYPING = 'chat_broadcast_typing';

    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $main_tpl;
    protected ilUserSettingsConfig $user_settings_config;
    protected ilObjUser $user;
    protected ilSetting $settings;
    protected \Psr\Http\Message\RequestInterface $request;
    protected ilProfileChecklistStatus $checklist_status;
    protected ilPersonalProfileMode $profile_mode;
    private \ILIAS\UI\Factory $uiFactory;
    private \ILIAS\UI\Renderer $uiRenderer;
    private \ILIAS\Refinery\Factory $refinery;
    protected ilSetting $chatSettings;
    protected ilSetting $notificationSettings;
    protected ilAppEventHandler $event;

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

    public function executeCommand() : void
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

    public function workWithUserSetting(string $setting) : bool
    {
        return $this->user_settings_config->isVisibleAndChangeable($setting);
    }

    public function userSettingVisible(string $setting) : bool
    {
        return $this->user_settings_config->isVisible($setting);
    }

    public function showPrivacySettings(
        \ILIAS\UI\Component\Input\Container\Form\Standard $form = null
    ) : void {
        $main_tpl = $this->main_tpl;
        $user = $this->user;
        $lng = $this->lng;

        $html = "";
        if ($this->checklist_status->anyVisibilitySettings()) {
            if (is_null($form)) {
                $form = $this->initPrivacySettingsForm();
            }
            $html = $this->uiRenderer->render([$form]);
        }

        $pub_profile = new ilPublicUserProfileGUI($user->getId());
        if ($this->profile_mode->isEnabled()) {
            $html .= $pub_profile->getEmbeddable();
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
     */
    public function initPrivacySettingsForm() : \ILIAS\UI\Component\Input\Container\Form\Standard
    {
        $sections = [];

        $this->populateWithAwarenessSettingsSection($sections);
        $this->populateWithContactsSettingsSection($sections);
        $this->populateWithChatSettingsSection($sections);
        $this->populateWithNotificationSettingsSection($sections);

        $form_action = $this->ctrl->getLinkTarget($this, "savePrivacySettings");

        return $this->uiFactory->input()
            ->container()
            ->form()
            ->standard($form_action, $sections)
            ->withAdditionalTransformation($this->refinery->custom()->transformation(static function (array $values) : array {
                return array_merge(...array_values($values));
            }));
    }

    private function shouldShowOnScreenChatOptions() : bool
    {
        return (
            $this->chatSettings->get('enable_osc', false) &&
            !$this->settings->get('usr_settings_hide_chat_osc_accept_msg', false)
        );
    }

    private function shouldShowChatTypingBroadcastOption() : bool
    {
        return (
            !$this->settings->get('usr_settings_hide_chat_broadcast_typing', false)
        );
    }

    public function shouldDisplayChatSection() : bool
    {
        return (
            $this->chatSettings->get('chat_enabled', false)
        );
    }

    private function shouldShowNotificationOptions() : bool
    {
        return $this->notificationSettings->get('play_sound', false);
    }

    public function shouldDisplayNotificationSection() : bool
    {
        return $this->notificationSettings->get('enable_osd', false);
    }

    protected function populateWithAwarenessSettingsSection(
        array &$formSections
    ) : void {
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
        $val = $this->user->prefs["hide_own_online_status"] ?? "";
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
                (bool)
                $this->settings->get("usr_settings_disable_hide_own_online_status")
            );

        $formSections['awrn_sec'] = $this->uiFactory->input()->field()->section($fields, $this->lng->txt('obj_awra'));
    }

    protected function populateWithContactsSettingsSection(
        array &$formSections
    ) : void {
        if (!$this->isContactSettingVisible()) {
            return;
        }

        $this->lng->loadLanguageModule('buddysystem');
        $bs_allow_contact_me = isset($this->user->prefs['bs_allow_to_contact_me']) ?
            $this->user->prefs['bs_allow_to_contact_me'] === 'y' : false;
        $fields["bs_allow_to_contact_me"] = $this->uiFactory->input()
            ->field()
            ->checkbox(
                $this->lng->txt("buddy_allow_to_contact_me"),
                $this->lng->txt("buddy_allow_to_contact_me_info")
            )
            ->withValue($bs_allow_contact_me)
            ->withDisabled(
                (bool)
                $this->settings->get('usr_settings_disable_bs_allow_to_contact_me')
            );

        $formSections['contacts_sec'] = $this->uiFactory->input()->field()->section($fields, $this->lng->txt('mm_contacts'));
    }

    /**
     * @param Section[] $formSections
     */
    protected function populateWithNotificationSettingsSection(array &$formSections) : void
    {
        if (!$this->shouldDisplayNotificationSection()) {
            return;
        }

        $fields = [];

        if ($this->shouldShowNotificationOptions()) {
            $this->lng->loadLanguageModule('notification_adm');
            $fields[self::PROP_ENABLE_SOUND] = $this->uiFactory->input()->field()
                                                               ->checkbox($this->lng->txt('play_sound'), $this->lng->txt('play_sound_desc'))
                                                               ->withValue((bool) $this->user->getPref('play_sound'));
        }

        if ($fields !== []) {
            $formSections['notification_sec'] = $this->uiFactory->input()->field()->section(
                $fields,
                $this->lng->txt('notification_settings')
            );
        }
    }

    protected function populateWithChatSettingsSection(
        array &$formSections
    ) : void {
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
                if (ilUtil::yn2tf((string) $this->user->getPref('chat_osc_accept_msg'))) {
                    $groupValue = [
                        self::PROP_ENABLE_BROWSER_NOTIFICATIONS => ilUtil::yn2tf((string) $this->user->getPref('chat_osc_browser_notifications')),
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
                    ->withValue(ilUtil::yn2tf((string) $this->user->getPref('chat_osc_accept_msg')));
            }

            $fields[self::PROP_ENABLE_OSC] = $enabledOsc;
        }

        if ($this->shouldShowChatTypingBroadcastOption()) {
            $fields[self::PROP_ENABLE_BROADCAST_TYPING] = $fieldFactory
                ->checkbox($this->lng->txt('chat_broadcast_typing'), $this->lng->txt('chat_broadcast_typing_info'))
                ->withAdditionalTransformation($checkboxStateToBooleanTrafo)
                ->withValue(ilUtil::yn2tf((string) $this->user->getPref('chat_broadcast_typing')));
        }

        if ($fields !== []) {
            $formSections['chat_sec'] = $this->uiFactory->input()->field()->section(
                $fields,
                $this->lng->txt('chat_settings')
            );
        }
    }

    public function savePrivacySettings() : void
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

            if ($this->shouldDisplayNotificationSection()) {
                if ($this->shouldShowNotificationOptions()) {
                    $oldPlaySoundValue = (int) $this->user->getPref('play_sound');
                    $playASound = (int) ($formData[self::PROP_ENABLE_SOUND] ?? 0);

                    if ($oldPlaySoundValue !== $playASound) {
                        $this->user->setPref('play_sound', $playASound);
                    }
                }
            }

            if ($this->shouldDisplayChatSection()) {
                $preferencesUpdated = false;

                if ($this->shouldShowOnScreenChatOptions()) {
                    $oldEnableOscValue = ilUtil::yn2tf((string) $this->user->getPref('chat_osc_accept_msg'));
                    $enableOsc = $formData[self::PROP_ENABLE_OSC] ?? null;
                    if (!is_bool($enableOsc)) {
                        $enableOsc = is_array($enableOsc);
                    }

                    if (!$this->settings->get('usr_settings_disable_chat_osc_accept_msg', false)) {
                        $preferencesUpdated = true;
                        if ($oldEnableOscValue !== $enableOsc) {
                            $this->user->setPref('chat_osc_accept_msg', ilUtil::tf2yn($enableOsc));
                            $preferencesUpdated = true;
                        }
                    }

                    if ($enableOsc && $this->chatSettings->get('enable_browser_notifications', false)) {
                        $oldBrowserNotificationValue = ilUtil::yn2tf((string) $this->user->getPref('chat_osc_browser_notifications'));

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

                if ($this->shouldShowChatTypingBroadcastOption()) {
                    $oldBroadcastTypingValue = ilUtil::yn2tf((string) $this->user->getPref('chat_broadcast_typing'));
                    $broadcastTyping = (bool) ($formData[self::PROP_ENABLE_BROADCAST_TYPING] ?? false);

                    if ($oldBroadcastTypingValue !== $broadcastTyping) {
                        $this->user->setPref('chat_broadcast_typing', ilUtil::tf2yn($broadcastTyping));
                        $preferencesUpdated = true;
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
            $this->main_tpl->setOnScreenMessage('success', $lng->txt('msg_obj_modified'), true);
            $ctrl->redirect($this, '');
        }

        $this->showPrivacySettings($form);
    }

    protected function appendChatJsToTemplate(
        ilGlobalTemplateInterface $pageTemplate
    ) : ilTemplate {
        $tpl = new ilTemplate('tpl.personal_chat_settings_form.html', true, true, 'Modules/Chatroom');
        if ($this->shouldShowOnScreenChatOptions() && $this->chatSettings->get('enable_browser_notifications', false)) {
            $pageTemplate->addJavaScript('./Services/Notifications/js/browser_notifications.js');

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
