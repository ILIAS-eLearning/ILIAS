<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
require_once 'Modules/Chatroom/classes/class.ilChatroomUser.php';

/**
 * Class ilChatroomPrivateRoomTask
 *
 * @author Jan Posselt <jposselt@databay.de>
 * @version $Id$
 *
 * @ingroup ModulesChatroom
 */
class ilChatroomPrivateRoomTask extends ilChatroomTaskHandler
{

	private $gui;

	/**
	 * Constructor
	 *
	 * Sets $this->gui using given $gui
	 *
	 * @param ilChatroomObjectGUI $gui
	 */
	public function __construct(ilChatroomObjectGUI $gui)
	{
		$this->gui = $gui;
	}

	public function executeDefault($method) {

	}

	/**
	 * Prepares and posts message fetched from $_REQUEST['message']
	 * to recipients fetched from $_REQUEST['recipient']
	 * and adds an entry to history if successful.
	 *
	 * @global ilTemplate $tpl
	 * @global ilObjUser $ilUser
	 * @param string $method
	 */
	public function create()
	{
	    global $tpl, $ilUser, $ilCtrl;

	    require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
	    require_once 'Modules/Chatroom/classes/class.ilChatroomUser.php';

	    if ( !ilChatroom::checkUserPermissions( 'read', $this->gui->ref_id ) )
	    {
	    	$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", ROOT_FOLDER_ID);
	    	$ilCtrl->redirectByClass("ilrepositorygui", "");
	    }

	    $room = ilChatroom::byObjectId( $this->gui->object->getId() );

	    $chat_user = new ilChatroomUser($ilUser, $room);
	    $user_id = $chat_user->getUserId();

	    if( !$room )
	    {
		$response = json_encode( array(
			'success'	=> false,
			'reason'	=> 'unkown room'
		) );
		echo json_encode( $response );
		exit;
	    }
	    else if( !$room->isSubscribed( $chat_user->getUserId() ) )
	    {
		$response = json_encode( array(
			'success'	=> false,
			'reason'	=> 'not subscribed'
		) );
		echo json_encode( $response );
		exit;
	    }

	    $title	    = $room->getUniquePrivateRoomTitle($_REQUEST['title']);
	    $connector  = $this->gui->getConnector();
	    $response   = $connector->createPrivateRoom($room, $title, $chat_user);

	    echo json_encode($response);
	    exit;
	}

	public function delete()
	{
	    global $tpl, $ilUser, $rbacsystem;

	    require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
	    require_once 'Modules/Chatroom/classes/class.ilChatroomUser.php';

	    $room = ilChatroom::byObjectId( $this->gui->object->getId() );

	    $chat_user = new ilChatroomUser($ilUser, $room);
	    $user_id = $chat_user->getUserId();

	    if( !$room )
	    {
		$response = json_encode( array(
			    'success'	=> false,
			    'reason'	=> 'unkown room'
			    ) );
		echo json_encode( $response );
		exit;
	    }
	    else if( !$room->isOwnerOfPrivateRoom( $user_id, $_REQUEST['sub'] ) && !$rbacsystem->checkAccess('moderate', $this->gui->getRefId()) )
	    {
		$response = json_encode( array(
			'success'   => false,
			'reason'    => 'not owner of private room'
		) );
		echo json_encode( $response );
		exit;
	    }

	    $scope = $room->getRoomId();
	    $params = array();

	    $params['user'] =  $chat_user->getUserId();
	    $params['id']   = $room->closePrivateRoom($_REQUEST['sub'], $chat_user, $settings);
	    $query	    = http_build_query( $params );
	    $connector	    = $this->gui->getConnector();

	    if( true || $responseObject->success == true )
	    {
		$message = json_encode( array(
			  'type'	=> 'private_room_deleted',
			//'recipients'	=> $chat_user->getUserId(),//$users,
			  'timestamp'	=> date( 'c' ),
			  'public'	=> 1,
			//'title'	=> $title,
			  'id'		=> $responseObject->id,
			  'proom_id'	=> $_REQUEST['sub'],
			  'message'	=> array(
					    'message'	=> 'room deleted',
					    'public'	=> '1',
					    'user'	=> 'system'
					    )
		) );

		$connector->sendMessage( $room->getRoomId(), $message, array('public' => 1) );
	    }

	    $response = json_encode(array('success' => true));
	    echo $response;
	    exit;
	}

