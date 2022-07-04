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
 * Class ilForumNotification
 * @author  Nadia Matuschek <nmatuschek@databay.de>
 * @version $Id:$
 * @ingroup ModulesForum
 */
class ilForumNotification
{
    /** @var array<int, array<string, mixed>[]>  */
    private static array $node_data_cache = [];
    /** @var array<int, self> */
    private static array $forced_events_cache = [];

    private ilDBInterface $db;
    private ilObjUser $user;
    private int $notification_id;
    private ?int $user_id = null;
    private int $forum_id;
    private int $thread_id;
    private bool $admin_force = false;
    private bool $user_toggle = false;
    private int $interested_events = 0;
    private int $ref_id;
    private int $user_id_noti;

    public function __construct(int $ref_id)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->user = $DIC->user();
        $this->ref_id = $ref_id;
        $this->forum_id = $DIC['ilObjDataCache']->lookupObjId($ref_id);
    }

    public function setNotificationId(int $a_notification_id) : void
    {
        $this->notification_id = $a_notification_id;
    }

    public function getNotificationId() : int
    {
        return $this->notification_id;
    }

    public function setUserId(?int $a_user_id) : void
    {
        $this->user_id = $a_user_id;
    }

    public function getUserId() : ?int
    {
        return $this->user_id;
    }

    public function setForumId(int $a_forum_id) : void
    {
        $this->forum_id = $a_forum_id;
    }

    public function getForumId() : int
    {
        return $this->forum_id;
    }

    public function setThreadId(int $a_thread_id) : void
    {
        $this->thread_id = $a_thread_id;
    }

    public function getThreadId() : int
    {
        return $this->thread_id;
    }

    public function setInterestedEvents($interested_events) : void
    {
        $this->interested_events = $interested_events;
    }

    public function getInterestedEvents() : int
    {
        return $this->interested_events;
    }

    public function setAdminForce(bool $a_admin_force) : void
    {
        $this->admin_force = $a_admin_force;
    }

    public function getAdminForce() : bool
    {
        return $this->admin_force;
    }

    public function setUserToggle(bool $a_user_toggle) : void
    {
        $this->user_toggle = $a_user_toggle;
    }

    public function getUserToggle() : bool
    {
        return $this->user_toggle;
    }

    public function setForumRefId(int $a_ref_id) : void
    {
        $this->ref_id = $a_ref_id;
    }

    public function getForumRefId() : int
    {
        return $this->ref_id;
    }

    //user_id of who sets the setting to notify members
    public function setUserIdNoti(int $a_user_id_noti) : void
    {
        $this->user_id_noti = $a_user_id_noti;
    }

    //user_id of who sets the setting to notify members
    public function getUserIdNoti() : int
    {
        return $this->user_id_noti;
    }

    public function isAdminForceNotification() : bool
    {
        $res = $this->db->queryF(
            '
			SELECT admin_force_noti FROM frm_notification
			WHERE user_id = %s
			AND frm_id = %s
			AND user_id_noti > %s ',
            ['integer', 'integer', 'integer'],
            [(int) $this->getUserId(), $this->getForumId(), 0]
        );

        if ($row = $this->db->fetchAssoc($res)) {
            return (bool) $row['admin_force_noti'];
        }

        return false;
    }

    public function isUserToggleNotification() : bool
    {
        $res = $this->db->queryF(
            '
			SELECT user_toggle_noti FROM frm_notification
			WHERE user_id = %s
			AND frm_id = %s
			AND user_id_noti > %s',
            ['integer', 'integer', 'integer'],
            [(int) $this->getUserId(), $this->getForumId(), 0]
        );

        if ($row = $this->db->fetchAssoc($res)) {
            return (bool) $row['user_toggle_noti'];
        }
        return false;
    }

    public function insertAdminForce() : void
    {
        $next_id = $this->db->nextId('frm_notification');
        $this->db->manipulateF(
            '
			INSERT INTO frm_notification
				(notification_id, user_id, frm_id, admin_force_noti, user_toggle_noti, user_id_noti)
			VALUES(%s, %s, %s, %s, %s, %s)',
            ['integer', 'integer', 'integer', 'integer', 'integer', 'integer'],
            [
                $next_id,
                (int) $this->getUserId(),
                $this->getForumId(),
                $this->getAdminForce(),
                $this->getUserToggle(),
                $this->user->getId()
            ]
        );
    }

    public function deleteAdminForce() : void
    {
        $this->db->manipulateF(
            '
			DELETE FROM frm_notification
			WHERE 	user_id = %s
			AND		frm_id = %s
			AND		admin_force_noti = %s
			AND		user_id_noti > %s',
            ['integer', 'integer', 'integer', 'integer'],
            [(int) $this->getUserId(), $this->getForumId(), 1, 0]
        );
    }

    public function deleteUserToggle() : void
    {
        $this->db->manipulateF(
            '
			DELETE FROM frm_notification
			WHERE 	user_id = %s
			AND		frm_id = %s
			AND		admin_force_noti = %s
			AND		user_toggle_noti = %s
			AND		user_id_noti > %s',
            ['integer', 'integer', 'integer', 'integer', 'integer'],
            [(int) $this->getUserId(), $this->getForumId(), 1, 1, 0]
        );
    }

    public function updateUserToggle() : void
    {
        $this->db->manipulateF(
            'UPDATE frm_notification SET user_toggle_noti = %s WHERE user_id = %s AND frm_id = %s AND admin_force_noti = %s',
            ['integer', 'integer', 'integer', 'integer'],
            [$this->getUserToggle(), (int) $this->getUserId(), $this->getForumId(), 1]
        );
    }

    /* If a new member enters a Course or a Group, this function checks
     * if this CRS/GRP contains a forum and a notification setting set by admin or moderator
     * and inserts the new member into frm_notification
     * */
    public static function checkForumsExistsInsert(int $ref_id, int $user_id) : void
    {
        global $DIC;

        $ilUser = $DIC->user();

        $node_data = self::getCachedNodeData($ref_id);

        foreach ($node_data as $data) {
            //check frm_properties if frm_noti is enabled
            $frm_noti = new ilForumNotification((int) $data['ref_id']);
            if ($user_id !== 0) {
                $frm_noti->setUserId($user_id);
            } else {
                $frm_noti->setUserId($ilUser->getId());
            }

            $admin_force = ilForumProperties::_isAdminForceNoti((int) $data['obj_id']);
            $frm_noti->setAdminForce($admin_force);

            $user_toggle = ilForumProperties::_isUserToggleNoti((int) $data['obj_id']);
            if ($user_toggle) {
                $frm_noti->setAdminForce(true);
            }

            if ($admin_force || $user_toggle) {
                $frm_noti->setUserToggle($user_toggle);
                $frm_noti->setForumId((int) $data['obj_id']);
                if ($frm_noti->existsNotification() === false) {
                    $frm_noti->insertAdminForce();
                }
            }
        }
    }

    public static function checkForumsExistsDelete(int $ref_id, int $user_id) : void
    {
        global $DIC;

        $ilUser = $DIC->user();

        $node_data = self::getCachedNodeData($ref_id);

        foreach ($node_data as $data) {
            $frm_noti = new ilForumNotification((int) $data['ref_id']);
            $objFrmMods = new ilForumModerators((int) $data['ref_id']);
            $moderator_ids = $objFrmMods->getCurrentModerators();

            if ($user_id !== 0) {
                $frm_noti->setUserId($user_id);
            } else {
                $frm_noti->setUserId($ilUser->getId());
            }

            $frm_noti->setForumId((int) $data['obj_id']);
            if (!in_array($frm_noti->getUserId(), $moderator_ids, true)) {
                $frm_noti->deleteAdminForce();
            }
        }
    }

    public static function getCachedNodeData(int $ref_id) : array
    {
        global $DIC;

        if (!array_key_exists($ref_id, self::$node_data_cache)) {
            $node_data = $DIC->repositoryTree()->getSubTree(
                $DIC->repositoryTree()->getNodeData($ref_id),
                true,
                ['frm']
            );
            $node_data = array_filter($node_data, static function (array $forum_node) use ($DIC, $ref_id) : bool {
                // filter out forum if a grp lies in the path (#0027702)
                foreach ($DIC->repositoryTree()->getNodePath((int) $forum_node['child'], $ref_id) as $path_node) {
                    $notRootNode = (int) $path_node['child'] !== (int) $ref_id;
                    $isGroup = $path_node['type'] === 'grp';
                    if ($notRootNode && $isGroup) {
                        return false;
                    }
                }

                return true;
            });
            self::$node_data_cache[$ref_id] = $node_data;
        }

        return self::$node_data_cache[$ref_id];
    }

    public static function _isParentNodeGrpCrs(int $a_ref_id) : bool
    {
        global $DIC;

        $parent_ref_id = $DIC->repositoryTree()->getParentId($a_ref_id);
        $parent_obj = ilObjectFactory::getInstanceByRefId($parent_ref_id);

        return $parent_obj->getType() === 'crs' || $parent_obj->getType() === 'grp';
    }

    public static function _clearForcedForumNotifications(array $move_tree_event) : void
    {
        global $DIC;
        $ilDB = $DIC->database();
        $ilObjDataCache = $DIC['ilObjDataCache'];

        if ($move_tree_event['tree'] !== 'tree') {
            return;
        }

        $ref_id = (int) $move_tree_event['source_id'];
        $is_parent = self::_isParentNodeGrpCrs($ref_id);

        if ($is_parent) {
            $forum_id = $ilObjDataCache->lookupObjId($ref_id);

            $ilDB->manipulateF(
                'DELETE FROM frm_notification WHERE frm_id = %s AND admin_force_noti = %s',
                ['integer', 'integer'],
                [$forum_id, 1]
            );
        }
    }

    public function update() : void
    {
        $this->db->manipulateF(
            'UPDATE frm_notification SET admin_force_noti = %s, user_toggle_noti = %s, ' .
            'interested_events = %s WHERE user_id = %s AND frm_id = %s',
            ['integer', 'integer', 'integer', 'integer', 'integer'],
            [
                (int) $this->getAdminForce(),
                (int) $this->getUserToggle(),
                $this->getInterestedEvents(),
                (int) $this->getUserId(),
                $this->getForumId()
            ]
        );
    }

    public function deleteNotificationAllUsers() : void
    {
        $this->db->manipulateF(
            'DELETE FROM frm_notification WHERE frm_id = %s AND user_id_noti > %s',
            ['integer', 'integer'],
            [$this->getForumId(), 0]
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function read() : array
    {
        $result = [];

        $res = $this->db->queryF('SELECT * FROM frm_notification WHERE frm_id = %s', ['integer'], [$this->getForumId()]);
        while ($row = $this->db->fetchAssoc($res)) {
            $result[(int) $row['user_id']]['notification_id'] = (int) $row['notification_id'];
            $result[(int) $row['user_id']]['user_id'] = (int) $row['user_id'];
            $result[(int) $row['user_id']]['frm_id'] = (int) $row['frm_id'];
            $result[(int) $row['user_id']]['thread_id'] = (int) $row['thread_id'];
            $result[(int) $row['user_id']]['admin_force_noti'] = (int) $row['admin_force_noti'];
            $result[(int) $row['user_id']]['user_toggle_noti'] = (int) $row['user_toggle_noti'];
            $result[(int) $row['user_id']]['interested_events'] = (int) $row['interested_events'];
        }

        return $result;
    }

    public static function mergeThreadNotifications($merge_source_thread_id, $merge_target_thread_id) : void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $res = $ilDB->queryF(
            'SELECT notification_id, user_id FROM frm_notification WHERE frm_id = %s AND  thread_id = %s ORDER BY user_id ASC',
            ['integer', 'integer'],
            [0, $merge_source_thread_id]
        );

        $res_2 = $ilDB->queryF(
            'SELECT DISTINCT user_id FROM frm_notification WHERE frm_id = %s AND  thread_id = %s ORDER BY user_id ASC',
            ['integer', 'integer'],
            [0, $merge_target_thread_id]
        );

        $users_already_notified = [];
        while ($users_row = $ilDB->fetchAssoc($res_2)) {
            $users_already_notified[(int) $users_row['user_id']] = (int) $users_row['user_id'];
        }

        while ($row = $ilDB->fetchAssoc($res)) {
            if (isset($users_already_notified[(int) $row['user_id']])) {
                $ilDB->manipulateF(
                    'DELETE FROM frm_notification WHERE notification_id = %s',
                    ['integer'],
                    [$row['notification_id']]
                );
            } else {
                $ilDB->update(
                    'frm_notification',
                    ['thread_id' => ['integer', $merge_target_thread_id]],
                    ['thread_id' => ['integer', $merge_source_thread_id]]
                );
            }
        }
    }

    public function existsNotification() : bool
    {
        $res = $this->db->queryF(
            'SELECT user_id FROM frm_notification WHERE user_id = %s AND frm_id = %s AND admin_force_noti = %s',
            ['integer', 'integer', 'integer'],
            [(int) $this->getUserId(), $this->getForumId(), (int) $this->getAdminForce()]
        );

        return $this->db->numRows($res) > 0;
    }

    public function cloneFromSource(int $sourceRefId) : void
    {
        $sourceNotificationSettings = new self($sourceRefId);
        $records = $sourceNotificationSettings->read();

        foreach ($records as $usrId => $row) {
            $this->setUserId($usrId);
            $this->setAdminForce((bool) $row['admin_force_noti']);
            $this->setUserToggle((bool) $row['user_toggle_noti']);
            $this->setUserIdNoti((int) $row['user_id_noti']);
            $this->setInterestedEvents((int) $row['interested_events']);

            $this->insertAdminForce();
        }
    }

    public function updateInterestedEvents() : void
    {
        $this->db->manipulateF(
            'UPDATE frm_notification SET interested_events = %s WHERE user_id = %s AND frm_id = %s',
            ['integer', 'integer', 'integer'],
            [$this->getInterestedEvents(), (int) $this->getUserId(), $this->getForumId()]
        );
    }

    public function readInterestedEvents() : int
    {
        $interested_events = ilForumNotificationEvents::DEACTIVATED;

        $this->db->setLimit(1);
        $res = $this->db->queryF(
            'SELECT interested_events FROM frm_notification WHERE user_id = %s AND frm_id = %s',
            ['integer', 'integer'],
            [(int) $this->getUserId(), $this->getForumId()]
        );

        while ($row = $this->db->fetchAssoc($res)) {
            $interested_events = (int) $row['interested_events'];
        }

        return $interested_events;
    }

    /**
     * @return array<int, ilForumNotification>
     */
    public function readAllForcedEvents() : array
    {
        $res = $this->db->queryF(
            'SELECT * FROM frm_notification WHERE admin_force_noti = %s AND frm_id = %s',
            ['integer', 'integer'],
            [1, $this->forum_id]
        );

        while ($row = $this->db->fetchAssoc($res)) {
            $notificationConfig = new self($this->ref_id);
            $notificationConfig->setNotificationId((int) $row['notification_id']);
            $notificationConfig->setUserId((int) $row['user_id']);
            $notificationConfig->setForumId((int) $row['frm_id']);
            $notificationConfig->setAdminForce((bool) $row['admin_force_noti']);
            $notificationConfig->setUserToggle((bool) $row['user_toggle_noti']);
            $notificationConfig->setInterestedEvents((int) $row['interested_events']);

            self::$forced_events_cache[(int) $row['user_id']] = $notificationConfig;
        }

        return self::$forced_events_cache;
    }

    public function getForcedEventsObjectByUserId(int $user_id) : self
    {
        if (!isset(self::$forced_events_cache[$user_id])) {
            $this->readAllForcedEvents();
        }

        return self::$forced_events_cache[$user_id];
    }
}
