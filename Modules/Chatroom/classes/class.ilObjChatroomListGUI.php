<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Object/classes/class.ilObjectListGUI.php';

/**
 * Class ilObjChatlistListGUI
 * @author   Jan Posselt <jposselt at databay.de>
 * @version  $Id$
 * @ingroup  ModulesChatroom
 */
class ilObjChatroomListGUI extends ilObjectListGUI
{
    /**
     * @var int
     */
    private static $publicRoomObjId;

    /**
     * @var null|boolean
     */
    private static $chat_enabled = null;

    /**
     * {@inheritdoc}
     */
    public function __construct($a_context = self::CONTEXT_REPOSITORY)
    {
        parent::__construct($a_context);

        require_once 'Modules/Chatroom/classes/class.ilObjChatroom.php';

        self::$publicRoomObjId = ilObjChatroom::_getPublicObjId();
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->info_screen_enabled = true;
        $this->type = 'chtr';
        $this->gui_class_name = 'ilobjchatroomgui';

        require_once 'Modules/Chatroom/classes/class.ilObjChatroomAccess.php';
        $this->commands = ilObjChatroomAccess::_getCommands();
    }

    /**
     * {@inheritdoc}
     */
    public function getProperties()
    {
        global $DIC;

        $props = array();

        require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
        $room = ilChatroom::byObjectId($this->obj_id);
        if ($room) {
            $props[] = array(
                'alert' => false, 'property' => $DIC->language()->txt('chat_users_active'),
                'value' => $room->countActiveUsers()
            );

            if ($this->obj_id == self::$publicRoomObjId) {
                $props[] = array('alert' => false, 'property' => $DIC->language()->txt('notice'), 'value' => $DIC->language()->txt('public_room'));
            }

            if (self::$chat_enabled === null) {
                $chatSetting = new ilSetting('chatroom');
                self::$chat_enabled = (boolean) $chatSetting->get('chat_enabled');
            }

            if (!self::$chat_enabled) {
                $props[] = array('alert' => true, 'property' => $DIC->language()->txt('chtr_server_status'), 'value' => $DIC->language()->txt('server_disabled'));
            }

            if (!$room->getSetting('online_status')) {
                $props[] = array('alert' => true, 'property' => $DIC->language()->txt('status'),
                                 'value' => $DIC->language()->txt('offline'));
            }
        }

        return $props;
    }
}
