<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
require_once 'Modules/Chatroom/classes/class.ilChatroomUser.php';

/**
 * Class ilChatroomInviteUsersToPrivateRoomTask
 *
 * @author Jan Posselt <jposselt@databay.de>
 * @version $Id$
 *
 * @ingroup ModulesChatroom
 */
class ilChatroomInviteUsersToPrivateRoomTask extends ilDBayTaskHandler
{

	private $gui;

	/**
	 * Constructor
	 *
	 * Sets $this->gui using given $gui
	 *
	 * @param ilDBayObjectGUI $gui
	 */
	public function __construct(ilDBayObjectGUI $gui)
	{
		$this->gui = $gui;
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
	public function executeDefault($method)
	{
		$this->byLogin();
	}

	public function byLogin() {
		$this->inviteById(ilObjUser::_lookupId($_REQUEST['users']));
	}

	public function byId() {
		$this->inviteById($_REQUEST['users']);
	}

	private function inviteById($invited_id)
	{
		global $tpl, $ilUser, $ilCtrl;

		if ( !ilChatroom::checkUserPermissions( 'read', $this->gui->ref_id ) )
		{
	    	$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", ROOT_FOLDER_ID);
	    	$ilCtrl->redirectByClass("ilrepositorygui", "");
		}

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
		else if( $_REQUEST['sub'] && !$room->isOwnerOfPrivateRoom( $user_id, $_REQUEST['sub'] ) )
		{
			$response = json_encode( array(
			    'success' => false,
			    'reason' => 'not owner of private room'
			) );
			echo json_encode( $response );
			exit;
		}

		$connector = $this->gui->getConnector();

		//if ($_REQUEST['sub']) {
			$result = $connector->inviteToPrivateRoom($room, $_REQUEST['sub'], $ilUser, $invited_id);
		//}

		$room->sendInvitationNotification($this->gui, $ilUser->getId(), $invited_id, (int)$_REQUEST['sub']);

		echo json_encode($result);
		exit;
	}

	public function getUserList()
	{
		require_once 'Services/User/classes/class.ilUserAutoComplete.php';
		$autocomplete = new ilUserAutoComplete();
		echo $autocomplete->getList($_REQUEST['q']);
		exit;
	}

}

?>
