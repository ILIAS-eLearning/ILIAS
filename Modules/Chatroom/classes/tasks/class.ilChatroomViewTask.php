<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
require_once 'Modules/Chatroom/classes/class.ilChatroomUser.php';

/**
 * Class ilChatroomViewTask
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomViewTask extends ilChatroomTaskHandler
{
	/**
	 * @var ilChatroomObjectGUI
	 */
	private $gui;

	/**
	 * @param ilChatroomObjectGUI $gui
	 */
	public function __construct(ilChatroomObjectGUI $gui)
	{
		$this->gui = $gui;
	}

	/**
	 * Calls ilUtil::sendFailure method using given $message as parameter.
	 * @param string $message
	 */
	private function cancelJoin($message)
	{
		ilUtil::sendFailure($message);
	}

	/**
	 * Prepares and displays chatroom and connects user to it.
	 * @param ilChatroom     $room
	 * @param ilChatroomUser $chat_user
	 */
	private function showRoom(ilChatroom $room, ilChatroomUser $chat_user)
	{
		/**
		 * @var $tpl                 ilTemplate    $tpl
		 * @var $ilUser              ilObjUser
		 * @var $ilCtrl              ilCtrl
		 * @var $rbacsystem          ilRbacSystem
		 * @var $lng                 ilLanguage
		 * @var $ilNavigationHistory ilNavigationHistory
		 */
		global $tpl, $ilUser, $ilCtrl, $rbacsystem, $lng, $ilNavigationHistory;

		if(!ilChatroom::checkUserPermissions('read', $this->gui->ref_id))
		{
			$ilCtrl->setParameterByClass('ilrepositorygui', 'ref_id', ROOT_FOLDER_ID);
			$ilCtrl->redirectByClass('ilrepositorygui', '');
		}

		$user_id = $chat_user->getUserId($ilUser);

		$ilNavigationHistory->addItem($_GET['ref_id'], $ilCtrl->getLinkTargetByClass('ilrepositorygui', 'view'), 'chtr');

		if($room->isUserBanned($user_id))
		{
			$this->cancelJoin($lng->txt('banned'));
			return;
		}

		$scope     = $room->getRoomId();
		$connector = $this->gui->getConnector();
		$response  = @$connector->connect($scope, $user_id);

		if(!$response)
		{
			ilUtil::sendFailure($lng->txt('unable_to_connect'), true);
			$ilCtrl->redirectByClass('ilinfoscreengui', 'info');
		}

		if(!$room->isSubscribed($chat_user->getUserId()) && $room->connectUser($chat_user))
		{
			$messageObject = array(
				'type'      => 'connected',
				'users'     => array(
					array(
						'login' => $chat_user->getUsername(),
						'id'    => $user_id,
					),
				),
				'timestamp' => time() * 1000
			);
			$message       = json_encode($messageObject);
			$connector->sendMessage(
				$scope,
				$message
			);

			$room->addHistoryEntry($messageObject);
		}

		$connection_info    = json_decode($response);
		$settings           = $connector->getSettings();
		$known_private_room = $room->getActivePrivateRooms($ilUser->getId());

		$initial                        = new stdClass();
		$initial->users                 = $room->getConnectedUsers();
		$initial->private_rooms         = array_values($known_private_room);
		$initial->redirect_url          = $ilCtrl->getLinkTarget($this->gui, 'view-lostConnection', '', false, false);
		$initial->private_rooms_enabled = (boolean)$room->getSetting('private_rooms_enabled');

		$initial->userinfo = array(
			'moderator' => $rbacsystem->checkAccess('moderate', (int)$_GET['ref_id']),
			'userid'    => $chat_user->getUserId()
		);

		$smileys = array();

		include_once('Modules/Chatroom/classes/class.ilChatroomSmilies.php');

		if($settings->getSmiliesEnabled())
		{
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

			$initial->smileys = $smileys;
		}
		else
		{
			$initial->smileys = '{}';
		}

		$initial->messages = array();

		if(isset($_REQUEST['sub']))
		{
			if($known_private_room[$_REQUEST['sub']])
			{
				if(!$room->isAllowedToEnterPrivateRoom($chat_user->getUserId(), $_REQUEST['sub']))
				{
					$initial->messages[] = array(
						'type'    => 'error',
						'message' => $lng->txt('not_allowed_to_enter'),
					);
				}
				else
				{
					$scope          = $room->getRoomId();
					$params         = array();
					$params['user'] = $chat_user->getUserId();
					$params['sub']  = $_REQUEST['sub'];

					$params['message'] = json_encode(
						array(
							'type' => 'private_room_entered',
							'user' => $user_id
						)
					);

					$query     = http_build_query($params);
					$connector = $this->gui->getConnector();
					$response  = $connector->enterPrivateRoom($scope, $query);

					$responseObject = json_decode($response);

					if($responseObject->success == true)
					{
						$room->subscribeUserToPrivateRoom($params['sub'], $params['user']);
					}

					$message = json_encode(array(
						'type' => 'private_room_entered',
						'user' => $params['user'],
						'sub'  => $params['sub']
					));

					$connector->sendMessage($room->getRoomId(), $message, array('public' => 1, 'sub' => $params['sub']));

					$initial->enter_room = $_REQUEST['sub'];
					$initial->messages[] = array(
						'type'     => 'notice',
						'user'     => $params['user'],
						'sub'      => $params['sub'],
						'entersub' => 1
					);
				}

				if($_SESSION['show_invitation_message'])
				{
					$initial->messages[] = array(
						'type'    => 'notice',
						'message' => $lng->txt('user_invited'),
						'sub'     => $_REQUEST['sub']
					);
					unset($_SESSION['show_invitation_message']);
				}
			}
			else
			{
				$initial->messages[] = array(
					'type'    => 'error',
					'message' => $lng->txt('user_invited'),
				);
			}
		}

		if((int)$room->getSetting('display_past_msgs'))
		{
			$initial->messages = array_merge($initial->messages, array_reverse($room->getLastMessages($room->getSetting('display_past_msgs'), $chat_user)));
		}

		$roomTpl = new ilTemplate('tpl.chatroom.html', true, true, 'Modules/Chatroom');
		$roomTpl->setVariable('SESSION_ID', $connection_info->{'session-id'});
		$roomTpl->setVariable('BASEURL', $settings->getBaseURL());
		$roomTpl->setVariable('INSTANCE', $settings->getInstance());
		$roomTpl->setVariable('SCOPE', $scope);
		$roomTpl->setVariable('MY_ID', $user_id);
		$roomTpl->setVariable('INITIAL_DATA', json_encode($initial));
		$roomTpl->setVariable('POSTURL', $ilCtrl->getLinkTarget($this->gui, 'postMessage', '', true, true));

		$roomTpl->setVariable('ACTIONS', $lng->txt('actions'));
		$roomTpl->setVariable('LBL_CREATE_PRIVATE_ROOM', $lng->txt('create_private_room_label'));
		$roomTpl->setVariable('LBL_USER', $lng->txt('user'));
		$roomTpl->setVariable('LBL_USER_TEXT', $lng->txt('invite_username'));
		$roomTpl->setVariable('LBL_AUTO_SCROLL', $lng->txt('auto_scroll'));

		$roomTpl->setVariable('INITIAL_USERS', json_encode($room->getConnectedUsers()));

		$this->renderFontSettings($roomTpl, array());
		$this->renderFileUploadForm($roomTpl);
		$this->renderSendMessageBox($roomTpl);
		$this->renderLanguageVariables($roomTpl);

		$roomRightTpl = new ilTemplate('tpl.chatroom_right.html', true, true, 'Modules/Chatroom');
		$this->renderRightUsersBlock($roomRightTpl);

		require_once 'Services/UIComponent/Panel/classes/class.ilPanelGUI.php';
		$right_content_panel = ilPanelGUI::getInstance();
		$right_content_panel->setHeading($lng->txt('users'));
		$right_content_panel->setPanelStyle(ilPanelGUI::PANEL_STYLE_SECONDARY);
		$right_content_panel->setHeadingStyle(ilPanelGUI::HEADING_STYLE_BLOCK);
		$right_content_panel->setBody($roomRightTpl->get());

		$tpl->setContent($roomTpl->get());
		$tpl->setRightContent($right_content_panel->getHTML());
	}

	/**
	 * @param ilTemplate $roomTpl
	 */
	protected function renderLanguageVariables(ilTemplate $roomTpl)
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

		$js_translations = array(
			'LBL_MAINROOM' => 'chat_mainroom',
			'LBL_LEAVE_PRIVATE_ROOM' => 'leave_private_room',
			'LBL_JOIN' => 'chat_join',
			'LBL_DELETE_PRIVATE_ROOM' => 'delete_private_room',
			'LBL_INVITE_TO_PRIVATE_ROOM' => 'invite_to_private_room',
			'LBL_KICK' => 'chat_kick',
			'LBL_BAN' => 'chat_ban',
			'LBL_KICK_QUESTION' => 'kick_question',
			'LBL_BAN_QUESTION' => 'ban_question',
			'LBL_ADDRESS' => 'chat_address',
			'LBL_WHISPER' => 'chat_whisper',
			'LBL_CONNECT' => 'chat_connection_established',
			'LBL_DISCONNECT' => 'chat_connection_disconnected',
			'LBL_TO_MAINROOM' => 'chat_to_mainroom',
			'LBL_CREATE_PRIVATE_ROOM_JS' => 'chat_create_private_room_button',
			'LBL_WELCOME_TO_CHAT' => 'welcome_to_chat',
			'LBL_USER_INVITED' => 'user_invited',
			'LBL_USER_KICKED' => 'user_kicked',
			'LBL_USER_INVITED_SELF' => 'user_invited_self',
			'LBL_PRIVATE_ROOM_CLOSED' => 'private_room_closed',
			'LBL_PRIVATE_ROOM_ENTERED' => 'private_room_entered',
			'LBL_PRIVATE_ROOM_LEFT' => 'private_room_left',
			'LBL_PRIVATE_ROOM_ENTERED_USER' => 'private_room_entered_user',
			'LBL_KICKED_FROM_PRIVATE_ROOM' => 'kicked_from_private_room',
			'LBL_OK' => 'ok',
			'LBL_CANCEL' => 'cancel',
			'LBL_WHISPER_TO' => 'whisper_to',
			'LBL_SPEAK_TO' => 'speak_to',
			'LBL_HISTORY_CLEARED' => 'history_cleared',
			'LBL_CLEAR_ROOM_HISTORY' => 'clear_room_history',
			'LBL_CLEAR_ROOM_HISTORY_QUESTION' => 'clear_room_history_question',
			'LBL_END_WHISPER' => 'end_whisper',
			'LBL_SHOW_SETTINGS_JS' => 'show_settings',
			'LBL_HIDE_SETTINGS' => 'hide_settings',
			'LBL_TIMEFORMAT' => 'lang_timeformat_no_sec',
			'LBL_DATEFORMAT' => 'lang_dateformat'
		);
		foreach($js_translations as $placeholder => $lng_variable)
		{
			$roomTpl->setVariable($placeholder, json_encode($lng->txt($lng_variable)));
		}

		$roomTpl->setVariable('LBL_CREATE_PRIVATE_ROOM', $lng->txt('chat_create_private_room_button'));
		$roomTpl->setVariable('LBL_CREATE_PRIVATE_ROOM_TEXT', $lng->txt('create_private_room_text'));
		$roomTpl->setVariable('LBL_LAYOUT', $lng->txt('layout'));
		$roomTpl->setVariable('LBL_SHOW_SETTINGS', $lng->txt('show_settings'));
		$roomTpl->setVariable('LBL_USER_IN_ROOM', $lng->txt('user_in_room'));
		$roomTpl->setVariable('LBL_USER_IN_ILIAS', $lng->txt('user_in_ilias'));
	}

	/**
	 * @param ilTemplate $roomTpl
	 */
	protected function renderRightUsersBlock(ilTemplate $roomTpl)
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

		$roomTpl->setVariable('LBL_NO_FURTHER_USERS', $lng->txt('no_further_users'));
	}

	/**
	 * @param ilTemplate $roomTpl
	 */
	protected function renderSendMessageBox(ilTemplate $roomTpl)
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

		$roomTpl->setVariable('LBL_MESSAGE', $lng->txt('chat_message'));
		$roomTpl->setVariable('LBL_TOALL', $lng->txt('chat_message_to_all'));
		$roomTpl->setVariable('LBL_OPTIONS', $lng->txt('chat_message_options'));
		$roomTpl->setVariable('LBL_DISPLAY', $lng->txt('chat_message_display'));
		$roomTpl->setVariable('LBL_SEND', $lng->txt('send'));
	}

	/**
	 * Prepares and displays name selection.
	 * Fetches name option by calling getChatNameSuggestions method on
	 * given $chat_user object.
	 * @param ilChatroomUser $chat_user
	 */
	private function showNameSelection(ilChatroomUser $chat_user)
	{
		/**
		 * @var $lng    ilLanguage
		 * @var $ilCtrl ilCtrl
		 * @var $tpl    ilTemplate
		 */
		global $lng, $ilCtrl, $tpl;

		require_once 'Modules/Chatroom/classes/class.ilChatroomFormFactory.php';

		$name_options  = $chat_user->getChatNameSuggestions();
		$formFactory   = new ilChatroomFormFactory();
		$selectionForm = $formFactory->getUserChatNameSelectionForm($name_options);

		$ilCtrl->saveParameter($this->gui, 'sub');

		$selectionForm->addCommandButton('view-joinWithCustomName', $lng->txt('enter'));
		$selectionForm->setFormAction(
			$ilCtrl->getFormAction($this->gui, 'view-joinWithCustomName')
		);

		$tpl->setVariable('ADM_CONTENT', $selectionForm->getHtml());
	}

	/**
	 * Adds CSS and JavaScript files that should be included in the header.
	 */
	private function setupTemplate()
	{
		/**
		 * @var $tpl ilTemplate
		 */
		global $tpl;

		$tpl->addJavaScript('Modules/Chatroom/js/colorpicker/jquery.colorPicker.js');
		$tpl->addJavaScript('Modules/Chatroom/js/chat.js');
		$tpl->addJavaScript('Modules/Chatroom/js/iliaschat.jquery.js');
		$tpl->addJavaScript('Services/jQuery/js/jquery.outside.events.min.js');
		$tpl->addJavaScript('Modules/Chatroom/js/json2.js');

		$tpl->addJavaScript('./Services/UIComponent/AdvancedSelectionList/js/AdvancedSelectionList.js');

		$tpl->addCSS('Modules/Chatroom/js/colorpicker/colorPicker.css');
		$tpl->addCSS('Modules/Chatroom/templates/default/style.css');
	}

	/**
	 * Joins user to chatroom with custom username, fetched from
	 * $_REQUEST['custom_username_text'] or by calling buld method.
	 * If sucessful, $this->showRoom method is called, otherwise
	 * $this->showNameSelection.
	 */
	public function joinWithCustomName()
	{
		/**
		 * @var $ilUser ilObjUser
		 * @var $lng    ilLanguage
		 */
		global $ilUser, $lng;

		$this->gui->switchToVisibleMode();
		$this->setupTemplate();
		$room      = ilChatroom::byObjectId($this->gui->object->getId());
		$chat_user = new ilChatroomUser($ilUser, $room);
		$failure   = false;
		$username  = '';

		if($_REQUEST['custom_username_radio'] == 'custom_username')
		{
			$username = $_REQUEST['custom_username_text'];
		}
		elseif(method_exists($chat_user, 'build' . $_REQUEST['custom_username_radio']))
		{
			$username = $chat_user->{'build' . $_REQUEST['custom_username_radio']}();
		}
		else
		{
			$failure = true;
		}

		if(!$failure && trim($username) != '')
		{
			$chat_user->setUsername($username);
			$this->showRoom($room, $chat_user);
		}
		else
		{
			ilUtil::sendFailure($lng->txt('no_username_given'));
			$this->showNameSelection($chat_user);
		}
	}

	/**
	 * Chatroom and Chatuser get prepared before $this->showRoom method
	 * is called. If custom usernames are allowed, $this->showNameSelection
	 * method is called if user isn't already registered in the Chatroom.
	 * @param string $method
	 */
	public function executeDefault($method)
	{
		/**
		 * @var $ilUser ilObjUser
		 * @var $lng    ilLanguage
		 * @var $ilCtrl ilCtrl
		 */
		global $ilUser, $lng, $ilCtrl;

		include_once 'Modules/Chatroom/classes/class.ilChatroom.php';

		ilChatroom::checkUserPermissions('read', $this->gui->ref_id);

		$this->gui->switchToVisibleMode();
		$this->setupTemplate();

		$chatSettings = new ilSetting('chatroom');
		if(!$chatSettings->get('chat_enabled'))
		{
			$ilCtrl->redirect($this->gui, 'settings-general');
			exit;
		}

		$room = ilChatroom::byObjectId($this->gui->object->getId());

		if(!$room->getSetting('allow_anonymous') && $ilUser->isAnonymous())
		{
			$this->cancelJoin($lng->txt('anonymous_not_allowed'));
			return;
		}

		$chat_user = new ilChatroomUser($ilUser, $room);

		if($room->getSetting('allow_custom_usernames'))
		{
			if($room->isSubscribed($chat_user->getUserId()))
			{
				$chat_user->setUsername($chat_user->getUsername());
				$this->showRoom($room, $chat_user);
			}
			else
			{
				$this->showNameSelection($chat_user);
			}
		}
		else
		{
			$chat_user->setUsername($ilUser->getLogin());
			$this->showRoom($room, $chat_user);
		}
	}

	/**
	 *
	 */
	public function invitePD()
	{
		/**
		 * @var $ilUser ilObjUser
		 * @var $ilCtrl ilCtrl
		 */
		global $ilUser, $ilCtrl;

		$chatSettings = new ilSetting('chatroom');
		if(!$chatSettings->get('chat_enabled'))
		{
			$ilCtrl->redirect($this->gui, 'settings-general');
		}

		$room      = ilChatroom::byObjectId($this->gui->object->getId());
		$chat_user = new ilChatroomUser($ilUser, $room);
		$user_id   = $_REQUEST['usr_id'];
		$connector = $this->gui->getConnector();
		$title     = $room->getUniquePrivateRoomTitle($chat_user->getUsername());
		$response  = $connector->createPrivateRoom($room, $title, $chat_user);
		$connector->inviteToPrivateRoom($room, $response->id, $ilUser, $user_id);
		$room->sendInvitationNotification($this->gui, $chat_user, $user_id, $response->id);

		$_REQUEST['sub'] = $response->id;

		$_SESSION['show_invitation_message'] = $user_id;

		$ilCtrl->setParameter($this->gui, 'sub', $response->id);
		$ilCtrl->redirect($this->gui, 'view');
	}

	/**
	 * Prepares given $roomTpl with font settings using given $defaultSettings
	 * among other things.
	 * @param ilTemplate $roomTpl
	 * @param array      $defaultSettings
	 */
	private function renderFontSettings(ilTemplate $roomTpl, array $defaultSettings)
	{
		/**
		 * @var $lng    ilLanguage
		 * @var $ilCtrl ilCtrl
		 */
		global $lng, $ilCtrl;

		$font_family = array(
			'sans'      => 'Sans Serif',
			'times'     => 'Times',
			'monospace' => 'Monospace',
		);

		$font_style = array(
			'italic'     => $lng->txt('italic'),
			'bold'       => $lng->txt('bold'),
			'normal'     => $lng->txt('normal'),
			'underlined' => $lng->txt('underlined'),
		);

		$font_size = array(
			'small'  => $lng->txt('small'),
			'normal' => $lng->txt('normal'),
			'large'  => $lng->txt('large')
		);

		$default_font_color = '#000000';

		$default_font_family = (
		isset($defaultSettings['font_family']) &&
			isset($font_family[$defaultSettings['font_family']]) ?
			$defaultSettings['font_family'] : 'sans'
		);

		$default_font_style = (
		isset($defaultSettings['font_style']) &&
			isset($font_family[$defaultSettings['font_style']]) ?
			$defaultSettings['font_style'] : 'normal'
		);

		$default_font_size = (
		isset($defaultSettings['font_size']) &&
			isset($font_family[$defaultSettings['font_size']]) ?
			$defaultSettings['font_size'] : 'normal'
		);

		$roomTpl->setVariable('VAL_FONTCOLOR', $default_font_color);

		foreach($font_family as $font => $label)
		{
			$roomTpl->setCurrentBlock('chat_fontfamily');
			$roomTpl->setVariable('VAL_FONTFAMILY', $font);
			$roomTpl->setVariable('LBL_FONTFAMILY', $label);
			$roomTpl->setVariable(
				'SELECTED_FONTFAMILY', $font == $default_font_family ?
					'selected="selected"' : ''
			);
			$roomTpl->parseCurrentBlock();
		}

		foreach($font_style as $font => $label)
		{
			$roomTpl->setCurrentBlock('chat_fontstyle');
			$roomTpl->setVariable('VAL_FONTSTYLE', $font);
			$roomTpl->setVariable('LBL_FONTSTYLE', $label);
			$roomTpl->setVariable(
				'SELECTED_FONTSTYLE', $font == $default_font_style ?
					'selected="selected"' : ''
			);
			$roomTpl->parseCurrentBlock();
		}

		foreach($font_size as $font => $label)
		{
			$roomTpl->setCurrentBlock('chat_fontsize');
			$roomTpl->setVariable('VAL_FONTSIZE', $font);
			$roomTpl->setVariable('LBL_FONTSIZE', $label);
			$roomTpl->setVariable(
				'SELECTED_FONTSIZE',
				$font == $default_font_size ? 'selected="selected"' : ''
			);
			$roomTpl->parseCurrentBlock();
		}

		$roomTpl->setVariable('LBL_FONTCOLOR', $lng->txt('fontcolor'));
		$roomTpl->setVariable('LBL_FONTFAMILY', $lng->txt('fontfamily'));
		$roomTpl->setVariable('LBL_FONTSTYLE', $lng->txt('fontstyle'));
		$roomTpl->setVariable('LBL_FONTSIZE', $lng->txt('fontsize'));

		$logoutLink = $ilCtrl->getLinkTarget($this->gui, 'view-logout');
		$roomTpl->setVariable('LOGOUT_LINK', $logoutLink);
	}

	/**
	 * Prepares Fileupload form and displays it.
	 * @param ilTemplate $roomTpl
	 */
	public function renderFileUploadForm(ilTemplate $roomTpl)
	{
		// @todo: Not implemented yet
		return;

		require_once 'Modules/Chatroom/classes/class.ilChatroomFormFactory.php';
		$formFactory = new ilChatroomFormFactory();
		$file_upload = $formFactory->getFileUploadForm();
		//$file_upload->setFormAction( $ilCtrl->getFormAction($this->gui, 'UploadFile-uploadFile') );
		$roomTpl->setVariable('FILE_UPLOAD', $file_upload->getHTML());
	}

	/**
	 * Performs logout.
	 */
	public function logout()
	{
		/**
		 * @var $tree   ilTree
		 * @var $ilCtrl ilCtrl
		 */
		global $tree, $ilCtrl;

		/**
		 * @todo logout user from room
		 */
		$pid = $tree->getParentId($this->gui->getRefId());
		$ilCtrl->setParameterByClass('ilrepositorygui', 'ref_id', $pid);
		$ilCtrl->redirectByClass('ilrepositorygui', '');
	}

	/**
	 *
	 */
	public function lostConnection()
	{
		/**
		 * @var $lng    ilLanguage
		 * @var $ilCtrl ilCtrl
		 */
		global $lng, $ilCtrl;

		ilUtil::sendFailure($lng->txt('lost_connection'), true);
		$ilCtrl->redirectByClass('ilinfoscreengui', 'info');
	}
}
