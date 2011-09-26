<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilChatroomViewTask
 *
 * @author Jan Posselt <jposselt@databay.de>
 * @version $Id$
 *
 * @ingroup ModulesChatroom
 */
class ilChatroomViewTask extends ilDBayTaskHandler {

	private $gui;

	/**
	 * Constructor
	 *
	 * Requires ilChatroom and ilChatroomUser.
	 * Sets $this->gui using given $gui.
	 *
	 * @param ilDBayObjectGUI $gui
	 */
	public function __construct(ilDBayObjectGUI $gui)
	{
	    $this->gui = $gui;
	    require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
	    require_once 'Modules/Chatroom/classes/class.ilChatroomUser.php';
	}

	/**
	 * Calls ilUtil::sendFailure method using given $message as parameter.
	 *
	 * @param string $message
	 */
	private function cancelJoin($message)
	{
	    ilUtil::sendFailure($message);
	}

	/**
	 * Prepares and displays chatroom and connects user to it.
	 *
	 * @global iltemplate $tpl
	 * @global ilObjUser $ilUser
	 * @global ilCtrl2 $ilCtrl
	 * @global ilLanguage $lng
	 * @param ilChatroom $room
	 * @param ilChatroomUser $chat_user
	 */
	private function showRoom(ilChatroom $room, ilChatroomUser $chat_user)
	{
	    global $tpl, $ilUser, $ilCtrl, $lng, $ilAccess, $lng, $ilNavigationHistory;

	    if ( !ilChatroom::checkUserPermissions( 'read', $this->gui->ref_id ) )
	    {
		ilUtil::redirect("repository.php");
	    }

	    $user_id = $chat_user->getUserId($ilUser);

	    $ilNavigationHistory->addItem($_GET['ref_id'], 'repository.php?cmd=view&ref_id='.$_GET['ref_id'], 'chtr');
	    
	    if( $room->isUserBanned($user_id) )
	    {
		$this->cancelJoin( $lng->txt('banned') );
		return;
	    }

	    $scope	= $room->getRoomId();
	    $connector	= $this->gui->getConnector();
	    $response	= @$connector->connect($scope, $user_id);

	    if( !$response )
	    {
		    ilUtil::sendFailure($lng->txt('unable_to_connect'), true);
		    $link = $ilCtrl->getLinkTargetByClass(
			    'ilinfoscreengui', 'info', '', false, false
		    );
		    ilUtil::redirect($link);
		    exit;
	    }

	    if( !$room->isSubscribed($chat_user->getUserId()) && $room->connectUser($chat_user) )
	    {
		    $messageObject = array(
			'type'  => 'connected',
			'users' => array(
				array(
				    'login'  => $chat_user->getUsername(),
				    'id'    => $user_id,
				),
			),
			'timestamp' => time() * 1000//date( 'c' )
		    );
		    $message = json_encode($messageObject);
		    $connector->sendMessage(
			$scope,
			$message
		    );

		    if( true || $room->getSetting('enable_history') ) {
			$room->addHistoryEntry($messageObject);
		    }
	    }

	    $connection_info	= json_decode($response);
	    $settings		= $connector->getSettings();
	    $known_private_room = $room->getActivePrivateRooms($ilUser->getId());

	    $initial = new stdClass();
	    $initial->users = $room->getConnectedUsers();
	    $initial->private_rooms = array_values($known_private_room);
	    $initial->redirect_url = $ilCtrl->getLinkTarget($this->gui, 'view-lostConnection', '', false, false);
	    $initial->private_rooms_enabled = (boolean)$room->getSetting('private_rooms_enabled');

	    $initial->userinfo = array(
		'moderator' => $ilAccess->checkAccess('moderate', '', $_GET['ref_id']),
		'userid' => $chat_user->getUserId()
	    );

	    $smileys = array();

	    include_once('Modules/Chatroom/classes/class.ilChatroomSmilies.php');

	    if ($settings->getSmiliesEnabled()) {
		$smileys_array = ilChatroomSmilies::_getSmilies();

		foreach( $smileys_array as $smiley_array )
		{
		    foreach( $smiley_array as $key => $value )
		    {
			if( $key == 'smiley_keywords' )
			{
			    $new_keys = explode("\n", $value);
			}

			if( $key == 'smiley_fullpath' )
			{
			    $new_val = $value;
			}
		    }

		    foreach( $new_keys as $new_key )
		    {
			$smileys[$new_key] = $new_val;
		    }
		}

		$initial->smileys = $smileys;
	    }
	    else {
		$initial->smileys = '{}';
	    }
	    
	    $initial->messages	= array();

	    if( isset($_REQUEST['sub']) )
	    {
		if( $known_private_room[$_REQUEST['sub']] )
		{
		    if( !$room->isAllowedToEnterPrivateRoom( $chat_user->getUserId(), $_REQUEST['sub'] ) )
		    {
			$initial->messages[] = array(
			    'type'	=> 'error',
			    'message'	=> $lng->txt('not_allowed_to_enter'),
			);
		    }
		    else
		    {
			$scope		    = $room->getRoomId();
			$params		    = array();
			$params['user']	    = $chat_user->getUserId();
			$params['sub']	    = $_REQUEST['sub'];

			$params['message']  = json_encode(
			    array( 'type' => 'private_room_entered',
				   'user' => $user_id
			    )
			);

			$query		= http_build_query( $params );
			$connector	= $this->gui->getConnector();
			$response	= $connector->enterPrivateRoom( $scope, $query );

			$responseObject = json_decode( $response );

			if( $responseObject->success == true )
			{
			    $room->subscribeUserToPrivateRoom( $params['sub'], $params['user'] );
			}

			$message = json_encode( array(
			    'type'  => 'private_room_entered',
			    'user'  => $params['user'],
			    'sub'   => $params['sub']
			));

			$connector->sendMessage( $room->getRoomId(), $message, array('public' => 1, 'sub' => $params['sub']) );

			$initial->enter_room = $_REQUEST['sub'];
			$initial->messages[] = array(
			    'type'	=> 'notice',
			    'user'	=> $params['user'],
			    'sub'	=> $params['sub'],
			    'entersub'	=> 1
			);
		    }
		    
		    if ($_SESSION['show_invitation_message']) {
			$initial->messages[] = array(
			    'type'  => 'notice',
			    'message' => $lng->txt('user_invited'),
			    'sub' => $_REQUEST['sub']
			);
			unset ($_SESSION['show_invitation_message']);
		    }
		}
		else
		{
		    $initial->messages[] = array(
			'type'	=> 'error',
			'message'	=> $lng->txt('user_invited'),
		    );
		}
	    }

	    if ((int)$room->getSetting('display_past_msgs')) {
		$initial->messages = array_merge($initial->messages, array_reverse($room->getLastMessages($room->getSetting('display_past_msgs'))));
	    }
	    
	    //var_dump($initial->messages);
	    
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
	    $this->renderRightUsersDock($roomTpl);

	    $tpl->setVariable('ADM_CONTENT', $roomTpl->get());
	}

