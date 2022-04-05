<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilChatroomKickGUI
 * @author  Jan Posselt <jposselt@databay.de>
 * @author  Thomas Joußen <tjoussen@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomClearGUI extends ilChatroomGUIHandler
{
    public function executeDefault(string $requestedMethod) : void
    {
        $this->redirectIfNoPermission('moderate');

        $room = $this->getRoomByObjectId($this->gui->getObject()->getId());
        $this->exitIfNoRoomExists($room);

        $chat_user = new ilChatroomUser($this->ilUser, $room);
        $subRoomId = $this->getRequestValue('sub', $this->refinery->kindlyTo()->int());

        $room->clearMessages($subRoomId);

        $connector = $this->gui->getConnector();
        $response = $connector->sendClearMessages($room->getRoomId(), $subRoomId, $chat_user->getUserId());

        $this->sendResponse($response);
    }
}
