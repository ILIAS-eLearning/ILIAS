<?php

require_once './Modules/Chatroom/classes/class.ilChatroomGUIHandler.php';

/**
 * Class ilChatroomTaskHandlerMock
 * @author Thomas Joußen <tjoussen@gmx.de>
 */
class ilChatroomTaskHandlerMock extends ilChatroomGUIHandler
{
    public function executeDefault($requestedMethod)
    {
        return $requestedMethod;
    }

    public function testFunc()
    {
        return true;
    }

    /**
     * @param ilChatroom|PHPUnit_Framework_MockObject_MockObject $room
     * @param int                                                $subRoomId
     * @param int                                                $userId
     * @return bool
     */
    public function mockedCanModerate($room, $subRoomId, $userId)
    {
        return $this->canModerate($room, $subRoomId, $userId);
    }

    /**
     * @param ilChatroom $room
     */
    public function mockedExitIfNoRoomExists($room)
    {
        $this->exitIfNoRoomExists($room);
    }

    /**
     * @param ilChatroom     $room
     * @param int            $subRoomId
     * @param ilChatroomUser $user
     */
    public function mockedExitIfNoRoomPermission($room, $subRoomId, $user)
    {
        $this->exitIfNoRoomPermission($room, $subRoomId, $user);
    }

    /**
     * Override parent sendResponse for test purposes. It should echo json encoded data and exit the process.
     * @param array $response
     * @throws Exception
     */
    public function sendResponse($response)
    {
        throw new Exception(json_encode($response), 1456319946);
    }
}
