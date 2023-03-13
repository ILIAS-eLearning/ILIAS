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
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomKickGUI extends ilChatroomGUIHandler
{
    private function buildMessage(string $messageString, ilChatroomUser $chat_user): stdClass
    {
        $data = new stdClass();

        $data->user = $this->gui->getObject()->getPersonalInformation($chat_user);
        $data->userToKick = $messageString;
        $data->timestamp = date('c');
        $data->type = 'kick';

        return $data;
    }

    public function executeDefault(string $requestedMethod): void
    {
        $this->main();
    }

    public function main(): void
    {
        $this->redirectIfNoPermission(['read', 'moderate']);

        $room = ilChatroom::byObjectId($this->gui->getObject()->getId());
        $this->exitIfNoRoomExists($room);

        $userToKick = $this->getRequestValue('user', $this->refinery->kindlyTo()->int());

        $connector = $this->gui->getConnector();
        $response = $connector->sendKick($room->getRoomId(), $userToKick);

        if ($this->isSuccessful($response)) {
            // 2013-09-11: Should already been done by the chat server
            $room->disconnectUser($userToKick);
        }

        $this->sendResponse($response);
    }
}
