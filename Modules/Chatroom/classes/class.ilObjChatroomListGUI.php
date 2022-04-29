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

    public function __construct(int $a_context = self::CONTEXT_REPOSITORY)
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

        $room = ilChatroom::byObjectId($this->obj_id);
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
