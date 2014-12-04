<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilChatroom
 *
 * @author Jan Posselt <jposselt@databay.de>
 * @version $Id$
 *
 * @ingroup ModulesChatroom
 */
class ilChatroom
{

	private $settings = array();
	private static $settingsTable		= 'chatroom_settings';
	private static $historyTable		= 'chatroom_history';
	private static $userTable		= 'chatroom_users';
	private static $sessionTable		= 'chatroom_sessions';
	private static $banTable		= 'chatroom_bans';
	private static $privateRoomsTable	= 'chatroom_prooms';
	private static $privateSessionsTable	= 'chatroom_psessions';
	private static $uploadTable		= 'chatroom_uploads';
	private static $privateRoomsAccessTable = 'chatroom_proomaccess';

	/**
	 * Each value of this array describes a setting with the internal type.
	 * The type must be a type wich can be set by the function settype
	 *
	 * @see http://php.net/manual/de/function.settype.php
	 * @var array string => string
	 */
	private $availableSettings		= array(
			'object_id' 				=> 'integer',
			'allow_anonymous' 			=> 'boolean',
			'allow_custom_usernames' 	=> 'boolean',
			'enable_history' 			=> 'boolean',
			'restrict_history' 			=> 'boolean',
			'autogen_usernames' 		=> 'string',
			'room_type' 		=> 'string',
			'allow_private_rooms' 		=> 'integer',
			'display_past_msgs' => 'integer',
			'private_rooms_enabled' => 'boolean'
	);	
	private $roomId;

	private $object;

	public function getTitle() 
	{
	    if( !$this->object )
	    {
		$this->object = ilObjectFactory::getInstanceByObjId($this->getSetting('object_id'));
	    }

	    return $this->object->getTitle();
	}

	/**
	 * Checks user permissions by given array and ref_id.
	 *
	 * @global  Rbacsystem	$rbacsystem
	 * @param   mixed	$permissions
	 * @param   integer	$ref_id 
	 */
	public static function checkUserPermissions($permissions, $ref_id, $send_info = true)
	{
	    global $rbacsystem, $lng;
	    
	    if( !is_array($permissions) )
	    {
		$permissions = array( $permissions );
	    }

	    foreach( $permissions as $permission )
	    {
		if( !$rbacsystem->checkAccess( $permission, $ref_id ) )
		{
			if ($send_info) {
				ilUtil::sendFailure( $lng->txt("permission_denied"), true );
			}
			return false;
		}
	    }

	    return true;
	}

	/**
	 * Checks user permissions in question for a given user id in relation
	 * to a given ref_id.
	 *
	 * @global ilRbacSystem $rbacsystem
	 * @global ilLanguage $lng
	 * @param integer $usr_id
	 * @param mixed $permissions
	 * @param integer $ref_id
	 * @return boolean 
	 */
	public static function checkPermissionsOfUser($usr_id, $permissions, $ref_id)
	{
	    global $rbacsystem, $lng;

	    if( !is_array($permissions) )
	    {
		$permissions = array( $permissions );
	    }

	    foreach( $permissions as $permission )
	    {
		if( !$rbacsystem->checkAccessOfUser($usr_id, $permission, $ref_id ) )
		{
		   return false;
		}
	    }

	    return true;
	}

	public function getDescription()
	{
	    if (!$this->object)
	    {
		$this->object = ilObjectFactory::getInstanceByObjId($this->getSetting('object_id'));
	    }

	    return $this->object->getDescription();
	}

	/**
	 * Returns setting from $this->settings array by given name.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function getSetting($name)
	{
	    return $this->settings[$name];
	}

	/**
	 * Sets given name and value as setting into $this->settings array.
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function setSetting($name, $value)
	{
		$this->settings[$name] = $value;
	}

	/**
	 * Saves settings using $this->settings
	 */
	public function save()
	{
	    $this->saveSettings( $this->settings );
	}

	/**
	 * Inserts entry into historyTable.
	 *
	 * @todo $recipient, $publicMessage speichern
	 *
	 * @global ilDBMySQL $ilDB
	 * @param string $message
	 * @param string $recipient
	 * @param boolean $publicMessage
	 */
	public function addHistoryEntry($message, $recipient = null, $publicMessage = true)
	{
	    global $ilDB;

	    $subRoom = 0;
	    if (is_array($message)) {
		$subRoom = (int) $message['sub'];
	    }
	    else if (is_object($message)) {
		$subRoom = (int) $message->sub;
	    }

	    $ilDB->insert(
		self::$historyTable,
		array(
		    'room_id'	=> array('integer', $this->roomId),
		    'sub_room'	=> array('integer', $subRoom),
		    'message'	=> array('text', json_encode($message)),
		    'timestamp'	=> array('integer', time()),
		)
	    );
	}

	/**
	 * Connects user by inserting userdata into userTable.
	 *
	 * Checks if user is already connected by using the given $user object
	 * for selecting the userId from userTable. If no entry is found, matching
	 * userId and roomId, the userdata is inserted into the userTable to
	 * connect the user.
	 *
	 * @global ilDBMySQL $ilDB
	 * @param ilChatroomUser $user
	 * @return boolean
	 */
	public function connectUser(ilChatroomUser $user)
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		$userdata = array(
			'login' => $user->getUsername(),
			'id'    => $user->getUserId()
		);

