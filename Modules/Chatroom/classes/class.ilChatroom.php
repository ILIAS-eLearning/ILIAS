<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilChatroom
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroom
{
    private static $settingsTable = 'chatroom_settings';
    private static $historyTable = 'chatroom_history';
    private static $userTable = 'chatroom_users';
    private static $sessionTable = 'chatroom_sessions';
    private static $banTable = 'chatroom_bans';
    private static $privateRoomsTable = 'chatroom_prooms';
    private static $privateSessionsTable = 'chatroom_psessions';
    private static $uploadTable = 'chatroom_uploads';
    private static $privateRoomsAccessTable = 'chatroom_proomaccess';
    private $settings = array();
    /**
     * Each value of this array describes a setting with the internal type.
     * The type must be a type wich can be set by the function settype
     * @see http://php.net/manual/de/function.settype.php
     * @var array string => string
     */
    private $availableSettings = array(
        'object_id'              => 'integer',
        'online_status'          => 'integer',
        'allow_anonymous'        => 'boolean',
        'allow_custom_usernames' => 'boolean',
        'enable_history'         => 'boolean',
        'restrict_history'       => 'boolean',
        'autogen_usernames'      => 'string',
        'room_type'              => 'string',
        'allow_private_rooms'    => 'integer',
        'display_past_msgs'      => 'integer',
        'private_rooms_enabled'  => 'boolean'
    );
    private $roomId;

    private $object;

    /**
     * Checks user permissions by given array and ref_id.
     * @param string|array  $permissions
     * @param integer       $ref_id
     * @param bool          $send_info
     * @return bool
     */
    public static function checkUserPermissions($permissions, $ref_id, $send_info = true)
    {
        global $DIC;

        if (!is_array($permissions)) {
            $permissions = array($permissions);
        }

        $hasPermissions = self::checkPermissions($DIC->user()->getId(), $ref_id, $permissions);
        if (!$hasPermissions && $send_info) {
            ilUtil::sendFailure($DIC->language()->txt('permission_denied'), true);

            return false;
        }

        return $hasPermissions;
    }

    /**
     * Checks user permissions in question for a given user id in relation
     * to a given ref_id.
     * @param integer       $usr_id
     * @param array|string  $permissions
     * @param integer       $ref_id
     * @return bool
     */
    public static function checkPermissionsOfUser($usr_id, $permissions, $ref_id)
    {
        if (!is_array($permissions)) {
            $permissions = array($permissions);
        }

        return self::checkPermissions($usr_id, $ref_id, $permissions);
    }

    /**
     * @param int   $usrId
     * @param int   $refId
     * @param array $permissions
     * @return bool
     */
    protected static function checkPermissions($usrId, $refId, array $permissions)
    {
        global $DIC;

        require_once 'Modules/Chatroom/classes/class.ilObjChatroom.php';
        $pub_ref_id = ilObjChatroom::_getPublicRefId();

        foreach ($permissions as $permission) {
            if ($pub_ref_id == $refId) {
                $hasAccess = $DIC->rbac()->system()->checkAccessOfUser($usrId, $permission, $refId);
                if ($hasAccess) {
                    $hasWritePermission = $DIC->rbac()->system()->checkAccessOfUser($usrId, 'write', $refId);
                    if ($hasWritePermission) {
                        continue;
                    }

                    $visible  = null;
                    $a_obj_id = ilObject::_lookupObjId($refId);
                    $active   = ilObjChatroomAccess::isActivated($refId, $a_obj_id, $visible);

                    switch ($permission) {
                        case 'visible':
                            if (!$active) {
                                $GLOBALS['DIC']->access()->addInfoItem(IL_NO_OBJECT_ACCESS, $GLOBALS['DIC']->language()->txt('offline'));
                            }

                            if (!$active && !$visible) {
                                return false;
                            }
                            break;

                        case 'read':
                            if (!$active) {
                                $GLOBALS['DIC']->access()->addInfoItem(IL_NO_OBJECT_ACCESS, $GLOBALS['DIC']->language()->txt('offline'));
                                return false;
                            }
                            break;
                    }
                }
            } else {
                $hasAccess = $DIC->access()->checkAccessOfUser($usrId, $permission, '', $refId);
            }

            if (!$hasAccess) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns ilChatroom object by given $object_id.
     * @param integer        $object_id
     * @return ilChatroom
     */
    public static function byObjectId($object_id)
    {
        global $DIC;

        $query  = 'SELECT * FROM ' . self::$settingsTable . ' WHERE object_id = %s';
        $types  = array('integer');
        $values = array($object_id);
        $rset   = $DIC->database()->queryF($query, $types, $values);

        if ($row = $DIC->database()->fetchAssoc($rset)) {
            $room = new self();
            $room->initialize($row);
            return $room;
        }
    }

    /**
     * Sets $this->roomId by given array $rowdata and calls setSetting method
     * foreach available setting in $this->availableSettings.
     * @param array $rowdata
     */
    public function initialize(array $rowdata)
    {
        $this->roomId = $rowdata['room_id'];

        foreach ($this->availableSettings as $setting => $type) {
            if (isset($rowdata[$setting])) {
                settype($rowdata[$setting], $this->availableSettings[$setting]);
                $this->setSetting($setting, $rowdata[$setting]);
            }
        }
    }

    /**
     * Sets given name and value as setting into $this->settings array.
     * @param string $name
     * @param mixed  $value
     */
    public function setSetting($name, $value)
    {
        $this->settings[$name] = $value;
    }

    /**
     * Returns ilChatroom by given $room_id
     * @param integer        $room_id
     * @return ilChatroom
     */
    public static function byRoomId($room_id, $initObject = false)
    {
        global $DIC;

        $query = 'SELECT * FROM ' . self::$settingsTable . ' WHERE room_id = %s';

        $types  = array('integer');
        $values = array($room_id);

        $rset = $DIC->database()->queryF($query, $types, $values);

        if ($row = $DIC->database()->fetchAssoc($rset)) {
            $room = new self();
            $room->initialize($row);

            if ($initObject) {
                $room->object = ilObjectFactory::getInstanceByObjId($row['object_id']);
            }

            return $room;
        }
    }

    /**
     * Deletes all entrys from userTable.
     */
    public static function disconnectAllUsersFromAllRooms()
    {
        global $DIC;

        $DIC->database()->manipulate('DELETE FROM ' . self::$userTable);
        $DIC->database()->manipulate('UPDATE ' . self::$privateRoomsTable . ' SET closed = ' . $DIC->database()->quote(time(), 'integer') . ' WHERE closed = 0 OR closed IS NULL');
        $DIC->database()->manipulate('UPDATE ' . self::$privateSessionsTable . ' SET disconnected = ' . $DIC->database()->quote(time(), 'integer') . ' WHERE disconnected = 0');
        /**
         * @todo nicht nur löschen, auch in Session Tabelle nachpflegen
         */
    }

    public static function findDeletablePrivateRooms()
    {
        global $DIC;

        $query = '
			SELECT private_rooms.proom_id id, MIN(disconnected) min_disconnected, MAX(disconnected) max_disconnected
			FROM ' . self::$privateSessionsTable . ' private_sessions
			INNER JOIN ' . self::$privateRoomsTable . ' private_rooms
				ON private_sessions.proom_id = private_rooms.proom_id
			WHERE closed = 0
			GROUP BY private_rooms.proom_id
			HAVING MIN(disconnected) > 0 AND MAX(disconnected) < %s';
        $rset  = $DIC->database()->queryF(
            $query,
            array('integer'),
            array(time() + 60 * 5)
        );

        $rooms = array();

        while ($row = $DIC->database()->fetchAssoc($rset)) {
            $rooms[$row['id']] = $row['id'];
        }

        $query = 'SELECT DISTINCT proom_id, room_id, object_id FROM ' . self::$privateRoomsTable
            . ' INNER JOIN ' . self::$settingsTable . ' ON parent_id = room_id '
            . ' WHERE ' . $DIC->database()->in('proom_id', $rooms, false, 'integer');

        $rset  = $DIC->database()->query($query);
        $rooms = array();
        while ($row = $DIC->database()->fetchAssoc($rset)) {
            $rooms[] = array(
                'proom_id'  => $row['proom_id'],
                'room_id'   => $row['room_id'],
                'object_id' => $row['object_id']
            );
        }

        return $rooms;
    }

    public static function getUntrashedChatReferences($filter = array())
    {
        global $DIC;

        // Check for parent because of an invalid parent node for the old public chat (thx @ jposselt ;-)).
        // We cannot find this old public chat and clean this automatically
        $query = '
			SELECT od.obj_id, od.title, ore.ref_id, od.type, odp.title parent_title
			FROM object_data od
			INNER JOIN object_reference ore ON ore.obj_id = od.obj_id
			INNER JOIN tree t ON t.child = ore.ref_id
			INNER JOIN tree p ON p.child = t.parent
			INNER JOIN object_reference orep ON orep.ref_id = p.child
			INNER JOIN object_data odp ON odp.obj_id = orep.obj_id
			INNER JOIN object_reference pre ON pre.ref_id = t.parent
			INNER JOIN object_data pod ON pod.obj_id = pre.obj_id
		';

        if (isset($filter['last_activity'])) {
            $threshold = $DIC->database()->quote($filter['last_activity'], 'integer');
            $query .= "
				INNER JOIN chatroom_settings ON chatroom_settings.object_id = od.obj_id
				INNER JOIN chatroom_history ON chatroom_history.room_id = chatroom_settings.room_id AND chatroom_history.timestamp > $threshold
			";
        }

        $query .= '
			WHERE od.type = %s AND t.tree > 0 AND ore.deleted IS NULL
			GROUP BY od.obj_id, od.title, ore.ref_id, od.type, odp.title
			ORDER BY od.title
		';
        $res = $DIC->database()->queryF($query, array('text'), array('chtr'));

        $chats = array();
        while ($row = $DIC->database()->fetchAssoc($res)) {
            $chats[] = $row;
        }

        return $chats;
    }

    public function getDescription()
    {
        if (!$this->object) {
            $this->object = ilObjectFactory::getInstanceByObjId($this->getSetting('object_id'));
        }

        return $this->object->getDescription();
    }

    /**
     * Returns setting from $this->settings array by given name.
     * @param string $name
     * @return mixed
     */
    public function getSetting($name)
    {
        return $this->settings[$name];
    }

    /**
     * Saves settings using $this->settings
     */
    public function save()
    {
        $this->saveSettings($this->settings);
    }

    /**
     * Saves settings into settingsTable using given settings array.
     * @param array          $settings
     */
    public function saveSettings(array $settings)
    {
        global $DIC;

        $localSettings = array();

        foreach ($this->availableSettings as $setting => $type) {
            if (isset($settings[$setting])) {
                if ($type == 'boolean') {
                    $settings[$setting] = (boolean) $settings[$setting];
                }
                $localSettings[$setting] = array($this->phpTypeToMDBType($type), $settings[$setting]);
            }
        }

        if (!$localSettings['room_type'][1]) {
            $localSettings['room_type'][1] = 'repository';
        }

        if ($this->roomId) {
            $DIC->database()->update(
                self::$settingsTable,
                $localSettings,
                array('room_id' => array('integer', $this->roomId))
            );
        } else {
            $this->roomId = $DIC->database()->nextId(self::$settingsTable);

            $localSettings['room_id'] = array(
                $this->availableSettings['room_id'], $this->roomId
            );

            $DIC->database()->insert(self::$settingsTable, $localSettings);
        }
    }

    private function phpTypeToMDBType($type)
    {
        switch ($type) {
            case 'string':
                return 'text';
            default:
                return $type;
        }
    }

    /**
     * Inserts entry into historyTable.
     * @todo $recipient, $publicMessage speichern
     * @param string         $message
     * @param string         $recipient
     * @param boolean        $publicMessage
     */
    public function addHistoryEntry($message, $recipient = null, $publicMessage = true)
    {
        global $DIC;

        $subRoom   = 0;
        $timestamp = 0;
        if (is_array($message)) {
            $subRoom   = (int) $message['sub'];
            $timestamp = (int) $message['timestamp'];
        } elseif (is_object($message)) {
            $subRoom   = (int) $message->sub;
            $timestamp = (int) $message->timestamp;
        }

        $id = $DIC->database()->nextId(self::$historyTable);
        $DIC->database()->insert(
            self::$historyTable,
            array(
                'hist_id'   => array('integer', $id),
                'room_id'   => array('integer', $this->roomId),
                'sub_room'  => array('integer', $subRoom),
                'message'   => array('text', json_encode($message)),
                'timestamp' => array('integer', ($timestamp > 0 ? $timestamp : time())),
            )
        );
    }

    /**
     * Connects user by inserting userdata into userTable.
     * Checks if user is already connected by using the given $user object
     * for selecting the userId from userTable. If no entry is found, matching
     * userId and roomId, the userdata is inserted into the userTable to
     * connect the user.
     * @param ilChatroomUser $user
     * @return boolean
     */
    public function connectUser(ilChatroomUser $user)
    {
        global $DIC;

        $userdata = array(
            'login' => $user->getUsername(),
            'id'    => $user->getUserId()
        );

        $query  = 'SELECT user_id FROM ' . self::$userTable . ' WHERE room_id = %s AND user_id = %s';
        $types  = array('integer', 'integer');
        $values = array($this->roomId, $user->getUserId());

        if (!$DIC->database()->fetchAssoc($DIC->database()->queryF($query, $types, $values))) {
            // Notice: Using replace instead of insert looks strange, because we actually know whether the selected data exists or not
            // But we occasionally found some duplicate key errors although the data set should not exist when the following code is reached
            $DIC->database()->replace(
                self::$userTable,
                array(
                    'room_id' => array('integer', $this->roomId),
                    'user_id' => array('integer', $user->getUserId())
                ),
                array(
                    'userdata'  => array('text', json_encode($userdata)),
                    'connected' => array('integer', time()),
                )
            );

            return true;
        }

        return false;
    }

    /**
     * Returns an array of connected users.
     * Returns an array of user objects containing all users having an entry
     * in userTable, matching the roomId.
     * @param bool $only_data
     * @return array
     */
    public function getConnectedUsers($only_data = true)
    {
        global $DIC;

        $query  = 'SELECT ' . ($only_data ? 'userdata' : '*') . ' FROM ' . self::$userTable . ' WHERE room_id = %s';
        $types  = array('integer');
        $values = array($this->roomId);
        $rset   = $DIC->database()->queryF($query, $types, $values);
        $users  = array();

        while ($row = $DIC->database()->fetchAssoc($rset)) {
            $users[] = $only_data ? json_decode($row['userdata']) : $row;
        }

        return $users;
    }

    /**
     * Creates userId array by given $user object and calls disconnectUsers
     * method.
     * @param int $user_id
     */
    public function disconnectUser($user_id)
    {
        $this->disconnectUsers(array($user_id));
    }

    /**
     * Disconnects users by deleting userdata from userTable using given userId array.
     * Deletes entrys from userTable, matching roomId and userId if existing and
     * inserts userdata and disconnection time into sessionTable.
     * @param array          $userIds
     */
    public function disconnectUsers(array $userIds)
    {
        global $DIC;

        $query = 'SELECT * FROM ' . self::$userTable . ' WHERE room_id = %s AND ' .
            $DIC->database()->in('user_id', $userIds, false, 'integer');

        $types  = array('integer');
        $values = array($this->roomId);
        $rset   = $DIC->database()->queryF($query, $types, $values);

        if ($row = $DIC->database()->fetchAssoc($rset)) {
            $query       = 'SELECT proom_id FROM ' . self::$privateRoomsTable . ' WHERE parent_id = %s';
            $rset_prooms = $DIC->database()->queryF($query, array('integer'), array($this->roomId));

            $prooms = array();

            while ($row_prooms = $DIC->database()->fetchAssoc($rset_prooms)) {
                $prooms[] = $row_prooms['proom_id'];
            }

            if (true || $this->getSetting('enable_history')) {
                $query = 'UPDATE ' . self::$privateSessionsTable . ' SET disconnected = %s WHERE ' . $DIC->database()->in('user_id', $userIds, false, 'integer') . ' AND ' . $DIC->database()->in('proom_id', $prooms, false, 'integer');
                $DIC->database()->manipulateF($query, array('integer'), array(time()));
            } else {
                $query = 'DELETE FROM ' . self::$privateSessionsTable . ' WHERE ' . $DIC->database()->in('user_id', $userIds, false, 'integer') . ' AND ' . $DIC->database()->in('proom_id', $prooms, false, 'integer');
                $DIC->database()->manipulate($query);
            }

            $query = 'DELETE FROM ' . self::$userTable . ' WHERE room_id = %s AND ' .
                $DIC->database()->in('user_id', $userIds, false, 'integer');

            $types  = array('integer');
            $values = array($this->roomId);
            $DIC->database()->manipulateF($query, $types, $values);

            do {
                if ($this->getSetting('enable_history')) {
                    $id = $DIC->database()->nextId(self::$sessionTable);
                    $DIC->database()->insert(
                        self::$sessionTable,
                        array(
                            'sess_id'      => array('integer', $id),
                            'room_id'      => array('integer', $this->roomId),
                            'user_id'      => array('integer', $row['user_id']),
                            'userdata'     => array('text', $row['userdata']),
                            'connected'    => array('integer', $row['connected']),
                            'disconnected' => array('integer', time())
                        )
                    );
                }
            } while ($row = $DIC->database()->fetchAssoc($rset));
        }
    }

    /**
     * Returns $this->settings array.
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Returns true if entry exists in userTable matching given $chat_userid
     * and $this->roomId.
     * @param integer        $chat_userid
     * @return boolean
     */
    public function isSubscribed($chat_userid)
    {
        global $DIC;

        $query = 'SELECT count(user_id) as cnt FROM ' . self::$userTable .
            ' WHERE room_id = %s AND user_id = %s';

        $types  = array('integer', 'integer');
        $values = array($this->roomId, $chat_userid);
        $rset   = $DIC->database()->queryF($query, $types, $values);

        if ($rset && ($row = $DIC->database()->fetchAssoc($rset)) && $row['cnt'] == 1) {
            return true;
        }

        return false;
    }

    public function isAllowedToEnterPrivateRoom($chat_userid, $proom_id)
    {
        global $DIC;

        $query = 'SELECT count(user_id) cnt FROM ' . self::$privateRoomsAccessTable .
            ' WHERE proom_id = %s AND user_id = %s';

        $types  = array('integer', 'integer');
        $values = array($proom_id, $chat_userid);
        $rset   = $DIC->database()->queryF($query, $types, $values);

        if ($rset && ($row = $DIC->database()->fetchAssoc($rset)) && $row['cnt'] == 1) {
            return true;
        }

        $query = 'SELECT count(*) cnt FROM ' . self::$privateRoomsTable .
            ' WHERE proom_id = %s AND owner = %s';

        $types  = array('integer', 'integer');
        $values = array($proom_id, $chat_userid);
        $rset   = $DIC->database()->queryF($query, $types, $values);

        if ($rset && ($row = $DIC->database()->fetchAssoc($rset)) && $row['cnt'] == 1) {
            return true;
        }

        return false;
    }

    /**
     * Returns array containing history data selected from historyTable by given
     * ilDateTime, $restricted_session_userid and matching roomId.
     * @param ilDateTime     $from
     * @param ilDateTime     $to
     * @param integer        $restricted_session_userid
     * @param bool           $respect_target
     * @return array
     */
    public function getHistory(ilDateTime $from = null, ilDateTime $to = null, $restricted_session_userid = null, $proom_id = 0, $respect_target = true)
    {
        global $DIC;

        $join = '';

        if ($proom_id) {
            $join .=
                'INNER JOIN ' . self::$privateSessionsTable . ' pSessionTable ' .
                'ON pSessionTable.user_id = ' . $DIC->database()->quote($restricted_session_userid, 'integer') . ' ' .
                'AND pSessionTable.proom_id = historyTable.sub_room ' .
                'AND timestamp >= pSessionTable.connected ' .
                'AND timestamp <= pSessionTable.disconnected ';
        }

        $query =
            'SELECT historyTable.* ' .
            'FROM ' . self::$historyTable . ' historyTable ' . $join . ' ' .
            'WHERE historyTable.room_id = ' . $this->getRoomId();

        if ($proom_id !== null) {
            $query .= ' AND historyTable.sub_room = ' . $DIC->database()->quote($proom_id, 'integer');
        }

        $filter = array();

        if ($from != null) {
            $filter[] = 'timestamp >= ' . $DIC->database()->quote($from->getUnixTime(), 'integer');
        }

        if ($to != null) {
            $filter[] = 'timestamp <= ' . $DIC->database()->quote($to->getUnixTime(), 'integer');
        }

        if ($filter) {
            $query .= ' AND ' . join(' AND ', $filter);
        }
        $query .= ' ORDER BY timestamp ASC';

        $rset   = $DIC->database()->query($query);
        $result = array();

        while ($row = $DIC->database()->fetchAssoc($rset)) {
            $message = json_decode($row['message']);
            if ($message === null) {
                $message = json_decode('{}');
            }

            $row['message']            =  $message;
            $row['message']->timestamp = $row['timestamp'];
            if (
                $respect_target &&
                $row['message']->target !== null &&
                !$row['message']->target->public &&
                !in_array($DIC->user()->getId(), explode(',', $row['recipients']))
            ) {
                continue;
            }

            $result[] = $row;
        }
        return $result;
    }

    /**
     * Returns roomID from $this->roomId
     * @return integer
     */
    public function getRoomId()
    {
        return $this->roomId;
    }

    public function getPrivateRoomSessions(ilDateTime $from = null, ilDateTime $to = null, $user_id = 0, $room_id = 0)
    {
        global $DIC;

        $query = 'SELECT proom_id, title FROM ' . self::$privateRoomsTable . ' WHERE proom_id IN (
			SELECT proom_id FROM ' . self::$privateSessionsTable . ' WHERE connected >= %s AND disconnected <= %s AND user_id = %s

		) AND parent_id = %s';

        $rset   = $DIC->database()->queryF($query, array('integer', 'integer', 'integer', 'integer'), array($from->getUnixTime(), $to->getUnixTime(), $user_id, $room_id));
        $result = array();
        while ($row = $DIC->database()->fetchAssoc($rset)) {
            $result[] = $row;
        }
        return $result;
    }

    /**
     * Saves information about file uploads in DB.
     * @param integer        $user_id
     * @param string         $filename
     * @param string         $type
     */
    public function saveFileUploadToDb($user_id, $filename, $type)
    {
        global $DIC;

        $upload_id = $DIC->database()->nextId(self::$uploadTable);

        $DIC->database()->insert(
            self::$uploadTable,
            array(
                'upload_id' => array('integer', $upload_id),
                'room_id'   => array('integer', $this->roomId),
                'user_id'   => array('integer', $user_id),
                'filename'  => array('text', $filename),
                'filetype'  => array('text', $type),
                'timestamp' => array('integer', time())
            )
        );
    }

    /**
     * Inserts user into banTable, using given $user_id
     * @param integer        $user_id
     * @param integer        $actor_id
     * @param string         $comment
     */
    public function banUser($user_id, $actor_id, $comment = '')
    {
        global $DIC;

        $DIC->database()->replace(
            self::$banTable,
            array(
                'room_id' => array('integer', $this->roomId),
                'user_id' => array('integer', $user_id)
            ),
            array(
                'actor_id'  => array('integer', $actor_id),
                'timestamp' => array('integer', time()),
                'remark'    => array('text', $comment)
            )
        );
    }

    /**
     * Deletes entry from banTable matching roomId and given $user_id and
     * returns true if sucessful.
     * @param mixed          $user_id
     * @return boolean
     */
    public function unbanUser($user_id)
    {
        global $DIC;

        if (!is_array($user_id)) {
            $user_id = array($user_id);
        }

        $query = 'DELETE FROM ' . self::$banTable . ' WHERE room_id = %s AND ' . $DIC->database()->in('user_id', $user_id, false, 'integer');

        $types  = array('integer');
        $values = array($this->getRoomId());

        return $DIC->database()->manipulateF($query, $types, $values);
    }

    /**
     * Returns true if there's an entry in banTable matching roomId and given
     * $user_id
     * @param integer        $user_id
     * @return boolean
     */
    public function isUserBanned($user_id)
    {
        global $DIC;

        $query = 'SELECT COUNT(user_id) cnt FROM ' . self::$banTable . ' WHERE user_id = %s AND room_id = %s';

        $types  = array('integer', 'integer');
        $values = array($user_id, $this->getRoomId());

        $rset = $DIC->database()->queryF($query, $types, $values);

        if ($rset && ($row = $DIC->database()->fetchAssoc($rset)) && $row['cnt']) {
            return true;
        }

        return false;
    }

    /**
     * Returns an multidimensional array containing userdata from users
     * having an entry in banTable with matching roomId.
     * @return array
     */
    public function getBannedUsers()
    {
        global $DIC;

        $query  = 'SELECT chb.* FROM ' . self::$banTable . ' chb INNER JOIN usr_data ud ON chb.user_id = ud.usr_id WHERE chb.room_id = %s ';
        $types  = array('integer');
        $values = array($this->getRoomId());
        $rset   = $DIC->database()->queryF($query, $types, $values);
        $result = array();

        if ($rset) {
            while ($row = $DIC->database()->fetchAssoc($rset)) {
                if ($row['user_id'] > 0) {
                    $user     = new ilObjUser($row['user_id']);
                    $userdata = array(
                        'user_id'   => $user->getId(),
                        'firstname' => $user->getFirstname(),
                        'lastname'  => $user->getLastname(),
                        'login'     => $user->getLogin(),
                        'timestamp' => $row['timestamp'],
                        'actor_id'  => $row['actor_id'],
                        'remark'    => $row['remark']
                    );

                    $result[] = $userdata;
                }
            }
        }

        return $result;
    }

    /**
     * Returns last session from user.
     * Returns row from sessionTable where user_id matches userId from given
     * $user object.
     * @param ilChatroomUser $user
     * @return array
     */
    public function getLastSession(ilChatroomUser $user)
    {
        global $DIC;

        $query = 'SELECT * FROM ' . self::$sessionTable . ' WHERE user_id = ' .
            $DIC->database()->quote($user->getUserId(), 'integer') .
            ' ORDER BY connected DESC';

        $DIC->database()->setLimit(1);
        $rset = $DIC->database()->query($query);

        if ($row = $DIC->database()->fetchAssoc($rset)) {
            return $row;
        }
    }

    /**
     * Returns all session from user
     * Returns all from sessionTable where user_id matches userId from given
     * $user object.
     * @param ilChatroomUser $user
     * @return array
     */
    public function getSessions(ilChatroomUser $user)
    {
        global $DIC;

        $query = 'SELECT * FROM ' . self::$sessionTable
            . ' WHERE room_id = ' .
            $DIC->database()->quote($this->getRoomId(), 'integer') .
            ' ORDER BY connected DESC';

        $rset = $DIC->database()->query($query);

        $result = array();

        while ($row = $DIC->database()->fetchAssoc($rset)) {
            $result[] = $row;
        }

        return $result;
    }

    public function addPrivateRoom($title, ilChatroomUser $owner, $settings)
    {
        global $DIC;

        $nextId = $DIC->database()->nextId(self::$privateRoomsTable);
        $DIC->database()->insert(
            self::$privateRoomsTable,
            array(
                'proom_id'  => array('integer', $nextId),
                'parent_id' => array('integer', $this->roomId),
                'title'     => array('text', $title),
                'owner'     => array('integer', $owner->getUserId()),
                'closed'    => array('integer', (isset($settings['closed']) ? $settings['closed'] : 0)),
                'created'   => array('integer', (isset($settings['created']) ? $settings['created'] : time())),
                'is_public' => array('integer', $settings['public']),
            )
        );

        return $nextId;
    }

    public function closePrivateRoom($id)
    {
        global $DIC;

        $DIC->database()->manipulateF(
            'UPDATE ' . self::$privateRoomsTable . ' SET closed = %s WHERE proom_id = %s',
            array('integer', 'integer'),
            array(time(), $id)
        );
    }

    public function isOwnerOfPrivateRoom($user_id, $proom_id)
    {
        global $DIC;

        $query  = 'SELECT proom_id FROM ' . self::$privateRoomsTable . ' WHERE proom_id = %s AND owner = %s';
        $types  = array('integer', 'integer');
        $values = array($proom_id, $user_id);

        $rset = $DIC->database()->queryF($query, $types, $values);

        if ($rset && $DIC->database()->fetchAssoc($rset)) {
            return true;
        }
        return false;
    }

    /**
     * @param        $gui
     * @param mixed  $sender (can be an instance of ilChatroomUser or an user id of an ilObjUser instance
     * @param int    $recipient_id
     * @param int    $subScope
     * @param string $invitationLink
     * @throws InvalidArgumentException
     */
    public function sendInvitationNotification($gui, $sender, $recipient_id, $subScope = 0, $invitationLink = '')
    {
        global $DIC;

        if ($gui && !$invitationLink) {
            $invitationLink = $this->getChatURL($gui, $subScope);
        }

        if ($recipient_id > 0 && !in_array(ANONYMOUS_USER_ID, array($recipient_id))) {
            if (is_numeric($sender) && $sender > 0) {
                $sender_id = $sender;
                /**
                 * @var $usr ilObjUser
                 */
                $usr         = ilObjectFactory::getInstanceByObjId($sender);
                $public_name = $usr->getPublicName();
            } elseif ($sender instanceof ilChatroomUser) {
                if ($sender->getUserId() > 0) {
                    $sender_id = $sender->getUserId();
                } else {
                    $sender_id = ANONYMOUS_USER_ID;
                }
                $public_name = $sender->getUsername();
            } else {
                throw new InvalidArgumentException('$sender must be an instance of ilChatroomUser or an id of an ilObjUser instance');
            }

            $userLang   = ilLanguageFactory::_getLanguageOfUser($recipient_id);
            $userLang->loadLanguageModule('mail');
            require_once 'Services/Mail/classes/class.ilMail.php';
            $bodyParams = array(
                'link'         => $invitationLink,
                'inviter_name' => $public_name,
                'room_name'    => $this->getTitle(),
                'salutation'   => ilMail::getSalutation($recipient_id, $userLang)
            );

            if ($subScope) {
                $bodyParams['room_name'] .= ' - ' . self::lookupPrivateRoomTitle($subScope);
            }

            require_once 'Services/Notifications/classes/class.ilNotificationConfig.php';
            $notification = new ilNotificationConfig('chat_invitation');
            $notification->setTitleVar('chat_invitation', $bodyParams, 'chatroom');
            $notification->setShortDescriptionVar('chat_invitation_short', $bodyParams, 'chatroom');
            $notification->setLongDescriptionVar('chat_invitation_long', $bodyParams, 'chatroom');
            $notification->setAutoDisable(false);
            $notification->setLink($invitationLink);
            $notification->setIconPath('templates/default/images/icon_chtr.svg');
            $notification->setValidForSeconds(ilNotificationConfig::TTL_LONG);
            $notification->setVisibleForSeconds(ilNotificationConfig::DEFAULT_TTS);

            $notification->setHandlerParam('mail.sender', $sender_id);

            $notification->notifyByUsers(array($recipient_id));
        }
    }

    /**
     * @param         $gui
     * @param integer $scope_id
     * @return string
     */
    public function getChatURL($gui, $scope_id = 0)
    {
        include_once 'Services/Link/classes/class.ilLink.php';

        $url = '';

        if (is_object($gui)) {
            if ($scope_id) {
                $url = ilLink::_getStaticLink($gui->object->getRefId(), $gui->object->getType(), true, '_' . $scope_id);
            } else {
                $url = ilLink::_getStaticLink($gui->object->getRefId(), $gui->object->getType());
            }
        }

        return $url;
    }

    public function getTitle()
    {
        if (!$this->object) {
            $this->object = ilObjectFactory::getInstanceByObjId($this->getSetting('object_id'));
        }

        return $this->object->getTitle();
    }

    public static function lookupPrivateRoomTitle($proom_id)
    {
        global $DIC;

        $query  = 'SELECT title FROM ' . self::$privateRoomsTable . ' WHERE proom_id = %s';
        $types  = array('integer');
        $values = array($proom_id);

        $rset = $DIC->database()->queryF($query, $types, $values);

        if ($row = $DIC->database()->fetchAssoc($rset)) {
            return $row['title'];
        }

        return 'unkown';
    }

    public function inviteUserToPrivateRoomByLogin($login, $proom_id)
    {
        $user_id = ilObjUser::_lookupId($login);
        $this->inviteUserToPrivateRoom($user_id, $proom_id);
    }

    /**
     * @param int $user_id
     * @param int $proom_id
     */
    public function inviteUserToPrivateRoom($user_id, $proom_id)
    {
        global $DIC;

        $DIC->database()->replace(self::$privateRoomsAccessTable, array(
            'user_id'  => array('integer', $user_id),
            'proom_id' => array('integer', $proom_id)
        ), array());
    }

    public function getActivePrivateRooms($userid)
    {
        global $DIC;

        $query  = '
			SELECT roomtable.title, roomtable.proom_id, accesstable.user_id id, roomtable.owner rowner
			FROM ' . self::$privateRoomsTable . ' roomtable
			LEFT JOIN ' . self::$privateRoomsAccessTable . ' accesstable
			ON roomtable.proom_id = accesstable.proom_id
			AND accesstable.user_id = %s
			WHERE parent_id = %s
			AND (closed = 0 OR closed IS NULL)
			AND (accesstable.user_id IS NOT NULL OR roomtable.owner = %s)';
        $types  = array('integer', 'integer', 'integer');
        $values = array($userid, $this->roomId, $userid);
        $rset   = $DIC->database()->queryF($query, $types, $values);
        $rooms  = array();

        while ($row = $DIC->database()->fetchAssoc($rset)) {
            $row['active_users']     = $this->listUsersInPrivateRoom($row['id']);
            $row['owner']            = $row['rowner'];
            $rooms[$row['proom_id']] = $row;
        }

        return $rooms;
    }

    public function listUsersInPrivateRoom($private_room_id)
    {
        global $DIC;

        $query  = '
			SELECT chatroom_users.user_id FROM ' . self::$privateSessionsTable . '
			INNER JOIN chatroom_users ON chatroom_users.user_id = ' . self::$privateSessionsTable . '.user_id WHERE proom_id = %s AND disconnected = 0
		';
        $types  = array('integer');
        $values = array($private_room_id);
        $rset   = $DIC->database()->queryF($query, $types, $values);

        $users = array();

        while ($row = $DIC->database()->fetchAssoc($rset)) {
            $users[$row['user_id']] = $row['user_id'];
        }

        return array_values($users);
    }

    public function subscribeUserToPrivateRoom($room_id, $user_id)
    {
        global $DIC;

        if (!$this->userIsInPrivateRoom($room_id, $user_id)) {
            $id = $DIC->database()->nextId(self::$privateSessionsTable);
            $DIC->database()->insert(
                self::$privateSessionsTable,
                array(
                    'psess_id'     => array('integer', $id),
                    'proom_id'     => array('integer', $room_id),
                    'user_id'      => array('integer', $user_id),
                    'connected'    => array('integer', time()),
                    'disconnected' => array('integer', 0),
                )
            );
        }
    }

    public function userIsInPrivateRoom($room_id, $user_id)
    {
        global $DIC;

        $query  = 'SELECT proom_id id FROM ' . self::$privateSessionsTable . ' WHERE user_id = %s AND proom_id = %s AND disconnected = 0';
        $types  = array('integer', 'integer');
        $values = array($user_id, $room_id);
        $rset   = $DIC->database()->queryF($query, $types, $values);
        if ($DIC->database()->fetchAssoc($rset)) {
            return true;
        }
        return false;
    }

    /**
     * @param integer        $room_id
     * @param integer        $user_id
     */
    public function unsubscribeUserFromPrivateRoom($room_id, $user_id)
    {
        global $DIC;

        $DIC->database()->update(
            self::$privateSessionsTable,
            array(
                'disconnected' => array('integer', time())
            ),
            array(
                'proom_id' => array('integer', $room_id),
                'user_id'  => array('integer', $user_id)
            )
        );
    }

    public function countActiveUsers()
    {
        global $DIC;

        $query = 'SELECT count(user_id) as cnt FROM ' . self::$userTable .
            ' WHERE room_id = %s';

        $types  = array('integer');
        $values = array($this->roomId);
        $rset   = $DIC->database()->queryF($query, $types, $values);

        if ($rset && ($row = $DIC->database()->fetchAssoc($rset)) && $row['cnt'] == 1) {
            return $row['cnt'];
        }

        return 0;
    }

    public function getPrivateRooms()
    {
        global $DIC;

        $query = 'SELECT * FROM ' . self::$privateRoomsTable . ' WHERE parent_id = %s';
        $rset  = $DIC->database()->queryF($query, array('integer'), array($this->roomId));

        $rooms = array();

        while ($row = $DIC->database()->fetchAssoc($rset)) {
            $rooms[] = $row;
        }

        return $rooms;
    }

    /**
     * @param int $subRoomId
     * @return int[]
     */
    public function getPrivilegedUsersForPrivateRoom($subRoomId)
    {
        global $DIC;

        $query = 'SELECT user_id FROM ' . self::$privateRoomsAccessTable . ' WHERE proom_id = %s';
        $rset  = $DIC->database()->queryF($query, array('integer'), array($subRoomId));

        $userIds = array();

        while ($row = $DIC->database()->fetchAssoc($rset)) {
            $userIds[] = $row['user_id'];
        }

        return $userIds;
    }

    public function getUniquePrivateRoomTitle($title)
    {
        global $DIC;

        $query = 'SELECT title FROM ' . self::$privateRoomsTable . ' WHERE parent_id = %s and closed = 0';
        $rset  = $DIC->database()->queryF($query, array('integer'), array($this->roomId));

        $titles = array();

        while ($row = $DIC->database()->fetchAssoc($rset)) {
            $titles[] = $row['title'];
        }

        $suffix = '';
        $i      = 0;
        do {
            if (!in_array($title . $suffix, $titles)) {
                $title .= $suffix;
                break;
            }

            ++$i;

            $suffix = ' (' . $i . ')';
        } while (true);

        return $title;
    }

    /**
     * Fetches and returns a Array<Integer, String> of all accessible repository object chats in the main tree
     * @param integer $user_id
     * @return string[]
     */
    public function getAccessibleRoomIdByTitleMap($user_id)
    {
        global $DIC;

        $query = "
			SELECT room_id, od.title, objr.ref_id
			FROM object_data od
			INNER JOIN  " . self::$settingsTable . "
				ON object_id = od.obj_id
			INNER JOIN object_reference objr
				ON objr.obj_id = od.obj_id
				AND objr.deleted IS NULL
			INNER JOIN tree
				ON tree.child = objr.ref_id
				AND tree.tree = %s
			WHERE od.type = %s
       ";

        $types  = array('integer', 'text');
        $values = array(1, 'chtr');

        $res = $DIC->database()->queryF($query, $types, $values);

        $rooms = [];

        while ($row = $DIC->database()->fetchAssoc($res)) {
            if (ilChatroom::checkPermissionsOfUser($user_id, 'read', $row['ref_id'])) {
                $rooms[$row['room_id']] = $row['title'];
            }
        }

        return $rooms;
    }

    public function getPrivateSubRooms($parent_room, $user_id)
    {
        global $DIC;

        $query = "
       SELECT      proom_id, parent_id
       FROM        " . self::$privateRoomsTable . "
       WHERE       parent_id = %s
       AND     owner = %s
       AND     closed = 0
       ";

        $types  = array('integer', 'integer');
        $values = array($parent_room, $user_id);

        $res = $DIC->database()->queryF($query, $types, $values);

        $priv_rooms = array();

        while ($row = $DIC->database()->fetchAssoc($res)) {
            $proom_id              = $row['proom_id'];
            $priv_rooms[$proom_id] = $row['parent_id'];
        }

        return $priv_rooms;
    }

    /**
     * Returns ref_id of given room_id
     * @param integer        $room_id
     * @return integer
     */
    public function getRefIdByRoomId($room_id)
    {
        global $DIC;

        $query = "
       SELECT      objr.ref_id
       FROM        object_reference    objr

       INNER JOIN  chatroom_settings   cs
           ON      cs.object_id = objr.obj_id

       INNER JOIN  object_data     od
           ON      od.obj_id = cs.object_id

       WHERE       cs.room_id = %s
       ";

        $types  = array('integer');
        $values = array($room_id);

        $res = $DIC->database()->queryF($query, $types, $values);

        $row = $DIC->database()->fetchAssoc($res);

        return $row['ref_id'];
    }

    public function getLastMessagesForChatViewer($number, $chatuser = null)
    {
        return $this->getLastMessages($number, $chatuser);
    }

    public function getLastMessages($number, $chatuser = null)
    {
        global $DIC;

        // There is currently no way to check if a message is private or not
        // by sql. So we fetch twice as much as we need and hope that there
        // are not more than $number private messages.
        $DIC->database()->setLimit($number);
        $rset = $DIC->database()->query(
            'SELECT *
			FROM ' . self::$historyTable . '
			WHERE room_id = ' . $DIC->database()->quote($this->roomId, 'integer') . '
			AND sub_room = 0
			AND (
				(' . $DIC->database()->like('message', 'text', '%"type":"message"%') . ' AND NOT ' . $DIC->database()->like('message', 'text', '%"public":0%') . ')
		  		OR ' . $DIC->database()->like('message', 'text', '%"target":{%"id":"' . $chatuser->getUserId() . '"%') . '
				OR ' . $DIC->database()->like('message', 'text', '%"from":{"id":' . $chatuser->getUserId() . '%') . '
			)
			ORDER BY timestamp DESC'
        );

        $result_count = 0;
        $results      = array();
        while (($row = $DIC->database()->fetchAssoc($rset)) && $result_count < $number) {
            $tmp = json_decode($row['message']);
            if ($chatuser !== null && $tmp->target != null && $tmp->target->public == 0) {
                if ($chatuser->getUserId() == $tmp->target->id || $chatuser->getUserId() == $tmp->from->id) {
                    $results[] = $tmp;
                    ++$result_count;
                }
            } else {
                $results[] = $tmp;
                ++$result_count;
            }
        }

        $rset = $DIC->database()->query(
            'SELECT *
			FROM ' . self::$historyTable . '
			WHERE room_id = ' . $DIC->database()->quote($this->roomId, 'integer') . '
			AND sub_room = 0
			AND ' . $DIC->database()->like('message', 'text', '%"type":"notice"%') . '
			AND timestamp <= ' . $DIC->database()->quote($results[0]->timestamp, 'integer') . ' AND timestamp >= ' . $DIC->database()->quote($results[$result_count - 1]->timestamp, 'integer') . '

			ORDER BY timestamp DESC'
        );

        while (($row = $DIC->database()->fetchAssoc($rset))) {
            $tmp       = json_decode($row['message']);
            $results[] = $tmp;
        }

        \usort($results, function ($a, $b) {
            $a_timestamp = strlen($a->timestamp) == 13 ? substr($a->timestamp, 0, -3) : $a->timestamp;
            $b_timestamp = strlen($b->timestamp) == 13 ? substr($b->timestamp, 0, -3) : $b->timestamp;

            return $b_timestamp - $a_timestamp;
        });

        return $results;
    }

    public function clearMessages($sub_room)
    {
        global $DIC;

        $DIC->database()->queryF(
            'DELETE FROM ' . self::$historyTable . ' WHERE room_id = %s AND sub_room = %s',
            array('integer', 'integer'),
            array($this->roomId, (int) $sub_room)
        );

        if ($sub_room) {
            $DIC->database()->queryF(
                'DELETE FROM ' . self::$privateSessionsTable . ' WHERE proom_id = %s AND disconnected < %s',
                array('integer', 'integer'),
                array($sub_room, time())
            );
        } else {
            $DIC->database()->queryF(
                'DELETE FROM ' . self::$sessionTable . ' WHERE room_id = %s AND disconnected < %s',
                array('integer', 'integer'),
                array($this->roomId, time())
            );
        }
    }
}
