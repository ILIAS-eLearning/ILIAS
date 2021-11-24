<?php declare(strict_types=1);
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjChatlistListGUI
 * @author   Jan Posselt <jposselt at databay.de>
 * @version  $Id$
 * @ingroup  ModulesChatroom
 */
class ilObjChatroomListGUI extends ilObjectListGUI
{
    private static int $publicRoomObjId;
    private static ?bool $chat_enabled = null;

    public function __construct($a_context = self::CONTEXT_REPOSITORY)
    {
        parent::__construct($a_context);
        self::$publicRoomObjId = ilObjChatroom::_getPublicObjId();
    }

    public function init() : void
    {
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->info_screen_enabled = true;
        $this->type = 'chtr';
        $this->gui_class_name = 'ilobjchatroomgui';

        $this->commands = ilObjChatroomAccess::_getCommands();
    }

    public function getProperties() : array
    {
        $props = [];

        $this->lng->loadLanguageModule('chatroom');

        $room = ilChatroom::byObjectId((int) $this->obj_id);
        if ($room) {
            $props[] = [
                'alert' => false,
                'property' => $this->lng->txt('chat_users_active'),
                'value' => $room->countActiveUsers()
            ];

            if ($this->obj_id === self::$publicRoomObjId) {
                $props[] = [
                    'alert' => false,
                    'property' => $this->lng->txt('notice'),
                    'value' => $this->lng->txt('public_room')
                ];
            }

            if (self::$chat_enabled === null) {
                $chatSetting = new ilSetting('chatroom');
                self::$chat_enabled = (bool) $chatSetting->get('chat_enabled', '0');
            }

            if (!self::$chat_enabled) {
                $props[] = [
                    'alert' => true,
                    'property' => $this->lng->txt('chtr_server_status'),
                    'value' => $this->lng->txt('server_disabled')
                ];
            }

            if (!$room->getSetting('online_status')) {
                $props[] = [
                    'alert' => true,
                    'property' => $this->lng->txt('status'),
                    'value' => $this->lng->txt('offline')
                ];
            }
        }

        return $props;
    }
}