		$query = 'SELECT user_id FROM ' . self::$userTable . ' WHERE room_id = %s AND user_id = %s';
		$types  = array('integer', 'integer');
		$values = array($this->roomId, $user->getUserId());

		if(!$ilDB->fetchAssoc($ilDB->queryF($query, $types, $values)))
		{
			// Notice: Using replace instead of insert looks strange, because we actually know whether the selected data exists or not
			// But we occasionally found some duplicate key errors although the data set should not exist when the following code is reached
			$ilDB->replace(
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
	 *
	 * Returns an array of user objects containing all users having an entry
	 * in userTable, matching the roomId.
	 *
	 * @global ilDBMySQL $ilDB
	 * @return array
	 */
	public function getConnectedUsers()
	{
		global $ilDB;

		$query	= 'SELECT userdata FROM ' . self::$userTable . ' WHERE room_id = %s';
		$types	= array('integer');
		$values = array($this->roomId);
		$rset	= $ilDB->queryF( $query, $types, $values );
		$users	= array();

		while( $row = $ilDB->fetchAssoc( $rset ) )
		{
			$users[] = json_decode( $row['userdata'] );
		}

		return $users;
	}

	/**
	 * Creates userId array by given $user object and calls disconnectUsers
	 * method.
	 *
	 * @param int $user_id
	 */
	public function disconnectUser($user_id)
	{
		$this->disconnectUsers( array($user_id) );
	}

	/**
	 * Disconnects users by deleting userdata from userTable using given userId array.
	 *
	 * Deletes entrys from userTable, matching roomId and userId if existing and
	 * inserts userdata and disconnection time into sessionTable.
	 *
	 * @global ilDB $ilDB
	 * @param array $userIds
	 */
	public function disconnectUsers(array $userIds)
	{
		global $ilDB;

		$query = 'SELECT * FROM ' . self::$userTable . ' WHERE room_id = %s AND ' .
		$ilDB->in( 'user_id', $userIds, false, 'integer' );

		$types	= array('integer');
		$values = array($this->roomId);
		$rset	= $ilDB->queryF( $query, $types, $values );

		if( $row = $ilDB->fetchAssoc( $rset ) )
		{
			$query = 'SELECT proom_id FROM ' . self::$privateRoomsTable . ' WHERE parent_id = %s';
			$rset_prooms = $ilDB->queryF($query, array('integer'), array($this->roomId));

			$prooms = array();

			while($row_prooms  = $ilDB->fetchAssoc($rset_prooms)) {
				$prooms[] = $row_prooms['proom_id'];
			}

			if (true || $this->getSetting( 'enable_history' )) {
				$query = 'UPDATE ' . self::$privateSessionsTable . ' SET disconnected = %s WHERE ' . $ilDB->in('user_id', $userIds, false, 'integer') . ' AND ' . $ilDB->in('proom_id', $prooms, false, 'integer');
				$ilDB->manipulateF($query, array('integer'), array(time()));
			}
			else {
				$query = 'DELETE FROM ' . self::$privateSessionsTable . ' WHERE ' . $ilDB->in('user_id', $userIds, false, 'integer') . ' AND ' . $ilDB->in('proom_id', $prooms, false, 'integer');
				$ilDB->manipulate($query);
			}

			$query = 'DELETE FROM ' . self::$userTable . ' WHERE room_id = %s AND ' .
			$ilDB->in( 'user_id', $userIds, false, 'integer' );
				
			$types	= array('integer');
			$values = array($this->roomId);
			$ilDB->manipulateF( $query, $types, $values );

			do
			{
				if ($this->getSetting( 'enable_history' )) {
					$ilDB->insert(
					self::$sessionTable,
					array(
								'room_id'		=> array('integer', $this->roomId),
								'user_id'		=> array('integer', $row['user_id']),
								'userdata'		=> array('text', $row['userdata']),
								'connected'		=> array('integer', $row['connected']),
								'disconnected'	=> array('integer', time()),
					)
					);
				}
			}
			while( $row = $ilDB->fetchAssoc( $rset ) );
		}

	}

	private function phpTypeToMDBType($type) {
		switch($type) {
			case 'string':
				return 'text';
			default:
				return $type;
		}

	}

	/**
	 * Saves settings into settingsTable using given settings array.
	 *
	 * @global ilDBMySQL $ilDB
	 * @param array $settings
	 */
	public function saveSettings(array $settings)
	{
		global $ilDB;

		$localSettings = array();

		foreach( $this->availableSettings as $setting => $type )
		{
			if( isset( $settings[$setting] ) ) {
				if ($type == 'boolean') {
				    $settings[$setting] = (boolean)$settings[$setting];
				}
				$localSettings[$setting] = array($this->phpTypeToMDBType($type), $settings[$setting]);
			}
		}

		if (!$localSettings['room_type'][1]) {
			$localSettings['room_type'][1] = 'repository';
		}

		if( $this->roomId )
		{
			$ilDB->update(
			    self::$settingsTable,
			    $localSettings,
			    array( 'room_id' => array('integer', $this->roomId) )
			);
		}
		else
		{
			$this->roomId = $ilDB->nextId( self::$settingsTable );

			$localSettings['room_id'] = array(
			$this->availableSettings['room_id'], $this->roomId
			);

			$ilDB->insert( self::$settingsTable, $localSettings );
		}
	}

	/**
	 * Returns $this->settings array.
	 *
	 * @return array
	 */
	public function getSettings()
	{
		return $this->settings;
	}

	/**
	 * Returns ilChatroom object by given $object_id.
	 *
	 * @global ilDBMySQL $ilDB
	 * @param integer $object_id
	 * @return ilChatroom
	 */
	public static function byObjectId($object_id)
	{
		global $ilDB;
		$query	= 'SELECT * FROM ' . self::$settingsTable . ' WHERE object_id = %s';
		$types	= array('integer');
		$values = array($object_id);
		$rset	= $ilDB->queryF( $query, $types, $values );

		if( $row = $ilDB->fetchAssoc( $rset ) )
		{
			$room = new self();
			$room->initialize( $row );
			return $room;
		}
	}

	/**
	 * Returns ilChatroom by given $room_id
	 *
	 * @global ilDBMySQL $ilDB
	 * @param integer $room_id
	 * @return ilChatroom
	 */
	public static function byRoomId($room_id, $initObject = false)
	{
		global $ilDB;

		$query = 'SELECT * FROM ' . self::$settingsTable . ' WHERE room_id = %s';

		$types = array('integer');
		$values = array($room_id);

		$rset = $ilDB->queryF( $query, $types, $values );

		if( $row = $ilDB->fetchAssoc( $rset ) )
		{
			$room = new self();
			$room->initialize( $row );

			if ($initObject) {
			    $room->object = ilObjectFactory::getInstanceByObjId($row['object_id']);
			}

			return $room;
		}
	}

	/**
	 * Sets $this->roomId by given array $rowdata and calls setSetting method
	 * foreach available setting in $this->availableSettings.
	 *
	 * @param array $rowdata
	 */
	public function initialize(array $rowdata)
	{
		$this->roomId = $rowdata['room_id'];

		foreach( $this->availableSettings as $setting => $type )
		{
			if( isset($rowdata[$setting]) )
			{
				settype($rowdata[$setting], $this->availableSettings[$setting]);
				$this->setSetting( $setting, $rowdata[$setting] );
			}
		}
	}

	/**
	 * Returns roomID from $this->roomId
	 *
	 * @return integer
	 */
	public function getRoomId()
	{
		return $this->roomId;
	}

	/**
	 * Returns true if entry exists in userTable matching given $chat_userid
	 * and $this->roomId.
	 *
	 * @global ilDBMySQL $ilDB
	 * @param integer $chat_userid
	 * @return boolean
	 */
	public function isSubscribed($chat_userid)
	{
		global $ilDB;

		$query = 'SELECT count(user_id) as cnt FROM ' . self::$userTable .
				 ' WHERE room_id = %s AND user_id = %s';

		$types	= array('integer', 'integer');
		$values = array($this->roomId, $chat_userid);
		$rset	= $ilDB->queryF( $query, $types, $values );

		if( $rset && ($row = $ilDB->fetchAssoc( $rset )) && $row['cnt'] == 1 )
		return true;

		return false;
	}

	public function isAllowedToEnterPrivateRoom($chat_userid, $proom_id) {
		//echo call_user_func_array('sprintf', array_merge(array($query), $values));
		global $ilDB;

		$query = 'SELECT count(user_id) cnt FROM ' . self::$privateRoomsAccessTable .
				 ' WHERE proom_id = %s AND user_id = %s';

		$types	= array('integer', 'integer');
		$values = array($proom_id, $chat_userid);
		$rset	= $ilDB->queryF( $query, $types, $values );

		if( $rset && ($row = $ilDB->fetchAssoc( $rset )) && $row['cnt'] == 1 )
		return true;

		$query = 'SELECT count(*) cnt FROM ' . self::$privateRoomsTable .
				 ' WHERE proom_id = %s AND owner = %s';

		$types	= array('integer', 'integer');
		$values = array($proom_id, $chat_userid);
		$rset	= $ilDB->queryF( $query, $types, $values );

		if( $rset && ($row = $ilDB->fetchAssoc( $rset )) && $row['cnt'] == 1 )
		return true;

		return false;
	}

	/**
	 * Deletes all entrys from userTable.
	 *
	 * @global ilDBMySQL $ilDB
	 */
	public function disconnectAllUsersFromAllRooms()
	{
		global $ilDB;

		$ilDB->manipulate( 'DELETE FROM ' . self::$userTable );
		$ilDB->manipulate( 'UPDATE ' . self::$privateRoomsTable . ' SET closed = ' . $ilDB->quote( time() ,'integer') . ' WHERE closed = 0 OR closed IS NULL');
		$ilDB->manipulate( 'UPDATE ' . self::$privateSessionsTable . ' SET disconnected = ' . $ilDB->quote( time() ,'integer') . ' WHERE disconnected = 0 OR disconnected IS NULL');
		/**
		 * @todo nicht nur löschen, auch in Session Tabelle nachpflegen
		 */
	}

	/**
	 * Returns array containing history data selected from historyTable by given
	 * ilDateTime, $restricted_session_userid and matching roomId.
	 *
	 * @global ilDBMySQL $ilDB
	 * @param ilDateTime $from
	 * @param ilDateTime $to
	 * @param integer $restricted_session_userid
	 * @return array
	 */
	public function getHistory(ilDateTime $from = null, ilDateTime $to = null, $restricted_session_userid = null, $proom_id = 0)
	{
		global $ilDB, $ilUser;

		$join = '';

		if ($proom_id) {
			$join .= ' INNER JOIN ' . self::$privateSessionsTable .
				' pSessionTable ON pSessionTable.user_id = ' .
				$ilDB->quote( $restricted_session_userid, 'integer' ) .
				' AND historyTable.sub_room = pSessionTable.proom_id AND timestamp >= pSessionTable.connected AND timestamp <= pSessionTable.disconnected ';
		}
		
		$query = 'SELECT historyTable.* FROM ' . self::$historyTable . ' historyTable ' .
			$join . ' WHERE historyTable.room_id = ' . $this->getRoomId();

		$filter = array();

		if( $from != null )
		{
			$filter[] = 'timestamp >= ' . $ilDB->quote( $from->getUnixTime(), 'integer' );
		}

		if( $to != null )
		{
			$filter[] = 'timestamp <= ' . $ilDB->quote( $to->getUnixTime(), 'integer' );
		}

		if( $filter )
		$query .= ' AND ' . join( ' AND ', $filter );
		$query .= ' ORDER BY timestamp ASC';
		
		$rset	= $ilDB->query( $query );
		$result = array();

		while( $row = $ilDB->fetchAssoc( $rset ) )
		{
			$row['message'] = json_decode( $row['message'] );
			$row['message']->timestamp = $row['timestamp'];
			if ($row['message']->public !== null && !$row['message']->public && !in_array($ilUser->getId(), explode(',', $row['recipients']))) {
			    continue;
			}
			
			$result[] = $row;
		}
		return $result;
	}

	public function getPrivateRoomSessions(ilDateTime $from = null, ilDateTime $to = null, $user_id = 0, $room_id=0 ) {
		global $ilDB;
		
		$query = 'SELECT proom_id, title FROM ' . self::$privateRoomsTable . ' WHERE proom_id IN (
			SELECT proom_id FROM '.self::$privateSessionsTable.' WHERE connected >= %s AND disconnected <= %s AND user_id = %s

		) AND parent_id = %s';
		
		$rset = $ilDB->queryF($query, array('integer','integer','integer','integer'), array($from->getUnixTime(), $to->getUnixTime(), $user_id, $room_id));
		$result = array();
		while( $row = $ilDB->fetchAssoc( $rset ) )
		{
			$result[] = $row;
		}		
		return $result;
	}

	/**
	 * Saves information about file uploads in DB.
	 *
	 * @global ilDBMySQL $ilDB
	 * @param integer $user_id
	 * @param string $filename
	 * @param string $type
	 */
	public function saveFileUploadToDb($user_id, $filename, $type)
	{
		global $ilDB;

		$upload_id	= $ilDB->nextId( self::$uploadTable );

		$ilDB->insert(
		self::$uploadTable,
		array(
					'upload_id'	=> array('integer', $upload_id),
					'room_id'	=> array('integer', $this->roomId),
					'user_id'	=> array('integer', $user_id),
					'filename'	=> array('text', $filename),
					'filetype'	=> array('text', $type),
					'timestamp'	=> array('integer', time())
		)
		);
	}

	/**
	 * Inserts user into banTable, using given $user_id
	 *
	 * @global ilDBMySQL $ilDB
	 * @param integer $user_id
	 * @param string $comment
	 */
	public function banUser($user_id, $comment = '')
	{
		global $ilDB;

		$ilDB->insert(
		self::$banTable,
		array(
					'room_id'	=> array('integer', $this->roomId),
					'user_id'	=> array('integer', $user_id),
					'timestamp' => array('integer', time()),
					'remark'	=> array('text', $comment),
		)
		);
	}

	/**
	 * Deletes entry from banTable matching roomId and given $user_id and
	 * returns true if sucessful.
	 *
	 * @global ilDBMySQL $ilDB
	 * @param mixed $user_id
	 * @return boolean
	 */
	public function unbanUser($user_id)
	{
		global $ilDB;

		if( !is_array( $user_id ) )
		$user_id = array($user_id);

		$query = 'DELETE FROM ' . self::$banTable . ' WHERE room_id = %s AND ' .
		$ilDB->in( 'user_id', $user_id, false, 'integer' );

		$types	= array('integer');
		$values = array($this->getRoomId());

		return $ilDB->manipulateF( $query, $types, $values );
	}

	/**
	 * Returns true if there's an entry in banTable matching roomId and given
	 * $user_id
	 *
	 * @global ilDBMySQL $ilDB
	 * @param integer $user_id
	 * @return boolean
	 */
	public function isUserBanned($user_id)
	{
		global $ilDB;

		$query = 'SELECT count(user_id) cnt FROM ' . self::$banTable .
				' WHERE user_id = %s AND room_id = %s';

		$types	= array('integer', 'integer');
		$values = array($user_id, $this->getRoomId());

		$rset = $ilDB->queryF( $query, $types, $values );

		if( $rset && ($row = $ilDB->fetchAssoc( $rset )) && $row['cnt'] )
		    return true;

		return false;
	}

	/**
	 * Returns an multidimensional array containing userdata from users
	 * having an entry in banTable with matching roomId.
	 *
	 * @global ilDBMySQL $ilDB
	 * @return array
	 */
	public function getBannedUsers()
	{
		global $ilDB;

		$query	= 'SELECT * FROM ' . self::$banTable . ' WHERE room_id = %s ';
		$types	= array('integer');
		$values = array($this->getRoomId());
		$rset	= $ilDB->queryF( $query, $types, $values );
		$result = array();

		if( $rset )
		{
			while( $row = $ilDB->fetchAssoc( $rset ) )
			{
				if( $row['user_id'] > 0 )
				{
					$user = new ilObjUser( $row['user_id'] );
					$userdata = array(
						'user_id'	=> $user->getId(),
						'firstname' => $user->getFirstname(),
						'lastname'	=> $user->getLastname(),
						'login'		=> $user->getLogin(),
						'remark'	=> $row['remark']
					);

					$result[] = $userdata;
				}
				else
				{
					//@todo anonymous user
				}
			}
		}

		return $result;
	}

	/**
	 * Returns last session from user.
	 *
	 * Returns row from sessionTable where user_id matches userId from given
	 * $user object.
	 *
	 * @global ilDBMySQL $ilDB
	 * @param ilChatroomUser $user
	 * @return array
	 */
	public function getLastSession(ilChatroomUser $user)
	{
		global $ilDB;

		$query = 'SELECT * FROM ' . self::$sessionTable . ' WHERE user_id = ' .
		$ilDB->quote( $user->getUserId(), 'integer' ) .
				 ' ORDER BY connected DESC';

		$ilDB->setLimit( 1 );
		$rset = $ilDB->query( $query );

		if( $row = $ilDB->fetchAssoc( $rset ) )
		{
			return $row;
		}
	}

	/**
	 * Returns all session from user
	 *
	 * Returns all from sessionTable where user_id matches userId from given
	 * $user object.
	 *
	 * @global ilDBMySQL $ilDB
	 * @param ilChatroomUser $user
	 * @return array
	 */
	public function getSessions(ilChatroomUser $user)
	{
		global $ilDB;

		$query = 'SELECT * FROM ' . self::$sessionTable 
		. ' WHERE room_id = '.
		$ilDB->quote( $this->getRoomId(), 'integer' ) .
				 ' ORDER BY connected DESC';

		$rset = $ilDB->query( $query );

		$result = array();

		while( $row = $ilDB->fetchAssoc( $rset ) )
		{
			$result[] = $row;
		}

		return $result;
	}

	public function addPrivateRoom($title, ilChatroomUser $owner, $settings)
	{
		global $ilDB;

		$nextId = $ilDB->nextId('chatroom_prooms');

		$ilDB->insert(
		self::$privateRoomsTable,
		array(
					'proom_id'	=> array('integer', $nextId),
					'parent_id'	=> array('integer', $this->roomId),
					'title'	=> array('text', $title),
					'owner'	=> array('integer', $owner->getUserId()),
					'created' => array('integer', time()),
					'is_public' => array('integer', $settings['public']),
		)
		);

		return $nextId;
	}

	public function closePrivateRoom($id)
	{
		global $ilDB;

		$ilDB->manipulateF(
			'UPDATE ' . self::$privateRoomsTable . ' SET closed = %s WHERE proom_id = %s',
		array('integer', 'integer'),
		array(time(), $id)
		);
	}

	public function isOwnerOfPrivateRoom($user_id, $proom_id) {
		global $ilDB;

		$query = 'SELECT proom_id FROM ' . self::$privateRoomsTable . ' WHERE proom_id = %s AND owner = %s';
		$types = array('integer', 'integer');
		$values = array($proom_id, $user_id);

		$rset = $ilDB->queryF($query, $types, $values);

		if ($rset && $ilDB->fetchAssoc($rset)) {
			return true;
		}
		return false;
	}

	/**
	 * @param int $user_id
	 * @param int $proom_id
	 */
	public function inviteUserToPrivateRoom($user_id, $proom_id)
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		$query  = 'DELETE FROM ' . self::$privateRoomsAccessTable . ' WHERE user_id = %s AND proom_id = %s';
		$types  = array('integer', 'integer');
		$values = array($user_id, $proom_id);

		$ilDB->manipulateF($query, $types, $values);

		$ilDB->insert(self::$privateRoomsAccessTable, array(
			'user_id'  => array('integer', $user_id),
			'proom_id' => array('integer', $proom_id)
		));
	}

