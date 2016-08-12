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
	 * @var array
	 */
	protected $chatSettings = array();

	/**
	 * @var array
	 */
	protected $notificationSettings = array();

	/**
	 * ilPersonalChatSettingsFormGUI constructor.
	 * @param bool $init_form
	 */
	public function __construct($init_form = true)
	{
		global $DIC;
		
		parent::__construct();

		$this->user    = $DIC->user();
		$this->ctrl    = $DIC->ctrl();
		$this->mainTpl = $DIC['tpl'];
		$this->lng     = $DIC['lng'];

		$this->chatSettings         = new ilSetting('chatroom');
		$this->notificationSettings = new ilSetting('notifications');

		if($init_form)
		{
			$this->initForm();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function executeCommand()
	{
		switch($this->ctrl->getCmd())
		{
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
		return $this->chatSettings->get('enable_osc', false);
	}

	/**
	 * 
	 */
	protected function initForm()
	{
		$this->lng->loadLanguageModule('chatroom');

		$this->setFormAction($this->ctrl->getFormAction($this, 'saveChatOptions'));
		$this->setTitle($this->lng->txt("chat_settings"));

		if($this->shouldShowNotificationOptions())
		{
			$chb = new ilCheckboxInputGUI($this->lng->txt('play_invitation_sound'), 'play_invitation_sound');
			$this->addItem($chb);
		}

		if($this->shouldShowOnScreenChatOptions())
		{
			$chb = new ilCheckboxInputGUI($this->lng->txt('chat_osc_allow_to_contact_me'), 'chat_osc_allow_to_contact_me');
			$chb->setInfo($this->lng->txt('chat_osc_allow_to_contact_me_info'));
			$this->addItem($chb);
		}

		$this->addCommandButton('saveChatOptions', $this->lng->txt('save'));
	}

	/**
	 * 
	 */
	protected function showChatOptions()
	{
		if(!$this->isAccessible())
		{
			$this->ctrl->returnToParent($this);
		}

		$this->setValuesByArray(array(
			'play_invitation_sound'        => $this->user->getPref('chat_play_invitation_sound'),
			'chat_osc_allow_to_contact_me' => $this->user->getPref('chat_osc_allow_to_contact_me')
		));

		$this->mainTpl->setContent($this->getHTML());
		$this->mainTpl->show();
	}

	/**
	 *
	 */
	protected function saveChatOptions()
	{
		if(!$this->isAccessible())
		{
			$this->ctrl->returnToParent($this);
		}

		if(!$this->checkInput())
		{
			$this->showChatOptions();
			return;
		}

		if($this->shouldShowNotificationOptions())
		{
			$this->user->setPref('chat_play_invitation_sound', (int)$this->getInput('play_invitation_sound'));
		}

		if($this->shouldShowOnScreenChatOptions())
		{
			$this->user->setPref('chat_osc_allow_to_contact_me', (int)$this->getInput('chat_osc_allow_to_contact_me'));
		}
		$this->user->writePrefs();

		ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
		$this->showChatOptions();
	}
}