<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
require_once 'Modules/Chatroom/classes/class.ilChatroomUser.php';

/**
 * Class ilChatroomInviteUsersToPrivateRoomGUI
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomInviteUsersToPrivateRoomGUI extends ilChatroomGUIHandler
{

    /**
     * @param string $method
     * @return mixed
     */
    public function executeDefault($method)
    {
        $this->byLogin();
    }

    /**
     *
     */
    public function byLogin()
    {
        $this->inviteById(ilObjUser::_lookupId($_REQUEST['user']));
    }

    /**
     * @param int $invited_id
     */
    private function inviteById($invited_id)
    {
        $this->redirectIfNoPermission('read');

        $room      = ilChatroom::byObjectId($this->gui->object->getId());
        $subRoomId = (int) $_REQUEST['sub'];
        $chat_user = new ilChatroomUser($this->ilUser, $room);

        $this->exitIfNoRoomExists($room);
        $this->exitIfNoRoomPermission($room, $subRoomId, $chat_user);

        if (!$this->isMainRoom($subRoomId)) {
            $room->inviteUserToPrivateRoom($invited_id, $subRoomId);
        }

        $connector = $this->gui->getConnector();
        $response  = $connector->sendInviteToPrivateRoom($room->getRoomId(), $subRoomId, $chat_user->getUserId(), $invited_id);

        $room->sendInvitationNotification($this->gui, $chat_user, $invited_id, $subRoomId);

        $this->sendResponse($response);
    }

    /**
     *
     */
    public function byId()
    {
        $this->inviteById($_REQUEST['user']);
    }

    /**
     *
     */
    public function getUserList()
    {
        require_once 'Services/User/classes/class.ilUserAutoComplete.php';
        $auto = new ilUserAutoComplete();
        $auto->setUser($this->ilUser);
        $auto->setPrivacyMode(ilUserAutoComplete::PRIVACY_MODE_RESPECT_USER_SETTING);
        if ($this->ilUser->isAnonymous()) {
            $auto->setSearchType(ilUserAutoComplete::SEARCH_TYPE_EQUALS);
        }

        if (($_REQUEST['fetchall'])) {
            $auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
        }
        $auto->setMoreLinkAvailable(true);
        $auto->setSearchFields(array('firstname', 'lastname'));
        $auto->setResultField('login');
        $auto->enableFieldSearchableCheck(true);
        echo $auto->getList($_REQUEST['q']);
        exit;
    }
}
