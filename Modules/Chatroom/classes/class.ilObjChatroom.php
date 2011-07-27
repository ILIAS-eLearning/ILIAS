<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./classes/class.ilObject.php";

/**
 * Class ilObjChatroom
 *
 * @author Jan Posselt <jposselt at databay.de>
 * @version $Id$
 *
 * @ingroup ModulesChatroom
 */
class ilObjChatroom extends ilObject
{

	/**
	 * Constructor
	 *
	 * @param	integer	reference_id or object_id
	 * @param	boolean	treat the id as reference_id (true) or object_id (false)
	 */
	public function __construct($a_id = 0, $a_call_by_reference = true)
	{
		$this->type = 'chtr';
		$this->ilObject($a_id, $a_call_by_reference);
	}

	/**
	 * Prepares and returns $userInfo using given $user object.
	 *
	 * @param ilChatroomUser $user
	 * @return stdClass
	 */
	public function getPersonalInformation(ilChatroomUser $user)
	{
		$userInfo = new stdClass();
		$userInfo->username = $user->getUsername();
		$userInfo->id = $user->getUserId();

		return $userInfo;
	}


	public static function _getPublicRefId() {
		$settings = new ilSetting('chatroom');
		return $settings->get('public_room_ref', 0);
	}

	public static function _getPublicObjId() {
		global $ilDB;

		$rset = $ilDB->query('SELECT object_id FROM chatroom_settings WHERE room_type=\'default\'');
		if ($row = $ilDB->fetchAssoc($rset)) {
			return $row['object_id'];
		}
		return 0;
	}
}

?>