	protected function renderRightUsersDock($roomTpl) {
		global $lng;

		$roomTpl->setVariable('LBL_MAINROOM', $lng->txt('chat_mainroom'));
		$roomTpl->setVariable('LBL_LEAVE_PRIVATE_ROOM', $lng->txt('leave_private_room'));
		$roomTpl->setVariable('LBL_JOIN', $lng->txt('chat_join'));
		$roomTpl->setVariable('LBL_DELETE_PRIVATE_ROOM', $lng->txt('delete_private_room'));
		$roomTpl->setVariable('LBL_INVITE_TO_PRIVATE_ROOM', $lng->txt('invite_to_private_room'));
		$roomTpl->setVariable('LBL_KICK', $lng->txt('chat_kick'));
		$roomTpl->setVariable('LBL_BAN', $lng->txt('chat_ban'));
		$roomTpl->setVariable('LBL_KICK_QUESTION', $lng->txt('kick_question'));
		$roomTpl->setVariable('LBL_BAN_QUESTION', $lng->txt('ban_question'));
		$roomTpl->setVariable('LBL_ADDRESS', $lng->txt('chat_address'));
		$roomTpl->setVariable('LBL_WHISPER', $lng->txt('chat_whisper'));
		$roomTpl->setVariable('LBL_CONNECT', $lng->txt('chat_connection_established'));
		$roomTpl->setVariable('LBL_DISCONNECT', $lng->txt('chat_connection_disconnected'));
		$roomTpl->setVariable('LBL_INVITE_USERS', $lng->txt('chat_invite_users'));
		$roomTpl->setVariable('LBL_USER_TAB', $lng->txt('chat_right_box_user'));
		$roomTpl->setVariable('LBL_PRIVATE_ROOM', $lng->txt('chat_private_room'));
		$roomTpl->setVariable('LBL_CREATE_NEW_PRIVATE_ROOM', $lng->txt('chat_create_new_private_room'));
		$roomTpl->setVariable('LBL_TO_MAINROOM', $lng->txt('chat_to_mainroom'));
		$roomTpl->setVariable('LBL_CREATE_PRIVATE_ROOM', $lng->txt('chat_create_private_room_button'));
		$roomTpl->setVariable('LBL_CREATE_PRIVATE_ROOM_TEXT', $lng->txt('create_private_room_text'));
		
		$roomTpl->setVariable('LBL_WELCOME_TO_CHAT', $lng->txt('welcome_to_chat'));
		$roomTpl->setVariable('LBL_USER_INVITED', $lng->txt('user_invited'));
		$roomTpl->setVariable('LBL_USER_KICKED', $lng->txt('user_kicked'));
		$roomTpl->setVariable('LBL_USER_INVITED_SELF', $lng->txt('user_invited_self'));
		$roomTpl->setVariable('LBL_PRIVATE_ROOM_CLOSED', $lng->txt('private_room_closed'));
		$roomTpl->setVariable('LBL_PRIVATE_ROOM_ENTERED', $lng->txt('private_room_entered'));
		$roomTpl->setVariable('LBL_PRIVATE_ROOM_LEFT', $lng->txt('private_room_left'));
		$roomTpl->setVariable('LBL_PRIVATE_ROOM_ENTERED_USER', $lng->txt('private_room_entered_user'));
		$roomTpl->setVariable('LBL_KICKED_FROM_PRIVATE_ROOM', $lng->txt('kicked_from_private_room'));
		$roomTpl->setVariable('LBL_OK', $lng->txt('ok'));
		$roomTpl->setVariable('LBL_CANCEL', $lng->txt('cancel'));
		$roomTpl->setVariable('LBL_WHISPER_TO', $lng->txt('whisper_to'));
		$roomTpl->setVariable('LBL_SPEAK_TO', $lng->txt('speak_to'));
		
		$roomTpl->setVariable('LBL_USER_IN_ROOM', $lng->txt('user_in_room'));
		$roomTpl->setVariable('LBL_USER_IN_ILIAS', $lng->txt('user_in_ilias'));
		
		$roomTpl->setVariable('LBL_HISTORY_CLEARED', $lng->txt('history_cleared'));
		$roomTpl->setVariable('LBL_CLEAR_ROOM_HISTORY', $lng->txt('clear_room_history'));
		$roomTpl->setVariable('LBL_CLEAR_ROOM_HISTORY_QUESTION', $lng->txt('clear_room_history_question'));
		
		$roomTpl->setVariable('LBL_LAYOUT', $lng->txt('layout'));
		$roomTpl->setVariable('LBL_SHOW_SETTINGS', $lng->txt('show_settings'));
		$roomTpl->setVariable('LBL_HIDE_SETTINGS', $lng->txt('hide_settings'));
		$roomTpl->setVariable('LBL_NO_FURTHER_USERS', $lng->txt('no_further_users'));
		$roomTpl->setVariable('LBL_USERS', $lng->txt('users'));
	}