	/**
	 *
	 * @global ilCtrl $ilCtrl
	 * @param <type> $gui
	 * @param <type> $scope_id
	 */
	public function getChatURL($gui, $scope_id = 0)
	{
		include_once 'Services/Link/classes/class.ilLink.php';

		$url = '';

		if(is_object($gui))
		{
			if($scope_id)
			{
				$url = ilLink::_getStaticLink($gui->object->getRefId(), $gui->object->getType(), true, '_'.$scope_id);
			}
			else
			{
				$url = ilLink::_getStaticLink($gui->object->getRefId(), $gui->object->getType());
			}
		}

		return $url;
	}

	/**
	 * @param        $gui
	 * @param mixed $sender (can be an instance of ilChatroomUser or an user id of an ilObjUser instance
	 * @param int $recipient_id
	 * @param int    $subScope
	 * @param string $invitationLink
	 * @throws InvalidArgumentException
	 */
	public function sendInvitationNotification($gui, $sender, $recipient_id, $subScope = 0, $invitationLink = '')
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

		if($gui && !$invitationLink)
		{
			$invitationLink = $this->getChatURL($gui, $subScope);
		}

		if($recipient_id > 0 && !in_array(ANONYMOUS_USER_ID, array($recipient_id)))
		{
			if(is_numeric($sender) && $sender > 0)
			{
				$sender_id = $sender;
				/**
				 * @var $usr ilObjUser
				 */
				$usr = ilObjectFactory::getInstanceByObjId($sender);
				$public_name = $usr->getPublicName();
			}
			else if($sender instanceof ilChatroomUser)
			{
				if($sender->getUserId() > 0)
				{
					$sender_id = $sender->getUserId();
				}
				else
				{
					$sender_id = ANONYMOUS_USER_ID;
				}
				$public_name = $sender->getUsername();
			}
			else
			{
				throw new InvalidArgumentException('$sender must be an instance of ilChatroomUser or an id of an ilObjUser instance');
			}

			$lng->loadLanguageModule('mail');

			$recipient = ilObjectFactory::getInstanceByObjId($recipient_id);
			$bodyParams = array(
				'link'         => $invitationLink,
				'inviter_name' => $public_name,
				'room_name'    => $this->getTitle(),
				'salutation'   => $lng->txt('mail_salutation_' . $recipient->getGender()) . ' ' . $recipient->getFullname()
			);

			if($subScope)
			{
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
			$notification->setValidForSeconds(0);

			$notification->setHandlerParam('mail.sender', $sender_id);

			$notification->notifyByUsers(array($recipient_id));
		}
	}

