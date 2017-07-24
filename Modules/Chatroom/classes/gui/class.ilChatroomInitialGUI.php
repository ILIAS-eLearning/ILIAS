<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilChatroomInitialGUI
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomInitialGUI extends ilChatroomGUIHandler
{

	/**
	 * Constructor
	 * Requires ilChatroom and ilChatroomUser.
	 * Sets $this->gui using given $gui.
	 * @param ilChatroomObjectGUI $gui
	 */
	public function __construct(ilChatroomObjectGUI $gui)
	{
		parent::__construct($gui);
		require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
		require_once 'Modules/Chatroom/classes/class.ilChatroomUser.php';
	}

	public function executeDefault($method)
	{
		throw new Exception('METHOD_NOT_IN_USE', 1456435027);
		global $tpl, $ilUser, $ilCtrl, $lng, $rbacsystem;

		$room      = ilChatroom::byObjectId($this->gui->object->getId());
		$chat_user = new ilChatroomUser($ilUser, $room);

		$result                = new stdClass();
		$result->users         = $room->getConnectedUsers();
		$result->private_rooms = $room->getActivePrivateRooms($ilUser->getId());
		$result->userinfo      = array(
			'moderator' => ilChatroom::checkUserPermissions('moderate', (int)$_GET['ref_id'], false),
			'userid'    => $chat_user->getUserId()
		);

		$smileys = array();

		include_once('Modules/Chatroom/classes/class.ilChatroomSmilies.php');

		$smileys_array = ilChatroomSmilies::_getSmilies();

		foreach($smileys_array as $smiley_array)
		{
			foreach($smiley_array as $key => $value)
			{
				if($key == 'smiley_keywords')
				{
					$new_keys = explode("\n", $value);
				}

				if($key == 'smiley_fullpath')
				{
					$new_val = $value;
				}
			}

			foreach($new_keys as $new_key)
			{
				$smileys[$new_key] = $new_val;
			}
		}

		$result->smileys = $smileys;

		echo json_encode($result);
		exit;
	}

}

?>
