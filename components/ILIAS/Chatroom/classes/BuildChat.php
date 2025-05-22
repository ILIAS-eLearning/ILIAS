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

namespace ILIAS\Chatroom;

use ilCtrlInterface;
use ilLanguage;
use ilChatroomObjectGUI;
use ilChatroom;
use ilChatroomServerSettings;
use ilUtil;
use ilTemplate;
use ilObjUser;
use ilCalendarSettings;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;

class BuildChat
{
    public function __construct(
        private readonly ilCtrlInterface $ilCtrl,
        private readonly ilLanguage $ilLng,
        private readonly ilChatroomObjectGUI $gui,
        private readonly ilChatroom $room,
        private readonly ilChatroomServerSettings $settings,
        private readonly ilObjUser $user,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
    ) {
    }

    public function template(bool $read_only, array $initial, string $input, string $output): ilTemplate
    {
        $room_tpl = new ilTemplate('tpl.chatroom.html', true, true, 'components/ILIAS/Chatroom');
        $set_json_var = fn($var, $value) => $room_tpl->setVariable($var, json_encode($value));
        $set_json_var('BASEURL', $this->settings->generateClientUrl());
        $set_json_var('INSTANCE', $this->settings->getInstance());
        $set_json_var('SCOPE', $this->room->getRoomId());
        $set_json_var('POSTURL', ILIAS_HTTP_PATH . '/' . $this->ilCtrl->getLinkTarget($this->gui, 'postMessage', '', true));
        $room_tpl->setVariable('JS_CALL', 'il.Chatroom.' . ($read_only ? 'runReadOnly' : 'run'));

        $set_json_var('INITIAL_DATA', $initial);
        $set_json_var('INITIAL_USERS', $this->room->getConnectedUsers());
        $set_json_var('DATE_FORMAT', (string) $this->user->getDateFormat());
        $set_json_var('TIME_FORMAT', $this->timeFormat());
        $set_json_var('NOTHING_FOUND', $this->ui_renderer->render($this->ui_factory->messageBox()->info($this->ilLng->txt('chat_osc_no_usr_found'))));

        $room_tpl->setVariable('CHAT_OUTPUT', $output);
        $room_tpl->setVariable('CHAT_INPUT', $input);

        $this->renderLanguageVariables($room_tpl);

        return $room_tpl;
    }

    public function initialData(array $users, bool $show_auto_messages, ?string $redirect_url, array $userinfo, array $messages): array
    {
        $initial = [];
        $initial['users'] = $users;
        $initial['redirect_url'] = $redirect_url;
        $initial['profile_image_url'] = $this->ilCtrl->getLinkTarget($this->gui, 'view-getUserProfileImages', '', true);
        $initial['no_profile_image_url'] = ilUtil::getImagePath('placeholder/no_photo_xxsmall.jpg');
        $initial['subdirectory'] = $this->settings->getSubDirectory();

        $initial['userinfo'] = $userinfo;
        $initial['messages'] = $messages;

        $initial['state'] = [
            'scrolling' => true,
            'show_auto_msg' => $show_auto_messages,
            'system_message_update_url' => $this->ilCtrl->getFormAction($this->gui, 'view-toggleAutoMessageDisplayState', '', true, false),
        ];

        return $initial;
    }

    private function renderLanguageVariables(ilTemplate $room_tpl): void
    {
        $set_vars = fn($a) => array_map($room_tpl->setVariable(...), array_keys($a), array_values($a));

        $js_translations = [
            'LBL_MAINROOM' => 'chat_mainroom',
            'LBL_JOIN' => 'chat_join',
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
            'LBL_WELCOME_TO_CHAT' => 'welcome_to_chat',
            'LBL_USER_INVITED' => 'user_invited',
            'LBL_USER_KICKED' => 'user_kicked',
            'LBL_USER_BANNED' => 'user_banned',
            'LBL_USER_INVITED_SELF' => 'user_invited_self',
            'LBL_PRIVATE_ROOM_CLOSED' => 'private_room_closed',
            'LBL_PRIVATE_ROOM_ENTERED' => 'private_room_entered',
            'LBL_PRIVATE_ROOM_LEFT' => 'private_room_left',
            'LBL_PRIVATE_ROOM_ENTERED_USER' => 'private_room_entered_user',
            'LBL_KICKED_FROM_PRIVATE_ROOM' => 'kicked_from_private_room',
            'LBL_OK' => 'ok',
            'LBL_DELETE' => 'delete',
            'LBL_INVITE' => 'chat_invite',
            'LBL_CANCEL' => 'cancel',
            'LBL_HISTORY_CLEARED' => 'history_cleared',
            'LBL_CLEAR_ROOM_HISTORY' => 'clear_room_history',
            'LBL_CLEAR_ROOM_HISTORY_QUESTION' => 'clear_room_history_question',
            'LBL_END_WHISPER' => 'end_whisper',
            'LBL_TIMEFORMAT' => 'lang_timeformat_no_sec',
            'LBL_DATEFORMAT' => 'lang_dateformat',
            'LBL_START_PRIVATE_CHAT' => 'start_private_chat',
        ];

        $set_vars(array_map(
            fn($v) => json_encode($this->ilLng->txt($v), JSON_THROW_ON_ERROR),
            $js_translations
        ));

        $this->ilLng->toJSMap([
            'chat_user_x_is_typing' => $this->ilLng->txt('chat_user_x_is_typing'),
            'chat_users_are_typing' => $this->ilLng->txt('chat_users_are_typing'),
        ]);

        $vars = [
            'LBL_LAYOUT' => 'layout',
            'LBL_SHOW_SETTINGS' => 'show_settings',
            'LBL_USER_IN_ROOM' => 'user_in_room',
            'LOADING_IMAGE' => 'media/loader.svg',
        ];

        $set_vars(array_map($this->ilLng->txt(...), $vars));
        $room_tpl->setVariable('LOADING_IMAGE', ilUtil::getImagePath('media/loader.svg'));
    }

    private function timeFormat(): string
    {
        return match ($this->user->getTimeFormat()) {
            (string) ilCalendarSettings::TIME_FORMAT_12 => 'h:ia',
            default => 'H:i',
        };
    }
}