	public function inviteUserToPrivateRoomByLogin($login, $proom_id) {
		global $ilDB;
		$user_id = ilObjUser::_lookupId($login);
		$this->inviteUserToPrivateRoom($user_id, $proom_id);
	}

	public static function lookupPrivateRoomTitle($proom_id) {
		global $ilDB;

		$query = 'SELECT title FROM ' . self::$privateRoomsTable . ' WHERE proom_id = %s';
		$types = array('integer');
		$values = array($proom_id);

		$rset = $ilDB->queryF($query, $types, $values);

		if ($row = $ilDB->fetchAssoc($rset)) {
			return $row['title'];
		}

		return 'unkown';
	}

	public function getActivePrivateRooms($userid)
	{
		global $ilDB;

		$query	= '
			SELECT roomtable.title, roomtable.proom_id, accesstable.user_id id, roomtable.owner owner FROM ' . self::$privateRoomsTable . ' roomtable
			LEFT JOIN '.self::$privateRoomsAccessTable.' accesstable ON roomtable.proom_id = accesstable.proom_id AND accesstable.user_id = %s
			WHERE parent_id = %s AND (closed = 0 OR closed IS NULL) AND (accesstable.user_id IS NOT NULL OR roomtable.owner = %s)';
		$types	= array('integer', 'integer', 'integer');
		$values = array($userid, $this->roomId, $userid);
		$rset	= $ilDB->queryF( $query, $types, $values );
		$rooms = array();

		while( $row = $ilDB->fetchAssoc( $rset ) )
		{
			$row['active_users'] = $this->listUsersInPrivateRoom($row['id']);
			$row['owner'] = $row['owner'];
			$rooms[$row['proom_id']] = $row;
		}

		return $rooms;
	}

