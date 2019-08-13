<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Component\Input\Container\Form\Standard;
use ILIAS\UI\Implementation\Component\Legacy\Legacy;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * Class ilPersonalChatSettingsFormGUI
 * @author Michael Jansen <mjansen@databay.de>
 * @ilCtrl_IsCalledBy ilPersonalChatSettingsFormGUI: ilPersonalSettingsGUI
 */
class ilPersonalChatSettingsFormGUI
{
    const PROP_ENABLE_OSC = 'chat_osc_accept_msg';
    const PROP_ENABLE_BROWSER_NOTIFICATIONS = 'chat_osc_browser_notifications';
    const PROP_ENABLE_SOUND = 'play_invitation_sound';

    /** @var ilLanguage */
    protected $lng;

    /** @var ilCtrl */
    protected $ctrl;

    /** @var ilObjUser */
    protected $user;

    /** @var ilTemplate */
    protected $mainTpl;

    /** @var ilSetting */
    protected $settings;

    /** @var array */
    protected $chatSettings = array();

    /** @var array */
    protected $notificationSettings = array();

    /** @var ilAppEventHandler */
    protected $event;

    /** @var Factory */
    private $uiFactory;

    /** @var Renderer */
    private $uiRenderer;

    /** @var ServerRequestInterface */
    private $httpRequest;

    /** @var \ILIAS\Refinery\Factory */
    private $refinery;

    /**
     * ilPersonalChatSettingsFormGUI constructor.
     */
    public function __construct()
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        $this->settings = $DIC['ilSetting'];
        $this->mainTpl = $DIC['tpl'];
        $this->lng = $DIC['lng'];
        $this->event = $DIC->event();
        $this->uiFactory = $DIC->ui()->factory();
        $this->uiRenderer = $DIC->ui()->renderer();
        $this->httpRequest = $DIC->http()->request();
        $this->refinery = $DIC->refinery();

        $this->lng->loadLanguageModule('chatroom');
        $this->lng->loadLanguageModule('chatroom_adm');

        $this->chatSettings = new ilSetting('chatroom');
        $this->notificationSettings = new ilSetting('notifications');
    }

    /**
     *
     */
    public function executeCommand() : void
    {
        switch ($this->ctrl->getCmd()) {
            case 'saveChatOptions':
                $this->saveChatOptions();
                break;

            case 'showChatOptions':
            default:
                $this->showChatOptions();
                break;
        }
    }

    /**
     * @return bool
     */
    public function isAccessible() : bool
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
     * @return Standard
     */
    private function buildForm() : Standard
    {
        $fieldFactory = $this->uiFactory->input()->field();

        $fields = [];

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
                        $this->lng->txt('chat_osc_accept_msg'), $this->lng->txt('chat_osc_accept_msg_info')
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

        $section = $fieldFactory
            ->section($fields, $this->lng->txt('chat_settings'), '');

        return $this->uiFactory->input()
            ->container()
            ->form()
            ->standard(
                $this->ctrl->getFormAction($this, 'saveChatOptions'),
                [$section]
            )
            ->withAdditionalTransformation($this->refinery->custom()->transformation(function ($values) {
                return call_user_func_array('array_merge', $values);
            }));
    }

    /**
     * @param Standard|null $form
     * @throws ilTemplateException
     */
    public function showChatOptions(Standard $form = null) : void
    {
        if (!$this->isAccessible()) {
            $this->ctrl->returnToParent($this);
        }

        if (null === $form) {
            $form = $this->buildForm();
        }

        $tpl = new ilTemplate('tpl.personal_chat_settings_form.html', true, true, 'Modules/Chatroom');
        if ($this->shouldShowOnScreenChatOptions() && $this->chatSettings->get('enable_browser_notifications', false)) {
            $this->mainTpl->addJavascript('./Services/Notifications/js/browser_notifications.js');

            $tpl->setVariable('ALERT_IMAGE_SRC', ilUtil::getImagePath('icon_alert.svg'));

            $this->lng->toJSMap([
                'osc_browser_noti_no_permission_error' => $this->lng->txt('osc_browser_noti_no_permission_error'),
                'osc_browser_noti_no_support_error' => $this->lng->txt('osc_browser_noti_no_support_error'),
                'osc_browser_noti_req_permission_error' => $this->lng->txt('osc_browser_noti_req_permission_error'),
            ], $this->mainTpl);
        }

        $this->mainTpl->setContent($this->uiRenderer->render([
            $form,
            new Legacy($tpl->get())
        ]));
        $this->mainTpl->printToStdout();
    }

    /**
     *
     */
    public function saveChatOptions() : void
    {
        if (!$this->isAccessible()) {
            $this->ctrl->returnToParent($this);
        }

        $form = $this->buildForm();

        if ('POST' === $this->httpRequest->getMethod()) {
            $form = $form->withRequest($this->httpRequest);

            $formData = $form->getData();
            $update_possible = !is_null($formData);
            if ($update_possible) {
                $this->saveFormData($formData);
            }
        }

        ilUtil::sendFailure($this->lng->txt('form_input_not_valid'));
        $this->showChatOptions($form);
    }

    private function saveFormData(array $formData) : void
    {
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
                if (is_array($formData[self::PROP_ENABLE_OSC])) {
                    if (true === $formData[self::PROP_ENABLE_OSC][self::PROP_ENABLE_BROWSER_NOTIFICATIONS]) {
                        $sendBrowserNotifications = true;
                    }
                    if (isset($formData[self::PROP_ENABLE_OSC][0])) {
                        $sendBrowserNotifications = (bool) $formData[self::PROP_ENABLE_OSC][0];
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

        ilUtil::sendSuccess($this->lng->txt('saved_successfully'), true);
        $this->ctrl->redirect($this);
    }
}
