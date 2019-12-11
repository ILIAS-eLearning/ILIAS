<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
require_once 'Modules/Chatroom/classes/class.ilChatroomUser.php';

/**
 * Class ilChatroomPrivateRoomGUI
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomPrivateRoomGUI extends ilChatroomGUIHandler
{
    public function executeDefault($method)
    {
    }

    public function create()
    {
        $this->redirectIfNoPermission('read');

        $room      = ilChatroom::byObjectId($this->gui->object->getId());
        $chat_user = new ilChatroomUser($this->ilUser, $room);

        $this->exitIfNoRoomExists($room);
        $this->exitIfNoRoomSubscription($room, $chat_user);

        $title     = $room->getUniquePrivateRoomTitle($_REQUEST['title']);
        $subRoomId = $room->addPrivateRoom($title, $chat_user, array('public' => false));

        $connector = $this->gui->getConnector();
        $response  = $connector->sendCreatePrivateRoom($room->getRoomId(), $subRoomId, $chat_user->getUserId(), $title);

        if ($this->isSuccessful($response)) {
            $response = array(
                'success'   => true,
                'title'     => $title,
                'owner'     => $chat_user->getUserId(),
                'subRoomId' => $subRoomId
            );
        }

        $this->sendResponse($response);
    }

    /**
     * @param ilChatroom     $room
     * @param ilChatroomUser $chat_user
     */
    protected function exitIfNoRoomSubscription($room, $chat_user)
    {
        if (!$room->isSubscribed($chat_user->getUserId())) {
            $this->sendResponse(array(
                'success' => false,
                'reason'  => 'not subscribed'
            ));
        }
    }

    public function delete()
    {
        $room      = ilChatroom::byObjectId($this->gui->object->getId());
        $subRoom   = $_REQUEST['sub'];
        $chat_user = new ilChatroomUser($this->ilUser, $room);

        $this->exitIfNoRoomExists($room);
        $this->exitIfNoRoomSubscription($room, $chat_user);

        $room->closePrivateRoom($subRoom);

        $connector = $this->gui->getConnector();
        $response  = $connector->sendDeletePrivateRoom($room->getRoomId(), $subRoom, $chat_user->getUserId());

        $this->sendResponse($response);
    }

    public function leave()
    {
        $room      = ilChatroom::byObjectId($this->gui->object->getId());
        $chat_user = new ilChatroomUser($this->ilUser, $room);
        $subRoom   = $_REQUEST['sub'];

        $this->exitIfNoRoomExists($room);
        $this->exitIfNoRoomSubscription($room, $chat_user);

        $connector = $this->gui->getConnector();
        $response  = $connector->sendLeavePrivateRoom($room->getRoomId(), $subRoom, $chat_user->getUserId());

        if ($room->userIsInPrivateRoom($subRoom, $chat_user->getUserId())) {
            $room->unsubscribeUserFromPrivateRoom($subRoom, $chat_user->getUserId());
        }

        $this->sendResponse($response);
    }

    public function enter()
    {
        $this->redirectIfNoPermission('read');

        $room      = ilChatroom::byObjectId($this->gui->object->getId());
        $subRoom   = $_REQUEST['sub'];
        $chat_user = new ilChatroomUser($this->ilUser, $room);

        $this->exitIfNoRoomExists($room);
        $this->exitIfEnterRoomIsNotAllowed($room, $subRoom, $chat_user);

        $connector = $this->gui->getConnector();
        $response  = $connector->sendEnterPrivateRoom($room->getRoomId(), $subRoom, $chat_user->getUserId());

        if ($this->isSuccessful($response)) {
            $room->subscribeUserToPrivateRoom($subRoom, $chat_user->getUserId());
        }

        $this->sendResponse($response);
    }

    /**
     * @param ilChatroom     $room
     * @param int            $subRoom
     * @param ilChatroomUser $chat_user
     */
    protected function exitIfEnterRoomIsNotAllowed($room, $subRoom, $chat_user)
    {
        if (!$room->isAllowedToEnterPrivateRoom($chat_user->getUserId(), $subRoom)) {
            $this->sendResponse(array(
                'success' => false,
                'reason'  => 'not allowed enter to private room'
            ));
        }
    }

    public function listUsers()
    {
        $room = ilChatroom::byObjectId($this->gui->object->getId());

        $response = $room->listUsersInPrivateRoom($_REQUEST['sub']);
        $this->sendResponse($response);
    }
}
