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
class ilChatroomClearTask extends ilChatroomTaskHandler
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
	    global $ilUser, $ilCtrl;

	    require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
	    require_once 'Modules/Chatroom/classes/class.ilChatroomUser.php';

	    if ( !ilChatroom::checkUserPermissions( array('moderate') , $this->gui->ref_id ) )
	    {
	    	$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", ROOT_FOLDER_ID);
	    	$ilCtrl->redirectByClass("ilrepositorygui", "");
	    }

	    $room = ilChatroom::byObjectId( $this->gui->object->getId() );

	    if( $room )
	    {
		// if user is in scope
		$scope = $room->getRoomId();

		$chat_user = new ilChatroomUser( $ilUser, $room );

		$message = json_encode( $this->buildMessage(
		    ilUtil::stripSlashes( (int)$_REQUEST['sub'] ), $chat_user
		) );

		$params = array(
			'message' => $message,
		);

		$query		= http_build_query( $params );
		$connector	= $this->gui->getConnector();
		$response	= $connector->post( $scope, $query );
		$responseObject = json_decode( $response );
		
		$room->clearMessages($_REQUEST['sub']);
	    }
	    else
	    {
		$response = json_encode( array(
		    'success'   => false,
		    'reason'    => 'unkown room'
		) );
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
		$data->timestamp    = date( 'c' );
		$data->type	    = 'clear';
		$data->sub	    = $messageString;

		return $data;
	}
}

?>