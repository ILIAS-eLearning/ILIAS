<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
require_once 'Modules/Chatroom/classes/class.ilChatroomUser.php';

/**
 * Class ilChatroomKickGUI
 * @author  Jan Posselt <jposselt@databay.de>
 * @author  Thomas Joußen <tjoussen@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomClearGUI extends ilChatroomGUIHandler
{
    /**
     * {@inheritdoc}
     */
    public function executeDefault($method)
    {
        $this->redirectIfNoPermission('moderate');

        $room = $this->getRoomByObjectId($this->gui->object->getId());
        $this->exitIfNoRoomExists($room);

        $chat_user = new ilChatroomUser($this->ilUser, $room);
        $subRoomId = $_REQUEST['sub'];

        $room->clearMessages($subRoomId);

        $connector = $this->gui->getConnector();
        $response  = $connector->sendClearMessages($room->getRoomId(), $subRoomId, $chat_user->getUserId());

        $this->sendResponse($response);
    }
}