	public function listUsersInPrivateRoom($private_room_id) {
		global $ilDB;

		$query	= 'SELECT user_id FROM ' . self::$privateSessionsTable . ' WHERE proom_id = %s AND disconnected = 0 OR disconnected IS NULL';
		$types	= array('integer');
		$values = array($private_room_id);
		$rset	= $ilDB->queryF( $query, $types, $values );

		$users = array();

		while ($row = $ilDB->fetchAssoc($rset)) {
			$users[] = $row['user_id'];
		}

		return $users;
	}

	public function userIsInPrivateRoom($room_id, $user_id)
	{
		global $ilDB;

		$query	= 'SELECT proom_id id FROM ' . self::$privateSessionsTable . ' WHERE user_id = %s AND proom_id = %s AND disconnected = 0 OR disconnected IS NULL';
		$types	= array('integer', 'integer');
		$values = array($user_id, $room_id);
		$rset	= $ilDB->queryF( $query, $types, $values );
		if ($ilDB->fetchAssoc($rset))
		return true;
		return false;
	}

	public function subscribeUserToPrivateRoom($room_id, $user_id)
	{
		global $ilDB;

		if (!$this->userIsInPrivateRoom($room_id, $user_id)) {
			$ilDB->insert(
			self::$privateSessionsTable,
			array(
                            'proom_id' => array('integer', $room_id),
                            'user_id' => array('integer', $user_id),
                            'connected' => array('integer', time()),
                            'disconnected' => array('integer', 0),
			)
			);
		}
	}

