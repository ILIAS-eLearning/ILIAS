<?php declare(strict_types=1);
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilChatroomInviteUsersToPrivateRoomGUI
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomInviteUsersToPrivateRoomGUI extends ilChatroomGUIHandler
{
    public function executeDefault(string $requestedMethod) : void
    {
        $this->byLogin();
    }

    public function byLogin() : void
    {
        $user = $this->refinery->kindlyTo()->string()->transform($this->getRequestValue('user'));
        $this->inviteById((int) ilObjUser::_lookupId($user));
    }

    private function inviteById(int $invited_id) : void
    {
        $this->redirectIfNoPermission('read');

        $room = ilChatroom::byObjectId($this->gui->object->getId());
        $this->exitIfNoRoomExists($room);

        $chat_user = new ilChatroomUser($this->ilUser, $room);
        $subRoomId = $this->refinery->kindlyTo()->int()->transform($this->getRequestValue('sub'));
        $this->exitIfNoRoomModeratePermission($room, $subRoomId, $chat_user);

        if (!$this->isMainRoom($subRoomId)) {
            $room->inviteUserToPrivateRoom($invited_id, $subRoomId);
        }

        $connector = $this->gui->getConnector();
        $response = $connector->sendInviteToPrivateRoom(
            $room->getRoomId(),
            $subRoomId,
            $chat_user->getUserId(),
            $invited_id
        );

        $room->sendInvitationNotification($this->gui, $chat_user, $invited_id, $subRoomId);

        $this->sendResponse($response);
    }

    public function byId() : void
    {
        $this->inviteById($this->refinery->kindlyTo()->int()->transform($this->getRequestValue('user')));
    }

    public function getUserList() : void
    {
        $auto = new ilUserAutoComplete();
        $auto->setUser($this->ilUser);
        $auto->setPrivacyMode(ilUserAutoComplete::PRIVACY_MODE_RESPECT_USER_SETTING);
        if ($this->ilUser->isAnonymous()) {
            $auto->setSearchType(ilUserAutoComplete::SEARCH_TYPE_EQUALS);
        }
        
        $query = ilUtil::stripSlashes(
            $this->refinery->kindlyTo()->string()->transform($this->getRequestValue('q'))
        );

        if ($this->http->wrapper()->query()->has('fetchall')) {
            $auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
        }
        $auto->setMoreLinkAvailable(true);
        $auto->setSearchFields(['firstname', 'lastname']);
        $auto->setResultField('login');
        $auto->enableFieldSearchableCheck(true);

        $this->sendResponse($auto->getList($query), true);
    }
}
