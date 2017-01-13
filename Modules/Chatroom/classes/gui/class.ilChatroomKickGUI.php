<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
require_once 'Modules/Chatroom/classes/class.ilChatroomUser.php';

/**
 * Class ilChatroomKickGUI
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomKickGUI extends ilChatroomGUIHandler
{

	/**
	 * Instantiates stdClass, sets $data->user and $data->userToKick using given
	 * $messageString and $chat_user and returns $data
	 * @param string         $messageString
	 * @param ilChatroomUser $chat_user
	 * @return stdClass
	 */
	private function buildMessage($messageString, ilChatroomUser $chat_user)
	{
		$data = new stdClass();

		$data->user       = $this->gui->object->getPersonalInformation($chat_user);
		$data->userToKick = $messageString;
		$data->timestamp  = date('c');
		$data->type       = 'kick';

		return $data;
	}

	/**
	 * Displays window box to kick a user fetched from $_REQUEST['user'].
	 * @global ilObjUser $ilUser
	 * @param string     $method
	 */
	public function executeDefault($method)
	{
		$this->redirectIfNoPermission(array('read', 'moderate'));

		$room       = ilChatroom::byObjectId($this->gui->object->getId());
		$userToKick = $_REQUEST['user'];
		$subRoomId  = $_REQUEST['sub'];

		$this->exitIfNoRoomExists($room);

		$connector = $this->gui->getConnector();
		$response  = $connector->sendKick($room->getRoomId(), $subRoomId, $userToKick);

		if($this->isSuccessful($response) && !$subRoomId)
		{
			// 2013-09-11: Should already been done by the chat server
			$room->disconnectUser($userToKick);
		}

		$this->sendResponse($response);
	}

	public function main()
	{
		$room       = ilChatroom::byObjectId($this->gui->object->getId());
		$userToKick = $_REQUEST['user'];
		$subRoomId  = $_REQUEST['sub'];

		$this->exitIfNoRoomExists($room);

		$connector = $this->gui->getConnector();
		$response  = $connector->sendKick($room->getRoomId(), $subRoomId, $userToKick);

		if($this->isSuccessful($response))
		{
			// 2013-09-11: Should already been done by the chat server
			$room->disconnectUser($userToKick);
		}

		$this->sendResponse($response);
	}

	/**
	 * Kicks user from subroom into mainroom
	 * @global ilObjUser $ilUser
	 */
	public function sub()
	{
		global $ilUser, $ilCtrl;

		$room = ilChatroom::byObjectId($this->gui->object->getId());

		if($room)
		{
			if(!$room->isOwnerOfPrivateRoom($ilUser->getId(), $_REQUEST['sub']))
			{
				if(!ilChatroom::checkPermissionsOfUser($ilUser->getId(), array('read', 'moderate'), $this->gui->ref_id))
				{
					$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", ROOT_FOLDER_ID);
					$ilCtrl->redirectByClass("ilrepositorygui", "");
				}
			}

			$roomId     = $room->getRoomId();
			$subRoomId  = $_REQUEST['sub'];
			$userToKick = $_REQUEST['user'];

			if($room->userIsInPrivateRoom($subRoomId, $userToKick))
			{
				$connector = $this->gui->getConnector();
				$response  = $connector->sendKick($roomId, $subRoomId, $userToKick);
				$this->sendResponse($response);
			}

			/*
			if( $responseObject->success == true && $room->getSetting( 'enable_history' ) )
			{
			//$room->addHistoryEntry( $message, $recipient, $publicMessage );
			}



			$message = json_encode(array(
										'type'  => 'userjustkicked',
										'user'  => $params['user'],
										'sub'   => $params['sub']
								   ));

			$connector->sendMessage($room->getRoomId(), $message, array(
																	   'public'  => 1,
																	   'sub'     => 0
																  ));
		}
		else
		{
			$response = json_encode(array(
										 'success'  => true,
										 'message'  => 'was not in room'
									));
		}

		echo $response;
		exit;*/
		}
	}
}