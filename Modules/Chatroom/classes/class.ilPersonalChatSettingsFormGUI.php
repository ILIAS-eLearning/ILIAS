<?php

require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';

/**
 * Class ilPersonalChatSettingsFormGUI
 * @ilCtrl_IsCalledBy ilPersonalChatSettingsFormGUI: ilPersonalSettingsGUI
 */
class ilPersonalChatSettingsFormGUI extends ilPropertyFormGUI
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilTemplate
     */
    protected $mainTpl;

    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var array
     */
    protected $chatSettings = array();

    /**
     * @var array
     */
    protected $notificationSettings = array();

    /**
     * @var \ilAppEventHandler
     */
    protected $event;

    /**
     * ilPersonalChatSettingsFormGUI constructor.
     * @param bool $init_form
     */
    public function __construct($init_form = true)
    {
        global $DIC;
        
        parent::__construct();

        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        $this->settings = $DIC['ilSetting'];
        $this->mainTpl = $DIC['tpl'];
        $this->lng = $DIC['lng'];
        $this->event = $DIC->event();

        $this->chatSettings = new ilSetting('chatroom');
        $this->notificationSettings = new ilSetting('notifications');

        if ($init_form) {
            $this->initForm();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function executeCommand()
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
    public function isAccessible()
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
    protected function shouldShowNotificationOptions()
    {
        return $this->notificationSettings->get('enable_osd', false) && $this->chatSettings->get('play_invitation_sound', false);
    }

    /**
     * @return bool
     */
    protected function shouldShowOnScreenChatOptions()
    {
        return (
            $this->chatSettings->get('enable_osc', false) &&
            !(bool) $this->settings->get('usr_settings_hide_chat_osc_accept_msg', false)
        );
    }

    /**
     *
     */
    protected function initForm()
    {
        $this->lng->loadLanguageModule('chatroom');

        $this->setFormAction($this->ctrl->getFormAction($this, 'saveChatOptions'));
        $this->setTitle($this->lng->txt("chat_settings"));

        if ($this->shouldShowNotificationOptions()) {
            $chb = new ilCheckboxInputGUI($this->lng->txt('play_invitation_sound'), 'play_invitation_sound');
            $chb->setInfo($this->lng->txt('play_invitation_sound_info'));
            $this->addItem($chb);
        }

        if ($this->shouldShowOnScreenChatOptions()) {
            $chb = new ilCheckboxInputGUI($this->lng->txt('chat_osc_accept_msg'), 'chat_osc_accept_msg');
            $chb->setInfo($this->lng->txt('chat_osc_accept_msg_info'));
            $chb->setDisabled((bool) $this->settings->get('usr_settings_disable_chat_osc_accept_msg', false));
            $this->addItem($chb);
        }

        $this->addCommandButton('saveChatOptions', $this->lng->txt('save'));
    }

    /**
     *
     */
    protected function showChatOptions()
    {
        if (!$this->isAccessible()) {
            $this->ctrl->returnToParent($this);
        }

        $this->setValuesByArray(array(
            'play_invitation_sound' => $this->user->getPref('chat_play_invitation_sound'),
            'chat_osc_accept_msg' => ilUtil::yn2tf($this->user->getPref('chat_osc_accept_msg'))
        ));

        $this->mainTpl->setContent($this->getHTML());
        $this->mainTpl->show();
    }

    /**
     *
     */
    protected function saveChatOptions()
    {
        if (!$this->isAccessible()) {
            $this->ctrl->returnToParent($this);
        }

        if (!$this->checkInput()) {
            $this->showChatOptions();
            return;
        }

        if ($this->shouldShowNotificationOptions()) {
            $this->user->setPref('chat_play_invitation_sound', (int) $this->getInput('play_invitation_sound'));
        }

        if ($this->shouldShowOnScreenChatOptions() && !(bool) $this->settings->get('usr_settings_disable_chat_osc_accept_msg', false)) {
            $this->user->setPref('chat_osc_accept_msg', ilUtil::tf2yn((bool) $this->getInput('chat_osc_accept_msg')));
        }

        $this->user->writePrefs();

        $this->event->raise(
            'Modules/Chatroom',
            'chatSettingsChanged',
            [
                'user' => $this->user
            ]
        );

        ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
        $this->showChatOptions();
    }
}
