<?php

declare(strict_types=1);

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
        $this->redirectIfNoPermission(['read', 'moderate']);

        $room = ilChatroom::byObjectId($this->gui->getObject()->getId());
        $this->exitIfNoRoomExists($room);

        $userToKick = $this->getRequestValue('user', $this->refinery->kindlyTo()->int());
        $subRoomId = $this->getRequestValue('sub', $this->refinery->kindlyTo()->int(), 0);

        $connector = $this->gui->getConnector();
        $response = $connector->sendKick($room->getRoomId(), $subRoomId, $userToKick);

        if (!$subRoomId && $this->isSuccessful($response)) {
            // 2013-09-11: Should already been done by the chat server
            $room->disconnectUser($userToKick);
        }

        $this->sendResponse($response);
    }

    public function main(): void
    {
        $room = ilChatroom::byObjectId($this->gui->getObject()->getId());
        $this->exitIfNoRoomExists($room);

        $userToKick = $this->getRequestValue('user', $this->refinery->kindlyTo()->int());
        $subRoomId = $this->getRequestValue('sub', $this->refinery->kindlyTo()->int());

        $connector = $this->gui->getConnector();
        $response = $connector->sendKick($room->getRoomId(), $subRoomId, $userToKick);

        if ($this->isSuccessful($response)) {
            // 2013-09-11: Should already been done by the chat server
            $room->disconnectUser($userToKick);
        }

        $this->sendResponse($response);
    }

    /**
     * Kicks user from subroom into mainroom
     */
    public function sub(): void
    {
        $room = ilChatroom::byObjectId($this->gui->getObject()->getId());
        if ($room) {
            $subRoomId = $this->getRequestValue('sub', $this->refinery->kindlyTo()->int());
            if (
                !ilChatroom::checkUserPermissions(['read', 'moderate'], $this->gui->getRefId()) &&
                !$room->isOwnerOfPrivateRoom(
                    $this->ilUser->getId(),
                    $subRoomId
                )
            ) {
                $this->ilCtrl->setParameterByClass(ilRepositoryGUI::class, 'ref_id', ROOT_FOLDER_ID);
                $this->ilCtrl->redirectByClass(ilRepositoryGUI::class);
            }

            $roomId = $room->getRoomId();

            $userToKick = $this->getRequestValue('user', $this->refinery->kindlyTo()->int());
            if ($room->userIsInPrivateRoom($subRoomId, $userToKick)) {
                $connector = $this->gui->getConnector();
                $response = $connector->sendKick($roomId, $subRoomId, $userToKick);
                $this->sendResponse($response);
            }
        }
    }
}