	protected function renderSendMessageBox($roomTpl) {
		global $lng;

		$roomTpl->setVariable('LBL_MESSAGE', $lng->txt('chat_message'));
		$roomTpl->setVariable('LBL_TOALL', $lng->txt('chat_message_to_all'));
		$roomTpl->setVariable('LBL_OPTIONS', $lng->txt('chat_message_options'));
		$roomTpl->setVariable('LBL_DISPLAY', $lng->txt('chat_message_display'));
		$roomTpl->setVariable('LBL_SEND', $lng->txt('send'));
	}

	/**
	 * Prepares and displays name selection.
	 *
	 * Fetches name option by calling getChatNameSuggestions method on
	 * given $chat_user object.
	 *
	 * @global ilLanguage $lng
	 * @global ilCtrl2 $ilCtrl
	 * @global iltemplate $tpl
	 * @param ilChatroomUser $chat_user
	 */
	private function showNameSelection(ilChatroomUser $chat_user) {
		global $lng, $ilCtrl, $tpl;

		require_once 'Modules/Chatroom/classes/class.ilChatroomFormFactory.php';

		$name_options = $chat_user->getChatNameSuggestions();
		$formFactory = new ilChatroomFormFactory();
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
	 *
	 * @global ilTemplate $tpl
	 */
	private function setupTemplate() {
		global $tpl;

		$tpl->addJavaScript('./Modules/Scorm2004/scripts/questions/jquery.js');
		$tpl->addJavaScript('Modules/Chatroom/js/colorpicker/jquery.colorPicker.js');
		$tpl->addJavaScript('Modules/Chatroom/js/chat.js');
		$tpl->addJavaScript('Modules/Chatroom/js/iliaschat.jquery.js');
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
	 *
	 * @global ilObjUser $ilUser
	 * @global ilLanguage $lng
	 */
	public function joinWithCustomName() {
		global $ilUser, $lng;

		$this->gui->switchToVisibleMode();
		$this->setupTemplate();
		$room = ilChatroom::byObjectId($this->gui->object->getId());
		$chat_user = new ilChatroomUser($ilUser, $room);

		if ($_REQUEST['custom_username_radio'] == 'custom_username') {
			$username = $_REQUEST['custom_username_text'];
		} elseif (method_exists($chat_user, 'build' . $_REQUEST['custom_username_radio'])) {
			$username = $chat_user->{'build' . $_REQUEST['custom_username_radio']}();
		} else {
			$failure = true;
		}

		if (!$failure && trim($username) != '') {
			$chat_user->setUsername($username);
			$this->showRoom($room, $chat_user);
		} else {
			ilUtil::sendFailure($lng->txt('no_username_given'));
			$this->showNameSelection($chat_user);
		}
	}

	/**
	 * Chatroom and Chatuser get prepared before $this->showRoom method
	 * is called. If custom usernames are allowed, $this->showNameSelection
	 * method is called if user isn't already registered in the Chatroom.
	 *
	 * @global ilObjUser $ilUser
	 * @global ilLanguage $lng
	 * @param string $method
	 */
	public function executeDefault($method)
	{
		global $ilUser, $lng, $ilCtrl;

		include_once 'Modules/Chatroom/classes/class.ilChatroom.php';

		ilChatroom::checkUserPermissions( 'read', $this->gui->ref_id );

		$this->gui->switchToVisibleMode();
		$this->setupTemplate();

		$chatSettings = new ilSetting('chatroom');
		if (!$chatSettings->get('chat_enabled')) {
			$ilCtrl->redirect($this->gui, 'settings-general');
			exit;
		}

		$room = ilChatroom::byObjectId($this->gui->object->getId());

		if (!$room->getSetting('allow_anonymous') && $ilUser->getId() == ANONYMOUS_USER_ID) {
			$this->cancelJoin($lng->txt('anonymous_not_allowed'));
			return;
		}

		$chat_user = new ilChatroomUser($ilUser, $room);

		if ($room->getSetting('allow_custom_usernames')) {
			if ($room->isSubscribed($chat_user->getUserId())) {
				$chat_user->setUsername($chat_user->getUsername());
				$this->showRoom($room, $chat_user);
			} else {
				$this->showNameSelection($chat_user);
			}
		} else {
			$chat_user->setUsername($ilUser->getLogin());
			$this->showRoom($room, $chat_user);
		}
	}

	public function invitePD() {
		global $ilUser,$ilCtrl,$lng;

		$chatSettings = new ilSetting('chatroom');
		if (!$chatSettings->get('chat_enabled')) {
			$ilCtrl->redirect($this->gui, 'settings-general');
			exit;
		}

		$room = ilChatroom::byObjectId($this->gui->object->getId());
		$chat_user = new ilChatroomUser($ilUser, $room);
		$user_id = $_REQUEST['usr_id'];

		$connector = $this->gui->getConnector();

		if (!$room->isSubscribed($chat_user->getUserId()) && $room->connectUser($chat_user)) {
			$connector->sendMessage(
			$scope, $message = json_encode(
			array(
                        'type' => 'connected',
                        'users' => array(
			array(
                                'login' => $chat_user->getUsername(),
                                'id' => $user_id,
			),
			),
                        'timestamp' => time() * 1000
			)
			)
			);
		}

		$title = $room->getUniquePrivateRoomTitle($chat_user->getUsername());
		$response = $connector->createPrivateRoom($room, $title, $chat_user);
		$connector->inviteToPrivateRoom($room, $response->id, $ilUser, $user_id);

		$room->sendInvitationNotification($this->gui, $ilUser->getId(), $user_id, $response->id);

		$_REQUEST['sub'] = $response->id;

		//ilUtil::sendInfo($lng->txt('user_invited'), true);
		$_SESSION['show_invitation_message'] = $user_id;

		$ilCtrl->setParameter($this->gui, 'sub', $response->id);
		$ilCtrl->redirect($this->gui, 'view');

	}

	/**
	 * Prepares given $roomTpl with font settings using given $defaultSettings
	 * among other things.
	 *
	 * @global ilLanguage $lng
	 * @global ilCtrl2 $ilCtrl
	 * @param ilTemplate $roomTpl
	 * @param array $defaultSettings
	 */
	private function renderFontSettings($roomTpl, $defaultSettings) {
		global $lng, $ilCtrl;

		$font_family = array(
            'sans' => 'Sans Serif',
            'times' => 'Times',
            'monospace' => 'Monospace',
		);

		$font_style = array(
            'italic' => $lng->txt('italic'),
            'bold' => $lng->txt('bold'),
            'normal' => $lng->txt('normal'),
            'underlined' => $lng->txt('underlined'),
		);

		$font_size = array(
            'small' => $lng->txt('small'),
            'normal' => $lng->txt('normal'),
            'large' => $lng->txt('large')
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

		foreach ($font_family as $font => $label) {
			$roomTpl->setCurrentBlock('chat_fontfamily');
			$roomTpl->setVariable('VAL_FONTFAMILY', $font);
			$roomTpl->setVariable('LBL_FONTFAMILY', $label);
			$roomTpl->setVariable(
                    'SELECTED_FONTFAMILY', $font == $default_font_family ?
                            'selected="selected"' : ''
                            );
                            $roomTpl->parseCurrentBlock();
		}

		foreach ($font_style as $font => $label) {
			$roomTpl->setCurrentBlock('chat_fontstyle');
			$roomTpl->setVariable('VAL_FONTSTYLE', $font);
			$roomTpl->setVariable('LBL_FONTSTYLE', $label);
			$roomTpl->setVariable(
                    'SELECTED_FONTSTYLE', $font == $default_font_style ?
                            'selected="selected"' : ''
                            );
                            $roomTpl->parseCurrentBlock();
		}

		foreach ($font_size as $font => $label) {
			$roomTpl->setCurrentBlock('chat_fontsize');
			$roomTpl->setVariable('VAL_FONTSIZE', $font);
			$roomTpl->setVariable('LBL_FONTSIZE', $label);
			$roomTpl->setVariable(
                    'SELECTED_FONTSIZE', $font == $default_font_size ?
                            'selected="selected"' : ''
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
	 *
	 * @global ilCtrl2 $ilCtrl
	 * @param string $roomTpl
	 */
	public function renderFileUploadForm($roomTpl) {
		return;

		global $ilCtrl;

		require_once 'Modules/Chatroom/classes/class.ilChatroomFormFactory.php';

		$formFactory = new ilChatroomFormFactory();
		$file_upload = $formFactory->getFileUploadForm();
		//$file_upload->setFormAction( $ilCtrl->getFormAction($this->gui, 'UploadFile-uploadFile') );

		$roomTpl->setVariable('FILE_UPLOAD', $file_upload->getHTML());
	}

	/**
	 * Performs logout.
	 *
	 * @global ilTree $tree
	 */
	public function logout() {
		//global $ilCtrl, $tree;
		global $tree;

		/**
		 * @todo logout user from room
		 */
		$pid = $tree->getParentId($this->gui->getRefId());
		ilUtil::redirect('repository.php?ref_id=' . $pid);
	}

	public function lostConnection() {
		global $lng, $ilCtrl;

		ilUtil::sendFailure($lng->txt('lost_connection'), true);

		$url = $ilCtrl->getLinkTargetByClass('ilinfoscreengui', 'info', false, false, false);

		ilUtil::redirect($url);
	}
}

?>
