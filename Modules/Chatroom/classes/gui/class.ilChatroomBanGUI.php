<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
require_once 'Modules/Chatroom/classes/class.ilChatroomUser.php';

/**
 * Class ilChatroomBanGUI
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomBanGUI extends ilChatroomGUIHandler
{
	/**
	 * Unbans users fetched from $_REQUEST['banned_user_id'].
	 */
	public function delete()
	{
		$users = $_REQUEST['banned_user_id'];

		if(!is_array($users))
		{
			ilUtil::sendInfo($this->ilLng->txt('no_checkbox'), true);
			$this->ilCtrl->redirect($this->gui, 'ban-show');
		}

		$room = ilChatroom::byObjectId($this->gui->object->getId());
		$room->unbanUser($users);

		$this->ilCtrl->redirect($this->gui, 'ban-show');
	}

	/**
	 * {@inheritdoc}
	 */
	public function executeDefault($method)
	{
		$this->show();
	}

	/**
	 * Displays banned users task.
	 */
	public function show()
	{
		include_once 'Modules/Chatroom/classes/class.ilChatroom.php';

		$this->redirectIfNoPermission('read');

		$this->gui->switchToVisibleMode();

		require_once 'Modules/Chatroom/classes/class.ilBannedUsersTableGUI.php';

		$table = new ilBannedUsersTableGUI($this->gui, 'ban-show');
		$table->setFormAction($GLOBALS['DIC']->ctrl()->getFormAction($this->gui, 'ban-show'));

		$room = ilChatroom::byObjectId($this->gui->object->getId());
		if($room)
		{
			$data = $room->getBannedUsers();

			$actorIDs = array_filter(array_map(function($row) {
				return $row['actor_id'];
			}, $data));

			require_once 'Services/User/classes/class.ilUserUtil.php';
			$sortable_names = ilUserUtil::getNamePresentation($actorIDs);
			$names          = ilUserUtil::getNamePresentation($actorIDs, false, false, '', false, false, false);

			array_walk($data, function(&$row) use ($names, $sortable_names) {
				if($row['actor_id'] > 0 && isset($names[$row['actor_id']]))
				{
					$row['actor_display'] = $names[$row['actor_id']];
					$row['actor']         = $sortable_names[$row['actor_id']];
				}
				else
				{
					$row['actor_display'] = $GLOBALS['DIC']->language()->txt('unknown');
					$row['actor']         = $GLOBALS['DIC']->language()->txt('unknown');
				}
			});

			$table->setData($data);
		}

		$this->gui->tpl->setVariable('ADM_CONTENT', $table->getHTML());
	}

	/**
	 * Kicks and bans user, fetched from $_REQUEST['user'] and adds history entry.
	 */
	public function active()
	{
		$this->redirectIfNoPermission(array('read', 'moderate'));

		$room      = ilChatroom::byObjectId($this->gui->object->getId());
		$subRoomId = $_REQUEST['sub'];
		$userToBan = $_REQUEST['user'];

		$this->exitIfNoRoomExists($room);

		$connector = $this->gui->getConnector();
		$response  = $connector->sendBan($room->getRoomId(), $subRoomId, $userToBan); // @TODO Respect Scope

		if($this->isSuccessful($response))
		{
			$room->banUser($_REQUEST['user'], $GLOBALS['DIC']->user()->getId());
			$room->disconnectUser($_REQUEST['user']);
		}

		$this->sendResponse($response);
	}
}