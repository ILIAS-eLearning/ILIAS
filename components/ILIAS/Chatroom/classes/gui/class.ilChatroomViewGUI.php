<?php

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

declare(strict_types=1);

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\HTTP\Response\ResponseHeader;
use ILIAS\UI\Component\Component;
use ILIAS\Chatroom\BuildChat;
use ILIAS\UI\Component\Button\Button;

/**
 * Class ilChatroomViewGUI
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomViewGUI extends ilChatroomGUIHandler
{
    public function joinWithCustomName(): void
    {
        $this->redirectIfNoPermission('read');

        $this->gui->switchToVisibleMode();
        $this->setupTemplate();
        $room = ilChatroom::byObjectId($this->gui->getObject()->getId());
        $chat_user = new ilChatroomUser($this->ilUser, $room);
        $failure = true;
        $username = '';
        $custom_username = false;

        if ($this->hasRequestValue('custom_username_radio')) {
            if (
                $this->hasRequestValue('custom_username_text') &&
                $this->getRequestValue('custom_username_radio', $this->refinery->kindlyTo()->string()) === 'custom_username'
            ) {
                $custom_username = true;
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
                $chat_user->setProfilePictureVisible(!$custom_username);
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
    private function setupTemplate(): void
    {
        ilLinkifyUtil::initLinkify($this->mainTpl);
        $this->mainTpl->addJavaScript('assets/js/socket.io.min.js');
        $this->mainTpl->addJavaScript('assets/js/Chatroom.min.js');
        $this->mainTpl->addJavaScript('assets/js/AdvancedSelectionList.js');

        $this->mainTpl->setPermanentLink($this->gui->getObject()->getType(), $this->gui->getObject()->getRefId());
    }

    /**
     * Prepares and displays chatroom and connects user to it.
     */
    private function showRoom(ilChatroom $room, ilChatroomUser $chat_user): void
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
            $this->ilCtrl->redirectByClass(ilInfoScreenGUI::class, 'showSummary');
        }

        if (!$room->isSubscribed($chat_user->getUserId())) {
            $room->connectUser($chat_user);
        }

        $response = $connector->sendEnterPrivateRoom($scope, $user_id);
        if (!$response) {
            $this->mainTpl->setOnScreenMessage('failure', $this->ilLng->txt('unable_to_connect'), true);
            $this->ilCtrl->redirectByClass(ilInfoScreenGUI::class, 'showSummary');
        }

        $messages = $room->getSetting('display_past_msgs') ? array_reverse(array_filter(
            $room->getLastMessages($room->getSetting('display_past_msgs'), $chat_user),
            fn($entry) => $entry->type !== 'notice'
        )) : [];

        $is_moderator = ilChatroom::checkUserPermissions('moderate', $ref_id, false);
        $show_auto_messages = !$this->ilUser->getPref('chat_hide_automsg_' . $room->getRoomId());

        $build = $this->buildChat($room, $connector->getSettings());

        $room_tpl = $build->template(false, $build->initialData(
            $room->getConnectedUsers(),
            $show_auto_messages,
            $this->ilCtrl->getLinkTarget($this->gui, 'view-lostConnection', '', false),
            [
                'moderator' => $is_moderator,
                'id' => $chat_user->getUserId(),
                'login' => $chat_user->getUsername(),
                'broadcast_typing' => $chat_user->enabledBroadcastTyping(),
                'profile_picture_visible' => $chat_user->isProfilePictureVisible(),
            ],
            $messages
        ), $this->panel($this->ilLng->txt('write_message'), $this->sendMessageForm()), $this->panel($this->ilLng->txt('messages'), $this->legacy('<div id="chat_messages"></div>')));

        $this->mainTpl->setContent($room_tpl->get());
        $this->mainTpl->setRightContent($this->userList() . $this->chatFunctions($show_auto_messages, $is_moderator));
    }

    public function readOnlyChatWindow(ilChatroom $room, array $messages): ilTemplate
    {
        $build = $this->buildChat($room, $this->gui->getConnector()->getSettings());

        return $build->template(true, $build->initialData([], true, null, [
            'moderator' => false,
            'id' => -1,
            'login' => null,
            'broadcast_typing' => false,
        ], $messages), $this->panel($this->ilLng->txt('messages'), $this->legacy('<div id="chat_messages"></div>')), '');
    }

    private function sendMessageForm(): Component
    {
        $template = new ilTemplate('tpl.chatroom_send_message_form.html', true, true, 'components/ILIAS/Chatroom');
        $this->renderSendMessageBox($template);

        return $this->legacy($template->get());
    }

    private function userList(): string
    {
        $roomRightTpl = new ilTemplate('tpl.chatroom_right.html', true, true, 'components/ILIAS/Chatroom');
        $this->renderRightUsersBlock($roomRightTpl);

        return $this->panel($this->ilLng->txt('users'), $this->legacy($roomRightTpl->get()));
    }

    private function chatFunctions(bool $show_auto_messages, bool $is_moderator): string
    {
        $txt = $this->ilLng->txt(...);
        $js_escape = json_encode(...);
        $format = fn($format, ...$args) => sprintf($format, ...array_map($js_escape, $args));
        $register = fn($name, $c) => $c->withOnLoadCode(fn($id) => $format(
            'il.Chatroom.bus.send(%s, document.getElementById(%s));',
            $name,
            $id
        ));

        $b = $this->uiFactory->button();
        $toggle = fn($label, $enabled) => $b->toggle($label, '#', '#', $enabled)->withAriaLabel($label);

        $bind = fn($key, $m) => $m->withAdditionalOnLoadCode(fn(string $id) => $format(
            '$(() => il.Chatroom.bus.send(%s, {
                       node: document.getElementById(%s),
                       showModal: () => $(document).trigger(%s, {}),
                       closeModal: () => $(document).trigger(%s, {})
                     }));',
            $key,
            $id,
            $m->getShowSignal()->getId(),
            $m->getCloseSignal()->getId()
        ));

        $interrupt = fn($key, $label, $text, $button = null) => $bind($key, $this->uiFactory->modal()->interruptive(
            $label,
            $text,
            ''
        ))->withActionButtonLabel($button ?? $label);

        $auto_scroll = $register('auto-scroll-toggle', $toggle($txt('auto_scroll'), true));
        $messages = $register('system-messages-toggle', $toggle($txt('chat_show_auto_messages'), $show_auto_messages));

        $invite = $bind('invite-modal', $this->uiFactory->modal()->roundtrip($txt('chat_invite'), $this->legacy($txt('invite_to_private_room')), [
            $this->uiFactory->input()->field()->text($txt('chat_invite')),
        ])->withSubmitLabel($txt('chat_invite')));

        $buttons = [];
        $buttons[] = $register('invite-button', $b->shy($txt('invite_to_private_room'), ''));
        if ($is_moderator) {
            $buttons[] = $register('clear-history-button', $b->shy($txt('clear_room_history'), ''));
        }

        return $this->panel($txt('chat_functions'), [
            $this->legacy('<div id="chat_function_list">'),
            ...$buttons,
            $invite,
            $interrupt('kick-modal', $txt('chat_kick'), $txt('kick_question')),
            $interrupt('ban-modal', $txt('chat_ban'), $txt('ban_question')),
            $interrupt('clear-history-modal', $txt('clear_room_history'), $txt('clear_room_history_question')),
            $this->legacy('</div>'),
            $this->legacy(sprintf('<div>%s%s</div>', $this->checkbox($auto_scroll), $this->checkbox($messages))),
        ]);
    }

    private function checkbox(Component $component): string
    {
        return sprintf('<div class="chatroom-centered-checkboxes">%s</div>', $this->uiRenderer->render($component));
    }

    private function legacy(string $html): Component
    {
        return $this->uifactory->legacy()->content($html);
    }

    /**
     * @param Component|array<Component> $body
     */
    private function panel(string $title, $body): string
    {
        if (is_array($body)) {
            $body = $this->uiFactory->legacy()->content(join('', array_map($this->uiRenderer->render(...), $body)));
        }
        $panel = $this->uiFactory->panel()->secondary()->legacy($title, $body);

        return $this->uiRenderer->render($panel);
    }

    public function toggleAutoMessageDisplayState(): void
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
     */
    private function cancelJoin(string $message): void
    {
        $this->mainTpl->setOnScreenMessage('failure', $message);
    }

    protected function renderSendMessageBox(ilTemplate $roomTpl): void
    {
        $roomTpl->setVariable('PLACEHOLDER', $this->ilLng->txt('chat_osc_write_a_msg'));
        $roomTpl->setVariable('LBL_SEND', $this->ilLng->txt('send'));
    }

    protected function renderRightUsersBlock(ilTemplate $roomTpl): void
    {
        $roomTpl->setVariable('LBL_NO_FURTHER_USERS', $this->ilLng->txt('no_further_users'));
    }

    private function showNameSelection(ilChatroomUser $chat_user): void
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
    public function executeDefault(string $requestedMethod): void
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
            $chat_user->setUsername($this->ilUser->getPublicName());
            $chat_user->setProfilePictureVisible(true);
            $this->showRoom($room, $chat_user);
        }
    }

    public function logout(): void
    {
        $pid = $this->tree->getParentId($this->gui->getRefId());
        $this->ilCtrl->setParameterByClass(ilRepositoryGUI::class, 'ref_id', $pid);
        $this->ilCtrl->redirectByClass(ilRepositoryGUI::class);
    }

    public function lostConnection(): void
    {
        if ($this->http->wrapper()->query()->has('msg')) {
            match ($this->http->wrapper()->query()->retrieve('msg', $this->refinery->kindlyTo()->string())) {
                'kicked' => $this->mainTpl->setOnScreenMessage('failure', $this->ilLng->txt('kicked'), true),
                'banned' => $this->mainTpl->setOnScreenMessage('failure', $this->ilLng->txt('banned'), true),
                default => $this->mainTpl->setOnScreenMessage('failure', $this->ilLng->txt('lost_connection'), true),
            };
        } else {
            $this->mainTpl->setOnScreenMessage('failure', $this->ilLng->txt('lost_connection'), true);
        }

        $this->ilCtrl->redirectByClass(ilInfoScreenGUI::class, 'showSummary');
    }

    public function getUserProfileImages(): void
    {
        global $DIC;

        $response = [];

        $request = json_decode($this->http->request()->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

        ilWACSignedPath::setTokenMaxLifetimeInSeconds(30);

        $users = $this->refinery->kindlyTo()->listOf($this->refinery->byTrying([
            $this->refinery->kindlyTo()->recordOf([
                'id' => $this->refinery->kindlyTo()->int(),
                'username' => $this->refinery->kindlyTo()->string(),
                'profile_picture_visible' => $this->refinery->kindlyTo()->bool(),
            ]),
            $this->refinery->kindlyTo()->recordOf([
                'id' => $this->refinery->kindlyTo()->int(),
                'username' => $this->refinery->kindlyTo()->string(),
            ]),
        ]))->transform($request['profiles'] ?? []);

        $user_ids = array_column($users, 'id');

        $public_data = ilUserUtil::getNamePresentation($user_ids, true, false, '', false, true, false, true);

        foreach ($users as $user) {
            if ($user['profile_picture_visible'] ?? false) {
                $public_image = $public_data[$user['id']]['img'] ?? '';
            } else {
                /** @var ilUserAvatar $avatar */
                $avatar = $DIC["user.avatar.factory"]->avatar('xsmall');
                $avatar->setUsrId(ANONYMOUS_USER_ID);
                $avatar->setName(ilStr::subStr($user['username'], 0, 2));
                $public_image = $avatar->getUrl();
            }

            $response[json_encode($user, JSON_THROW_ON_ERROR)] = $public_image;
        }

        $this->sendJSONResponse($response);
    }

    public function userEntry(): void
    {
        global $DIC;

        $kindly = $this->refinery->kindlyTo();
        $s = $kindly->string();
        $int = $kindly->int();
        $get = $this->http->wrapper()->query()->retrieve(...);
        $get_or = fn($k, $t, $d = null) => $get($k, $this->refinery->byTrying([$t, $this->refinery->always($d)]));

        $ref_id = $get('ref_id', $int);
        $user_id = $get('user_id', $int);
        $username = $get('username', $s);
        $actions = $get_or('actions', $kindly->dictOf($s), []);

        $avatar = $DIC["user.avatar.factory"]->avatar('xsmall');
        $avatar->setUsrId(ANONYMOUS_USER_ID);
        $avatar->setName(ilStr::subStr($username, 0, 2));
        $public_image = $avatar->getUrl();
        $item = $this->uiFactory->item()->standard($username)->withLeadImage($this->uiFactory->image()->standard(
            $public_image,
            'Profile image of ' . $username
        ));

        if (ilChatroom::checkPermissionsOfUser($user_id, 'moderate', $ref_id)) {
            $item = $item->withProperties([
                $this->ilLng->txt('role') => $this->ilLng->txt('il_chat_moderator'),
            ]);
        }
        $item = $item->withActions($this->uiFactory->dropdown()->standard($this->buildUserActions($user_id, $actions)));


        $this->sendResponse($this->uiRenderer->renderAsync($item), 'text/html');
    }

    /**
     * @param array<string|int, string> $actions
     * @return array<Button>
     */
    private function buildUserActions(int $user_id, array $actions): array
    {
        $chat_settings = new ilSetting('chatroom');
        $osc_enabled = $chat_settings->get('chat_enabled') && $chat_settings->get('enable_osc');
        $translations = [
            'kick' => $this->ilLng->txt('chat_kick'),
            'ban' => $this->ilLng->txt('chat_ban'),
        ];

        if ($osc_enabled && ilObjUser::_lookupPref($user_id, 'chat_osc_accept_msg') === 'y') {
            $translations['chat'] = $this->ilLng->txt('start_private_chat');
        }

        $buttons = [];
        foreach ($actions as $key => $bus_id) {
            $label = $translations[$key] ?? false;
            if ($label) {
                $buttons[] = $this->uiFactory->button()->shy($label, '')->withAdditionalOnLoadCode(fn(string $id): string => (
                    'il.Chatroom.bus.send(' . json_encode(
                        $bus_id,
                        JSON_THROW_ON_ERROR
                    ) . ', document.getElementById(' . json_encode($id, JSON_THROW_ON_ERROR) . '));'
                ));
            }
        }

        return $buttons;
    }

    private function buildChat(ilChatroom $room, ilChatroomServerSettings $settings): BuildChat
    {
        return new BuildChat($this->ilCtrl, $this->ilLng, $this->gui, $room, $settings, $this->ilUser, $this->uiFactory, $this->uiRenderer);
    }
}
