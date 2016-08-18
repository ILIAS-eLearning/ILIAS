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

	/**
	 * @param ilSetting $chatSettings
	 * @return bool
	 */
	protected static function isOnScreenChatAccessible(ilSetting $chatSettings)
	{
		global $DIC;

		return !$chatSettings->get('chat_enabled') || !$chatSettings->get('enable_osc') || !$DIC->user() || $DIC->user()->isAnonymous();
	}

	/**
	 * @param ilChatroomServerSettings $chatSettings
	 * @return array
	 */
	protected static function getEmoticons(ilChatroomServerSettings $chatSettings)
	{
		$smileys = array();

		if($chatSettings->getSmiliesEnabled())
		{
			require_once 'Modules/Chatroom/classes/class.ilChatroomSmilies.php';
			$smileys_array = ilChatroomSmilies::_getSmilies();
			foreach($smileys_array as $smiley_array)
			{
				$new_keys = array();
				$new_val  = '';
				foreach($smiley_array as $key => $value)
				{
					if($key == 'smiley_keywords')
					{
						$new_keys = explode("\n", $value);
					}

					if($key == 'smiley_fullpath')
					{
						$new_val = $value;
					}
				}

				if(!$new_keys || !$new_val)
				{
					continue;
				}

				foreach($new_keys as $new_key)
				{
					$smileys[$new_key] = $new_val;
				}
			}
		}

		return $smileys;
	}

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

		require_once 'Services/OnScreenChat/classes/class.ilOnScreenChatUserUserAutoComplete.php';
		$auto = new ilOnScreenChatUserUserAutoComplete();
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
			$clientSettings = new ilSetting('chatroom');

			if(self::isOnScreenChatAccessible($clientSettings))
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
				'userListURL' => $DIC->ctrl()->getLinkTargetByClass("ilonscreenchatgui", 'getUserList', '', true, true),
				'loaderImg' => ilUtil::getImagePath("loader.svg"),
				'emoticons' => self::getEmoticons($settings)
			);
			$chatConfig = array(
				'url' => $settings->generateClientUrl() . '/' . $settings->getInstance() . '-im',
				'userId' => $DIC->user()->getId(),
				'username' => $DIC->user()->getLogin()
			);

			$DIC->language()->loadLanguageModule('onscreenchat');

			$DIC['tpl']->addJavascript('./Services/UIComponent/Modal/js/Modal.js');
			$DIC['tpl']->addJavaScript('Services/jQuery/js/jquery.outside.events.min.js');
			$DIC['tpl']->addJavascript('./libs/composer/components/moment/min/moment-with-locales.js');
			$DIC['tpl']->addJavascript('./Services/OnScreenChat/js/moment.js');
			$DIC['tpl']->addJavascript('./Modules/Chatroom/chat/node_modules/socket.io/node_modules/socket.io-client/socket.io.js');
			$DIC['tpl']->addJavascript('./Services/OnScreenChat/js/chat.js');
			$DIC['tpl']->addJavascript('./Services/OnScreenChat/js/onscreenchat.js');
			$DIC['tpl']->addOnLoadCode("il.Chat.setConfig(".ilJsonUtil::encode($chatConfig).");");
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
