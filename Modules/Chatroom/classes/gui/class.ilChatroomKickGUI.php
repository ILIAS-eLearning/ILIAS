<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilChatroomKickGUI
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomKickGUI extends ilChatroomGUIHandler
{
    private function buildMessage(string $messageString, ilChatroomUser $chat_user) : stdClass
    {
        $data = new stdClass();

        $data->user = $this->gui->object->getPersonalInformation($chat_user);
        $data->userToKick = $messageString;
        $data->timestamp = date('c');
        $data->type = 'kick';

        return $data;
    }

    public function executeDefault(string $requestedMethod) : void
    {
        $this->redirectIfNoPermission(['read', 'moderate']);

        $room = ilChatroom::byObjectId($this->gui->object->getId());
        $this->exitIfNoRoomExists($room);

        $userToKick = $this->refinery->kindlyTo()->int()->transform($this->getRequestValue('user'));
        $subRoomId = $this->refinery->kindlyTo()->int()->transform($this->getRequestValue('sub', 0));

        $connector = $this->gui->getConnector();
        $response = $connector->sendKick($room->getRoomId(), $subRoomId, $userToKick);

        if (!$subRoomId && $this->isSuccessful($response)) {
            // 2013-09-11: Should already been done by the chat server
            $room->disconnectUser($userToKick);
        }

        $this->sendResponse($response);
    }

    public function main() : void
    {
        $room = ilChatroom::byObjectId($this->gui->object->getId());
        $this->exitIfNoRoomExists($room);

        $userToKick = $this->refinery->kindlyTo()->int()->transform($this->getRequestValue('user'));
        $subRoomId = $this->refinery->kindlyTo()->int()->transform($this->getRequestValue('sub'));

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
    public function sub() : void
    {
        $room = ilChatroom::byObjectId($this->gui->object->getId());
        if ($room) {
            $subRoomId = $this->refinery->kindlyTo()->int()->transform($this->getRequestValue('sub'));
            if (!$room->isOwnerOfPrivateRoom($this->ilUser->getId(), $subRoomId)) {
                if (!ilChatroom::checkUserPermissions(['read', 'moderate'], $this->gui->ref_id)) {
                    $this->ilCtrl->setParameterByClass(ilRepositoryGUI::class, 'ref_id', ROOT_FOLDER_ID);
                    $this->ilCtrl->redirectByClass(ilRepositoryGUI::class);
                }
            }

            $roomId = $room->getRoomId();

            $userToKick = $this->refinery->kindlyTo()->int()->transform($this->getRequestValue('user'));
            if ($room->userIsInPrivateRoom($subRoomId, $userToKick)) {
                $connector = $this->gui->getConnector();
                $response = $connector->sendKick($roomId, $subRoomId, $userToKick);
                $this->sendResponse($response);
            }
        }
    }
}
