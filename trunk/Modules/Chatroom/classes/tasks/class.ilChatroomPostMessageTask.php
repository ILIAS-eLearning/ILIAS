<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilChatroomPostMessageTask
 *
 * @author Jan Posselt <jposselt@databay.de>
 * @version $Id$
 *
 * @ingroup ModulesChatroom
 */
class ilChatroomPostMessageTask extends ilChatroomTaskHandler
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

	/**
	 * Prepares and posts message fetched from $_REQUEST['message']
	 * to recipients fetched from $_REQUEST['recipient']
	 * and adds an entry to history if successful.
	 *
	 * @global ilObjUser $ilUser
	 * @param string $method
	 */
	public function executeDefault($method)
	{
		global $ilUser, $ilCtrl;

		require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
		require_once 'Modules/Chatroom/classes/class.ilChatroomUser.php';

		if ( !ilChatroom::checkUserPermissions( 'read', $this->gui->ref_id ) )
		{
	    	$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", ROOT_FOLDER_ID);
	    	$ilCtrl->redirectByClass("ilrepositorygui", "");
		}

		$room = ilChatroom::byObjectId( $this->gui->object->getId() );

		$chat_user  = new ilChatroomUser($ilUser, $room);
		$user_id    = $chat_user->getUserId();

		if( !$room )
		{
		    throw new Exception('unkown room');
		}
		else if( !$room->isSubscribed( $chat_user->getUserId() ) )
		{
		    throw new Exception('not subscribed');
		}
		else if ( isset($_REQUEST['sub']) && !$room->userIsInPrivateRoom( $_REQUEST['sub'], $chat_user->getUserId() ))
		{
		    $response = json_encode( array(
			    'success'	=> false,
			    'reason'	=> 'not subscribed to private room'
		    ) );
		    echo json_encode( $response );
		    exit;
	    }

		$scope	= $room->getRoomId();
		$params = array();

		if( ($recipient = $_REQUEST['recipient'] ) )
		{
		    $params['recipients'] = join( ',', array_unique( array($user_id, $recipient) ) );			
			$params['recipient_names'] = implode( ',', array($chat_user->getUsername(), $_REQUEST['recipient_name']) );
		    $params['public'] = isset( $_REQUEST['public'] ) ? (int)$_REQUEST['public'] : 0;
		}
		else
		{
		    $params['public'] = 1;
		}

		if ($_REQUEST['sub'])
		$params['sub'] = (int)$_REQUEST['sub'];

		$messageObject = $this->buildMessage(
			ilUtil::stripSlashes( $_REQUEST['message'] ),
			$params,
			$chat_user
		);
		
		$message = json_encode( $messageObject );

		$params			= array_merge( $params, array('message' => $message) );
		//print_r($params);exit;
		$query		= http_build_query( $params );
		$connector	= $this->gui->getConnector();
		$response	= $connector->post( $scope, $query );

		$responseObject = json_decode( $response );

		if( $responseObject->success == true /*&& $room->getSetting( 'enable_history' )*/ )
		{
			$room->addHistoryEntry( $messageObject, $recipient, $publicMessage );
		}

		echo $response;
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

		$data->user		= $this->gui->object->getPersonalInformation( $chat_user );
		$data->message		= $messageString;
		$data->timestamp	= time() * 1000;//date( 'c' );
		$data->type		= 'message';
		isset($params['sub']) ? ($data->sub = $params['sub']) : false;
		$data->public		= (int)$params['public'];
		$data->recipients	= $params['recipients']; // ? explode(",", $params['recipients']) : array();
		$data->recipient_names	= $params['recipient_names'];

		return $data;
	}

}

?>
