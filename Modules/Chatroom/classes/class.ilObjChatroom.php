<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject.php";

/**
 * Class ilObjChatroom
 * @author  Jan Posselt <jposselt at databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilObjChatroom extends ilObject
{
	/**
	 * {@inheritdoc}
	 */
	public function __construct($a_id = 0, $a_call_by_reference = true)
	{
		$this->type = 'chtr';
		parent::__construct($a_id, $a_call_by_reference);
	}

	public static function _getPublicRefId()
	{
		$settings = new ilSetting('chatroom');
		return $settings->get('public_room_ref', 0);
	}

	public static function _getPublicObjId()
	{
		global $ilDB;

		$rset = $ilDB->query('SELECT object_id FROM chatroom_settings WHERE room_type=' . $ilDB->quote('default', 'text'));
		if($row = $ilDB->fetchAssoc($rset))
		{
			return $row['object_id'];
		}
		return 0;
	}

	/**
	 * Prepares and returns $userInfo using given $user object.
	 * @param ilChatroomUser $user
	 * @return stdClass
	 */
	public function getPersonalInformation(ilChatroomUser $user)
	{
		$userInfo           = new stdClass();
		$userInfo->username = $user->getUsername();
		$userInfo->id       = $user->getUserId();

		return $userInfo;
	}

	/**
	 * {@inheritdoc}
	 */
	function initDefaultRoles()
	{
		include_once './Services/AccessControl/classes/class.ilObjRole.php';

		$role = $this->createDefaultRole();

		return array();
	}

	/**
	 * @return ilObjRole
	 */
	protected function createDefaultRole()
	{
		return ilObjRole::createDefaultRole(
			'il_chat_moderator_' . $this->getRefId(),
			"Moderator of chat obj_no." . $this->getId(),
			'il_chat_moderator',
			$this->getRefId()
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function cloneObject($a_target_id, $a_copy_id = 0)
	{
		/**
		 * @var $rbacreview ilRbacReview
		 */
		global $rbacreview;

		require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
		$original_room = ilChatroom::byObjectId($this->getId());

		$newObj = parent::cloneObject($a_target_id, $a_copy_id);

		$objId = $newObj->getId();

		$original_settings = $original_room->getSettings();
		$room              = new ilChatroom();

		$original_settings['object_id'] = $objId;

		$room->saveSettings($original_settings);

		// rbac log
		include_once "Services/AccessControl/classes/class.ilRbacLog.php";
		$rbac_log_roles = $rbacreview->getParentRoleIds($newObj->getRefId(), false);
		$rbac_log       = ilRbacLog::gatherFaPa($newObj->getRefId(), array_keys($rbac_log_roles), true);
		ilRbacLog::add(ilRbacLog::CREATE_OBJECT, $newObj->getRefId(), $rbac_log);

		return $newObj;
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete()
	{
		/**
		 * @var $ilDB ilDBInterface
		 */
		global $ilDB;

		$ilDB->manipulateF(
			'DELETE FROM chatroom_users WHERE chatroom_users.room_id IN (SELECT chatroom_settings.room_id FROM chatroom_settings WHERE chatroom_settings.object_id = %s)',
			array('integer'),
			array($this->getId())
		);

		$ilDB->manipulateF(
			'DELETE FROM chatroom_history WHERE chatroom_history.room_id IN (SELECT chatroom_settings.room_id FROM chatroom_settings WHERE chatroom_settings.object_id = %s)',
			array('integer'),
			array($this->getId())
		);

		$ilDB->manipulateF(
			'DELETE FROM chatroom_bans WHERE chatroom_bans.room_id IN (SELECT chatroom_settings.room_id FROM chatroom_settings WHERE chatroom_settings.object_id = %s)',
			array('integer'),
			array($this->getId())
		);

		$ilDB->manipulateF(
			'DELETE FROM chatroom_sessions WHERE chatroom_sessions.room_id IN (SELECT chatroom_settings.room_id FROM chatroom_settings WHERE chatroom_settings.object_id = %s)',
			array('integer'),
			array($this->getId())
		);

		$ilDB->manipulateF('
			DELETE FROM chatroom_proomaccess
			WHERE chatroom_proomaccess.proom_id IN (
				SELECT chatroom_prooms.proom_id
				FROM chatroom_prooms WHERE chatroom_prooms.parent_id IN (
					SELECT chatroom_settings.room_id
					FROM chatroom_settings
					WHERE chatroom_settings.object_id = %s
				)
			)',
			array('integer'),
			array($this->getId())
		);

		$ilDB->manipulateF('
			DELETE FROM chatroom_psessions
			WHERE chatroom_psessions.proom_id IN (
				SELECT chatroom_prooms.proom_id
				FROM chatroom_prooms WHERE chatroom_prooms.parent_id IN (
					SELECT chatroom_settings.room_id
					FROM chatroom_settings
					WHERE chatroom_settings.object_id = %s
				)
			)',
			array('integer'),
			array($this->getId())
		);

		$ilDB->manipulateF(
			'DELETE FROM chatroom_prooms WHERE chatroom_prooms.parent_id IN (SELECT chatroom_settings.room_id FROM chatroom_settings WHERE chatroom_settings.object_id = %s)',
			array('integer'),
			array($this->getId())
		);

		// Finally delete rooms
		$ilDB->manipulateF(
			'DELETE FROM chatroom_settings WHERE object_id = %s',
			array('integer'),
			array($this->getId())
		);

		return parent::delete();
	}
}