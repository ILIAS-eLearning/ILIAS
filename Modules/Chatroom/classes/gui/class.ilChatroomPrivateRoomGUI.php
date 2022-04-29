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
 * Class ilChatroomPrivateRoomGUI
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomPrivateRoomGUI extends ilChatroomGUIHandler
{
    protected function exitIfEnterRoomIsNotAllowed(ilChatroom $room, int $subRoom, ilChatroomUser $chat_user) : void
    {
        if (!$room->isAllowedToEnterPrivateRoom($chat_user->getUserId(), $subRoom)) {
            $this->sendResponse([
                'success' => false,
                'reason' => 'not allowed enter to private room'
            ]);
        }
    }

    protected function exitIfNoRoomSubscription(ilChatroom $room, ilChatroomUser $chat_user) : void
    {
        if (!$room->isSubscribed($chat_user->getUserId())) {
            $this->sendResponse([
                'success' => false,
                'reason' => 'not subscribed'
            ]);
        }
    }

    public function executeDefault(string $requestedMethod) : void
    {
    }

    public function create() : void
    {
        $this->redirectIfNoPermission('read');

        $room = ilChatroom::byObjectId($this->gui->getObject()->getId());
        $this->exitIfNoRoomExists($room);

        $chat_user = new ilChatroomUser($this->ilUser, $room);
        $this->exitIfNoRoomSubscription($room, $chat_user);

        $title = $room->getUniquePrivateRoomTitle(ilUtil::stripSlashes(
            $this->getRequestValue('title', $this->refinery->kindlyTo()->string())
        ));
        $subRoomId = $room->addPrivateRoom($title, $chat_user, ['public' => false]);

        $connector = $this->gui->getConnector();
        $response = $connector->sendCreatePrivateRoom($room->getRoomId(), $subRoomId, $chat_user->getUserId(), $title);

        if ($this->isSuccessful($response)) {
            $response = [
                'success' => true,
                'title' => $title,
                'owner' => $chat_user->getUserId(),
                'subRoomId' => $subRoomId
            ];
        }

        $this->sendResponse($response);
    }

    public function delete() : void
    {
        $room = ilChatroom::byObjectId($this->gui->getObject()->getId());

        $this->exitIfNoRoomExists($room);

        $subRoom = $this->getRequestValue('sub', $this->refinery->kindlyTo()->int());
        $chat_user = new ilChatroomUser($this->ilUser, $room);
        $this->exitIfNoRoomSubscription($room, $chat_user);

        $room->closePrivateRoom($subRoom);

        $connector = $this->gui->getConnector();
        $response = $connector->sendDeletePrivateRoom($room->getRoomId(), $subRoom, $chat_user->getUserId());

        $this->sendResponse($response);
    }

    public function leave() : void
    {
        $this->redirectIfNoPermission('read');

        $room = ilChatroom::byObjectId($this->gui->getObject()->getId());

        $this->exitIfNoRoomExists($room);
        
        $subRoom = $this->getRequestValue('sub', $this->refinery->kindlyTo()->int());
        $chat_user = new ilChatroomUser($this->ilUser, $room);
        $this->exitIfNoRoomSubscription($room, $chat_user);

        $connector = $this->gui->getConnector();
        $response = $connector->sendLeavePrivateRoom($room->getRoomId(), $subRoom, $chat_user->getUserId());

        if ($room->userIsInPrivateRoom($subRoom, $chat_user->getUserId())) {
            $room->unsubscribeUserFromPrivateRoom($subRoom, $chat_user->getUserId());
        }

        $this->sendResponse($response);
    }

    public function enter() : void
    {
        $this->redirectIfNoPermission('read');

        $room = ilChatroom::byObjectId($this->gui->getObject()->getId());
        $this->exitIfNoRoomExists($room);

        $subRoom = $this->getRequestValue('sub', $this->refinery->kindlyTo()->int());
        $chat_user = new ilChatroomUser($this->ilUser, $room);
        $this->exitIfEnterRoomIsNotAllowed($room, $subRoom, $chat_user);

        $connector = $this->gui->getConnector();
        $response = $connector->sendEnterPrivateRoom($room->getRoomId(), $subRoom, $chat_user->getUserId());

        if ($this->isSuccessful($response)) {
            $room->subscribeUserToPrivateRoom($subRoom, $chat_user->getUserId());
        }

        $this->sendResponse($response);
    }

    public function listUsers() : void
    {
        $this->redirectIfNoPermission('read');

        $room = ilChatroom::byObjectId($this->gui->getObject()->getId());
        $this->exitIfNoRoomExists($room);

        $response = $room->listUsersInPrivateRoom(
            $this->getRequestValue('sub', $this->refinery->kindlyTo()->int())
        );
        $this->sendResponse($response);
    }
}
