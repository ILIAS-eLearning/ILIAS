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

/**
 * Class ilChatroomKickGUI
 * @author  Jan Posselt <jposselt@databay.de>
 * @author  Thomas Joußen <tjoussen@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomClearGUI extends ilChatroomGUIHandler
{
    public function executeDefault(string $requestedMethod): void
    {
        $this->redirectIfNoPermission('moderate');

        $room = $this->getRoomByObjectId($this->gui->getObject()->getId());
        $this->exitIfNoRoomExists($room);

        $chat_user = new ilChatroomUser($this->ilUser, $room);

        $room->clearMessages();

        $connector = $this->gui->getConnector();
        $response = $connector->sendClearMessages($room->getRoomId(), $chat_user->getUserId());

        $this->sendResponse($response);
    }
}