	/**
	 *
	 * @global ilDB $ilDB
	 * @param integer $room_id
	 * @param integer $user_id
	 */
	public function unsubscribeUserFromPrivateRoom($room_id, $user_id)
	{
		global $ilDB;

		$ilDB->update(
		self::$privateSessionsTable,
		array(
			'disconnected' => array('integer', time())
		),
		array(
			'proom_id' => array('integer', $room_id),
			'user_id' => array('integer', $user_id),
		)
		);
	}

	public function countActiveUsers() {
		global $ilDB;

		$query = 'SELECT count(user_id) as cnt FROM ' . self::$userTable .
				 ' WHERE room_id = %s';

		$types	= array('integer');
		$values = array($this->roomId);
		$rset	= $ilDB->queryF( $query, $types, $values );

		if( $rset && ($row = $ilDB->fetchAssoc( $rset )) && $row['cnt'] == 1 )
		return $row['cnt'];

		return 0;
	}

	public function getUniquePrivateRoomTitle($title) {
		global $ilDB;

		$query = 'SELECT title FROM ' . self::$privateRoomsTable . ' WHERE parent_id = %s and closed = 0';
		$rset = $ilDB->queryF($query, array('integer'), array($this->roomId));

		$titles = array();

		while($row = $ilDB->fetchAssoc($rset)) {
			$titles[] = $row['title'];
		}

		$suffix = '';
		$i = 0;
		do {
			if(!in_array($title . $suffix, $titles)) {
				$title .= $suffix;
				break;
			}

			++$i;

			$suffix = ' (' . $i . ')';
		} while(true);

		return $title;
	}

