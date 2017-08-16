<?php

/**
 * Class ilOnScreenChatGUI
 *
 * @author  Thomas Joußen <tjoussen@databay.de>
 * @since   26.07.16
 */
class ilOnScreenChatGUI
{
	const WAC_TTL_TIME = 60;

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

		return $chatSettings->get('chat_enabled') && $chatSettings->get('enable_osc') && $DIC->user() && !$DIC->user()->isAnonymous();
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
			require_once 'Services/WebAccessChecker/classes/class.ilWACSignedPath.php';
			require_once 'Modules/Chatroom/classes/class.ilChatroomSmilies.php';

			ilWACSignedPath::setTokenMaxLifetimeInSeconds(self::WAC_TTL_TIME);

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
						$new_val = ilWACSignedPath::signFile($value);
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

		switch($cmd)
		{
			case 'getUserProfileImages':
				$this->getUserProfileImages();
				break;
			case 'verifyLogin':
				$this->verifyLogin();
				break;
			case 'getUserlist':
			default:
				$this->getUserList();
		}
	}

	/**
	 * Checks if a user is logged in. If not, this function should cause an redirect, to disallow chatting while not logged
	 * into ILIAS.
	 *
	 * @return bool
	 */
	public function verifyLogin()
	{
		global $DIC;

		require_once 'Services/Authentication/classes/class.ilSession.php';
		ilSession::enableWebAccessWithoutSession(true);

		echo json_encode(array(
			'loggedIn' => $DIC->user() && !$DIC->user()->isAnonymous()
		));
		exit;
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
		echo $auto->getList($_REQUEST['term']);
		exit;
	}

	public function getUserProfileImages()
	{
		global $DIC;

		$response = array();

		if(!$DIC->user() || $DIC->user()->isAnonymous())
		{
			echo json_encode($response);
			exit();
		}

		if(!isset($_GET['usr_ids']) || strlen($_GET['usr_ids']) == 0)
		{
			echo json_encode($response);
			exit();
		}

		$DIC['lng']->loadLanguageModule('user');
		
		require_once 'Services/WebAccessChecker/classes/class.ilWACSignedPath.php';
		ilWACSignedPath::setTokenMaxLifetimeInSeconds(self::WAC_TTL_TIME);

		$user_ids = array_filter(array_map('intval', array_map('trim', explode(',', $_GET['usr_ids']))));
		require_once 'Services/User/classes/class.ilUserUtil.php';
		$public_data  = ilUserUtil::getNamePresentation($user_ids, true, false, '', false, true, false, true);
		$public_names = ilUserUtil::getNamePresentation($user_ids, false, false, '', false, true, false, false);
		
		foreach($user_ids as $usr_id)
		{
			$public_image = isset($public_data[$usr_id]) && isset($public_data[$usr_id]['img']) ? $public_data[$usr_id]['img'] : '';

			$public_name = '';
			if(isset($public_names[$usr_id]))
			{
				$public_name = $public_names[$usr_id];
				if('unknown' == $public_name && isset($public_data[$usr_id]) && isset($public_data[$usr_id]['login']))
				{
					$public_name = $public_data[$usr_id]['login'];
				}
			}

			$response[$usr_id] = array(
				'public_name'   => $public_name,
				'profile_image' => $public_image
			);
		}

		require_once 'Services/Authentication/classes/class.ilSession.php';
		ilSession::enableWebAccessWithoutSession(true);

		echo json_encode($response);
		exit();
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

			if(!self::isOnScreenChatAccessible($clientSettings))
			{
				self::$frontend_initialized = true;
				return;
			}

			require_once 'Services/JSON/classes/class.ilJsonUtil.php';

			$settings = self::loadServerSettings();

			$DIC->language()->loadLanguageModule('chatroom');
			
			$renderer = $DIC->ui()->renderer();
			$factory  = $DIC->ui()->factory();

			$chatWindowTemplate = new ilTemplate('tpl.chat-window.html', false, false, 'Services/OnScreenChat');
			$chatWindowTemplate->setVariable('SUBMIT_ACTION', $renderer ->render(
				$factory->button()->standard($DIC->language()->txt('chat_osc_send'), 'onscreenchat-submit')
			));
			$chatWindowTemplate->setVariable('ADD_ACTION', $renderer ->render(
				$factory->glyph()->add('addUser')
			));
			$chatWindowTemplate->setVariable('CLOSE_ACTION', $renderer ->render(
				$factory->button()->close()
			));
			$chatWindowTemplate->setVariable('CONVERSATION_ICON', ilUtil::img(ilUtil::getImagePath('icon_chta.svg')));

			$guiConfig = array(
				'chatWindowTemplate' => $chatWindowTemplate->get(),
				'messageTemplate'    => (new ilTemplate('tpl.chat-message.html', false, false, 'Services/OnScreenChat'))->get(),
				'modalTemplate'      => (new ilTemplate('tpl.chat-add-user.html', false, false, 'Services/OnScreenChat'))->get(),
				'userId'             => $DIC->user()->getId(),
				'username'           => $DIC->user()->getLogin(),
				'userListURL'        => $DIC->ctrl()->getLinkTargetByClass('ilonscreenchatgui', 'getUserList', '', true, false),
				'userProfileDataURL' => $DIC->ctrl()->getLinkTargetByClass('ilonscreenchatgui', 'getUserProfileImages', '', true, false),
				'verifyLoginURL'     => $DIC->ctrl()->getLinkTargetByClass('ilonscreenchatgui', 'verifyLogin', '', true, false),
				'loaderImg'          => ilUtil::getImagePath('loader.svg'),
				'emoticons'          => self::getEmoticons($settings),
				'locale'             => $DIC->language()->getLangKey()
			);

			$chatConfig = array(
				'url'           => $settings->generateClientUrl() . '/' . $settings->getInstance() . '-im',
				'subDirectory'  => $settings->getSubDirectory() . '/socket.io',
				'userId'        => $DIC->user()->getId(),
				'username'      => $DIC->user()->getLogin()
			);

			$DIC->language()->toJS(array(
				'chat_osc_no_usr_found', 'chat_osc_emoticons', 'chat_osc_write_a_msg', 'autocomplete_more', 
				'close', 'chat_osc_invite_to_conversation', 'chat_osc_user', 'chat_osc_add_user'
			));

			require_once 'Services/jQuery/classes/class.iljQueryUtil.php';
			iljQueryUtil::initjQuery();
			iljQueryUtil::initjQueryUI();

			require_once 'Services/Link/classes/class.ilLinkifyUtil.php';
			ilLinkifyUtil::initLinkify();

			$DIC['tpl']->addJavaScript('./Services/jQuery/js/jquery.outside.events.min.js');
			$DIC['tpl']->addJavaScript('./Services/jQuery/js/jquery.ui.touch-punch.min.js');
			$DIC['tpl']->addJavascript('./Services/UIComponent/Modal/js/Modal.js');
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
