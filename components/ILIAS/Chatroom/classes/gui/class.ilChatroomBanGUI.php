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
 * Class ilChatroomBanGUI
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup components\ILIASChatroom
 */
class ilChatroomBanGUI extends ilChatroomGUIHandler
{
    private readonly ilCtrlInterface $controller;
    private readonly ilLanguage $language;
    private readonly ilObjUser $user;

    public function __construct(
        ilChatroomObjectGUI $gui,
        ilCtrlInterface $controller = null,
        ilLanguage $language = null,
        ilObjUser $user = null
    ) {
        if ($controller === null) {
            global $DIC;
            $controller = $DIC->ctrl();
        }
        $this->controller = $controller;

        if ($language === null) {
            global $DIC;
            $language = $DIC->language();
        }
        $this->language = $language;

        if ($user === null) {
            global $DIC;
            $user = $DIC->user();
        }
        $this->user = $user;

        parent::__construct($gui);
    }

    private function handleTableActions(): void
    {
        $action = $this->http->wrapper()->query()->retrieve(
            'chat_ban_table_action',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->always('')
            ])
        );
        match ($action) {
            'delete' => $this->delete(),
            default => $this->ilCtrl->redirect($this, 'show'),
        };
    }

    public function delete(): void
    {
        $userTrafo = $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int());

        $users = $this->getRequestValue('chat_ban_table_user_ids', $userTrafo, []);
        if ($users === []) {
            $this->mainTpl->setOnScreenMessage('info', $this->ilLng->txt('no_checkbox'), true);
            $this->ilCtrl->redirect($this->gui, 'ban-show');
        }

        $room = ilChatroom::byObjectId($this->gui->getObject()->getId());
        $this->exitIfNoRoomExists($room);

        $room->unbanUser($users);

        $this->mainTpl->setOnScreenMessage(
            $this->mainTpl::MESSAGE_TYPE_SUCCESS,
            $this->ilLng->txt('saved_successfully'),
            true
        );
        $this->ilCtrl->redirect($this->gui, 'ban-show');
    }

    public function executeDefault(string $requestedMethod): void
    {
        $this->show();
    }

    /**
     * Displays banned users task.
     */
    public function show(): void
    {
        $this->redirectIfNoPermission('read');

        $this->gui->switchToVisibleMode();

        $room = ilChatroom::byObjectId($this->gui->getObject()->getId());
        $this->exitIfNoRoomExists($room);

        $data = $room->getBannedUsers();
        $actorId = array_filter(array_map(static function (array $row): int {
            return (int) $row['actor_id'];
        }, $data));

        $sortable_names = ilUserUtil::getNamePresentation($actorId);
        $names = ilUserUtil::getNamePresentation($actorId, false, false, '', false, false, false);

        array_walk($data, function (&$row) use ($names, $sortable_names): void {
            if ($row['actor_id'] > 0 && isset($names[$row['actor_id']])) {
                $row['actor_display'] = $names[$row['actor_id']];
                $row['actor'] = $sortable_names[$row['actor_id']];
            } else {
                $row['actor_display'] = $this->language->txt('unknown');
                $row['actor'] = $this->language->txt('unknown');
            }
        });

        $tbl = new \ILIAS\Chatroom\Bans\BannedUsersTable(
            $room->getRoomId(),
            $data,
            $this->ilCtrl,
            $this->ilLng,
            $this->http,
            $this->uiFactory
        );

        $this->mainTpl->setContent($this->uiRenderer->render($tbl->getComponent()));
    }

    public function active(): void
    {
        $this->redirectIfNoPermission(['read', 'moderate']);

        $room = ilChatroom::byObjectId($this->gui->getObject()->getId());
        $this->exitIfNoRoomExists($room);

        $userToBan = $this->getRequestValue('user', $this->refinery->kindlyTo()->int());

        $connector = $this->gui->getConnector();
        $response = $connector->sendBan($room->getRoomId(), $userToBan);

        if ($this->isSuccessful($response)) {
            $room->banUser($userToBan, $this->user->getId());
            $room->disconnectUser($userToBan);
        }

        $this->sendResponse($response);
    }
}