	public static function findDeletablePrivateRooms() {
		global $ilDB;

		$query = 'SELECT private_rooms.proom_id id, MIN(disconnected) min_disconnected, MAX(disconnected) max_disconnected FROM ' . self::$privateSessionsTable . ' private_sessions INNER JOIN '.self::$privateRoomsTable.' private_rooms ON private_sessions.proom_id = private_rooms.proom_id WHERE closed = 0 GROUP BY private_rooms.proom_id HAVING min_disconnected > 0 AND max_disconnected < %s';
		$rset = $ilDB->queryF(
		$query,
		array('integer'),
		array(time() + 60 * 5)
		);

		$rooms = array();

		while ($row = $ilDB->fetchAssoc($rset)) {
			$rooms[] = $row['id'];
		}

		$query = 'SELECT DISTINCT proom_id, room_id, object_id FROM ' . self::$privateRoomsTable
		. ' INNER JOIN ' . self::$settingsTable . ' ON parent_id = room_id '
		. ' WHERE ' . $ilDB->in('proom_id', $rooms, false, 'integer');

		$rset = $ilDB->query($query);
		$rooms = array();
		while($row = $ilDB->fetchAssoc($rset)) {
			$rooms[] = array(
                    'proom_id' => $row['proom_id'],
                    'room_id' => $row['room_id'],
                    'object_id' => $row['object_id']
			);
		}

		return $rooms;
	}

   /**
    *  Fetches and returns the object ids of all rooms accessible
    *  by the user with $user_id
    * 
    * @global ilDBMySQL $ilDB
    * @param integer $user_id
    * @return array
    */
   public function getAllRooms($user_id)
   {
	   /**
		* @var $ilDB ilDB
		*/
	   global $ilDB;

	   $query = "
       SELECT      room_id, od.title
       FROM        object_data od

       INNER JOIN  " . self::$settingsTable . "
           ON      object_id = od.obj_id

       INNER JOIN  " . self::$privateRoomsTable . " prt
           ON      prt.owner = %s

       WHERE       od.type = 'chtr'
       ";

	   $types  = array('integer');
	   $values = array($user_id);

	   $res = $ilDB->queryF($query, $types, $values);

	   $rooms = array();

	   while($row = $ilDB->fetchAssoc($res))
	   {
		   $room_id         = $row['room_id'];
		   $rooms[$room_id] = $row['title'];
	   }

	   return $rooms;
   }



   public function getPrivateSubRooms($parent_room, $user_id)
   {
       global $ilDB;

       $query = "
       SELECT      proom_id, parent_id
       FROM        chatroom_prooms
       WHERE       parent_id = %s
       AND     owner = %s
       AND     closed = 0
       ";

       $types  = array( 'integer', 'integer' );
       $values = array( $parent_room, $user_id );

       $res = $ilDB->queryF( $query, $types, $values );

       $priv_rooms = array();

       while( $row = $ilDB->fetchAssoc( $res ) )
       {
       $proom_id       = $row['proom_id'];
       $priv_rooms[$proom_id]  = $row['parent_id'];
       }
       
       return $priv_rooms;
   }   

