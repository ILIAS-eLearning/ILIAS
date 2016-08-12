<?php

/**
 * Class ilOnScreenChatGUI
 *
 * @author  Thomas Joußen <tjoussen@databay.de>
 * @since   26.07.16
 */
class ilOnScreenChatGUI
{

	/**
	 * Boolean to track whether this service has already been initialized.
	 *
	 * @var bool
	 */
	protected static $frontend_initialized = false;

	public function executeCommand()
	{
		global $DIC;

		$cmd = $DIC->ctrl()->getCmd();

		switch($cmd) {
			case 'getUserlist':
			default:
				$this->getUserList();
		}
	}

	public function getUserList()
	{
		global $DIC;

		if(!$DIC->user() || $DIC->user()->isAnonymous())
		{
			return;
		}

		require_once 'Services/User/classes/class.ilUserAutoComplete.php';
		$auto = new ilUserAutoComplete();
		$auto->setUser($DIC->user());
		$auto->setPrivacyMode(ilUserAutoComplete::PRIVACY_MODE_RESPECT_USER_SETTING);
		if(($_REQUEST['fetchall']))
		{
			$auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
		}
		$auto->setMoreLinkAvailable(true);
		$auto->setSearchFields(array('firstname', 'lastname'));
		$auto->setResultField('login');
		$auto->enableFieldSearchableCheck(true);
		echo $auto->getList($_REQUEST['q']);
		exit;
	}

	/**
	 * Initialize frontend and delivers required javascript files and configuration to the global template.
	 */
	public static function initializeFrontend()
	{
		global $DIC;

		if(!self::$frontend_initialized)
		{
			$chatSettings = new ilSetting('chatroom');
			if(!$chatSettings->get('chat_enabled') || !$chatSettings->get('enable_osc') || !$DIC->user() || $DIC->user()->isAnonymous())
			{
				self::$frontend_initialized = true;
				return;
			}

			require_once 'Services/JSON/classes/class.ilJsonUtil.php';

			$settings = self::loadServerSettings();

			$guiConfig = array(
				'chatWindowTemplate' => file_get_contents('./Services/OnScreenChat/templates/default/tpl.chat-window.html'),
				'messageTemplate' => file_get_contents('./Services/OnScreenChat/templates/default/tpl.chat-message.html'),
				'modalTemplate' => file_get_contents('./Services/OnScreenChat/templates/default/tpl.chat-add-user.html'),
				'userId' => $DIC->user()->getId(),
				'username' => $DIC->user()->getLogin(),
				'userListURL' => $DIC->ctrl()->getLinkTargetByClass("ilonscreenchatgui", 'getUserList', '', true, true)
			);
			$chatConfig = array(
				'url' => $settings->generateClientUrl() . '/' . $settings->getInstance() . '-im',
				'userId' => $DIC->user()->getId(),
				'username' => $DIC->user()->getLogin()
			);

			$DIC->language()->loadLanguageModule('onscreenchat');

			$DIC['tpl']->addCss('./Services/OnScreenChat/templates/default/onscreenchat.css');

			$DIC['tpl']->addJavascript('./libs/composer/components/moment/min/moment-with-locales.js');
			$DIC['tpl']->addJavascript('./Services/OnScreenChat/js/moment.js');
			$DIC['tpl']->addJavascript('./Modules/Chatroom/chat/node_modules/socket.io/node_modules/socket.io-client/socket.io.js');
			$DIC['tpl']->addJavascript('./Services/OnScreenChat/js/chat.js');
			$DIC['tpl']->addJavascript('./Services/OnScreenChat/js/onscreenchat.js');
			$DIC['tpl']->addOnLoadCode("il.Chat.setConfig(".ilJsonUtil::encode($chatConfig).");");
			$DIC['tpl']->addOnLoadCode("il.Chat.init();");
			$DIC['tpl']->addOnLoadCode("il.OnScreenChat.setConfig(".ilJsonUtil::encode($guiConfig).");");
			$DIC['tpl']->addOnLoadCode("il.OnScreenChat.init();");

			self::$frontend_initialized = true;
		}
	}

	protected static function loadServerSettings()
	{
		require_once './Modules/Chatroom/classes/class.ilChatroomServerSettings.php';
		return ilChatroomServerSettings::loadDefault();
	}
}