	/**
	 * Prepares and posts message fetched from $_REQUEST['message']
	 * to recipients fetched from $_REQUEST['recipient']
	 * and adds an entry to history if successful.
	 *
	 * @global ilTemplate $tpl
	 * @global ilObjUser $ilUser
	 * @param string $method
	 */
	public function leave()
	{
		global $tpl, $ilUser;

		$room = ilChatroom::byObjectId( $this->gui->object->getId() );

		$chat_user = new ilChatroomUser( $ilUser, $room );
		$user_id = $chat_user->getUserId();
		$connector = $this->gui->getConnector();

		if( !$room )
		{
			$response = json_encode( array(
				    'success' => false,
				    'reason' => 'unkown room'
				    ) );
				    echo json_encode( $response );
				    exit;
		}
		else if( !$room->isSubscribed( $chat_user->getUserId() ) )
		{
			$response = json_encode( array(
				    'success' => false,
				    'reason' => 'not subscribed'
				    ) );
				    echo json_encode( $response );
				    exit;
		}

		$scope = $room->getRoomId();
		$params = array();

		$params['user'] = $chat_user->getUserId();
		$params['sub'] = $_REQUEST['sub'];

		$message = json_encode( array(
			'type' => 'private_room_left',
			'user' => $params['user'],
			'sub'	=> $params['sub']
		));

		$connector->sendMessage( $room->getRoomId(), $message, array('public' => 1, 'sub' => $params['sub']) );

		if( $room->userIsInPrivateRoom( $params['sub'], $params['user'] ) )
		{
			//$params			= array_merge( $params, array('message' => $message) );
			$query = http_build_query( $params );
			$connector = $this->gui->getConnector();
			$response = $connector->leavePrivateRoom( $scope, $query );
			$responseObject = json_decode( $response );
/*
			if( $responseObject->success == true && $room->getSetting( 'enable_history' ) )
			{
				//$room->addHistoryEntry( $message, $recipient, $publicMessage );
			}
*/
			$room->unsubscribeUserFromPrivateRoom( $params['sub'], $params['user'] );
		}
		else
		{
			$response = json_encode( array('success' => true, 'message' => 'was not in room') );
		}

		echo $response;
		exit;
	}

	public function enter()
	{
		global $tpl, $ilUser;

		ilChatroom::checkUserPermissions( 'read', $this->gui->ref_id );

		$room = ilChatroom::byObjectId( $this->gui->object->getId() );

		$chat_user = new ilChatroomUser( $ilUser, $room );
		$user_id = $chat_user->getUserId();

		if( !$room )
		{
			$response = json_encode( array(
				    'success' => false,
				    'reason' => 'unkown room'
				    ) );
				    echo json_encode( $response );
				    exit;
		}
		else if( !$room->isAllowedToEnterPrivateRoom( $chat_user->getUserId(), $_REQUEST['sub'] ) )
		{
			$response = json_encode( array(
				    'success' => false,
				    'reason' => 'not allowed enter to private room'
				    ) );
				    	
				    echo json_encode( $response );
				    exit;
		}

		$scope = $room->getRoomId();
		$params = array();

		$params['user'] = $chat_user->getUserId();
		$params['sub'] = $_REQUEST['sub'];
		$params['message'] = json_encode( array(
			'type' => 'private_room_entered',
			'user' => $user_id
		));


		$query = http_build_query( $params );
		$connector = $this->gui->getConnector();
		$response = $connector->enterPrivateRoom( $scope, $query );
		$responseObject = json_decode( $response );

		$message = json_encode( array(
			'type' => 'private_room_entered',
			'user' => $params['user'],
			'sub'	=> $params['sub']
		));

		$connector->sendMessage( $room->getRoomId(), $message, array('public' => 1, 'sub' => $params['sub']) );

		if( $responseObject->success == true )
		{
			$room->subscribeUserToPrivateRoom( $params['sub'], $params['user'] );
		}

		echo $response;
		exit;
	}

	public function listUsers()
	{
		global $ilUser;

		$room = ilChatroom::byObjectId( $this->gui->object->getId() );

		echo json_encode( $room->listUsersInPrivateRoom( $_REQUEST['sub'] ) );
		exit;
	}

	/**
	 * Instantiates stdClass, sets $data->user, $data->message, $data->public
	 * and $data->recipients using given $chat_user, $messageString and
	 * $params and returns $data.
	 *
	 * @param string $messageString
	 * @param array $params
	 * @param ilChatroomUser $chat_user
	 * @return stdClass
	 */
	private function buildMessage($messageString, $params, ilChatroomUser $chat_user)
	{
		$data = new stdClass();

		$data->user			= $chat_user->getUserId(); //$this->gui->object->getPersonalInformation( $chat_user );
		$data->title		= $params['title'];
		$data->timestamp	= date( 'c' );
		$data->type		= 'private_room_created';

		return $data;
	}

}

?>
