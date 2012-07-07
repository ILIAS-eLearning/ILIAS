<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Block/classes/class.ilBlockGUI.php';
require_once 'Modules/Chatroom/classes/class.ilChatroomServerConnector.php';
require_once 'Modules/Chatroom/classes/class.ilChatroomServerSettings.php';
require_once 'Services/JSON/classes/class.ilJsonUtil.php';
require_once 'Modules/Chatroom/classes/class.ilChatroomBlock.php';
require_once 'Modules/Chatroom/classes/class.ilChatroomSmilies.php';

/**
 * Class ilChatroomBlockGUI
 * @author			Michael Jansen <mjansen@databay.de>
 * @version		   $Id$
 * @ilCtrl_IsCalledBy ilChatroomBlockGUI: ilColumnGUI
 */
class ilChatroomBlockGUI extends ilBlockGUI
{
	/**
	 * @var string
	 * @static
	 */
	public static $block_type = 'chatviewer';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $lng;

		parent::__construct();

		$lng->loadLanguageModule('chat');
		$lng->loadLanguageModule('chatroom');
		
		$this->setImage(ilUtil::getImagePath('icon_chat.png'));
		$this->setTitle($lng->txt('chat_chatviewer'));
		$this->setAvailableDetailLevels(1, 0);
		$this->allow_moving = true;
	}

	/**
	 * @static
	 * @return string
	 */
	public static function getBlockType()
	{
		return self::$block_type;
	}

	/**
	 * @static
	 * @return bool
	 */
	public static function isRepositoryObject()
	{
		return false;
	}

	/**
	 * @static
	 * @return string
	 */
	public static function getScreenMode()
	{
		switch($_GET['cmd'])
		{
			default:
				return IL_SCREEN_SIDE;
				break;
		}
	}

	/**
	 * @return string
	 */
	public function executeCommand()
	{
		/**
		 * @var $ilCtrl ilCtrl
		 */
		global $ilCtrl;

		$cmd = $ilCtrl->getCmd('getHTML');

		return $this->$cmd();
	}

	/**
	 * @return string
	 */
	public function getHTML()
	{
		ilYuiUtil::initJson();

		$chatSetting = new ilSetting('chatroom');
		if($this->getCurrentDetailLevel() == 0 || !$chatSetting->get('chat_enabled', 0) || !(bool)@ilChatroomServerConnector::checkServerConnection())
		{
			return '';
		}
		else
		{
			return parent::getHTML();
		}
	}

	/**
	 * Fill data section
	 */
	public function fillDataSection()
	{
		/**
		 * @var $tpl	ilTemplate
		 * @var $lng	ilLanguage
		 * @var $ilCtrl ilCtrl
		 */
		global $tpl, $lng, $ilCtrl;

		//@todo: Dirty hack
		if($ilCtrl->isAsynch())
		{
			return $this->getMessages();
		}

		$tpl->addJavascript('./Modules/Chatroom/js/chatviewer.js');
		$tpl->addCss('./Modules/Chatroom/templates/default/style.css');

		$chatblock = new ilChatroomBlock();
		$body_tpl  = new ilTemplate('tpl.chatroom_block_message_body.html', true, true, 'Modules/Chatroom');

		$height = 120;
		if($this->getCurrentDetailLevel() > 0 && $this->getCurrentDetailLevel() <= 3)
		{
			$height *= $this->getCurrentDetailLevel();
		}
		$body_tpl->setVariable('BLOCK_HEIGHT', $height);
		$body_tpl->setVariable('TXT_ENABLE_AUTOSCROLL', $lng->txt('chat_enable_autoscroll'));

		$ilCtrl->setParameterByClass('ilcolumngui', 'block_id', 'block_' . $this->getBlockType() . '_' . (int)$this->getBlockId());
		$ilCtrl->setParameterByClass('ilcolumngui', 'ref_id', '#__ref_id');
		$body_tpl->setVariable('CHATBLOCK_BASE_URL', $ilCtrl->getLinkTargetByClass('ilcolumngui', 'updateBlock', '', true));
		$ilCtrl->setParameterByClass('ilcolumngui', 'block_id', '');
		$ilCtrl->setParameterByClass('ilcolumngui', 'ref_id', '');

		$smilieys = array();
		$settings = ilChatroomServerSettings::loadDefault();
		if($settings->getSmiliesEnabled())
		{
			$smilies_array = ilChatroomSmilies::_getSmilies();
			foreach($smilies_array as $smiley_array)
			{
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

				foreach($new_keys as $new_key)
				{
					$smilieys[$new_key] = $new_val;
				}
			}
		}
		else
		{
			$smilieys = new stdClass();
		}
		$body_tpl->setVariable('SMILIES', json_encode($smilieys));
		
		$body_tpl->setVariable('LBL_MAINROOM', $lng->txt('chat_mainroom'));
		$body_tpl->setVariable('LBL_LEAVE_PRIVATE_ROOM', $lng->txt('leave_private_room'));
		$body_tpl->setVariable('LBL_JOIN', $lng->txt('chat_join'));
		$body_tpl->setVariable('LBL_DELETE_PRIVATE_ROOM', $lng->txt('delete_private_room'));
		$body_tpl->setVariable('LBL_INVITE_TO_PRIVATE_ROOM', $lng->txt('invite_to_private_room'));
		$body_tpl->setVariable('LBL_KICK', $lng->txt('chat_kick'));
		$body_tpl->setVariable('LBL_BAN', $lng->txt('chat_ban'));
		$body_tpl->setVariable('LBL_KICK_QUESTION', $lng->txt('kick_question'));
		$body_tpl->setVariable('LBL_BAN_QUESTION', $lng->txt('ban_question'));
		$body_tpl->setVariable('LBL_ADDRESS', $lng->txt('chat_address'));
		$body_tpl->setVariable('LBL_WHISPER', $lng->txt('chat_whisper'));
		$body_tpl->setVariable('LBL_CONNECT', $lng->txt('chat_connection_established'));
		$body_tpl->setVariable('LBL_DISCONNECT', $lng->txt('chat_connection_disconnected'));
		$body_tpl->setVariable('LBL_INVITE_USERS', $lng->txt('chat_invite_users'));
		$body_tpl->setVariable('LBL_USER_TAB', $lng->txt('chat_right_box_user'));
		$body_tpl->setVariable('LBL_PRIVATE_ROOM', $lng->txt('chat_private_room'));
		$body_tpl->setVariable('LBL_CREATE_NEW_PRIVATE_ROOM', $lng->txt('chat_create_new_private_room'));
		$body_tpl->setVariable('LBL_TO_MAINROOM', $lng->txt('chat_to_mainroom'));
		$body_tpl->setVariable('LBL_CREATE_PRIVATE_ROOM', $lng->txt('chat_create_private_room_button'));
		$body_tpl->setVariable('LBL_CREATE_PRIVATE_ROOM_TEXT', $lng->txt('create_private_room_text'));

		$body_tpl->setVariable('LBL_WELCOME_TO_CHAT', $lng->txt('welcome_to_chat'));
		$body_tpl->setVariable('LBL_USER_INVITED', $lng->txt('user_invited'));
		$body_tpl->setVariable('LBL_USER_KICKED', $lng->txt('user_kicked'));
		$body_tpl->setVariable('LBL_USER_INVITED_SELF', $lng->txt('user_invited_self'));
		$body_tpl->setVariable('LBL_PRIVATE_ROOM_CLOSED', $lng->txt('private_room_closed'));
		$body_tpl->setVariable('LBL_PRIVATE_ROOM_ENTERED', $lng->txt('private_room_entered'));
		$body_tpl->setVariable('LBL_PRIVATE_ROOM_LEFT', $lng->txt('private_room_left'));
		$body_tpl->setVariable('LBL_PRIVATE_ROOM_ENTERED_USER', $lng->txt('private_room_entered_user'));
		$body_tpl->setVariable('LBL_KICKED_FROM_PRIVATE_ROOM', $lng->txt('kicked_from_private_room'));
		$body_tpl->setVariable('LBL_OK', $lng->txt('ok'));
		$body_tpl->setVariable('LBL_CANCEL', $lng->txt('cancel'));
		$body_tpl->setVariable('LBL_WHISPER_TO', $lng->txt('whisper_to'));
		$body_tpl->setVariable('LBL_SPEAK_TO', $lng->txt('speak_to'));

		$body_tpl->setVariable('LBL_USER_IN_ROOM', $lng->txt('user_in_room'));
		$body_tpl->setVariable('LBL_USER_IN_ILIAS', $lng->txt('user_in_ilias'));

		$body_tpl->setVariable('LBL_HISTORY_CLEARED', $lng->txt('history_cleared'));
		$body_tpl->setVariable('LBL_CLEAR_ROOM_HISTORY', $lng->txt('clear_room_history'));
		$body_tpl->setVariable('LBL_CLEAR_ROOM_HISTORY_QUESTION', $lng->txt('clear_room_history_question'));

		$body_tpl->setVariable('LBL_LAYOUT', $lng->txt('layout'));
		$body_tpl->setVariable('LBL_SHOW_SETTINGS', $lng->txt('show_settings'));
		$body_tpl->setVariable('LBL_HIDE_SETTINGS', $lng->txt('hide_settings'));
		$body_tpl->setVariable('LBL_NO_FURTHER_USERS', $lng->txt('no_further_users'));
		$body_tpl->setVariable('LBL_USERS', $lng->txt('users'));
		$body_tpl->setVariable('LBL_END_WHISPER', $lng->txt('end_whisper'));

		$content = $body_tpl->get() . $chatblock->getRoomSelect();
		$this->setDataSection($content);
	}

	/**

	 */
	protected function getMessages()
	{
		/**
		 * @var $ilAccess ilAccessHandler
		 * @var $ilUser   ilObjUser
		 * @var $lng	  ilLanguage
		 */
		global $ilAccess, $ilUser, $lng;

		$result = new stdClass();

		/**
		 * @var $object ilObjChatroom
		 */
		$object = ilObjectFactory::getInstanceByRefId((int)$_REQUEST['ref_id'], false);
		if(!$object || !$ilAccess->checkAccess('read', '', $_REQUEST['ref_id']))
		{
			ilObjUser::_writePref
			(
				$ilUser->getId(), 'chatviewer_last_selected_room',
				0
			);

			$result->ok       = false;
			$result->errormsg = $lng->txt('msg_no_perm_read');
			echo ilJsonUtil::encode($result);
			exit;
		}

		require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
		$room = ilChatroom::byObjectId($object->getId());

		$block = new ilChatroomBlock();
		$msg   = $block->getMessages($room);

		$ilUser->setPref
		(
			'chatviewer_last_selected_room',
			$object->getRefId()
		);
		ilObjUser::_writePref
		(
			$ilUser->getId(), 'chatviewer_last_selected_room',
			$object->getRefId()
		);

		$result->messages = array_reverse($msg);
		$result->ok       = true;

		include_once 'Services/JSON/classes/class.ilJsonUtil.php';
		echo ilJsonUtil::encode($result);
		exit;
	}
}