   /**
    * Returns ref_id of given room_id
    *
    * @global ilDBMySQL $ilDB
    * @param integer $room_id
    * @return integer
    */
   public function getRefIdByRoomId($room_id)
   {
       global $ilDB;

       $query = "
       SELECT      objr.ref_id
       FROM        object_reference    objr

       INNER JOIN  chatroom_settings   cs
           ON      cs.object_id = objr.obj_id

       INNER JOIN  object_data     od
           ON      od.obj_id = cs.object_id

       WHERE       cs.room_id = %s
       ";

       $types  = array( 'integer' );
       $values = array( $room_id );

       $res = $ilDB->queryF( $query, $types, $values );

       $row = $ilDB->fetchAssoc( $res );

       return $row['ref_id'];
    }

public function getLastMessages($number, $chatuser = null) {
	global $ilDB;
	
	// There is currently no way to check if a message is private or not
	// by sql. So we fetch twice as much as we need and hope that there
	// are not more than $number private messages.
	$ilDB->setLimit($number * 2);
	$rset = $ilDB->queryF('SELECT * FROM ' . self::$historyTable . ' WHERE room_id = %s AND sub_room = 0 ORDER BY timestamp DESC', array('integer'), array($this->roomId));

	$result_count = 0;
	$results = array();
	while(($row = $ilDB->fetchAssoc($rset)) && $result_count < $number) {
	    $tmp = json_decode($row['message']);
		if ($chatuser !== null && $tmp->public == 0 && $tmp->recipients) {
			if (in_array($chatuser->getUserId(), explode(',',$tmp->recipients))) {
				$results[] = $tmp;
				++$result_count;
			}
		}
		else if ($tmp->public == 1) {
			$results[] = $tmp;
			++$result_count;
		}
		
	}
	return $results;
    }

	public function getLastMessagesForChatViewer($number, $chatuser = null)
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		$ilDB->setLimit($number);
		$rset = $ilDB->query(
			'SELECT *
			FROM ' . self::$historyTable . '
			WHERE room_id = '.$ilDB->quote($this->roomId, 'integer').'
			AND sub_room = 0
			AND (
					(' . $ilDB->like('message', 'text', '%"type":"message"%') . ' AND ' . $ilDB->like('message', 'text', '%"public":1%') . ' AND ' . $ilDB->like('message', 'text', '%"recipients":null%') . ') 
					OR 
					' . $ilDB->like('message', 'text', '%"type":"%connected"%') . ')
			ORDER BY timestamp DESC'
		);

		$results = array();
		while(($row = $ilDB->fetchAssoc($rset)))
		{
			$tmp       = json_decode($row['message']);
			if($tmp->type != 'message' && $row['timestamp'] && !is_numeric($tmp->timestamp))
			{
				$tmp->timestamp = $row['timestamp'] * 1000;
			}
			$results[] = $tmp;
		}
		return $results;
	}
    
    public function clearMessages($sub_room) {
	global $ilDB;
	
	$ilDB->queryF(
		'DELETE FROM ' . self::$historyTable . ' WHERE room_id = %s AND sub_room = %s',
		array('integer', 'integer'),
		array($this->roomId, (int)$sub_room)
	);
	
	if ($sub_room) {
	    $ilDB->queryF(
		    'DELETE FROM ' . self::$sessionTable . ' WHERE proom_id = %s AND disconnected < %s',
		    array('integer', 'integer'),
		    array($sub_room, time())
	    );
	}
	else {
	    $ilDB->queryF(
		    'DELETE FROM ' . self::$sessionTable . ' WHERE room_id = %s AND disconnected < %s',
		    array('integer', 'integer'),
		    array($this->roomId, time())
	    );
	}
    }
	
	public static function getUntrashedChatReferences($filter = array())
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;
		
		// Check for parent because of an invalid parent node for the old public chat (thx @ jposselt ;-)).
		// We cannot find this old public chat and clean this automatically
		$query  = '
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

		if(isset($filter['last_activity']))
		{
			$threshold = $ilDB->quote($filter['last_activity'], 'integer');
			$query    .= "
				INNER JOIN chatroom_settings ON chatroom_settings.object_id = od.obj_id
				INNER JOIN chatroom_history ON chatroom_history.room_id = chatroom_settings.room_id AND chatroom_history.timestamp > $threshold
			";
		}

		$query .= '
			WHERE od.type = %s AND t.tree > 0 AND ore.deleted IS NULL
			GROUP BY od.obj_id, od.title, ore.ref_id, od.type, odp.title
			ORDER BY od.title
		';
		$res = $ilDB->queryF($query, array('text'), array('chtr'));
		
		$chats = array();
		while($row = $ilDB->fetchAssoc($res))
		{
			$chats[] = $row;
		}
		
		return $chats;
	}
}

?>
