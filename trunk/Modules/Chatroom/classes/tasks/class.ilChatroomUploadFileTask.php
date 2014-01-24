<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilChatroomUploadFileTask
 *
 * Provides methods to upload a file.
 *
 * @author Andreas Kordosz <akordosz@databay.de>
 * @version $Id$
 *
 * @ingroup ModulesChatroom
 */
class ilChatroomUploadFileTask extends ilChatroomTaskHandler
{

	private $gui;

	/**
	 * Constructor
	 *
	 * @param ilChatroomObjectGUI $gui
	 */
	public function __construct(ilChatroomObjectGUI $gui)
	{
		$this->gui = $gui;
	}

	/**
	 * Default execute method.
	 *
	 * @param string $requestedMethod
	 */
	public function executeDefault($requestedMethod)
	{

	}

	/**
	 * Saves file, fetched from $_FILES to specified upload path.
	 *
	 * @global ilObjUser $ilUser
	 */
	public function uploadFile()
	{
		global $ilCtrl;
		
	    if ( !ilChatroom::checkUserPermissions( 'read', $this->gui->ref_id ) )
	    {
	    	$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", ROOT_FOLDER_ID);
	    	$ilCtrl->redirectByClass("ilrepositorygui", "");
	    }

	    $upload_path = $this->getUploadPath();

	    $this->checkUploadPath($upload_path);

	    /**
	     * @todo: filename must be unique.
	     */
	    $file	= $_FILES['file_to_upload']['tmp_name'];
	    $filename	= $_FILES['file_to_upload']['name'];
	    $type	= $_FILES['file_to_upload']['type'];
	    $target	= $upload_path . $filename;

	    if( ilUtil::moveUploadedFile( $file, $filename, $target ) )
	    {
		global $ilUser;

		require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
		require_once 'Modules/Chatroom/classes/class.ilChatroomUser.php';

		$room	    = ilChatroom::byObjectId( $this->gui->object->getId() );
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

		$room->saveFileUploadToDb($user_id, $filename, $type);
		$this->displayLinkToUploadedFile($room, $chat_user);
	    }

	}

	protected function displayLinkToUploadedFile($room, $chat_user)
	{
		global $ilCtrl;

		$scope		    = $room->getRoomId();
		$params		    = array();
		$params['public']   = 1;
		/**
		 * @todo erwartet message als json
		 */
		$message = json_encode( $this->buildMessage(
		json_encode(array(
					'format' => array(),
					'content' => ilUtil::stripSlashes(
							'Eine neue Datei mit dem Link ' .
		$ilCtrl->getLinkTarget($this->gui, 'uploadFile-deliverFile') .
							' wurde hochgeladen'
							)
							)) , $params, $chat_user
							) );

							$params	= array_merge( $params, array('message' => $message) );
							$query	= http_build_query( $params );

							$connector		= $this->gui->getConnector();
							$response		= $connector->post( $scope, $query );
							$responseObject = json_decode( $response );
							/*
							 if( $responseObject->success == true && $room->getSetting( 'enable_history' ) )
							 {
							 $room->addHistoryEntry( $message, $recipient, $publicMessage );
							 }
							 */
							echo $response;
							exit;
	}

	public function deliverFile()
	{
		// send file

		echo "hello world";
	}

	private function buildMessage($messageString, $params, ilChatroomUser $chat_user)
	{
		$data = new stdClass();

		$data->user			= $this->gui->object->getPersonalInformation( $chat_user );
		$data->message		= $messageString;
		$data->timestamp	= date( 'c' );
		$data->type			= 'message';
		$data->public		= (int)$params['public'];
		$data->recipients	= $params['recipients']; // ? explode(",", $params['recipients']) : array();

		return $data;
	}

	/**
	 * Checks if given upload path exists, is readable or can be created.
	 *
	 * @param string $path
	 */
	public function checkUploadPath($path)
	{
		$err = false;

		switch( true )
		{
			case !file_exists( $path ):
				if(	! ilUtil::makeDirParents( $path ) )
				{
					$err = true;
					$msg = 'Error: Upload path could not be created!';
				}
				break;

			case !is_dir( $path ):
				$err = true;
				$msg = 'Error: Upload path is not a directory!';
				break;
					
			case !is_readable( $path ):
				$err = true;
				$msg = 'Error: Upload path is not readable!';
				break;

			default:
		}

		if( $err )
		{
			throw new Exception( $msg );
		}
	}

	/**
	 * Returns upload path
	 *
	 * @return string
	 */
	public function getUploadPath()
	{
		$path = ilUtil::getDataDir() . "/chatroom/" . $this->gui->object->getId() . "/uploads/";

		return $path;
	}

}

?>