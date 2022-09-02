<?php

declare(strict_types=1);

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

use ILIAS\Notifications\Model\ilNotificationConfig;
use ILIAS\Notifications\Model\ilNotificationLink;
use ILIAS\Notifications\Model\ilNotificationParameter;

/**
 * Class ilChatroom
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroom
{
    private static string $settingsTable = 'chatroom_settings';
    private static string $historyTable = 'chatroom_history';
    private static string $userTable = 'chatroom_users';
    private static string $sessionTable = 'chatroom_sessions';
    private static string $banTable = 'chatroom_bans';
    private static string $privateRoomsTable = 'chatroom_prooms';
    private static string $privateSessionsTable = 'chatroom_psessions';
    private static string $uploadTable = 'chatroom_uploads';
    private static string $privateRoomsAccessTable = 'chatroom_proomaccess';
    private array $settings = [];
    /**
     * Each value of this array describes a setting with the internal type.
     * The type must be a type wich can be set by the function settype
     * @see http://php.net/manual/de/function.settype.php
     * @var array<string, string>
     */
    private array $availableSettings = [
        'object_id' => 'integer',
        'online_status' => 'boolean',
        'allow_anonymous' => 'boolean',
        'allow_custom_usernames' => 'boolean',
        'enable_history' => 'boolean',
        'restrict_history' => 'boolean',
        'autogen_usernames' => 'string',
        'room_type' => 'string',
        'allow_private_rooms' => 'boolean',
        'display_past_msgs' => 'integer',
        'private_rooms_enabled' => 'boolean'
    ];
    private int $roomId = 0;
    private ?ilObjChatroom $object = null;

    /**
     * Checks user permissions by given array and ref_id.
     * @param string|string[] $permissions
     * @param int $ref_id
     * @param bool $send_info
     * @return bool
     */
    public static function checkUserPermissions($permissions, int $ref_id, bool $send_info = true): bool
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        if (!is_array($permissions)) {
            $permissions = [$permissions];
        }

        $hasPermissions = self::checkPermissions($DIC->user()->getId(), $ref_id, $permissions);
        if (!$hasPermissions && $send_info) {
            $main_tpl->setOnScreenMessage('failure', $DIC->language()->txt('permission_denied'), true);

            return false;
        }

        return $hasPermissions;
    }

    /**
     * Checks user permissions in question for a given user id in relation
     * to a given ref_id.
     * @param int $usr_id
     * @param string|string[] $permissions
     * @param int $ref_id
     * @return bool
     */
    public static function checkPermissionsOfUser(int $usr_id, $permissions, int $ref_id): bool
    {
        if (!is_array($permissions)) {
            $permissions = [$permissions];
        }

        return self::checkPermissions($usr_id, $ref_id, $permissions);
    }

    /**
     * @param int $usrId
     * @param int $refId
     * @param string[] $permissions
     * @return bool
     */
    protected static function checkPermissions(int $usrId, int $refId, array $permissions): bool
    {
        global $DIC;

        $pub_ref_id = ilObjChatroom::_getPublicRefId();

        foreach ($permissions as $permission) {
            if ($pub_ref_id === $refId) {
                $hasAccess = $DIC->rbac()->system()->checkAccessOfUser($usrId, $permission, $refId);
                if ($hasAccess) {
                    $hasWritePermission = $DIC->rbac()->system()->checkAccessOfUser($usrId, 'write', $refId);
                    if ($hasWritePermission) {
                        continue;
                    }

                    $visible = null;
                    $a_obj_id = ilObject::_lookupObjId($refId);
                    $active = ilObjChatroomAccess::isActivated($refId, $a_obj_id, $visible);

                    switch ($permission) {
                        case 'visible':
                            if (!$active) {
                                $DIC->access()->addInfoItem(
                                    ilAccessInfo::IL_NO_OBJECT_ACCESS,
                                    $DIC->language()->txt('offline')
                                );
                            }

                            if (!$active && !$visible) {
                                return false;
                            }
                            break;

                        case 'read':
                            if (!$active) {
                                $DIC->access()->addInfoItem(
                                    ilAccessInfo::IL_NO_OBJECT_ACCESS,
                                    $DIC->language()->txt('offline')
                                );
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

    public static function byObjectId(int $object_id): ?ilChatroom
    {
        global $DIC;

        $query = 'SELECT * FROM ' . self::$settingsTable . ' WHERE object_id = %s';
        $types = ['integer'];
        $values = [$object_id];
        $rset = $DIC->database()->queryF($query, $types, $values);

        if ($row = $DIC->database()->fetchAssoc($rset)) {
            $room = new self();
            $room->initialize($row);
            return $room;
        }

        return null;
    }

    /**
     * Sets $this->roomId by given array $rowdata and calls setSetting method
     * foreach available setting in $this->availableSettings.
     * @param array $rowdata
     */
    public function initialize(array $rowdata): void
    {
        $this->roomId = (int) $rowdata['room_id'];

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
    public function setSetting(string $name, $value): void
    {
        $this->settings[$name] = $value;
    }

    public static function byRoomId(int $room_id, bool $initObject = false): ?ilChatroom
    {
        global $DIC;

        $query = 'SELECT * FROM ' . self::$settingsTable . ' WHERE room_id = %s';

        $types = ['integer'];
        $values = [$room_id];

        $rset = $DIC->database()->queryF($query, $types, $values);

        if ($row = $DIC->database()->fetchAssoc($rset)) {
            $room = new self();
            $room->initialize($row);

            if ($initObject) {
                $room->object = ilObjectFactory::getInstanceByObjId((int) $row['object_id']);
            }

            return $room;
        }

        return null;
    }

    /**
     * @return array{proom_id: int, room_id: int, object_id: int}[]
     */
    public static function findDeletablePrivateRooms(): array
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
        $rset = $DIC->database()->queryF(
            $query,
            ['integer'],
            [time() + 60 * 5]
        );

        $rooms = [];

        while ($row = $DIC->database()->fetchAssoc($rset)) {
            $rooms[$row['id']] = $row['id'];
        }

        $query = 'SELECT DISTINCT proom_id, room_id, object_id FROM ' . self::$privateRoomsTable
            . ' INNER JOIN ' . self::$settingsTable . ' ON parent_id = room_id '
            . ' WHERE ' . $DIC->database()->in('proom_id', $rooms, false, 'integer');

        $rset = $DIC->database()->query($query);
        $rooms = [];
        while ($row = $DIC->database()->fetchAssoc($rset)) {
            $rooms[] = [
                'proom_id' => (int) $row['proom_id'],
                'room_id' => (int) $row['room_id'],
                'object_id' => (int) $row['object_id']
            ];
        }

        return $rooms;
    }

    public function getDescription(): string
    {
        if (!$this->object) {
            $this->object = ilObjectFactory::getInstanceByObjId((int) $this->getSetting('object_id'));
        }

        return $this->object->getDescription();
    }

    public function getSetting(string $name)
    {
        return $this->settings[$name];
    }

    public function save(): void
    {
        $this->saveSettings($this->settings);
    }

    public function saveSettings(array $settings): void
    {
        global $DIC;

        $localSettings = [];

        foreach ($this->availableSettings as $setting => $type) {
            if (isset($settings[$setting])) {
                if ($type === 'boolean') {
                    $settings[$setting] = (bool) $settings[$setting];
                }
                $localSettings[$setting] = [$this->phpTypeToMDBType($type), $settings[$setting]];
            }
        }

        if (!isset($localSettings['room_type']) || !$localSettings['room_type'][1]) {
            $localSettings['room_type'][0] = 'text';
            $localSettings['room_type'][1] = 'repository';
        }

        if ($this->roomId > 0) {
            $DIC->database()->update(
                self::$settingsTable,
                $localSettings,
                ['room_id' => ['integer', $this->roomId]]
            );
        } else {
            $this->roomId = $DIC->database()->nextId(self::$settingsTable);

            $localSettings['room_id'] = [
                'integer', $this->roomId
            ];

            $DIC->database()->insert(self::$settingsTable, $localSettings);
        }
    }

    private function phpTypeToMDBType(string $type): string
    {
        switch ($type) {
            case 'string':
                return 'text';

            case 'boolean':
                return 'integer';

            default:
                return $type;
        }
    }

    /**
     * @param string|array|stdClass $message
     */
    public function addHistoryEntry($message): void
    {
        global $DIC;

        $subRoom = 0;
        $timestamp = 0;
        if (is_array($message)) {
            $subRoom = (int) ($message['sub'] ?? 0);
            $timestamp = (int) $message['timestamp'];
        } elseif (is_object($message)) {
            $subRoom = (int) $message->sub;
            $timestamp = (int) $message->timestamp;
        }

        $id = $DIC->database()->nextId(self::$historyTable);
        $DIC->database()->insert(
            self::$historyTable,
            [
                'hist_id' => ['integer', $id],
                'room_id' => ['integer', $this->roomId],
                'sub_room' => ['integer', $subRoom],
                'message' => ['text', json_encode($message, JSON_THROW_ON_ERROR)],
                'timestamp' => ['integer', ($timestamp > 0 ? $timestamp : time())],
            ]
        );
    }

    public function connectUser(ilChatroomUser $user): bool
    {
        global $DIC;

        $userdata = [
            'login' => $user->getUsername(),
            'id' => $user->getUserId()
        ];

        $query = 'SELECT user_id FROM ' . self::$userTable . ' WHERE room_id = %s AND user_id = %s';
        $types = ['integer', 'integer'];
        $values = [$this->roomId, $user->getUserId()];

        if (!$DIC->database()->fetchAssoc($DIC->database()->queryF($query, $types, $values))) {
            // Notice: Using replace instead of insert looks strange, because we actually know whether the selected data exists or not
            // But we occasionally found some duplicate key errors although the data set should not exist when the following code is reached
            $DIC->database()->replace(
                self::$userTable,
                [
                    'room_id' => ['integer', $this->roomId],
                    'user_id' => ['integer', $user->getUserId()]
                ],
                [
                    'userdata' => ['text', json_encode($userdata, JSON_THROW_ON_ERROR)],
                    'connected' => ['integer', time()],
                ]
            );

            return true;
        }

        return false;
    }

    public function getConnectedUsers(bool $only_data = true): array
    {
        global $DIC;

        $query = 'SELECT ' . ($only_data ? 'userdata' : '*') . ' FROM ' . self::$userTable . ' WHERE room_id = %s';
        $types = ['integer'];
        $values = [$this->roomId];
        $rset = $DIC->database()->queryF($query, $types, $values);
        $users = [];

        while ($row = $DIC->database()->fetchAssoc($rset)) {
            $users[] = $only_data ? json_decode($row['userdata'], false, 512, JSON_THROW_ON_ERROR) : $row;
        }

        return $users;
    }

    public function disconnectUser(int $user_id): void
    {
        $this->disconnectUsers([$user_id]);
    }

    /**
     * @param int[] $userIds
     */
    public function disconnectUsers(array $userIds): void
    {
        global $DIC;

        $query = 'SELECT * FROM ' . self::$userTable . ' WHERE room_id = %s AND ' .
            $DIC->database()->in('user_id', $userIds, false, 'integer');

        $types = ['integer'];
        $values = [$this->roomId];
        $res = $DIC->database()->queryF($query, $types, $values);

        if ($row = $DIC->database()->fetchAssoc($res)) {
            $query = 'SELECT proom_id FROM ' . self::$privateRoomsTable . ' WHERE parent_id = %s';
            $rset_prooms = $DIC->database()->queryF($query, ['integer'], [$this->roomId]);

            $prooms = [];

            while ($row_prooms = $DIC->database()->fetchAssoc($rset_prooms)) {
                $prooms[] = $row_prooms['proom_id'];
            }

            $query = 'UPDATE ' . self::$privateSessionsTable . ' SET disconnected = %s WHERE ' .
                $DIC->database()->in('user_id', $userIds, false, 'integer') .
                ' AND ' . $DIC->database()->in('proom_id', $prooms, false, 'integer');
            $DIC->database()->manipulateF($query, ['integer'], [time()]);

            $query = 'DELETE FROM ' . self::$userTable . ' WHERE room_id = %s AND ' .
                $DIC->database()->in('user_id', $userIds, false, 'integer');

            $types = ['integer'];
            $values = [$this->roomId];
            $DIC->database()->manipulateF($query, $types, $values);

            do {
                if ($this->getSetting('enable_history')) {
                    $id = $DIC->database()->nextId(self::$sessionTable);
                    $DIC->database()->insert(
                        self::$sessionTable,
                        [
                            'sess_id' => ['integer', $id],
                            'room_id' => ['integer', $this->roomId],
                            'user_id' => ['integer', $row['user_id']],
                            'userdata' => ['text', $row['userdata']],
                            'connected' => ['integer', $row['connected']],
                            'disconnected' => ['integer', time()]
                        ]
                    );
                }
            } while ($row = $DIC->database()->fetchAssoc($res));
        }
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function isSubscribed(int $chat_userid): bool
    {
        global $DIC;

        $query = 'SELECT COUNT(user_id) as cnt FROM ' . self::$userTable .
            ' WHERE room_id = %s AND user_id = %s';

        $types = ['integer', 'integer'];
        $values = [$this->roomId, $chat_userid];
        $res = $DIC->database()->queryF($query, $types, $values);

        return ($row = $DIC->database()->fetchAssoc($res)) && (int) $row['cnt'] === 1;
    }

    public function isAllowedToEnterPrivateRoom(int $chat_userid, int $proom_id): bool
    {
        global $DIC;

        $query = 'SELECT COUNT(user_id) cnt FROM ' . self::$privateRoomsAccessTable .
            ' WHERE proom_id = %s AND user_id = %s';

        $types = ['integer', 'integer'];
        $values = [$proom_id, $chat_userid];
        $res = $DIC->database()->queryF($query, $types, $values);

        if (($row = $DIC->database()->fetchAssoc($res)) && (int) $row['cnt'] === 1) {
            return true;
        }

        $query = 'SELECT COUNT(*) cnt FROM ' . self::$privateRoomsTable .
            ' WHERE proom_id = %s AND owner = %s';

        $types = ['integer', 'integer'];
        $values = [$proom_id, $chat_userid];
        $res = $DIC->database()->queryF($query, $types, $values);

        return ($row = $DIC->database()->fetchAssoc($res)) && (int) $row['cnt'] === 1;
    }

    public function getHistory(
        ilDateTime $from = null,
        ilDateTime $to = null,
        int $restricted_session_userid = null,
        ?int $proom_id = 0,
        bool $respect_target = true
    ): array {
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

        $filter = [];

        if ($from !== null) {
            $filter[] = 'timestamp >= ' . $DIC->database()->quote($from->getUnixTime(), 'integer');
        }

        if ($to !== null) {
            $filter[] = 'timestamp <= ' . $DIC->database()->quote($to->getUnixTime(), 'integer');
        }

        if ($filter) {
            $query .= ' AND ' . implode(' AND ', $filter);
        }
        $query .= ' ORDER BY timestamp ASC';

        $rset = $DIC->database()->query($query);
        $result = [];

        while ($row = $DIC->database()->fetchAssoc($rset)) {
            try {
                $message = json_decode($row['message'], false, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                $message = null;
            } finally {
                if ($message === null) {
                    $message = json_decode('{}', false, 512, JSON_THROW_ON_ERROR);
                }
            }

            $row['message'] = $message;
            $row['message']->timestamp = $row['timestamp'];
            if (
                $respect_target &&
                property_exists($row['message'], 'target') &&
                $row['message']->target !== null &&
                !$row['message']->target->public && (
                    !isset($row['recipients']) ||
                    !in_array($DIC->user()->getId(), explode(',', $row['recipients']), false)
                )
            ) {
                continue;
            }

            $result[] = $row;
        }
        return $result;
    }

    public function getRoomId(): int
    {
        return $this->roomId;
    }

    public function getPrivateRoomSessions(
        ilDateTime $from,
        ilDateTime $to,
        int $user_id,
        int $room_id
    ): array {
        global $DIC;

        $query = 'SELECT proom_id, title FROM ' . self::$privateRoomsTable . ' WHERE proom_id IN (
            SELECT proom_id FROM ' . self::$privateSessionsTable . ' WHERE connected >= %s AND disconnected <= %s AND user_id = %s
        ) AND parent_id = %s';

        $res = $DIC->database()->queryF(
            $query,
            ['integer', 'integer', 'integer', 'integer'],
            [$from->getUnixTime(), $to->getUnixTime(), $user_id, $room_id]
        );
        $result = [];
        while ($row = $DIC->database()->fetchAssoc($res)) {
            $result[] = $row;
        }

        return $result;
    }

    public function saveFileUploadToDb(int $user_id, string $filename, string $type): void
    {
        global $DIC;

        $upload_id = $DIC->database()->nextId(self::$uploadTable);
        $DIC->database()->insert(
            self::$uploadTable,
            [
                'upload_id' => ['integer', $upload_id],
                'room_id' => ['integer', $this->roomId],
                'user_id' => ['integer', $user_id],
                'filename' => ['text', $filename],
                'filetype' => ['text', $type],
                'timestamp' => ['integer', time()]
            ]
        );
    }

    public function banUser(int $user_id, int $actor_id, string $comment = ''): void
    {
        global $DIC;

        $DIC->database()->replace(
            self::$banTable,
            [
                'room_id' => ['integer', $this->roomId],
                'user_id' => ['integer', $user_id]
            ],
            [
                'actor_id' => ['integer', $actor_id],
                'timestamp' => ['integer', time()],
                'remark' => ['text', $comment]
            ]
        );
    }

    /**
     * Deletes entry from banTable matching roomId and given $user_id and
     * returns the number of affected rows.
     * @param int|int[] $user_id
     */
    public function unbanUser($user_id): int
    {
        global $DIC;

        if (!is_array($user_id)) {
            $user_id = [$user_id];
        }

        $query = 'DELETE FROM ' . self::$banTable . ' WHERE room_id = %s AND ' . $DIC->database()->in('user_id', $user_id, false, 'integer');
        $types = ['integer'];
        $values = [$this->getRoomId()];

        return $DIC->database()->manipulateF($query, $types, $values);
    }

    public function isUserBanned(int $user_id): bool
    {
        global $DIC;

        $query = 'SELECT COUNT(user_id) cnt FROM ' . self::$banTable . ' WHERE user_id = %s AND room_id = %s';
        $types = ['integer', 'integer'];
        $values = [$user_id, $this->getRoomId()];

        $res = $DIC->database()->queryF($query, $types, $values);

        return ($row = $DIC->database()->fetchAssoc($res)) && $row['cnt'];
    }

    public function getBannedUsers(): array
    {
        global $DIC;

        $query = 'SELECT chb.* FROM ' . self::$banTable . ' chb INNER JOIN usr_data ud ON chb.user_id = ud.usr_id WHERE chb.room_id = %s ';
        $types = ['integer'];
        $values = [$this->getRoomId()];
        $res = $DIC->database()->queryF($query, $types, $values);
        $result = [];

        while ($row = $DIC->database()->fetchAssoc($res)) {
            if ($row['user_id'] > 0) {
                $user = new ilObjUser((int) $row['user_id']);
                $userdata = [
                    'user_id' => $user->getId(),
                    'firstname' => $user->getFirstname(),
                    'lastname' => $user->getLastname(),
                    'login' => $user->getLogin(),
                    'timestamp' => (int) $row['timestamp'],
                    'actor_id' => (int) $row['actor_id'],
                    'remark' => $row['remark']
                ];

                $result[] = $userdata;
            }
        }

        return $result;
    }

    public function getLastSession(ilChatroomUser $user): ?array
    {
        global $DIC;

        $query = 'SELECT * FROM ' . self::$sessionTable . ' WHERE user_id = ' .
            $DIC->database()->quote($user->getUserId(), 'integer') .
            ' ORDER BY connected DESC';

        $DIC->database()->setLimit(1);
        $res = $DIC->database()->query($query);

        if ($row = $DIC->database()->fetchAssoc($res)) {
            return $row;
        }

        return null;
    }

    public function getSessions(ilChatroomUser $user): array
    {
        global $DIC;

        $query = 'SELECT * FROM ' . self::$sessionTable
            . ' WHERE room_id = ' .
            $DIC->database()->quote($this->getRoomId(), 'integer') .
            ' ORDER BY connected DESC';

        $res = $DIC->database()->query($query);

        $result = [];
        while ($row = $DIC->database()->fetchAssoc($res)) {
            $result[] = $row;
        }

        return $result;
    }

    public function addPrivateRoom(string $title, ilChatroomUser $owner, array $settings): int
    {
        global $DIC;

        $nextId = $DIC->database()->nextId(self::$privateRoomsTable);
        $DIC->database()->insert(
            self::$privateRoomsTable,
            [
                'proom_id' => ['integer', $nextId],
                'parent_id' => ['integer', $this->roomId],
                'title' => ['text', $title],
                'owner' => ['integer', $owner->getUserId()],
                'closed' => ['integer', ($settings['closed'] ?? 0)],
                'created' => ['integer', ($settings['created'] ?? time())],
                'is_public' => ['integer', $settings['public']],
            ]
        );

        return $nextId;
    }

    public function closePrivateRoom(int $id): void
    {
        global $DIC;

        $DIC->database()->manipulateF(
            'UPDATE ' . self::$privateRoomsTable . ' SET closed = %s WHERE proom_id = %s',
            ['integer', 'integer'],
            [time(), $id]
        );
    }

    public function isOwnerOfPrivateRoom(int $user_id, int $proom_id): bool
    {
        global $DIC;

        $query = 'SELECT proom_id FROM ' . self::$privateRoomsTable . ' WHERE proom_id = %s AND owner = %s';
        $types = ['integer', 'integer'];
        $values = [$proom_id, $user_id];

        $res = $DIC->database()->queryF($query, $types, $values);
        if ($DIC->database()->fetchAssoc($res)) {
            return true;
        }

        return false;
    }

    /**
     * @param null|ilChatroomObjectGUI $gui
     * @param int|ilChatroomUser $sender (can be an instance of ilChatroomUser or an user id of an ilObjUser instance
     * @param int $recipient_id
     * @param int $subScope
     * @param string $invitationLink
     * @throws InvalidArgumentException
     */
    public function sendInvitationNotification(
        ?ilChatroomObjectGUI $gui,
        $sender,
        int $recipient_id,
        int $subScope = 0,
        string $invitationLink = ''
    ): void {
        $links = [];
        if ($invitationLink === '') {
            if ($gui) {
                $links[] = new ilNotificationLink(
                    new ilNotificationParameter('chat_join', [], 'chatroom'),
                    $this->getChatURL($gui, $subScope)
                );
            }
        } else {
            $links[] = new ilNotificationLink(new ilNotificationParameter('chat_join', [], 'chatroom'), $invitationLink);
        }

        if ($recipient_id > 0 && ANONYMOUS_USER_ID !== $recipient_id) {
            if (is_numeric($sender) && $sender > 0) {
                $sender_id = $sender;
                /** @var ilObjUser $usr */
                $usr = ilObjectFactory::getInstanceByObjId($sender);
                $public_name = $usr->getPublicName();
            } elseif ($sender instanceof ilChatroomUser) {
                if ($sender->getUserId() > 0) {
                    $sender_id = $sender->getUserId();
                } else {
                    $sender_id = ANONYMOUS_USER_ID;
                }
                $public_name = $sender->getUsername();
            } else {
                throw new InvalidArgumentException(
                    '$sender must be an instance of ilChatroomUser or an id of an ilObjUser instance'
                );
            }

            $userLang = ilLanguageFactory::_getLanguageOfUser($recipient_id);
            $userLang->loadLanguageModule('mail');
            $bodyParams = [
                'link' => $invitationLink !== '' ? $this->getChatURL($gui, $subScope) : '',
                'inviter_name' => $public_name,
                'room_name' => $this->getTitle(),
                'salutation' => ilMail::getSalutation($recipient_id, $userLang)
            ];

            if ($subScope) {
                $bodyParams['room_name'] .= ' - ' . self::lookupPrivateRoomTitle($subScope);
            }

            $notification = new ilNotificationConfig('chat_invitation');
            $notification->setTitleVar('chat_invitation', $bodyParams, 'chatroom');
            $notification->setShortDescriptionVar('chat_invitation_short', $bodyParams, 'chatroom');
            $notification->setLongDescriptionVar('chat_invitation_long', $bodyParams, 'chatroom');
            $notification->setLinks($links);
            $notification->setIconPath('templates/default/images/icon_chtr.svg');
            $notification->setValidForSeconds(ilNotificationConfig::TTL_LONG);
            $notification->setVisibleForSeconds(ilNotificationConfig::DEFAULT_TTS);

            $notification->setHandlerParam('mail.sender', (string) $sender_id);

            $notification->notifyByUsers([$recipient_id]);
        }
    }

    public function getChatURL(ilChatroomObjectGUI $gui, int $scope_id = 0): string
    {
        $url = '';
        if ($scope_id) {
            $url = ilLink::_getStaticLink($gui->getObject()->getRefId(), $gui->getObject()->getType(), true, '_' . $scope_id);
        } else {
            $url = ilLink::_getStaticLink($gui->getObject()->getRefId(), $gui->getObject()->getType());
        }

        return $url;
    }

    public function getTitle(): string
    {
        if (!$this->object) {
            $this->object = ilObjectFactory::getInstanceByObjId((int) $this->getSetting('object_id'));
        }

        return $this->object->getTitle();
    }

    public static function lookupPrivateRoomTitle(int $proom_id): string
    {
        global $DIC;

        $query = 'SELECT title FROM ' . self::$privateRoomsTable . ' WHERE proom_id = %s';
        $types = ['integer'];
        $values = [$proom_id];

        $rset = $DIC->database()->queryF($query, $types, $values);
        if ($row = $DIC->database()->fetchAssoc($rset)) {
            return $row['title'];
        }

        return 'unknown';
    }

    public function inviteUserToPrivateRoomByLogin(string $login, int $proom_id): void
    {
        $user_id = (int) ilObjUser::_lookupId($login);
        if ($user_id) {
            $this->inviteUserToPrivateRoom($user_id, $proom_id);
        }
    }

    public function inviteUserToPrivateRoom(int $user_id, int $proom_id): void
    {
        global $DIC;

        $DIC->database()->replace(
            self::$privateRoomsAccessTable,
            [
                'user_id' => ['integer', $user_id],
                'proom_id' => ['integer', $proom_id]
            ],
            []
        );
    }

    public function getActivePrivateRooms(int $userid): array
    {
        global $DIC;

        $query = '
			SELECT roomtable.title, roomtable.proom_id, accesstable.user_id id, roomtable.owner rowner
			FROM ' . self::$privateRoomsTable . ' roomtable
			LEFT JOIN ' . self::$privateRoomsAccessTable . ' accesstable
			ON roomtable.proom_id = accesstable.proom_id
			AND accesstable.user_id = %s
			WHERE parent_id = %s
			AND (closed = 0 OR closed IS NULL)
			AND (accesstable.user_id IS NOT NULL OR roomtable.owner = %s)';
        $types = ['integer', 'integer', 'integer'];
        $values = [$userid, $this->roomId, $userid];
        $rset = $DIC->database()->queryF($query, $types, $values);
        $rooms = [];
        while ($row = $DIC->database()->fetchAssoc($rset)) {
            $row['active_users'] = $this->listUsersInPrivateRoom((int) $row['id']);
            $row['owner'] = $row['rowner'];
            $rooms[$row['proom_id']] = $row;
        }

        return $rooms;
    }

    /**
     * @param int $private_room_id
     * @return int[]
     */
    public function listUsersInPrivateRoom(int $private_room_id): array
    {
        global $DIC;

        $query = '
            SELECT chatroom_users.user_id FROM ' . self::$privateSessionsTable . '
            INNER JOIN chatroom_users
                ON chatroom_users.user_id = ' . self::$privateSessionsTable . ' .user_id WHERE proom_id = %s AND disconnected = 0
        ';
        $types = ['integer'];
        $values = [$private_room_id];
        $rset = $DIC->database()->queryF($query, $types, $values);

        $users = [];
        while ($row = $DIC->database()->fetchAssoc($rset)) {
            $users[(int) $row['user_id']] = (int) $row['user_id'];
        }

        return array_values($users);
    }

    public function subscribeUserToPrivateRoom(int $room_id, int $user_id): void
    {
        global $DIC;

        if (!$this->userIsInPrivateRoom($room_id, $user_id)) {
            $id = $DIC->database()->nextId(self::$privateSessionsTable);
            $DIC->database()->insert(
                self::$privateSessionsTable,
                [
                    'psess_id' => ['integer', $id],
                    'proom_id' => ['integer', $room_id],
                    'user_id' => ['integer', $user_id],
                    'connected' => ['integer', time()],
                    'disconnected' => ['integer', 0],
                ]
            );
        }
    }

    public function userIsInPrivateRoom(int $room_id, int $user_id): bool
    {
        global $DIC;

        $query = 'SELECT proom_id id FROM ' . self::$privateSessionsTable .
            ' WHERE user_id = %s AND proom_id = %s AND disconnected = 0';
        $types = ['integer', 'integer'];
        $values = [$user_id, $room_id];
        $rset = $DIC->database()->queryF($query, $types, $values);
        if ($DIC->database()->fetchAssoc($rset)) {
            return true;
        }

        return false;
    }

    public function unsubscribeUserFromPrivateRoom(int $room_id, int $user_id): void
    {
        global $DIC;

        $DIC->database()->update(
            self::$privateSessionsTable,
            [
                'disconnected' => ['integer', time()]
            ],
            [
                'proom_id' => ['integer', $room_id],
                'user_id' => ['integer', $user_id]
            ]
        );
    }

    public function countActiveUsers(): int
    {
        global $DIC;

        $query = 'SELECT COUNT(user_id) cnt FROM ' . self::$userTable . ' WHERE room_id = %s';
        $types = ['integer'];
        $values = [$this->roomId];
        $res = $DIC->database()->queryF($query, $types, $values);

        if ($row = $DIC->database()->fetchAssoc($res)) {
            return (int) $row['cnt'];
        }

        return 0;
    }

    public function getPrivateRooms(): array
    {
        global $DIC;

        $query = 'SELECT * FROM ' . self::$privateRoomsTable . ' WHERE parent_id = %s';
        $rset = $DIC->database()->queryF($query, ['integer'], [$this->roomId]);

        $rooms = [];
        while ($row = $DIC->database()->fetchAssoc($rset)) {
            $rooms[] = $row;
        }

        return $rooms;
    }

    /**
     * @param int $subRoomId
     * @return int[]
     */
    public function getPrivilegedUsersForPrivateRoom(int $subRoomId): array
    {
        global $DIC;

        $query = 'SELECT user_id FROM ' . self::$privateRoomsAccessTable . ' WHERE proom_id = %s';
        $rset = $DIC->database()->queryF($query, ['integer'], [$subRoomId]);

        $userIds = [];
        while ($row = $DIC->database()->fetchAssoc($rset)) {
            $userIds[] = (int) $row['user_id'];
        }

        return $userIds;
    }

    public function getUniquePrivateRoomTitle(string $title): string
    {
        global $DIC;

        $query = 'SELECT title FROM ' . self::$privateRoomsTable . ' WHERE parent_id = %s and closed = 0';
        $rset = $DIC->database()->queryF($query, ['integer'], [$this->roomId]);

        $titles = [];
        while ($row = $DIC->database()->fetchAssoc($rset)) {
            $titles[] = $row['title'];
        }

        $suffix = '';
        $i = 0;
        do {
            if (!in_array($title . $suffix, $titles, true)) {
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
     * @param int $user_id
     * @return array<int, string>
     */
    public function getAccessibleRoomIdByTitleMap(int $user_id): array
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

        $types = ['integer', 'text'];
        $values = [1, 'chtr'];
        $res = $DIC->database()->queryF($query, $types, $values);

        $rooms = [];
        while ($row = $DIC->database()->fetchAssoc($res)) {
            if (self::checkPermissionsOfUser($user_id, 'read', (int) $row['ref_id'])) {
                $rooms[(int) $row['room_id']] = $row['title'];
            }
        }

        return $rooms;
    }

    /**
     * @param int $parent_room
     * @param int $user_id
     * @return array<int, int>
     */
    public function getPrivateSubRooms(int $parent_room, int $user_id): array
    {
        global $DIC;

        $query = "
        SELECT      proom_id, parent_id
        FROM        " . self::$privateRoomsTable . "
        WHERE       parent_id = %s
        AND         owner = %s
        AND         closed = 0
        ";

        $types = ['integer', 'integer'];
        $values = [$parent_room, $user_id];
        $res = $DIC->database()->queryF($query, $types, $values);

        $priv_rooms = [];
        while ($row = $DIC->database()->fetchAssoc($res)) {
            $proom_id = (int) $row['proom_id'];
            $priv_rooms[$proom_id] = (int) $row['parent_id'];
        }

        return $priv_rooms;
    }

    public function getRefIdByRoomId(int $room_id): int
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

        $types = ['integer'];
        $values = [$room_id];

        $res = $DIC->database()->queryF($query, $types, $values);

        $row = $DIC->database()->fetchAssoc($res);

        return (int) ($row['ref_id'] ?? 0);
    }

    public function getLastMessages(int $number, ilChatroomUser $chatuser): array
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
        $results = [];
        while (($row = $DIC->database()->fetchAssoc($rset)) && $result_count < $number) {
            $tmp = json_decode($row['message'], false, 512, JSON_THROW_ON_ERROR);
            if (property_exists($tmp, 'target') && $tmp->target instanceof stdClass && (int) $tmp->target->public === 0) {
                if (in_array($chatuser->getUserId(), [(int) $tmp->target->id, (int) $tmp->from->id], true)) {
                    $results[] = $tmp;
                    ++$result_count;
                }
            } else {
                $results[] = $tmp;
                ++$result_count;
            }
        }

        $rset = $DIC->database()->queryF(
            'SELECT *
			FROM ' . self::$historyTable . '
			WHERE room_id = %s
			AND sub_room = 0
			AND ' . $DIC->database()->like('message', 'text', '%%"type":"notice"%%') . '
			AND timestamp <= %s AND timestamp >= %s
			ORDER BY timestamp DESC',
            ['integer', 'integer', 'integer'],
            [$this->roomId, $results[0]->timestamp, $results[$result_count - 1]->timestamp]
        );

        while (($row = $DIC->database()->fetchAssoc($rset))) {
            $tmp = json_decode($row['message'], false, 512, JSON_THROW_ON_ERROR);
            $results[] = $tmp;
        }

        usort($results, static function (stdClass $a, stdClass $b): int {
            $a_timestamp = strlen((string) $a->timestamp) === 13 ? ((int) substr($a->timestamp, 0, -3)) : $a->timestamp;
            $b_timestamp = strlen((string) $b->timestamp) === 13 ? ((int) substr($b->timestamp, 0, -3)) : $b->timestamp;

            return $b_timestamp - $a_timestamp;
        });

        return $results;
    }

    public function clearMessages(int $sub_room): void
    {
        global $DIC;

        $DIC->database()->queryF(
            'DELETE FROM ' . self::$historyTable . ' WHERE room_id = %s AND sub_room = %s',
            ['integer', 'integer'],
            [$this->roomId, $sub_room]
        );

        if ($sub_room) {
            $DIC->database()->queryF(
                'DELETE FROM ' . self::$privateSessionsTable . ' WHERE proom_id = %s AND disconnected < %s',
                ['integer', 'integer'],
                [$sub_room, time()]
            );
        } else {
            $DIC->database()->queryF(
                'DELETE FROM ' . self::$sessionTable . ' WHERE room_id = %s AND disconnected < %s',
                ['integer', 'integer'],
                [$this->roomId, time()]
            );
        }
    }
}
