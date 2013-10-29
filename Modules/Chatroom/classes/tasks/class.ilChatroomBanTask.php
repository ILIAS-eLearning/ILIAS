<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Chatroom/classes/class.ilChatroomUser.php';

/**
 * Class ilChatroomBanTask
 *
 * @author Jan Posselt <jposselt@databay.de>
 * @version $Id$
 *
 * @ingroup ModulesChatroom
 */
class ilChatroomBanTask extends ilChatroomTaskHandler
{

	private $gui;

	/**
	 * Constructor
	 *
	 * Sets $this->gui using given $gui.
	 * Requires ilChatroom.
	 *
	 * @param ilChatroomObjectGUI $gui
	 */
	public function __construct(ilChatroomObjectGUI $gui)
	{
		$this->gui = $gui;
		require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
	}

	/**
	 * Displays banned users task.
	 *
	 * @global ilCtrl2 $ilCtrl
	 */
	public function show()
	{
		//global $lng, $ilCtrl;
		global $ilCtrl;

		include_once 'Modules/Chatroom/classes/class.ilChatroom.php';

		ilChatroom::checkUserPermissions( 'read', $this->gui->ref_id );

		$this->gui->switchToVisibleMode();

		require_once 'Modules/Chatroom/classes/class.ilBannedUsersTableGUI.php';

		$table = new ilBannedUsersTableGUI( $this->gui, 'ban-show' );
		$table->setFormAction( $ilCtrl->getFormAction( $this->gui, 'ban-show' ) );

		$room = ilChatroom::byObjectId( $this->gui->object->getId() );

		if( $room )
		{
			$table->setData( $room->getBannedUsers() );
		}

		$this->gui->tpl->setVariable( 'ADM_CONTENT', $table->getHTML() );
	}

	/**
	 * Unbans users fetched from $_REQUEST['banned_user_id'].
	 *
	 * @global ilCtrl2 $ilCtrl
	 */
	public function delete()
	{
		/**
		 * @var $ilCtrl ilCtrl
		 * @var $lng ilLanguage
		 */
		global $ilCtrl, $lng;

		$users = $_REQUEST['banned_user_id'];

		if( !is_array( $users ) )
		{
			ilUtil::sendInfo($lng->txt('no_checkbox'), true);
			$ilCtrl->redirect( $this->gui, 'ban-show' );
		}

		$room = ilChatroom::byObjectId( $this->gui->object->getId() );
		$room->unbanUser( $users );

		$ilCtrl->redirect( $this->gui, 'ban-show' );
	}

	/**
	 * Calls $this->show method.
	 *
	 * @param string $method
	 */
	public function executeDefault($method)
	{
		$this->show();
	}

	/**
	 * Kicks and bans user, fetched from $_REQUEST['user'] and adds history entry.
	 *
	 * @global ilObjUser $ilUser
	 */
	public function active()
	{
	    global $ilUser, $ilCtrl;

	    if ( !ilChatroom::checkUserPermissions( array('read', 'moderate') , $this->gui->ref_id ) )
	    {
	    	$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", ROOT_FOLDER_ID);
	    	$ilCtrl->redirectByClass("ilrepositorygui", "");
	    }

		$room = ilChatroom::byObjectId($this->gui->object->getId());

		if($room)
		{
			// if user is in scope
			$scope = $room->getRoomId();

			$chat_user = new ilChatroomUser($ilUser, $room);

			$messageObject = $this->buildMessage(
				ilUtil::stripSlashes($_REQUEST['user']),
				$chat_user
			);

			$message = json_encode($messageObject);


			$params = array(
				'message'        => $message,
				'userToKick'     => $_REQUEST['user']
			);

			$query          = http_build_query($params);
			$connector      = $this->gui->getConnector();
			$response       = $connector->kick($scope, $query);
			$responseObject = json_decode($response);

			$room->banUser($_REQUEST['user']);

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
	 * @param ilChatroomUser $user
	 * @return stdClass
	 */
	private function buildMessage($messageString, ilChatroomUser $user)
	{
		$data = new stdClass();

		$data->user			= $this->gui->object->getPersonalInformation( $user );
		$data->userToKick	= $messageString;
		$data->timestamp	= date( 'c' );
		$data->type			= 'kick';

		return $data;
	}

}

?>