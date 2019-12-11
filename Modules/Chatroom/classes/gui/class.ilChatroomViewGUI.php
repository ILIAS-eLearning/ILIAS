<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
require_once 'Modules/Chatroom/classes/class.ilChatroomUser.php';

/**
 * Class ilChatroomViewGUI
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomViewGUI extends ilChatroomGUIHandler
{
    /**
     * Joins user to chatroom with custom username, fetched from
     * $_REQUEST['custom_username_text'] or by calling buld method.
     * If sucessful, $this->showRoom method is called, otherwise
     * $this->showNameSelection.
     */
    public function joinWithCustomName()
    {
        $this->gui->switchToVisibleMode();
        $this->setupTemplate();
        $room      = ilChatroom::byObjectId($this->gui->object->getId());
        $chat_user = new ilChatroomUser($this->ilUser, $room);
        $failure   = false;
        $username  = '';

        if ($_REQUEST['custom_username_radio'] == 'custom_username') {
            $username = $_REQUEST['custom_username_text'];
        } elseif (method_exists($chat_user, 'build' . $_REQUEST['custom_username_radio'])) {
            $username = $chat_user->{'build' . $_REQUEST['custom_username_radio']}();
        } else {
            $failure = true;
        }

        if (!$failure && trim($username) != '') {
            if (!$room->isSubscribed($chat_user->getUserId())) {
                $chat_user->setUsername($chat_user->buildUniqueUsername($username));
            }

            $this->showRoom($room, $chat_user);
        } else {
            ilUtil::sendFailure($this->ilLng->txt('no_username_given'));
            $this->showNameSelection($chat_user);
        }
    }

    /**
     * Adds CSS and JavaScript files that should be included in the header.
     */
    private function setupTemplate()
    {
        $this->mainTpl->addJavaScript('Modules/Chatroom/js/chat.js');
        $this->mainTpl->addJavaScript('Modules/Chatroom/js/iliaschat.jquery.js');
        $this->mainTpl->addJavaScript('libs/bower/bower_components/jquery-outside-events/jquery.ba-outside-events.min.js');

        $this->mainTpl->addJavaScript('./Services/UIComponent/AdvancedSelectionList/js/AdvancedSelectionList.js');

        $this->mainTpl->addCSS('Modules/Chatroom/templates/default/style.css');
    }

    /**
     * Prepares and displays chatroom and connects user to it.
     * @param ilChatroom     $room
     * @param ilChatroomUser $chat_user
     */
    private function showRoom(ilChatroom $room, ilChatroomUser $chat_user)
    {
        $this->redirectIfNoPermission('read');

        $user_id = $chat_user->getUserId($this->ilUser);

        $this->navigationHistory->addItem($_GET['ref_id'], $this->ilCtrl->getLinkTargetByClass('ilrepositorygui', 'view'), 'chtr');

        if ($room->isUserBanned($user_id)) {
            $this->cancelJoin($this->ilLng->txt('banned'));
            return;
        }

        $scope     = $room->getRoomId();
        $connector = $this->gui->getConnector();
        $response  = @$connector->connect($scope, $user_id);

        if (!$response) {
            ilUtil::sendFailure($this->ilLng->txt('unable_to_connect'), true);
            $this->ilCtrl->redirectByClass('ilinfoscreengui', 'info');
        }

        if (!$room->isSubscribed($chat_user->getUserId())) {
            $room->connectUser($chat_user);
        }

        $subScope = 0;
        $response = $connector->sendEnterPrivateRoom($scope, $subScope, $user_id);
        if (!$response) {
            ilUtil::sendFailure($this->ilLng->txt('unable_to_connect'), true);
            $this->ilCtrl->redirectByClass('ilinfoscreengui', 'info');
        }

        $connection_info    = json_decode($response);
        $settings           = $connector->getSettings();
        $known_private_room = $room->getActivePrivateRooms($this->ilUser->getId());

        $initial                        = new stdClass();
        $initial->users                 = $room->getConnectedUsers();
        $initial->private_rooms         = array_values($known_private_room);
        $initial->redirect_url          = $this->ilCtrl->getLinkTarget($this->gui, 'view-lostConnection', '', false, false);
        $initial->profile_image_url     = $this->ilCtrl->getLinkTarget($this->gui, 'view-getUserProfileImages', '', true, false);
        $initial->no_profile_image_url  = ilUtil::getImagePath('no_photo_xxsmall.jpg');
        $initial->private_rooms_enabled = (boolean) $room->getSetting('private_rooms_enabled');
        $initial->subdirectory          = $settings->getSubDirectory();

        $initial->userinfo = array(
            'moderator' => ilChatroom::checkUserPermissions('moderate', (int) $_GET['ref_id'], false),
            'id'        => $chat_user->getUserId(),
            'login'     => $chat_user->getUsername()
        );

        $smileys = array();

        include_once('Modules/Chatroom/classes/class.ilChatroomSmilies.php');

        if ($settings->getSmiliesEnabled()) {
            $smileys_array = ilChatroomSmilies::_getSmilies();
            foreach ($smileys_array as $smiley_array) {
                $new_keys = array();
                $new_val  = '';
                foreach ($smiley_array as $key => $value) {
                    if ($key == 'smiley_keywords') {
                        $new_keys = explode("\n", $value);
                    }

                    if ($key == 'smiley_fullpath') {
                        $new_val = $value;
                    }
                }

                if (!$new_keys || !$new_val) {
                    continue;
                }

                foreach ($new_keys as $new_key) {
                    $smileys[$new_key] = $new_val;
                }
            }

            $initial->smileys = $smileys;
        } else {
            $initial->smileys = '{}';
        }

        $initial->messages = array();

        if (isset($_REQUEST['sub'])) {
            if ($known_private_room[$_REQUEST['sub']]) {
                if (!$room->isAllowedToEnterPrivateRoom($chat_user->getUserId(), $_REQUEST['sub'])) {
                    $initial->messages[] = array(
                        'type'    => 'error',
                        'message' => $this->ilLng->txt('not_allowed_to_enter'),
                    );
                } else {
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

                    $connector = $this->gui->getConnector();
                    $response  = $connector->sendEnterPrivateRoom($scope, $_REQUEST['sub'], $chat_user->getUserId());

                    if ($this->isSuccessful($response)) {
                        $room->subscribeUserToPrivateRoom($params['sub'], $params['user']);
                    }

                    $initial->enter_room = $_REQUEST['sub'];
                }
            } else {
                $initial->messages[] = array(
                    'type'    => 'error',
                    'message' => $this->ilLng->txt('user_invited'),
                );
            }
        }

        if ((int) $room->getSetting('display_past_msgs')) {
            $initial->messages = array_merge($initial->messages, array_reverse($room->getLastMessages($room->getSetting('display_past_msgs'), $chat_user)));
        }

        $roomTpl = new ilTemplate('tpl.chatroom.html', true, true, 'Modules/Chatroom');
        $roomTpl->setVariable('SESSION_ID', $connection_info->{'session-id'});
        $roomTpl->setVariable('BASEURL', $settings->generateClientUrl());
        $roomTpl->setVariable('INSTANCE', $settings->getInstance());
        $roomTpl->setVariable('SCOPE', $scope);
        $roomTpl->setVariable('MY_ID', $user_id);
        $roomTpl->setVariable('INITIAL_DATA', json_encode($initial));
        $roomTpl->setVariable('POSTURL', $this->ilCtrl->getLinkTarget($this->gui, 'postMessage', '', true, true));

        $roomTpl->setVariable('ACTIONS', $this->ilLng->txt('actions'));
        $roomTpl->setVariable('LBL_CREATE_PRIVATE_ROOM', $this->ilLng->txt('create_private_room_label'));
        $roomTpl->setVariable('LBL_USER', $this->ilLng->txt('user'));
        $roomTpl->setVariable('LBL_USER_TEXT', $this->ilLng->txt('invite_username'));
        $roomTpl->setVariable('LBL_AUTO_SCROLL', $this->ilLng->txt('auto_scroll'));

        $roomTpl->setVariable('INITIAL_USERS', json_encode($room->getConnectedUsers()));

        $this->renderFileUploadForm($roomTpl);
        $this->renderSendMessageBox($roomTpl);
        $this->renderLanguageVariables($roomTpl);

        require_once 'Services/UIComponent/Modal/classes/class.ilModalGUI.php';
        ilModalGUI::initJS();

        $roomRightTpl = new ilTemplate('tpl.chatroom_right.html', true, true, 'Modules/Chatroom');
        $this->renderRightUsersBlock($roomRightTpl);

        require_once 'Services/UIComponent/Panel/classes/class.ilPanelGUI.php';
        $right_content_panel = ilPanelGUI::getInstance();
        $right_content_panel->setHeading($this->ilLng->txt('users'));
        $right_content_panel->setPanelStyle(ilPanelGUI::PANEL_STYLE_SECONDARY);
        $right_content_panel->setHeadingStyle(ilPanelGUI::HEADING_STYLE_BLOCK);
        $right_content_panel->setBody($roomRightTpl->get());

        $this->mainTpl->setContent($roomTpl->get());
        $this->mainTpl->setRightContent($right_content_panel->getHTML());
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
     * @param ilTemplate $roomTpl
     */
    protected function renderSendMessageBox(ilTemplate $roomTpl)
    {
        $roomTpl->setVariable('LBL_MESSAGE', $this->ilLng->txt('chat_message'));
        $roomTpl->setVariable('LBL_TOALL', $this->ilLng->txt('chat_message_to_all'));
        $roomTpl->setVariable('LBL_OPTIONS', $this->ilLng->txt('chat_message_options'));
        $roomTpl->setVariable('LBL_DISPLAY', $this->ilLng->txt('chat_message_display'));
        $roomTpl->setVariable('LBL_SEND', $this->ilLng->txt('send'));
    }

    /**
     * @param ilTemplate $roomTpl
     */
    protected function renderLanguageVariables(ilTemplate $roomTpl)
    {
        $js_translations = array(
            'LBL_MAINROOM'                     => 'chat_mainroom',
            'LBL_LEAVE_PRIVATE_ROOM'           => 'leave_private_room',
            'LBL_LEFT_PRIVATE_ROOM'            => 'left_private_room',
            'LBL_JOIN'                         => 'chat_join',
            'LBL_DELETE_PRIVATE_ROOM'          => 'delete_private_room',
            'LBL_DELETE_PRIVATE_ROOM_QUESTION' => 'delete_private_room_question',
            'LBL_INVITE_TO_PRIVATE_ROOM'       => 'invite_to_private_room',
            'LBL_KICK'                         => 'chat_kick',
            'LBL_BAN'                          => 'chat_ban',
            'LBL_KICK_QUESTION'                => 'kick_question',
            'LBL_BAN_QUESTION'                 => 'ban_question',
            'LBL_ADDRESS'                      => 'chat_address',
            'LBL_WHISPER'                      => 'chat_whisper',
            'LBL_CONNECT'                      => 'chat_connection_established',
            'LBL_DISCONNECT'                   => 'chat_connection_disconnected',
            'LBL_TO_MAINROOM'                  => 'chat_to_mainroom',
            'LBL_CREATE_PRIVATE_ROOM_JS'       => 'chat_create_private_room_button',
            'LBL_WELCOME_TO_CHAT'              => 'welcome_to_chat',
            'LBL_USER_INVITED'                 => 'user_invited',
            'LBL_USER_KICKED'                  => 'user_kicked',
            'LBL_USER_INVITED_SELF'            => 'user_invited_self',
            'LBL_PRIVATE_ROOM_CLOSED'          => 'private_room_closed',
            'LBL_PRIVATE_ROOM_ENTERED'         => 'private_room_entered',
            'LBL_PRIVATE_ROOM_LEFT'            => 'private_room_left',
            'LBL_PRIVATE_ROOM_ENTERED_USER'    => 'private_room_entered_user',
            'LBL_KICKED_FROM_PRIVATE_ROOM'     => 'kicked_from_private_room',
            'LBL_OK'                           => 'ok',
            'LBL_INVITE'                       => 'chat_invite',
            'LBL_CANCEL'                       => 'cancel',
            'LBL_WHISPER_TO'                   => 'whisper_to',
            'LBL_SPEAK_TO'                     => 'speak_to',
            'LBL_HISTORY_CLEARED'              => 'history_cleared',
            'LBL_CLEAR_ROOM_HISTORY'           => 'clear_room_history',
            'LBL_CLEAR_ROOM_HISTORY_QUESTION'  => 'clear_room_history_question',
            'LBL_END_WHISPER'                  => 'end_whisper',
            'LBL_TIMEFORMAT'                   => 'lang_timeformat_no_sec',
            'LBL_DATEFORMAT'                   => 'lang_dateformat'
        );
        foreach ($js_translations as $placeholder => $lng_variable) {
            $roomTpl->setVariable($placeholder, json_encode($this->ilLng->txt($lng_variable)));
        }

        $roomTpl->setVariable('LBL_CREATE_PRIVATE_ROOM', $this->ilLng->txt('chat_create_private_room_button'));
        $roomTpl->setVariable('LBL_CREATE_PRIVATE_ROOM_TEXT', $this->ilLng->txt('create_private_room_text'));
        $roomTpl->setVariable('LBL_LAYOUT', $this->ilLng->txt('layout'));
        $roomTpl->setVariable('LBL_SHOW_SETTINGS', $this->ilLng->txt('show_settings'));
        $roomTpl->setVariable('LBL_USER_IN_ROOM', $this->ilLng->txt('user_in_room'));
        $roomTpl->setVariable('LBL_USER_IN_ILIAS', $this->ilLng->txt('user_in_ilias'));
    }

    /**
     * @param ilTemplate $roomTpl
     */
    protected function renderRightUsersBlock(ilTemplate $roomTpl)
    {
        $roomTpl->setVariable('LBL_NO_FURTHER_USERS', $this->ilLng->txt('no_further_users'));
    }

    /**
     * Prepares and displays name selection.
     * Fetches name option by calling getChatNameSuggestions method on
     * given $chat_user object.
     * @param ilChatroomUser $chat_user
     */
    private function showNameSelection(ilChatroomUser $chat_user)
    {
        require_once 'Modules/Chatroom/classes/class.ilChatroomFormFactory.php';

        $name_options  = $chat_user->getChatNameSuggestions();
        $formFactory   = new ilChatroomFormFactory();
        $selectionForm = $formFactory->getUserChatNameSelectionForm($name_options);

        $this->ilCtrl->saveParameter($this->gui, 'sub');

        $selectionForm->addCommandButton('view-joinWithCustomName', $this->ilLng->txt('enter'));
        $selectionForm->setFormAction(
            $this->ilCtrl->getFormAction($this->gui, 'view-joinWithCustomName')
        );

        $this->mainTpl->setVariable('ADM_CONTENT', $selectionForm->getHtml());
    }

    /**
     * Chatroom and Chatuser get prepared before $this->showRoom method
     * is called. If custom usernames are allowed, $this->showNameSelection
     * method is called if user isn't already registered in the Chatroom.
     * @param string $method
     */
    public function executeDefault($method)
    {
        include_once 'Modules/Chatroom/classes/class.ilChatroom.php';

        $this->redirectIfNoPermission('read');

        $this->gui->switchToVisibleMode();
        $this->setupTemplate();

        $chatSettings = new ilSetting('chatroom');
        if (!$chatSettings->get('chat_enabled')) {
            $this->ilCtrl->redirect($this->gui, 'settings-general');
            exit;
        }

        $room = ilChatroom::byObjectId($this->gui->object->getId());

        if (!$room->getSetting('allow_anonymous') && $this->ilUser->isAnonymous()) {
            $this->cancelJoin($this->ilLng->txt('chat_anonymous_not_allowed'));
            return;
        }

        $chat_user = new ilChatroomUser($this->ilUser, $room);

        if ($room->getSetting('allow_custom_usernames')) {
            if ($room->isSubscribed($chat_user->getUserId())) {
                $chat_user->setUsername($chat_user->getUsername());
                $this->showRoom($room, $chat_user);
            } else {
                $this->showNameSelection($chat_user);
            }
        } else {
            $chat_user->setUsername($this->ilUser->getLogin());
            $this->showRoom($room, $chat_user);
        }
    }

    /**
     *
     */
    public function invitePD()
    {
        $chatSettings = new ilSetting('chatroom');
        if (!$chatSettings->get('chat_enabled')) {
            $this->ilCtrl->redirect($this->gui, 'settings-general');
        }

        $room      = ilChatroom::byObjectId($this->gui->object->getId());
        $chat_user = new ilChatroomUser($this->ilUser, $room);
        $user_id   = $_REQUEST['usr_id'];
        $connector = $this->gui->getConnector();
        $title     = $room->getUniquePrivateRoomTitle($chat_user->buildLogin());
        $subRoomId = $room->addPrivateRoom($title, $chat_user, array('public' => false));

        $room->inviteUserToPrivateRoom($user_id, $subRoomId);
        $connector->sendCreatePrivateRoom($room->getRoomId(), $subRoomId, $chat_user->getUserId(), $title);
        $connector->sendInviteToPrivateRoom($room->getRoomId(), $subRoomId, $chat_user->getUserId(), $user_id);

        $room->sendInvitationNotification($this->gui, $chat_user, $user_id, $subRoomId);

        $_REQUEST['sub'] = $subRoomId;

        $_SESSION['show_invitation_message'] = $user_id;

        $this->ilCtrl->setParameter($this->gui, 'sub', $subRoomId);
        $this->ilCtrl->redirect($this->gui, 'view');
    }

    /**
     * Performs logout.
     */
    public function logout()
    {
        /**
         * @todo logout user from room
         */
        $pid = $this->tree->getParentId($this->gui->getRefId());
        $this->ilCtrl->setParameterByClass('ilrepositorygui', 'ref_id', $pid);
        $this->ilCtrl->redirectByClass('ilrepositorygui', '');
    }

    /**
     *
     */
    public function lostConnection()
    {
        if (isset($_GET['msg'])) {
            switch ($_GET['msg']) {
                case 'kicked':
                    ilUtil::sendFailure($this->ilLng->txt('kicked'), true);
                    break;

                case 'banned':
                    ilUtil::sendFailure($this->ilLng->txt('banned'), true);
                    break;

                default:
                    ilUtil::sendFailure($this->ilLng->txt('lost_connection'), true);
                    break;
            }
        } else {
            ilUtil::sendFailure($this->ilLng->txt('lost_connection'), true);
        }

        $this->ilCtrl->redirectByClass('ilinfoscreengui', 'info');
    }

    public function getUserProfileImages()
    {
        global $DIC;

        $response = array();

        if (!$this->ilUser) {
            echo json_encode($response);
            exit();
        }

        if (!isset($_GET['usr_ids']) || strlen($_GET['usr_ids']) == 0) {
            echo json_encode($response);
            exit();
        }

        $this->ilLng->loadLanguageModule('user');

        ilWACSignedPath::setTokenMaxLifetimeInSeconds(30);

        $user_ids = array_filter(array_map('intval', array_map('trim', explode(',', $_GET['usr_ids']))));

        $room = ilChatroom::byObjectId($this->gui->object->getId());
        $chatRoomUserDetails = ilChatroomUser::getUserInformation($user_ids, $room->getRoomId());
        $chatRoomUserDetailsByUsrId = array_combine(
            array_map(
                function (stdClass $userData) {
                    return $userData->id;
                },
                $chatRoomUserDetails
            ),
            $chatRoomUserDetails
        );

        $public_data  = ilUserUtil::getNamePresentation($user_ids, true, false, '', false, true, false, true);
        $public_names = ilUserUtil::getNamePresentation($user_ids, false, false, '', false, true, false, false);

        foreach ($user_ids as $usr_id) {
            if (!array_key_exists($usr_id, $chatRoomUserDetailsByUsrId)) {
                continue;
            }

            if ($room->getSetting('allow_custom_usernames')) {
                /** @var ilUserAvatar $avatar */
                $avatar = $DIC["user.avatar.factory"]->avatar('xsmall');
                $avatar->setUsrId(ANONYMOUS_USER_ID);
                $avatar->setName(ilStr::subStr($chatRoomUserDetailsByUsrId[$usr_id]->login, 0, 2));

                $public_name = $chatRoomUserDetailsByUsrId[$usr_id]->login;
                $public_image = $avatar->getUrl();
            } else {
                $public_image = isset($public_data[$usr_id]) && isset($public_data[$usr_id]['img']) ? $public_data[$usr_id]['img'] : '';
                $public_name  = '';
                if (isset($public_names[$usr_id])) {
                    $public_name = $public_names[$usr_id];
                    if ('unknown' == $public_name && isset($public_data[$usr_id]) && isset($public_data[$usr_id]['login'])) {
                        $public_name = $public_data[$usr_id]['login'];
                    }
                }
            }

            $response[$usr_id] = [
                'public_name'   => $public_name,
                'profile_image' => $public_image,
            ];
        }

        echo json_encode($response);
        exit();
    }
}
