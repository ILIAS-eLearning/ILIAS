<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
     * @global ilDBInterface $ilDB
     */
    public static function install() : void
    {
        /**
         * @var $ilDB ilDBInterface
         */
        global $DIC;

        $ilDB = $DIC->database();

        if (!$ilDB->tableExists('chatroom_settings')) {
            $fields = [
                'room_id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                'object_id' => ['type' => 'integer', 'length' => 4, 'notnull' => false, 'default' => 0],
                'room_type' => ['type' => 'text', 'length' => 20, 'notnull' => true],
                'allow_anonymous' => ['type' => 'integer', 'length' => 1, 'notnull' => false, 'default' => 0],
                'allow_custom_usernames' => ['type' => 'integer', 'length' => 1, 'notnull' => false, 'default' => 0],
                'enable_history' => ['type' => 'integer', 'length' => 1, 'notnull' => false, 'default' => 0],
                'restrict_history' => ['type' => 'integer', 'length' => 1, 'notnull' => false, 'default' => 0],
                'autogen_usernames' => ['type' => 'text', 'length' => 50, 'notnull' => false, 'default' => 'Anonymous #'],
                'allow_private_rooms' => ['type' => 'integer', 'length' => 1, 'notnull' => false, 'default' => 0],
            ];

            $ilDB->createTable('chatroom_settings', $fields);
            $ilDB->addPrimaryKey('chatroom_settings', ['room_id']);
            $ilDB->createSequence('chatroom_settings');
        }

        if (!$ilDB->tableExists('chatroom_users')) {
            $fields = [
                'room_id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                'user_id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                'userdata' => ['type' => 'text', 'length' => 4000, 'notnull' => true],
                'connected' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
            ];
            $ilDB->createTable('chatroom_users', $fields);
            $ilDB->addPrimaryKey('chatroom_users', ['room_id', 'user_id']);
        }

        if (!$ilDB->tableExists('chatroom_sessions')) {
            $fields = [
                'room_id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                'user_id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                'userdata' => ['type' => 'text', 'length' => 4000, 'notnull' => true],
                'connected' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                'disconnected' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
            ];
            $ilDB->createTable('chatroom_sessions', $fields);
        }

        if (!$ilDB->tableExists('chatroom_history')) {
            $fields = [
                'room_id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                'message' => ['type' => 'text', 'length' => 4000, 'notnull' => true],
                'timestamp' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
            ];
            $ilDB->createTable('chatroom_history', $fields);
        }

        if (!$ilDB->tableExists('chatroom_bans')) {
            $fields = [
                'room_id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                'user_id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                'timestamp' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                'remark' => ['type' => 'text', 'length' => 1000, 'notnull' => false],
            ];
            $ilDB->createTable('chatroom_bans', $fields);
        }

        if (!$ilDB->tableExists('chatroom_admconfig')) {
            $fields = [
                'instance_id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                'server_settings' => ['type' => 'text', 'length' => 2000, 'notnull' => true],
                'default_config' => ['type' => 'integer', 'length' => 1, 'notnull' => true, 'default' => 0],
            ];
            $ilDB->createTable('chatroom_admconfig', $fields);
            $ilDB->addPrimaryKey('chatroom_admconfig', ['instance_id']);
            $ilDB->createSequence('chatroom_admconfig');
        }

        if (!$ilDB->tableExists('chatroom_prooms')) {
            $fields = [
                'proom_id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                'parent_id' => ['type' => 'text', 'length' => 2000, 'notnull' => true],
                'title' => ['type' => 'text', 'length' => 200, 'notnull' => true, 'default' => 0],
                'owner' => ['type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0],
                'created' => ['type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0],
                'closed' => ['type' => 'integer', 'length' => 4, 'notnull' => false, 'default' => 0],
            ];
            $ilDB->createTable('chatroom_prooms', $fields);
            $ilDB->addPrimaryKey('chatroom_prooms', ['proom_id']);
            $ilDB->createSequence('chatroom_prooms');
        }

        if (!$ilDB->tableExists('chatroom_psessions')) {
            $fields = [
                'proom_id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                'user_id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                'connected' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                'disconnected' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
            ];
            $ilDB->createTable('chatroom_psessions', $fields);
        }

        if (!$ilDB->tableExists('chatroom_uploads')) {
            $fields = [
                'upload_id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                'room_id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                'user_id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                'filename' => ['type' => 'text', 'length' => 200, 'notnull' => true],
                'filetype' => ['type' => 'text', 'length' => 200, 'notnull' => true],
                'timestamp' => ['type' => 'integer', 'length' => 4, 'notnull' => true]
            ];
            $ilDB->createTable('chatroom_uploads', $fields);
            $ilDB->addPrimaryKey('chatroom_uploads', ['upload_id']);
            $ilDB->createSequence('chatroom_uploads');
        }

        if (!$ilDB->tableColumnExists('chatroom_prooms', 'is_public')) {
            $ilDB->addTableColumn('chatroom_prooms', 'is_public', ['type' => 'integer', 'default' => 1, 'length' => 1]);
        }

        if (!$ilDB->tableExists('chatroom_psessions')) {
            $fields = [
                'proom_id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                'user_id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                'connected' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                'disconnected' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
            ];
            $ilDB->createTable('chatroom_psessions', $fields);
        }

        if (!$ilDB->tableExists('chatroom_proomaccess')) {
            $fields = [
                'proom_id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                'user_id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
            ];
            $ilDB->createTable('chatroom_proomaccess', $fields);
        }

        if (!$ilDB->tableColumnExists('chatroom_admconfig', 'client_settings')) {
            $ilDB->addTableColumn(
                "chatroom_admconfig",
                "client_settings",
                [
                    "type" => "text",
                    "length" => 1000,
                    "notnull" => true
                ]
            );
        }

        if (!$ilDB->tableExists('chatroom_smilies')) {
            $fields = [
                'smiley_id' => [
                    'type' => 'integer',
                    'length' => 4,
                ],
                'smiley_keywords' => [
                    'type' => 'text',
                    'length' => 100,
                ],
                'smiley_path' => [
                    'type' => 'text',
                    'length' => 200,
                ]
            ];

            $ilDB->createTable('chatroom_smilies', $fields);
            $ilDB->addPrimaryKey('chatroom_smilies', ['smiley_id']);
            $ilDB->createSequence('chatroom_smilies');
        }

        self::registerObject();
        self::registerAdminObject();
        self::removeOldChatEntries();
        self::convertChatObjects();

        $notificationSettings = new ilSetting('notifications');
        $notificationSettings->set('enable_osd', '1');
    }

    /**
     * Registers chat object by inserting it into object_data.
     * @global ilDBInterface $ilDB
     */
    public static function registerObject() : void
    {
        global $DIC;

        /**@var $ilDB ilDBInterface */
        $ilDB = $DIC->database();

        $typ_id = null;

        $query = 'SELECT obj_id FROM object_data ' .
            'WHERE type = ' . $ilDB->quote('typ', 'text') . ' ' .
            'AND title = ' . $ilDB->quote('chtr', 'text');
        if (!($object_definition_row = $ilDB->fetchAssoc($ilDB->query($query)))) {
            $typ_id = $ilDB->nextId('object_data');
            $ilDB->insert(
                'object_data',
                [
                    'obj_id' => ['integer', $typ_id],
                    'type' => ['text', 'typ'],
                    'title' => ['text', 'chtr'],
                    'description' => ['text', 'Chatroom Object'],
                    'owner' => ['integer', -1],
                    'create_date' => ['timestamp', date('Y-m-d H:i:s')],
                    'last_update' => ['timestamp', date('Y-m-d H:i:s')]
                ]
            );

            // REGISTER RBAC OPERATIONS FOR OBJECT TYPE
            // 1: edit_permissions, 2: visible, 3: read, 4:write
            foreach ([1, 2, 3, 4] as $ops_id) {
                $query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ( " .
                    $ilDB->quote($typ_id, 'integer') . "," . $ilDB->quote($ops_id, 'integer') . ")";
                $ilDB->manipulate($query);
            }
        }

        if ($moderatePermissionId = self::getModeratorPermissionId()) {
            if (!$typ_id) {
                $typ_id = (int) $object_definition_row['obj_id'];
            }

            if ($typ_id) {
                $ilDB->manipulateF(
                    'DELETE FROM rbac_ta WHERE typ_id = %s AND ops_id = %s',
                    ['integer', 'integer'],
                    [$typ_id, $moderatePermissionId]
                );

                $ilDB->insert(
                    'rbac_ta',
                    [
                        'typ_id' => ['integer', $typ_id],
                        'ops_id' => ['integer', $moderatePermissionId],
                    ]
                );
            }
        }
    }

    private static function getModeratorPermissionId() : int
    {
        global $DIC;

        /**@var $ilDB ilDBInterface */
        $ilDB = $DIC->database();

        $rset = $ilDB->queryF(
            'SELECT ops_id FROM rbac_operations WHERE operation = %s',
            ['text'],
            ['moderate']
        );
        if ($row = $ilDB->fetchAssoc($rset)) {
            return (int) $row['ops_id'];
        }

        return 0;
    }

    /**
     * Registgers admin chat object by inserting it into object_data.
     * @global ilDBInterface $ilDB
     */
    public static function registerAdminObject() : void
    {
        global $DIC;

        /**@var $ilDB ilDBInterface */
        $ilDB = $DIC->database();

        $query = 'SELECT * FROM object_data WHERE type = ' . $ilDB->quote('chta', 'text');
        if (!$ilDB->fetchAssoc($ilDB->query($query))) {
            $obj_id = $ilDB->nextId('object_data');
            $ilDB->insert(
                'object_data',
                [
                    'obj_id' => ['integer', $obj_id],
                    'type' => ['text', 'chta'],
                    'title' => ['text', 'Chatroom Admin'],
                    'description' => ['text', 'Chatroom General Settings'],
                    'owner' => ['integer', -1],
                    'create_date' => ['timestamp', date('Y-m-d H:i:s')],
                    'last_update' => ['timestamp', date('Y-m-d H:i:s')]
                ]
            );

            $ref_id = $ilDB->nextId('object_reference');
            $query = "
                INSERT INTO object_reference (ref_id, obj_id) VALUES(" .
                $ilDB->quote($ref_id, 'integer') . ", " . $ilDB->quote($obj_id, 'integer') . ")";
            $ilDB->manipulate($query);

            $tree = new ilTree(ROOT_FOLDER_ID);
            $tree->insertNode($ref_id, SYSTEM_FOLDER_ID);
        }
    }

    public static function removeOldChatEntries() : void
    {
        global $DIC;

        /**@var $ilDB ilDBInterface */
        $ilDB = $DIC->database();

        $res = $ilDB->queryF(
            'SELECT object_data.obj_id, ref_id, lft, rgt
            FROM object_data
            INNER JOIN object_reference ON object_reference.obj_id = object_data.obj_id
            INNER JOIN tree ON child = ref_id
            WHERE type = %s',
            ['text'],
            ['chac']
        );

        $data = $ilDB->fetchAssoc($res);
        if ($data) {
            $res = $ilDB->queryF(
                'SELECT * FROM tree
                INNER JOIN object_reference ON ref_id = child
                INNER JOIN object_data ON object_data.obj_id = object_reference.obj_id 
                WHERE lft BETWEEN %s AND %s',
                ['integer', 'integer'],
                [$data['lft'], $data['rgt']]
            );
            while ($row = $ilDB->fetchAssoc($res)) {
                $ilDB->manipulate(
                    'DELETE FROM object_data WHERE obj_id = ' . $ilDB->quote($row['obj_id'], 'integer')
                );

                $ilDB->manipulate(
                    'DELETE FROM object_reference WHERE ref_id = ' . $ilDB->quote($row['ref_id'], 'integer')
                );

                $ilDB->manipulate('DELETE FROM tree WHERE child = ' . $ilDB->quote($row['ref_id'], 'integer'));
            }
        }

        $ilDB->manipulateF('DELETE FROM object_data WHERE type = %s AND title = %s', ['text', 'text'], ['typ', 'chat']);
        $ilDB->manipulateF('DELETE FROM object_data WHERE type = %s AND title = %s', ['text', 'text'], ['typ', 'chac']);
    }

    /**
     * Converts old 'chat' objects to 'chtr' objects.
     */
    public static function convertChatObjects() : void
    {
        global $DIC;

        /**@var $ilDB ilDBInterface */
        $ilDB = $DIC->database();

        $res = $ilDB->queryF(
            'SELECT obj_id FROM object_data WHERE type = %s',
            ['text'],
            ['chat']
        );

        $obj_ids = [];

        while ($row = $ilDB->fetchAssoc($res)) {
            $obj_ids[] = (int) $row['obj_id'];
        }

        $ilDB->manipulateF(
            'UPDATE object_data SET type = %s WHERE type = %s',
            ['text', 'text'],
            ['chtr', 'chat']
        );

        self::setChatroomSettings($obj_ids);
    }

    /**
     * Sets autogen_usernames default option for chatrooms
     * @param int[] $obj_ids
     */
    public static function setChatroomSettings(array $obj_ids) : void
    {
        foreach ($obj_ids as $obj_id) {
            $room = new ilChatroom();
            $room->saveSettings([
                'object_id' => $obj_id,
                'autogen_usernames' => 'Autogen #',
                'room_type' => 'repository'
            ]);
        }
    }

    public static function createDefaultPublicRoom(bool $force = false) : void
    {
        global $DIC;

        /**@var $ilDB ilDBInterface */
        $ilDB = $DIC->database();

        if ($force) {
            $query = 'DELETE FROM chatroom_settings WHERE room_type = ' . $ilDB->quote('default', 'text');
            $ilDB->manipulate($query);
            $create = true;
        } else {
            $query = 'SELECT * FROM chatroom_settings WHERE room_type = ' . $ilDB->quote('default', 'text');
            $rset = $ilDB->query($query);
            $create = !$ilDB->fetchAssoc($rset);
        }
        if ($create) {
            $query = "
                SELECT object_data.obj_id, object_reference.ref_id
                FROM object_data
                INNER JOIN object_reference ON object_reference.obj_id = object_data.obj_id
                WHERE type = " . $ilDB->quote('chta', 'text');
            $rset = $ilDB->query($query);
            $row = $ilDB->fetchAssoc($rset);
            $chatfolder_ref_id = (int) $row['ref_id'];

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
                [
                    'room_id' => ['integer', $id],
                    'object_id' => ['integer', $obj_id],
                    'room_type' => ['text', 'default'],
                    'allow_anonymous' => ['integer', 0],
                    'allow_custom_usernames' => ['integer', 0],
                    'enable_history' => ['integer', 0],
                    'restrict_history' => ['integer', 0],
                    'autogen_usernames' => ['text', 'Anonymous #'],
                    'allow_private_rooms' => ['integer', 1],
                ]
            );

            $settings = new ilSetting('chatroom');
            $settings->set('public_room_ref', (string) $ref_id);
        }
    }

    public static function createMissinRoomSettingsForConvertedObjects() : void
    {
        global $DIC;

        /**@var $ilDB ilDBInterface */
        $ilDB = $DIC->database();

        $res = $ilDB->queryF(
            'SELECT obj_id FROM object_data LEFT JOIN chatroom_settings ON object_id = obj_id ' .
            'WHERE type = %s AND room_id IS NULL',
            ['text'],
            ['chtr']
        );

        $roomsToFix = [];
        while ($row = $ilDB->fetchAssoc($res)) {
            $roomsToFix[] = (int) $row['obj_id'];
        }

        self::setChatroomSettings($roomsToFix);
    }

    /**
     * @param int $ref_id
     */
    public static function ensureCorrectPublicChatroomTreeLocation(int $ref_id) : void
    {
        global $DIC;

        /** @var ilTree $tree */
        $tree = $DIC->repositoryTree();
        /** @var ilDBInterface $ilDB */
        $ilDB = $DIC->database();
        /** @var ilRbacAdmin $rbacadmin */
        $rbacadmin = $DIC->rbac()->admin();

        $ilDB->setLimit(1, 0);
        $query = '
            SELECT object_data.obj_id, object_reference.ref_id
            FROM object_data
            INNER JOIN object_reference ON object_reference.obj_id = object_data.obj_id
            WHERE type = ' . $ilDB->quote('chta', 'text');
        $rset = $ilDB->query($query);
        $row = $ilDB->fetchAssoc($rset);
        $chatfolder_ref_id = (int) $row['ref_id'];
        $pid = (int) $tree->getParentId($ref_id);

        if (
            $chatfolder_ref_id &&
            $pid !== $chatfolder_ref_id &&
            !$tree->isDeleted($chatfolder_ref_id)
        ) {
            $tree->moveTree($ref_id, $chatfolder_ref_id);
            $rbacadmin->adjustMovedObjectPermissions($ref_id, $pid);
            ilConditionHandler::_adjustMovedObjectConditions($ref_id);
        }
    }
}
