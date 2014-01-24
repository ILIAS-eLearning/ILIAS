<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilChatroomKickTask
 *
 * @author Jan Posselt <jposselt@databay.de>
 * @version $Id$
 *
 * @ingroup ModulesChatroom
 */
class ilChatroomKickTask extends ilChatroomTaskHandler
{

	private $gui;

	/**
	 * Constructor
	 *
	 * Sets $this->gui using given $gui.
	 *
	 * @param ilChatroomObjectGUI $gui
	 */
	public function __construct(ilChatroomObjectGUI $gui)
	{
	    $this->gui = $gui;
	}

	/**
	 * Displays window box to kick a user fetched from $_REQUEST['user'].
	 *
	 * @global ilObjUser $ilUser
	 * @param string $method
	 */
	public function executeDefault($method)
	{
		/**
		 * @var $ilUser ilObjUser
		 * @var $ilCtrl ilCtrl 
		 */
		global $ilUser, $ilCtrl;

		require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
		require_once 'Modules/Chatroom/classes/class.ilChatroomUser.php';

		if(!ilChatroom::checkUserPermissions(array('read', 'moderate'), $this->gui->ref_id))
		{
			$ilCtrl->setParameterByClass('ilrepositorygui', 'ref_id', ROOT_FOLDER_ID);
			$ilCtrl->redirectByClass('ilrepositorygui', '');
		}

		$room = ilChatroom::byObjectId($this->gui->object->getId());

		if($room)
		{
			// if user is in scope
			$scope = $room->getRoomId();

			$chat_user = new ilChatroomUser($ilUser, $room);

			$messageObject = $this->buildMessage(
				ilUtil::stripSlashes($_REQUEST['user']), $chat_user
			);

			$message = json_encode($messageObject);

			$params = array(
				'message'    => $message,
				'userToKick' => $_REQUEST['user']
			);

			$query          = http_build_query($params);
			$connector      = $this->gui->getConnector();
			$response       = $connector->kick($scope, $query);
			$responseObject = json_decode($response);

			if($responseObject->success == true)
			{
				$room->addHistoryEntry($messageObject, '', 1);

				$message = json_encode(array(
					'type' => 'userjustkicked',
					'user' => $params['userToKick'],
					'sub'  => 0
				));

				$connector->sendMessage($room->getRoomId(), $message, array(
					'public' => 1,
					'sub'    => 0
				));

				// 2013-09-11: Should already been done by the chat server
				$room->disconnectUser($params['userToKick']);
			}
		}
		else
		{
			$response = json_encode(array(
				'success' => false,
				'reason'  => 'unkown room'
			));
		}

		echo $response;
		exit;
	}

	/**
	 * Instantiates stdClass, sets $data->user and $data->userToKick using given
	 * $messageString and $chat_user and returns $data
	 *
	 * @param string $messageString
	 * @param ilChatroomUser $chat_user
	 * @return stdClass
	 */
	private function buildMessage($messageString, ilChatroomUser $chat_user)
	{
		$data = new stdClass();

		$data->user	    = $this->gui->object->getPersonalInformation( $chat_user );
		$data->userToKick   = $messageString;
		$data->timestamp    = date( 'c' );
		$data->type	    = 'kick';

		return $data;
	}

	/**
	 * Kicks user from subroom into mainroom
	 * 
	 * @global ilObjUser $ilUser 
	 */
	public function sub()
	{
	    global $ilUser, $ilCtrl;

	    require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
	    require_once 'Modules/Chatroom/classes/class.ilChatroomUser.php';

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

			$scope          = $room->getRoomId();
			$params         = array();
			$params['user'] = $_REQUEST['user'];
			$params['sub']  = $_REQUEST['sub'];

			if($room->userIsInPrivateRoom($params['sub'], $params['user']))
			{
				$query          = http_build_query($params);
				$connector      = $this->gui->getConnector();
				$response       = $connector->leavePrivateRoom($scope, $query);
				$responseObject = json_decode($response);
				/*
				if( $responseObject->success == true && $room->getSetting( 'enable_history' ) )
				{
				//$room->addHistoryEntry( $message, $recipient, $publicMessage );
				}
	*/
				$room->unsubscribeUserFromPrivateRoom($params['sub'], $params['user']);
				$this->recantInvitation($params['sub'], $params['user']);

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
			exit;
		}
	}

	/**
	 * Recant invitation for given $user_id in given $subroom_id
	 * 
	 * @global ilDB $ilDB
	 * @param integer $subroom_id
	 * @param integer $user_id 
	 */
	public function recantInvitation($subroom_id, $user_id)
	{
	    global $ilDB;

	    $query = "
		SELECT		proom_id
		FROM		chatroom_proomaccess
		WHERE		proom_id = %s
		AND		user_id = %s
	    ";

	    $types  = array( 'integer', 'integer' );
	    $values = array( $subroom_id, $user_id );

	    $res = $ilDB->queryF( $query, $types, $values );

	    if( $row = $ilDB->fetchAssoc( $res ) )
	    {
		$delete = "
		    DELETE
		    FROM	chatroom_proomaccess
		    WHERE	proom_id = %s
		    AND		user_id = %s
		";

		$ilDB->manipulateF( $delete, $types, $values );
	    }
	}

}

?>