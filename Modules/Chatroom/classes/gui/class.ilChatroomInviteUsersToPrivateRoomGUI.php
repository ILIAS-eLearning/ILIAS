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
 * Class ilChatroomInviteUsersToPrivateRoomGUI
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomInviteUsersToPrivateRoomGUI extends ilChatroomGUIHandler
{
    public function executeDefault(string $requestedMethod): void
    {
        $this->byLogin();
    }

    public function byLogin(): void
    {
        $user = $this->getRequestValue('user', $this->refinery->kindlyTo()->string());
        $this->inviteById((int) ilObjUser::_lookupId($user));
    }

    private function inviteById(int $invited_id): void
    {
        $this->redirectIfNoPermission('read');

        $room = ilChatroom::byObjectId($this->gui->getObject()->getId());
        $this->exitIfNoRoomExists($room);

        $chat_user = new ilChatroomUser($this->ilUser, $room);
        $subRoomId = $this->getRequestValue('sub', $this->refinery->kindlyTo()->int());
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

    public function byId(): void
    {
        $this->inviteById($this->getRequestValue('user', $this->refinery->kindlyTo()->int()));
    }

    public function getUserList(): void
    {
        $auto = new ilUserAutoComplete();
        $auto->setUser($this->ilUser);
        $auto->setPrivacyMode(ilUserAutoComplete::PRIVACY_MODE_RESPECT_USER_SETTING);
        if ($this->ilUser->isAnonymous()) {
            $auto->setSearchType(ilUserAutoComplete::SEARCH_TYPE_EQUALS);
        }

        $query = ilUtil::stripSlashes(
            $this->getRequestValue('q', $this->refinery->kindlyTo()->string(), '')
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
