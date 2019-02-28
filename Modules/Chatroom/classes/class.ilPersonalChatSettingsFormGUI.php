<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Component\Input\Container\Form\Standard;
use ILIAS\UI\Implementation\Component\Legacy\Legacy;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Factory;
use \ILIAS\Transformation\Factory as TrafoFactory;
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

	/** @var \ilLanguage */
	protected $lng;

	/** @var \ilCtrl */
	protected $ctrl;

	/** @var \ilObjUser */
	protected $user;

	/** @var \ilTemplate */
	protected $mainTpl;

	/** @var \ilSetting */
	protected $settings;

	/** @var array */
	protected $chatSettings = array();

	/** @var array */
	protected $notificationSettings = array();

	/** @var \ilAppEventHandler */
	protected $event;

	/** @var Factory */
	private $uiFactory;

	/** @var Renderer */
	private $uiRenderer;

	/** @var TrafoFactory */
	private $transformationFactory;

	/** @var ServerRequestInterface */
	private $httpRequest;

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
		$this->transformationFactory = new \ILIAS\Transformation\Factory();

		$this->lng->loadLanguageModule('chatroom');
		$this->lng->loadLanguageModule('chatroom_adm');

		$this->chatSettings = new \ilSetting('chatroom');
		$this->notificationSettings = new \ilSetting('notifications');
	}

	/**
	 * 
	 */
	public function executeCommand()
	{
		switch($this->ctrl->getCmd())
		{
			case 'saveChatOptions':
				$this->saveChatOptions();
				break;

			case 'deactivateUnsupportedBrowserNotifications':
				$this->deactivateUnsupportedBrowserNotifications();
				break;

			case 'deactivateBlockeddBrowserNotifications':
				$this->deactivateBlockedBrowserNotifications();
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
	private function shouldShowNotificationOptions()
	{
		return $this->notificationSettings->get('enable_osd', false) && $this->chatSettings->get('play_invitation_sound', false);
	}

	/**
	 * @return bool
	 */
	private function shouldShowOnScreenChatOptions()
	{
		return (
			$this->chatSettings->get('enable_osc', false) &&
			!(bool)$this->settings->get('usr_settings_hide_chat_osc_accept_msg', false)
		);
	}

	/**
	 * @return Standard
	 */
	private function buildForm(): Standard
	{
		$fieldFactory = $this->uiFactory->input()->field();

		$fields = [];

		$checkboxStateToBooleanTrafo = $this->transformationFactory->custom(function($v) {
			if (is_array($v)) {
				return $v;
			}

			if ($v === 'checked') {
				return true;
			}
			return false;
		});

		if ($this->shouldShowOnScreenChatOptions()) {
			$oscAvailable = (bool)$this->settings->get('usr_settings_disable_chat_osc_accept_msg', false);
			$oscSubFormGroup = [];

			if ($this->chatSettings->get('enable_browser_notifications', false)) {
				$enabledBrowserNotifications = $fieldFactory->checkbox(
					$this->lng->txt('osc_enable_browser_notifications_label'),
					sprintf(
						$this->lng->txt('osc_enable_browser_notifications_info'),
						(int)$this->chatSettings->get('conversation_idle_state_in_minutes')
					)
				)->withValue(\ilUtil::yn2tf($this->user->getPref('chat_osc_browser_notifications')))
				->withDisabled($oscAvailable)
				->withAdditionalTransformation($checkboxStateToBooleanTrafo);

				$oscSubFormGroup[self::PROP_ENABLE_BROWSER_NOTIFICATIONS] = $enabledBrowserNotifications;
			}

			$enabledOsc = $fieldFactory->checkbox(
				$this->lng->txt('chat_osc_accept_msg'),
				$this->lng->txt('chat_osc_accept_msg_info')
			)->withValue(\ilUtil::yn2tf($this->user->getPref('chat_osc_accept_msg')))
			->withDisabled($oscAvailable)
			->withAdditionalTransformation($checkboxStateToBooleanTrafo);

			if (count($oscSubFormGroup) > 0) {
				$fields[self::PROP_ENABLE_OSC] = $enabledOsc->withDependantGroup(
					$fieldFactory->dependantGroup($oscSubFormGroup)
				);
			}
		}

		if ($this->shouldShowNotificationOptions()) {
			$fields[self::PROP_ENABLE_SOUND] = $fieldFactory->checkbox(
				$this->lng->txt('play_invitation_sound'),
				$this->lng->txt('play_invitation_sound_info')
			)->withValue((bool)$this->user->getPref('chat_play_invitation_sound'))
			->withAdditionalTransformation($checkboxStateToBooleanTrafo);
		}

		return $this->uiFactory->input()->container()->form()->standard(
			$this->ctrl->getFormAction($this, 'saveChatOptions'),
			[
				$fieldFactory->section($fields, $this->lng->txt('chat_settings'), '')
			]
		);
	}

	/**
	 * @param Standard|null $form
	 * @throws ilTemplateException
	 */
	public function showChatOptions(Standard $form = null)
	{
		if (!$this->isAccessible()) {
			$this->ctrl->returnToParent($this);
		}

		if (null === $form) {
			$form = $this->buildForm();
		}

		$tpl = new \ilTemplate('tpl.personal_chat_settings_form.html', true, true, 'Modules/Chatroom');
		if ($this->shouldShowOnScreenChatOptions() && $this->chatSettings->get('enable_browser_notifications', false)) {
			$this->mainTpl->addJavascript('./Services/Notifications/js/browser_notifications.js');

			$tpl->setVariable(
				'CALLBACK_URL_NO_SUPPORT',
				$this->ctrl->getLinkTarget($this, 'deactivateUnsupportedBrowserNotifications')
			);
			$tpl->setVariable(
				'CALLBACK_URL_NO_PERMISSION',
				$this->ctrl->getLinkTarget($this, 'deactivateBlockedBrowserNotifications')
			);

			$this->lng->toJSMap([
				'osc_browser_noti_no_permission_error' => $this->lng->txt('osc_browser_noti_no_permission_error')
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
	public function saveChatOptions()
	{
		if (!$this->isAccessible()) {
			$this->ctrl->returnToParent($this);
		}

		$form = $this->buildForm()->withRequest($this->httpRequest);

		if ('POST' === $this->httpRequest->getMethod()) {
			$form = $form->withRequest($this->httpRequest);

			$formData = $form->getData();
			$update_possible = !is_null($formData);
			if ($update_possible) {
				$this->saveFormData($formData);
			}
		}

		\ilUtil::sendFailure($this->lng->txt('form_input_not_valid'));
		$this->showChatOptions($form);
	}
	
	private function saveFormData(array $formData)
	{
		$playASound = $formData[0][self::PROP_ENABLE_SOUND];
		$enableOsc = $formData[0][self::PROP_ENABLE_OSC]['value'];
		$sendBrowserNotifications = $formData[0][self::PROP_ENABLE_OSC]['group_values']['dependant_group'][self::PROP_ENABLE_BROWSER_NOTIFICATIONS];

		if ($this->shouldShowNotificationOptions()) {
			$this->user->setPref('chat_play_invitation_sound', (int)$playASound);
		}

		if ($this->shouldShowOnScreenChatOptions()) {
			if (!(bool)$this->settings->get('usr_settings_disable_chat_osc_accept_msg', false)) {
				$this->user->setPref('chat_osc_accept_msg', \ilUtil::tf2yn($enableOsc));
			}

			if ($this->chatSettings->get('enable_browser_notifications', false) && $enableOsc) {
				$this->user->setPref('chat_osc_browser_notifications', \ilUtil::tf2yn($sendBrowserNotifications));
			}
		}

		$this->user->writePrefs();

		$this->event->raise(
			'Services/User',
			'chatSettingsChanged',
			[
				'user' => $this->user
			]
		);

		\ilUtil::sendSuccess($this->lng->txt('saved_successfully'), true);
		$this->ctrl->redirect($this);
	}

	/**
	 * 
	 */
	public function deactivateUnsupportedBrowserNotifications()
	{
		$this->user->setPref('chat_osc_browser_notifications', \ilUtil::tf2yn(false));
		$this->user->writePrefs();

		\ilUtil::sendFailure($this->lng->txt('osc_browser_noti_no_support_error'));
		$this->showChatOptions();
	}

	/**
	 *
	 */
	public function deactivateBlockedBrowserNotifications()
	{
		$this->user->setPref('chat_osc_browser_notifications', \ilUtil::tf2yn(false));
		$this->user->writePrefs();

		\ilUtil::sendFailure($this->lng->txt('osc_browser_noti_req_permission_error'));
		$this->showChatOptions();
	}
}
