<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilChatroomInstaller
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomInstaller
{
	/**
	 * Creates tables needed for chat and calls registerObject and
	 * registerAdminObject methods.
	 * @global ilDB $ilDB
	 */
	public static function install()
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		if(!$ilDB->tableExists('chatroom_settings'))
		{
			$fields = array(
				'room_id'                => array('type' => 'integer', 'length' => 4, 'notnull' => true),
				'object_id'              => array('type' => 'integer', 'length' => 4, 'notnull' => false, 'default' => 0),
				'room_type'              => array('type' => 'text', 'length' => 20, 'notnull' => true),
				'allow_anonymous'        => array('type' => 'integer', 'length' => 1, 'notnull' => false, 'default' => 0),
				'allow_custom_usernames' => array('type' => 'integer', 'length' => 1, 'notnull' => false, 'default' => 0),
				'enable_history'         => array('type' => 'integer', 'length' => 1, 'notnull' => false, 'default' => 0),
				'restrict_history'       => array('type' => 'integer', 'length' => 1, 'notnull' => false, 'default' => 0),
				'autogen_usernames'      => array('type' => 'text', 'length' => 50, 'notnull' => false, 'default' => 'Anonymous #'),
				'allow_private_rooms'    => array('type' => 'integer', 'length' => 1, 'notnull' => false, 'default' => 0),
			);

			$ilDB->createTable('chatroom_settings', $fields);
			$ilDB->addPrimaryKey('chatroom_settings', array('room_id'));
			$ilDB->createSequence('chatroom_settings');
		}

		if(!$ilDB->tableExists('chatroom_users'))
		{
			$fields = array(
				'room_id'   => array('type' => 'integer', 'length' => 4, 'notnull' => true),
				'user_id'   => array('type' => 'integer', 'length' => 4, 'notnull' => true),
				'userdata'  => array('type' => 'text', 'length' => 4000, 'notnull' => true),
				'connected' => array('type' => 'integer', 'length' => 4, 'notnull' => true),
			);
			$ilDB->createTable('chatroom_users', $fields);
			$ilDB->addPrimaryKey('chatroom_users', array('room_id', 'user_id'));
		}

		if(!$ilDB->tableExists('chatroom_sessions'))
		{
			$fields = array(
				'room_id'      => array('type' => 'integer', 'length' => 4, 'notnull' => true),
				'user_id'      => array('type' => 'integer', 'length' => 4, 'notnull' => true),
				'userdata'     => array('type' => 'text', 'length' => 4000, 'notnull' => true),
				'connected'    => array('type' => 'integer', 'length' => 4, 'notnull' => true),
				'disconnected' => array('type' => 'integer', 'length' => 4, 'notnull' => true),
			);
			$ilDB->createTable('chatroom_sessions', $fields);
		}

		if(!$ilDB->tableExists('chatroom_history'))
		{
			$fields = array(
				'room_id'   => array('type' => 'integer', 'length' => 4, 'notnull' => true),
				'message'   => array('type' => 'text', 'length' => 4000, 'notnull' => true),
				'timestamp' => array('type' => 'integer', 'length' => 4, 'notnull' => true),
			);
			$ilDB->createTable('chatroom_history', $fields);
		}

		if(!$ilDB->tableExists('chatroom_bans'))
		{
			$fields = array(
				'room_id'   => array('type' => 'integer', 'length' => 4, 'notnull' => true),
				'user_id'   => array('type' => 'integer', 'length' => 4, 'notnull' => true),
				'timestamp' => array('type' => 'integer', 'length' => 4, 'notnull' => true),
				'remark'    => array('type' => 'text', 'length' => 1000, 'notnull' => false),
			);
			$ilDB->createTable('chatroom_bans', $fields);
		}

		if(!$ilDB->tableExists('chatroom_admconfig'))
		{
			$fields = array(
				'instance_id'     => array('type' => 'integer', 'length' => 4, 'notnull' => true),
				'server_settings' => array('type' => 'text', 'length' => 2000, 'notnull' => true),
				'default_config'  => array('type' => 'integer', 'length' => 1, 'notnull' => true, 'default' => 0),
			);
			$ilDB->createTable('chatroom_admconfig', $fields);
			$ilDB->addPrimaryKey('chatroom_admconfig', array('instance_id'));
			$ilDB->createSequence('chatroom_admconfig');
		}

		if(!$ilDB->tableExists('chatroom_prooms'))
		{
			$fields = array(
				'proom_id'  => array('type' => 'integer', 'length' => 4, 'notnull' => true),
				'parent_id' => array('type' => 'text', 'length' => 2000, 'notnull' => true),
				'title'     => array('type' => 'text', 'length' => 200, 'notnull' => true, 'default' => 0),
				'owner'     => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0),
				'created'   => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0),
				'closed'    => array('type' => 'integer', 'length' => 4, 'notnull' => false, 'default' => 0),
			);
			$ilDB->createTable('chatroom_prooms', $fields);
			$ilDB->addPrimaryKey('chatroom_prooms', array('proom_id'));
			$ilDB->createSequence('chatroom_prooms');
		}

		if(!$ilDB->tableExists('chatroom_psessions'))
		{
			$fields = array(
				'proom_id'     => array('type' => 'integer', 'length' => 4, 'notnull' => true),
				'user_id'      => array('type' => 'integer', 'length' => 4, 'notnull' => true),
				'connected'    => array('type' => 'integer', 'length' => 4, 'notnull' => true),
				'disconnected' => array('type' => 'integer', 'length' => 4, 'notnull' => true),
			);
			$ilDB->createTable('chatroom_psessions', $fields);
		}

		if(!$ilDB->tableExists('chatroom_uploads'))
		{
			$fields = array(
				'upload_id'    => array('type' => 'integer', 'length' => 4, 'notnull' => true),
				'room_id'      => array('type' => 'integer', 'length' => 4, 'notnull' => true),
				'user_id'      => array('type' => 'integer', 'length' => 4, 'notnull' => true),
				'filename'     => array('type' => 'text', 'length' => 200, 'notnull' => true),
				'filetype'     => array('type' => 'text', 'length' => 200, 'notnull' => true),
				'timestamp'    => array('type' => 'integer', 'length' => 4, 'notnull' => true)
			);
			$ilDB->createTable('chatroom_uploads', $fields);
			$ilDB->addPrimaryKey('chatroom_uploads', array('upload_id'));
			$ilDB->createSequence('chatroom_uploads');
		}

		if(!$ilDB->tableColumnExists('chatroom_prooms', 'is_public'))
		{
			$ilDB->addTableColumn('chatroom_prooms', 'is_public', array('type' => 'integer', 'default' => 1, 'length' => 1));
		}

		if(!$ilDB->tableExists('chatroom_psessions'))
		{
			$fields = array(
				'proom_id'     => array('type' => 'integer', 'length' => 4, 'notnull' => true),
				'user_id'      => array('type' => 'integer', 'length' => 4, 'notnull' => true),
				'connected'    => array('type' => 'integer', 'length' => 4, 'notnull' => true),
				'disconnected' => array('type' => 'integer', 'length' => 4, 'notnull' => true),
			);
			$ilDB->createTable('chatroom_psessions', $fields);
		}

		if(!$ilDB->tableExists('chatroom_proomaccess'))
		{
			$fields = array(
				'proom_id' => array('type' => 'integer', 'length' => 4, 'notnull' => true),
				'user_id'  => array('type' => 'integer', 'length' => 4, 'notnull' => true),
			);
			$ilDB->createTable('chatroom_proomaccess', $fields);
		}

		if(!$ilDB->tableColumnExists('chatroom_admconfig', 'client_settings'))
		{
			$ilDB->addTableColumn(
				"chatroom_admconfig", "client_settings",
				array(
					"type"    => "text",
					"length"  => 1000,
					"notnull" => true
				)
			);
		}

		if(!$ilDB->tableExists('chatroom_smilies'))
		{
			$fields = array(
				'smiley_id'       => array(
					'type'   => 'integer',
					'length' => 4,
				),
				'smiley_keywords' => array(
					'type'   => 'text',
					'length' => 100,
				),
				'smiley_path'     => array(
					'type'   => 'text',
					'length' => 200,
				)
			);

			$ilDB->createTable('chatroom_smilies', $fields);
			$ilDB->addPrimaryKey('chatroom_smilies', array('smiley_id'));
			$ilDB->createSequence('chatroom_smilies');
		}

		self::registerObject();
		self::registerAdminObject();
		self::removeOldChatEntries();
		self::convertChatObjects();


		$notificationSettings = new ilSetting('notifications');
		$notificationSettings->set('enable_osd', true);
	}

	public static function removeOldChatEntries()
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		$res = $ilDB->queryF(
			'SELECT object_data.obj_id, ref_id, lft, rgt
			FROM object_data
            INNER JOIN object_reference ON object_reference.obj_id = object_data.obj_id
			INNER JOIN tree ON child = ref_id
            WHERE type = %s',
			array('text'),
			array('chac')
		);

		$data = $ilDB->fetchAssoc($res);
		if($data)
		{
			$res = $ilDB->queryF('SELECT * FROM tree
								  INNER JOIN object_reference ON ref_id = child
								  INNER JOIN object_data ON object_data.obj_id = object_reference.obj_id 
								  WHERE lft BETWEEN %s AND %s', array('integer', 'integer'), array($data['lft'], $data['rgt']));
			while($row = $ilDB->fetchAssoc($res))
			{
				$ilDB->manipulate(
					'DELETE
					FROM object_data
					WHERE obj_id = ' . $ilDB->quote($row['obj_id'], 'integer')
				);

				$ilDB->manipulate(
					'DELETE
					FROM object_reference
					WHERE ref_id = ' . $ilDB->quote($row['ref_id'], 'integer')
				);

				$ilDB->manipulate(
					'DELETE
					FROM tree
					WHERE child = ' . $ilDB->quote($row['ref_id'], 'integer')
				);
			}
		}

		$ilDB->manipulateF('DELETE FROM object_data WHERE type = %s AND title = %s', array('text', 'text'), array('typ', 'chat'));
		$ilDB->manipulateF('DELETE FROM object_data WHERE type = %s AND title = %s', array('text', 'text'), array('typ', 'chac'));
	}

	public static function createDefaultPublicRoom($force = false)
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		if($force)
		{
			$query = 'DELETE FROM chatroom_settings WHERE room_type = ' . $ilDB->quote('default', 'text');
			$ilDB->manipulate($query);
			$create = true;
		}
		else
		{
			$query  = 'SELECT * FROM chatroom_settings WHERE room_type = ' . $ilDB->quote('default', 'text');
			$rset   = $ilDB->query($query);
			$create = !$ilDB->fetchAssoc($rset);
		}
		if($create)
		{
			$query             = "
				SELECT object_data.obj_id, object_reference.ref_id
				FROM object_data
				INNER JOIN object_reference ON object_reference.obj_id = object_data.obj_id
				WHERE type = " . $ilDB->quote('chta', 'text');
			$rset              = $ilDB->query($query);
			$row               = $ilDB->fetchAssoc($rset);
			$chatfolder_ref_id = $row['ref_id'];
			
			require_once 'Modules/Chatroom/classes/class.ilObjChatroom.php';
			$newObj = new ilObjChatroom();

			$newObj->setType('chtr');
			$newObj->setTitle('Public Chat');
			$newObj->setDescription('');
			$newObj->create(); // true for upload
			$newObj->createReference();
			$newObj->putInTree($chatfolder_ref_id);
			$newObj->setPermissions($chatfolder_ref_id);

			$obj_id = $newObj->getId();
			$ref_id = $newObj->getRefId();

			$id = $ilDB->nextId('chatroom_settings');
			$ilDB->insert(
				'chatroom_settings',
				array(
					'room_id'                => array('integer', $id),
					'object_id'              => array('integer', $obj_id),
					'room_type'              => array('text', 'default'),
					'allow_anonymous'        => array('integer', 0),
					'allow_custom_usernames' => array('integer', 0),
					'enable_history'         => array('integer', 0),
					'restrict_history'       => array('integer', 0),
					'autogen_usernames'      => array('text', 'Anonymous #'),
					'allow_private_rooms'    => array('integer', 1),
				)
			);

			$settings = new ilSetting('chatroom');
			$settings->set('public_room_ref', $ref_id);
		}
	}

	/**
	 * Registers chat object by inserting it into object_data.
	 * @global ilDBMySQL $ilDB
	 */
	public static function registerObject()
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		$typ_id = null;

		$query = 'SELECT obj_id FROM object_data ' .
			'WHERE type = ' . $ilDB->quote('typ', 'text') . ' ' .
			'AND title = ' . $ilDB->quote('chtr', 'text');
		if(!($object_definition_row = $ilDB->fetchAssoc($ilDB->query($query))))
		{
			$typ_id = $ilDB->nextId('object_data');
			$ilDB->insert(
				'object_data',
				array(
					'obj_id'                => array('integer', $typ_id),
					'type'                  => array('text', 'typ'),
					'title'                 => array('text', 'chtr'),
					'description'           => array('text', 'Chatroom Object'),
					'owner'                 => array('integer', -1),
					'create_date'           => array('timestamp', date('Y-m-d H:i:s')),
					'last_update'           => array('timestamp', date('Y-m-d H:i:s'))
				)
			);

			// REGISTER RBAC OPERATIONS FOR OBJECT TYPE
			// 1: edit_permissions, 2: visible, 3: read, 4:write
			foreach(array(1, 2, 3, 4) as $ops_id)
			{
				$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ( " .
					$ilDB->quote($typ_id, 'integer') . "," . $ilDB->quote($ops_id, 'integer') .
					")";
				$ilDB->manipulate($query);
			}
		}

		if($moderatePermissionId = self::getModeratorPermissionId())
		{
			if(!$typ_id)
			{
				$typ_id = $object_definition_row['obj_id'];
			}

			if($typ_id)
			{
				$ilDB->manipulateF(
					'DELETE FROM rbac_ta WHERE typ_id = %s AND ops_id = %s',
					array('integer', 'integer'),
					array($typ_id, $moderatePermissionId)
				);
	
				$ilDB->insert(
					'rbac_ta',
					array(
						'typ_id' => array('integer', $typ_id),
						'ops_id' => array('integer', $moderatePermissionId),
					)
				);
			}
		}
	}

	private static function getModeratorPermissionId()
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		$rset = $ilDB->queryF(
			'SELECT ops_id FROM rbac_operations WHERE operation = %s',
			array('text'),
			array('moderate')
		);
		if($row = $ilDB->fetchAssoc($rset))
		{
			return $row['ops_id'];
		}
		return 0;
	}

	/**
	 * Registgers admin chat object by inserting it into object_data.
	 * @global ilDBMySQL $ilDB
	 */
	public static function registerAdminObject()
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		$query = 'SELECT * FROM object_data WHERE type = ' . $ilDB->quote('chta', 'text');
		if(!$ilDB->fetchAssoc($ilDB->query($query)))
		{
			$obj_id = $ilDB->nextId('object_data');
			$ilDB->insert(
				'object_data',
				array(
					'obj_id'                => array('integer', $obj_id),
					'type'                  => array('text', 'chta'),
					'title'                 => array('text', 'Chatroom Admin'),
					'description'           => array('text', 'Chatroom General Settings'),
					'owner'                 => array('integer', -1),
					'create_date'           => array('timestamp', date('Y-m-d H:i:s')),
					'last_update'           => array('timestamp', date('Y-m-d H:i:s'))
				)
			);

			$ref_id = $ilDB->nextId('object_reference');
			$query  = "INSERT INTO object_reference (ref_id, obj_id) VALUES(" . $ilDB->quote($ref_id, 'integer') . ", " . $ilDB->quote($obj_id, 'integer') . ")";
			$ilDB->manipulate($query);

			$tree = new ilTree(ROOT_FOLDER_ID);
			$tree->insertNode($ref_id, SYSTEM_FOLDER_ID);
		}
	}

	/**
	 * Sets autogen_usernames default option for chatrooms
	 * @param array $obj_ids
	 */
	public static function setChatroomSettings($obj_ids)
	{
		if(is_array($obj_ids))
		{
			foreach($obj_ids as $obj_id)
			{
				$room = new ilChatroom();
				$room->saveSettings(array(
					'object_id'                => $obj_id,
					'autogen_usernames'        => 'Autogen #',
					'room_type'                => 'repository'
				));
			}
		}
	}

	/**
	 * Converts old 'chat' objects to 'chtr' objects.
	 */
	public static function convertChatObjects()
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		$res = $ilDB->queryF(
			"SELECT		obj_id
			FROM		object_data
			WHERE		type = %s",
			array('text'),
			array('chat')
		);

		$obj_ids = array();

		while($row = $ilDB->fetchAssoc($res))
		{
			$obj_ids[] = $row['obj_id'];
		}

		$ilDB->manipulateF(
			"UPDATE		object_data
			SET		type = %s
			WHERE		type = %s",
			array('text', 'text'),
			array('chtr', 'chat')
		);

		self::setChatroomSettings($obj_ids);
	}

	public static function createMissinRoomSettingsForConvertedObjects()
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		$res = $ilDB->queryF(
			"SELECT obj_id FROM object_data
				LEFT JOIN chatroom_settings ON object_id = obj_id
			WHERE type = %s
				AND room_id IS NULL",
			array('text'),
			array('chtr')
		);

		$roomsToFix = array();
		while($row = $ilDB->fetchAssoc($res))
		{
			$roomsToFix[] = $row['obj_id'];
		}

		self::setChatroomSettings($roomsToFix);
	}

	/**
	 * @param int $ref_id
	 */
	public static function ensureCorrectPublicChatroomTreeLocation($ref_id)
	{
		/**
		 * @var $tree      ilTree
		 * @var $ilDB      ilDB
		 * @var $rbacadmin ilRbacAdmin
		 */
		global $tree, $ilDB, $rbacadmin;

		$ilDB->setLimit(1);
		$query             = "
			SELECT object_data.obj_id, object_reference.ref_id
			FROM object_data
			INNER JOIN object_reference ON object_reference.obj_id = object_data.obj_id
			WHERE type = " . $ilDB->quote('chta', 'text');
		$rset              = $ilDB->query($query);
		$row               = $ilDB->fetchAssoc($rset);
		$chatfolder_ref_id = $row['ref_id'];
		$pid               = $tree->getParentId($ref_id);

		if(
			$chatfolder_ref_id &&
			$pid != $chatfolder_ref_id &&
			!$tree->isDeleted($chatfolder_ref_id)
		)
		{
			$tree->moveTree($ref_id, $chatfolder_ref_id);
			$rbacadmin->adjustMovedObjectPermissions($ref_id, $pid);
			include_once('./Services/AccessControl/classes/class.ilConditionHandler.php');
			ilConditionHandler::_adjustMovedObjectConditions($ref_id);
		}
	}
}