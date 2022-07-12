<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\HTTP\Response\ResponseHeader;

/**
 * Class ilChatroomViewGUI
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomViewGUI extends ilChatroomGUIHandler
{
    public function joinWithCustomName() : void
    {
        $this->redirectIfNoPermission('read');

        $this->gui->switchToVisibleMode();
        $this->setupTemplate();
        $room = ilChatroom::byObjectId($this->gui->getObject()->getId());
        $chat_user = new ilChatroomUser($this->ilUser, $room);
        $failure = true;
        $username = '';

        if ($this->hasRequestValue('custom_username_radio')) {
            if (
                $this->hasRequestValue('custom_username_text') &&
                $this->getRequestValue('custom_username_radio', $this->refinery->kindlyTo()->string()) === 'custom_username'
            ) {
                $username = $this->getRequestValue('custom_username_text', $this->refinery->kindlyTo()->string());
                $failure = false;
            } elseif (
                method_exists(
                    $chat_user,
                    'build' . $this->getRequestValue('custom_username_radio', $this->refinery->kindlyTo()->string())
                )
            ) {
                $username = $chat_user->{
                    'build' . $this->getRequestValue('custom_username_radio', $this->refinery->kindlyTo()->string())
                }();
                $failure = false;
            }
        }

        if (!$failure && trim($username) !== '') {
            if (!$room->isSubscribed($chat_user->getUserId())) {
                $chat_user->setUsername($chat_user->buildUniqueUsername($username));
            }

            $this->showRoom($room, $chat_user);
        } else {
            $this->mainTpl->setOnScreenMessage('failure', $this->ilLng->txt('no_username_given'));
            $this->showNameSelection($chat_user);
        }
    }

    /**
     * Adds CSS and JavaScript files that should be included in the header.
     */
    private function setupTemplate() : void
    {
        $this->mainTpl->addJavaScript('Modules/Chatroom/js/chat.js');
        $this->mainTpl->addJavaScript('Modules/Chatroom/js/iliaschat.jquery.js');
        $this->mainTpl->addJavaScript('node_modules/jquery-outside-events/jquery.ba-outside-events.js');
        $this->mainTpl->addJavaScript('./Services/UIComponent/AdvancedSelectionList/js/AdvancedSelectionList.js');

        $this->mainTpl->addCss('Modules/Chatroom/templates/default/style.css');

        $this->mainTpl->setPermanentLink($this->gui->getObject()->getType(), $this->gui->getObject()->getRefId());
    }

    /**
     * Prepares and displays chatroom and connects user to it.
     * @param ilChatroom $room
     * @param ilChatroomUser $chat_user
     */
    private function showRoom(ilChatroom $room, ilChatroomUser $chat_user) : void
    {
        $this->redirectIfNoPermission('read');

        $user_id = $chat_user->getUserId();

        $ref_id = $this->getRequestValue('ref_id', $this->refinery->kindlyTo()->int());
        $this->navigationHistory->addItem(
            $ref_id,
            $this->ilCtrl->getLinkTargetByClass(ilRepositoryGUI::class, 'view'),
            'chtr'
        );

        if ($room->isUserBanned($user_id)) {
            $this->cancelJoin($this->ilLng->txt('banned'));
            return;
        }

        $scope = $room->getRoomId();
        $connector = $this->gui->getConnector();
        $response = $connector->connect($scope, $user_id);

        if (!$response) {
            $this->mainTpl->setOnScreenMessage('failure', $this->ilLng->txt('unable_to_connect'), true);
            $this->ilCtrl->redirectByClass(ilInfoScreenGUI::class, 'info');
        }

        if (!$room->isSubscribed($chat_user->getUserId())) {
            $room->connectUser($chat_user);
        }

        $subScope = 0;
        $response = $connector->sendEnterPrivateRoom($scope, $subScope, $user_id);
        if (!$response) {
            $this->mainTpl->setOnScreenMessage('failure', $this->ilLng->txt('unable_to_connect'), true);
            $this->ilCtrl->redirectByClass('ilinfoscreengui', 'info');
        }

        $settings = $connector->getSettings();
        $known_private_room = $room->getActivePrivateRooms($this->ilUser->getId());

        $initial = new stdClass();
        $initial->users = $room->getConnectedUsers();
        $initial->private_rooms = array_values($known_private_room);
        $initial->redirect_url = $this->ilCtrl->getLinkTarget($this->gui, 'view-lostConnection', '', false);
        $initial->profile_image_url = $this->ilCtrl->getLinkTarget($this->gui, 'view-getUserProfileImages', '', true);
        $initial->no_profile_image_url = ilUtil::getImagePath('no_photo_xxsmall.jpg');
        $initial->private_rooms_enabled = (bool) $room->getSetting('private_rooms_enabled');
        $initial->subdirectory = $settings->getSubDirectory();

        $initial->userinfo = [
            'moderator' => ilChatroom::checkUserPermissions('moderate', $ref_id, false),
            'id' => $chat_user->getUserId(),
            'login' => $chat_user->getUsername(),
            'broadcast_typing' => $chat_user->enabledBroadcastTyping(),
        ];

        $smileys = [];

        if ($settings->getSmiliesEnabled()) {
            $smileys_array = ilChatroomSmilies::_getSmilies();
            foreach ($smileys_array as $smiley_array) {
                $new_keys = [];
                $new_val = '';
                foreach ($smiley_array as $key => $value) {
                    if ($key === 'smiley_keywords') {
                        $new_keys = explode("\n", $value);
                    }

                    if ($key === 'smiley_fullpath') {
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

        $initial->messages = [];

        $sub = null;
        if ($this->hasRequestValue('sub')) {
            $sub = $this->getRequestValue('sub', $this->refinery->kindlyTo()->int());
        }

        if ($sub !== null) {
            if ($known_private_room[$sub]) {
                if (!$room->isAllowedToEnterPrivateRoom($chat_user->getUserId(), $sub)) {
                    $initial->messages[] = [
                        'type' => 'error',
                        'message' => $this->ilLng->txt('not_allowed_to_enter'),
                    ];
                } else {
                    $scope = $room->getRoomId();
                    $params = [];
                    $params['user'] = $chat_user->getUserId();
                    $params['sub'] = $sub;
                    
                    $params['message'] = json_encode([
                        'type' => 'private_room_entered',
                        'user' => $user_id
                    ], JSON_THROW_ON_ERROR);

                    $connector = $this->gui->getConnector();
                    $response = $connector->sendEnterPrivateRoom($scope, $sub, $chat_user->getUserId());

                    if ($this->isSuccessful($response)) {
                        $room->subscribeUserToPrivateRoom($params['sub'], $params['user']);
                    }

                    $initial->enter_room = $sub;
                }
            } else {
                $initial->messages[] = [
                    'type' => 'error',
                    'message' => $this->ilLng->txt('user_invited'),
                ];
            }
        }

        if ((int) $room->getSetting('display_past_msgs')) {
            $initial->messages = array_merge(
                $initial->messages,
                array_reverse($room->getLastMessages($room->getSetting('display_past_msgs'), $chat_user))
            );
        }

        $roomTpl = new ilTemplate('tpl.chatroom.html', true, true, 'Modules/Chatroom');
        $roomTpl->setVariable('BASEURL', $settings->generateClientUrl());
        $roomTpl->setVariable('INSTANCE', $settings->getInstance());
        $roomTpl->setVariable('SCOPE', $scope);
        $roomTpl->setVariable('POSTURL', $this->ilCtrl->getLinkTarget($this->gui, 'postMessage', '', true));

        $roomTpl->setVariable('ACTIONS', $this->ilLng->txt('actions'));
        $roomTpl->setVariable('LBL_CREATE_PRIVATE_ROOM', $this->ilLng->txt('create_private_room_label'));
        $roomTpl->setVariable('LBL_USER', $this->ilLng->txt('user'));
        $roomTpl->setVariable('LBL_USER_TEXT', $this->ilLng->txt('invite_username'));
        $showAutoMessages = true;
        if ($this->ilUser->getPref('chat_hide_automsg_' . $room->getRoomId())) {
            $showAutoMessages = false;
        }
        
        $roomTpl->setVariable(
            'TOGGLE_SCROLLING_COMPONENT',
            $this->uiRenderer->render(
                $this->uiFactory->button()->toggle(
                    $this->ilLng->txt('auto_scroll'),
                    '#',
                    '#',
                    true
                )
                ->withAriaLabel($this->ilLng->txt('auto_scroll'))
                ->withOnLoadCode(static function (string $id) : string {
                    return '
                        $("#' . $id . '")
                            .on("click", function(e) {
                                let t = $(this), msg = $("#chat_messages");
                                if (t.hasClass("on")) {
                                    msg.trigger("msg-scrolling:toggle", [true]);
                                } else {
                                    msg.trigger("msg-scrolling:toggle", [false]);
                                }
                            });
                    ';
                })
            )
        );

        $toggleUrl = $this->ilCtrl->getFormAction($this->gui, 'view-toggleAutoMessageDisplayState', '', true, false);
        $roomTpl->setVariable(
            'TOGGLE_AUTO_MESSAGE_COMPONENT',
            $this->uiRenderer->render(
                $this->uiFactory->button()->toggle(
                    $this->ilLng->txt('chat_show_auto_messages'),
                    '#',
                    '#',
                    $showAutoMessages
                )
                ->withAriaLabel($this->ilLng->txt('chat_show_auto_messages'))
                ->withOnLoadCode(static function (string $id) use ($toggleUrl) : string {
                    return '
                        $("#' . $id . '")
                            .on("click", function(e) {
                                let t = $(this), msg = $("#chat_messages");
                                if (t.hasClass("on")) {
                                    msg.trigger("auto-message:toggle", [true, "' . $toggleUrl . '"]);
                                } else {
                                    msg.trigger("auto-message:toggle", [false, "' . $toggleUrl . '"]);
                                }
                            });
                    ';
                })
            )
        );

        $initial->state = new stdClass();
        $initial->state->scrolling = true;
        $initial->state->show_auto_msg = $showAutoMessages;

        $roomTpl->setVariable('INITIAL_DATA', json_encode($initial, JSON_THROW_ON_ERROR));
        $roomTpl->setVariable('INITIAL_USERS', json_encode($room->getConnectedUsers(), JSON_THROW_ON_ERROR));

        $this->renderSendMessageBox($roomTpl);
        $this->renderLanguageVariables($roomTpl);

        ilModalGUI::initJS();

        $roomRightTpl = new ilTemplate('tpl.chatroom_right.html', true, true, 'Modules/Chatroom');
        $this->renderRightUsersBlock($roomRightTpl);

        $right_content_panel = ilPanelGUI::getInstance();
        $right_content_panel->setHeading($this->ilLng->txt('users'));
        $right_content_panel->setPanelStyle(ilPanelGUI::PANEL_STYLE_SECONDARY);
        $right_content_panel->setHeadingStyle(ilPanelGUI::HEADING_STYLE_BLOCK);
        $right_content_panel->setBody($roomRightTpl->get());

        $this->mainTpl->setContent($roomTpl->get());
        $this->mainTpl->setRightContent($right_content_panel->getHTML());
    }

    public function toggleAutoMessageDisplayState() : void
    {
        $this->redirectIfNoPermission('read');

        $room = ilChatroom::byObjectId($this->gui->getObject()->getId());
 
        $state = 0;
        if ($this->http->wrapper()->post()->has('state')) {
            $state = $this->http->wrapper()->post()->retrieve('state', $this->refinery->kindlyTo()->int());
        }

        ilObjUser::_writePref(
            $this->ilUser->getId(),
            'chat_hide_automsg_' . $room->getRoomId(),
            (string) ((int) (!(bool) $state))
        );

        $this->http->saveResponse(
            $this->http->response()
                ->withHeader(ResponseHeader::CONTENT_TYPE, 'application/json')
                ->withBody(Streams::ofString(json_encode(['success' => true], JSON_THROW_ON_ERROR)))
        );
        $this->http->sendResponse();
        $this->http->close();
    }

    /**
     * Calls ilUtil::sendFailure method using given $message as parameter.
     * @param string $message
     */
    private function cancelJoin(string $message) : void
    {
        $this->mainTpl->setOnScreenMessage('failure', $message);
    }

    protected function renderSendMessageBox(ilTemplate $roomTpl) : void
    {
        $roomTpl->setVariable('LBL_MESSAGE', $this->ilLng->txt('chat_message'));
        $roomTpl->setVariable('LBL_TOALL', $this->ilLng->txt('chat_message_to_all'));
        $roomTpl->setVariable('LBL_OPTIONS', $this->ilLng->txt('chat_message_options'));
        $roomTpl->setVariable('LBL_DISPLAY', $this->ilLng->txt('chat_message_display'));
        $roomTpl->setVariable('LBL_SEND', $this->ilLng->txt('send'));
    }

    protected function renderLanguageVariables(ilTemplate $roomTpl) : void
    {
        $js_translations = [
            'LBL_MAINROOM' => 'chat_mainroom',
            'LBL_LEAVE_PRIVATE_ROOM' => 'leave_private_room',
            'LBL_LEFT_PRIVATE_ROOM' => 'left_private_room',
            'LBL_JOIN' => 'chat_join',
            'LBL_DELETE_PRIVATE_ROOM' => 'delete_private_room',
            'LBL_DELETE_PRIVATE_ROOM_QUESTION' => 'delete_private_room_question',
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
            'LBL_INVITE' => 'chat_invite',
            'LBL_CANCEL' => 'cancel',
            'LBL_WHISPER_TO' => 'whisper_to',
            'LBL_SPEAK_TO' => 'speak_to',
            'LBL_HISTORY_CLEARED' => 'history_cleared',
            'LBL_CLEAR_ROOM_HISTORY' => 'clear_room_history',
            'LBL_CLEAR_ROOM_HISTORY_QUESTION' => 'clear_room_history_question',
            'LBL_END_WHISPER' => 'end_whisper',
            'LBL_TIMEFORMAT' => 'lang_timeformat_no_sec',
            'LBL_DATEFORMAT' => 'lang_dateformat'
        ];
        foreach ($js_translations as $placeholder => $lng_variable) {
            $roomTpl->setVariable($placeholder, json_encode($this->ilLng->txt($lng_variable), JSON_THROW_ON_ERROR));
        }
        $this->ilLng->toJSMap([
            'chat_user_x_is_typing' => $this->ilLng->txt('chat_user_x_is_typing'),
            'chat_users_are_typing' => $this->ilLng->txt('chat_users_are_typing'),
        ]);

        $roomTpl->setVariable('LBL_CREATE_PRIVATE_ROOM', $this->ilLng->txt('chat_create_private_room_button'));
        $roomTpl->setVariable('LBL_CREATE_PRIVATE_ROOM_TEXT', $this->ilLng->txt('create_private_room_text'));
        $roomTpl->setVariable('LBL_LAYOUT', $this->ilLng->txt('layout'));
        $roomTpl->setVariable('LBL_SHOW_SETTINGS', $this->ilLng->txt('show_settings'));
        $roomTpl->setVariable('LBL_USER_IN_ROOM', $this->ilLng->txt('user_in_room'));
        $roomTpl->setVariable('LBL_USER_IN_ILIAS', $this->ilLng->txt('user_in_ilias'));
    }

    protected function renderRightUsersBlock(ilTemplate $roomTpl) : void
    {
        $roomTpl->setVariable('LBL_NO_FURTHER_USERS', $this->ilLng->txt('no_further_users'));
    }

    private function showNameSelection(ilChatroomUser $chat_user) : void
    {
        $name_options = $chat_user->getChatNameSuggestions();
        $formFactory = new ilChatroomFormFactory();
        $selectionForm = $formFactory->getUserChatNameSelectionForm($name_options);

        $this->ilCtrl->saveParameter($this->gui, 'sub');

        $selectionForm->addCommandButton('view-joinWithCustomName', $this->ilLng->txt('enter'));
        $selectionForm->setFormAction(
            $this->ilCtrl->getFormAction($this->gui, 'view-joinWithCustomName')
        );

        $this->mainTpl->setVariable('ADM_CONTENT', $selectionForm->getHTML());
    }

    /**
     * Chatroom and Chatuser get prepared before $this->showRoom method
     * is called. If custom usernames are allowed, $this->showNameSelection
     * method is called if user isn't already registered in the Chatroom.
     * @inheritDoc
     */
    public function executeDefault(string $requestedMethod) : void
    {
        $this->redirectIfNoPermission('read');

        $this->gui->switchToVisibleMode();
        $this->setupTemplate();

        $chatSettings = new ilSetting('chatroom');
        if (!$chatSettings->get('chat_enabled', '0')) {
            $this->ilCtrl->redirect($this->gui, 'settings-general');
        }

        $room = ilChatroom::byObjectId($this->gui->getObject()->getId());

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

    public function invitePD() : void
    {
        $chatSettings = new ilSetting('chatroom');
        if (!$chatSettings->get('chat_enabled', '0')) {
            $this->ilCtrl->redirect($this->gui, 'settings-general');
        }

        $room = ilChatroom::byObjectId($this->gui->getObject()->getId());
        $chat_user = new ilChatroomUser($this->ilUser, $room);

        $user_id = $this->getRequestValue('usr_id', $this->refinery->kindlyTo()->int());
        
        $connector = $this->gui->getConnector();
        $title = $room->getUniquePrivateRoomTitle($chat_user->buildLogin());
        $subRoomId = $room->addPrivateRoom($title, $chat_user, ['public' => false]);

        $room->inviteUserToPrivateRoom($user_id, $subRoomId);
        $connector->sendCreatePrivateRoom($room->getRoomId(), $subRoomId, $chat_user->getUserId(), $title);
        $connector->sendInviteToPrivateRoom($room->getRoomId(), $subRoomId, $chat_user->getUserId(), $user_id);

        $room->sendInvitationNotification($this->gui, $chat_user, $user_id, $subRoomId);

        ilSession::set('show_invitation_message', $user_id);

        $this->ilCtrl->setParameter($this->gui, 'sub', $subRoomId);
        $this->ilCtrl->redirect($this->gui, 'view');
    }

    public function logout() : void
    {
        $pid = $this->tree->getParentId($this->gui->getRefId());
        $this->ilCtrl->setParameterByClass(ilRepositoryGUI::class, 'ref_id', $pid);
        $this->ilCtrl->redirectByClass(ilRepositoryGUI::class);
    }

    public function lostConnection() : void
    {
        if ($this->http->wrapper()->query()->has('msg')) {
            switch ($this->http->wrapper()->query()->retrieve('msg', $this->refinery->kindlyTo()->string())) {
                case 'kicked':
                    $this->mainTpl->setOnScreenMessage('failure', $this->ilLng->txt('kicked'), true);
                    break;

                case 'banned':
                    $this->mainTpl->setOnScreenMessage('failure', $this->ilLng->txt('banned'), true);
                    break;

                default:
                    $this->mainTpl->setOnScreenMessage('failure', $this->ilLng->txt('lost_connection'), true);
                    break;
            }
        } else {
            $this->mainTpl->setOnScreenMessage('failure', $this->ilLng->txt('lost_connection'), true);
        }

        $this->ilCtrl->redirectByClass(ilInfoScreenGUI::class, 'info');
    }

    public function getUserProfileImages() : void
    {
        global $DIC;

        $response = [];

        $usr_ids = null;
        if ($this->hasRequestValue('usr_ids')) {
            $usr_ids = $this->getRequestValue('usr_ids', $this->refinery->kindlyTo()->string());
        }
        if (null === $usr_ids || '' === $usr_ids) {
            $this->sendResponse($response);
        }

        $this->ilLng->loadLanguageModule('user');

        ilWACSignedPath::setTokenMaxLifetimeInSeconds(30);
        
        $user_ids = array_filter(array_map('intval', array_map('trim', explode(',', $usr_ids))));

        $room = ilChatroom::byObjectId($this->gui->getObject()->getId());
        $chatRoomUserDetails = ilChatroomUser::getUserInformation($user_ids, $room->getRoomId());
        $chatRoomUserDetailsByUsrId = array_combine(
            array_map(
                static function (stdClass $userData) : int {
                    return (int) $userData->id;
                },
                $chatRoomUserDetails
            ),
            $chatRoomUserDetails
        );

        $public_data = ilUserUtil::getNamePresentation($user_ids, true, false, '', false, true, false, true);
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
                $public_image = $public_data[$usr_id]['img'] ?? '';
                $public_name = '';
                if (isset($public_names[$usr_id])) {
                    $public_name = $public_names[$usr_id];
                    if (isset($public_data[$usr_id]['login']) && 'unknown' === $public_name) {
                        $public_name = $public_data[$usr_id]['login'];
                    }
                }
            }

            $response[$usr_id] = [
                'public_name' => $public_name,
                'profile_image' => $public_image,
            ];
        }

        $this->sendResponse($response);
    }
}
