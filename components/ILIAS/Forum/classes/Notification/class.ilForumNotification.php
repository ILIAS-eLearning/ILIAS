<?php

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

declare(strict_types=1);

use ILIAS\Forum\Notification\NotificationType;
use ILIAS\Forum\Notification\ForumNotificationMembershipRow;

/**
 * Class ilForumNotification
 * @author  Nadia Matuschek <nmatuschek@databay.de>
 * @version $Id:$
 * @ingroup components\ILIASForum
 */
class ilForumNotification
{
    /** @var array<int, array<string, mixed>[]>  */
    private static array $node_data_cache = [];
    /** @var array<int, self> */
    private static array $forced_events_cache = [];

    private readonly ilDBInterface $db;
    private readonly ilObjUser $user;
    private int $notification_id;
    private ?int $user_id = null;
    private int $forum_id;
    private int $thread_id;
    private bool $container_enforces_noti = false;
    private bool $member_may_disable_noti = false;
    private int $interested_events = 0;
    private int $user_id_noti;
    private bool $user_deactivated_noti = false;

    public function __construct(private int $ref_id)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->user = $DIC->user();
        $this->forum_id = $DIC['ilObjDataCache']->lookupObjId($ref_id);
    }

    public function setNotificationId(int $a_notification_id): void
    {
        $this->notification_id = $a_notification_id;
    }

    public function getNotificationId(): int
    {
        return $this->notification_id;
    }

    public function setUserId(?int $a_user_id): void
    {
        $this->user_id = $a_user_id;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setForumId(int $a_forum_id): void
    {
        $this->forum_id = $a_forum_id;
    }

    public function getForumId(): int
    {
        return $this->forum_id;
    }

    public function setThreadId(int $a_thread_id): void
    {
        $this->thread_id = $a_thread_id;
    }

    public function getThreadId(): int
    {
        return $this->thread_id;
    }

    public function setInterestedEvents(int $interested_events): void
    {
        $this->interested_events = $interested_events;
    }

    public function getInterestedEvents(): int
    {
        return $this->interested_events;
    }

    public function setContainerEnforcesNotification(bool $container_enforces): void
    {
        $this->container_enforces_noti = $container_enforces;
    }

    public function getContainerEnforcesNotification(): bool
    {
        return $this->container_enforces_noti;
    }

    public function setMemberMayDisableNotification(bool $member_may_disable): void
    {
        $this->member_may_disable_noti = $member_may_disable;
    }

    public function getMemberMayDisableNotification(): bool
    {
        return $this->member_may_disable_noti;
    }

    public function setForumRefId(int $a_ref_id): void
    {
        $this->ref_id = $a_ref_id;
    }

    public function getForumRefId(): int
    {
        return $this->ref_id;
    }

    //user_id of who sets the setting to notify members
    public function setUserIdNoti(int $a_user_id_noti): void
    {
        $this->user_id_noti = $a_user_id_noti;
    }

    //user_id of who sets the setting to notify members
    public function getUserIdNoti(): int
    {
        return $this->user_id_noti;
    }

    public function setUserDeactivatedNotification(bool $user_deactivated_noti): void
    {
        $this->user_deactivated_noti = $user_deactivated_noti;
    }

    public function getUserDeactivatedNotification(): bool
    {
        return $this->user_deactivated_noti;
    }

    public function isContainerEnforcingNotificationPersisted(): bool
    {
        $res = $this->db->queryF(
            '
			SELECT container_enforces_noti FROM frm_notification
			WHERE user_id = %s
			AND frm_id = %s
			AND user_id_noti > %s ',
            ['integer', 'integer', 'integer'],
            [(int) $this->getUserId(), $this->getForumId(), 0]
        );

        if ($row = $this->db->fetchAssoc($res)) {
            return (bool) $row['container_enforces_noti'];
        }

        return false;
    }

    public function isMemberMayDeactivateNotificationPersisted(): bool
    {
        $res = $this->db->queryF(
            '
			SELECT member_may_disable_noti FROM frm_notification
			WHERE user_id = %s
			AND frm_id = %s
			AND user_id_noti > %s',
            ['integer', 'integer', 'integer'],
            [(int) $this->getUserId(), $this->getForumId(), 0]
        );

        if ($row = $this->db->fetchAssoc($res)) {
            return (bool) $row['member_may_disable_noti'];
        }
        return false;
    }

    public function insertContainerMembershipNotification(): void
    {
        $next_id = $this->db->nextId('frm_notification');
        $this->setNotificationId($next_id);

        $this->db->manipulateF(
            '
			INSERT INTO frm_notification
				(notification_id, user_id, frm_id, container_enforces_noti, member_may_disable_noti, interested_events, user_id_noti, user_deactivated_noti)
			VALUES(%s, %s, %s, %s, %s, %s, %s, %s)',
            ['integer', 'integer', 'integer', 'integer', 'integer', 'integer', 'integer', 'integer'],
            [
                $next_id,
                (int) $this->getUserId(),
                $this->getForumId(),
                (int) $this->getContainerEnforcesNotification(),
                (int) $this->getMemberMayDisableNotification(),
                $this->getInterestedEvents(),
                $this->user->getId(),
                (int) $this->getUserDeactivatedNotification()
            ]
        );
    }

    public function deleteContainerEnforcedMembershipNotification(): void
    {
        $this->db->manipulateF(
            '
			DELETE FROM frm_notification
			WHERE 	user_id = %s
			AND		frm_id = %s
			AND		container_enforces_noti = %s
			AND		user_id_noti > %s',
            ['integer', 'integer', 'integer', 'integer'],
            [(int) $this->getUserId(), $this->getForumId(), 1, 0]
        );
    }

    public function deleteProvisioningRowRestrictingMemberDeactivate(): void
    {
        $this->db->manipulateF(
            '
			DELETE FROM frm_notification
			WHERE 	user_id = %s
			AND		frm_id = %s
			AND		container_enforces_noti = %s
			AND		member_may_disable_noti = %s
			AND		user_id_noti > %s',
            ['integer', 'integer', 'integer', 'integer', 'integer'],
            [(int) $this->getUserId(), $this->getForumId(), 1, 0, 0]
        );
    }

    public function updateMemberMayDisableOnContainerEnforcedRow(): void
    {
        $this->db->manipulateF(
            'UPDATE frm_notification SET member_may_disable_noti = %s WHERE user_id = %s AND frm_id = %s AND container_enforces_noti = %s',
            ['integer', 'integer', 'integer', 'integer'],
            [(int) $this->getMemberMayDisableNotification(), (int) $this->getUserId(), $this->getForumId(), 1]
        );
    }

    /* If a new member enters a Course or a Group, this function checks
     * if this CRS/GRP contains a forum and a notification setting set by admin or moderator
     * and inserts the new member into frm_notification
     * */
    public static function checkForumsExistsInsert(int $ref_id, int $user_id): void
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

            $properties = ilForumProperties::getInstance((int) $data['obj_id']);
            if ($properties->getNotificationType() !== NotificationType::DEFAULT) {
                if ($properties->isContainerEnforcingForumNotification()) {
                    $frm_noti->setInterestedEvents($properties->getInterestedEvents());
                }

                $frm_noti->setContainerEnforcesNotification($properties->isContainerEnforcingForumNotification());
                $frm_noti->setMemberMayDisableNotification($properties->isMemberMayDeactivateForumNotification());
                $frm_noti->setForumId($properties->getObjId());
                if (!$frm_noti->hasContainerProvisionedMembershipNotification()) {
                    $frm_noti->insertContainerMembershipNotification();
                }
            }
        }
    }

    public static function checkForumsExistsDelete(int $ref_id, int $user_id): void
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
                $frm_noti->deleteContainerEnforcedMembershipNotification();
            }
        }
    }

    public static function getCachedNodeData(int $ref_id): array
    {
        global $DIC;

        if (!array_key_exists($ref_id, self::$node_data_cache)) {
            $container_node = $DIC->repositoryTree()->getNodeData($ref_id);
            if (!isset($container_node['child'])) {
                return [];
            }

            $node_data = $DIC->repositoryTree()->getSubTree(
                $container_node,
                true,
                ['frm']
            );
            $node_data = array_filter($node_data, static function (array $forum_node) use ($DIC, $ref_id): bool {
                // filter out forum if a grp lies in the path (#0027702)
                foreach ($DIC->repositoryTree()->getNodePath((int) $forum_node['child'], $ref_id) as $path_node) {
                    $notRootNode = (int) $path_node['child'] !== $ref_id;
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

    public static function _clearForcedForumNotifications(array $move_tree_event): void
    {
        global $DIC;
        $ilDB = $DIC->database();

        if ($move_tree_event['tree'] !== 'tree') {
            return;
        }

        /** @var ilObjForum $source_forum */
        $source_forum = ilObjectFactory::getInstanceByRefId((int) $move_tree_event['source_id']);
        if ($source_forum->isParentMembershipEnabledContainer()) {
            $ilDB->manipulateF(
                'DELETE FROM frm_notification WHERE frm_id = %s AND container_enforces_noti = %s',
                ['integer', 'integer'],
                [$source_forum->getId(), 1]
            );
        }
    }

    public function update(): void
    {
        $this->db->manipulateF(
            'UPDATE frm_notification SET container_enforces_noti = %s, member_may_disable_noti = %s, ' .
            'interested_events = %s WHERE user_id = %s AND frm_id = %s',
            ['integer', 'integer', 'integer', 'integer', 'integer'],
            [
                (int) $this->getContainerEnforcesNotification(),
                (int) $this->getMemberMayDisableNotification(),
                $this->getInterestedEvents(),
                (int) $this->getUserId(),
                $this->getForumId()
            ]
        );
    }

    public function deleteNotificationAllUsers(): void
    {
        $this->db->manipulateF(
            'DELETE FROM frm_notification WHERE frm_id = %s AND user_id_noti > %s',
            ['integer', 'integer'],
            [$this->getForumId(), 0]
        );
    }

    /**
     * @return array<int, ForumNotificationMembershipRow>
     */
    public function read(): array
    {
        $res = $this->db->queryF('SELECT * FROM frm_notification WHERE frm_id = %s', ['integer'], [$this->getForumId()]);

        $result = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $result[(int) $row['user_id']] = new ForumNotificationMembershipRow($row);
        }

        return $result;
    }

    /**
     * Forum-level membership rows only (`thread_id = 0`). Use this when reading
     * admin-controlled flags such as {@see ForumNotificationMembershipRow::$member_may_disable_noti};
     * {@see read()} may otherwise pick an arbitrary thread row as the last row per user.
     *
     * @return array<int, ForumNotificationMembershipRow>
     */
    public function readForumLevelMembershipRows(): array
    {
        $res = $this->db->queryF(
            'SELECT * FROM frm_notification WHERE frm_id = %s AND thread_id = %s',
            ['integer', 'integer'],
            [$this->getForumId(), 0]
        );

        $result = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $result[(int) $row['user_id']] = new ForumNotificationMembershipRow($row);
        }

        return $result;
    }

    /**
     * @return list<int>
     */
    public function getDistinctForumNotificationUserIds(): array
    {
        $res = $this->db->queryF(
            'SELECT DISTINCT user_id FROM frm_notification WHERE frm_id = %s ORDER BY user_id ASC',
            ['integer'],
            [$this->getForumId()]
        );

        $ids = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $ids[] = (int) $row['user_id'];
        }

        return $ids;
    }

    public static function mergeThreadNotifications($merge_source_thread_id, $merge_target_thread_id): void
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

    public function hasContainerProvisionedMembershipNotification(): bool
    {
        $res = $this->db->queryF(
            'SELECT user_id FROM frm_notification WHERE user_id = %s AND frm_id = %s AND user_id_noti > %s AND thread_id = %s',
            ['integer', 'integer', 'integer', 'integer'],
            [(int) $this->getUserId(), $this->getForumId(), 0, 0]
        );

        return $this->db->numRows($res) > 0;
    }

    public function cloneFromSource(int $sourceRefId): void
    {
        $sourceNotificationSettings = new self($sourceRefId);
        $records = $sourceNotificationSettings->read();

        foreach ($records as $usrId => $row) {
            $this->setUserId($usrId);
            $this->setContainerEnforcesNotification($row->container_enforces_noti);
            $this->setMemberMayDisableNotification($row->member_may_disable_noti);
            $this->setUserIdNoti($row->user_id_noti);
            $this->setInterestedEvents($row->interested_events);
            $this->setUserDeactivatedNotification($row->user_deactivated_noti);

            $this->insertContainerMembershipNotification();
        }
    }

    public function updateInterestedEvents(): void
    {
        $this->db->manipulateF(
            'UPDATE frm_notification SET interested_events = %s WHERE user_id = %s AND frm_id = %s',
            ['integer', 'integer', 'integer'],
            [$this->getInterestedEvents(), (int) $this->getUserId(), $this->getForumId()]
        );
    }

    public function readInterestedEvents(): int
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
    public function readAllForcedEvents(): array
    {
        $res = $this->db->queryF(
            'SELECT * FROM frm_notification WHERE frm_id = %s AND thread_id = %s',
            ['integer', 'integer'],
            [$this->forum_id, 0]
        );

        while ($row = $this->db->fetchAssoc($res)) {
            $notificationConfig = new self($this->ref_id);
            $notificationConfig->setNotificationId((int) $row['notification_id']);
            $notificationConfig->setUserId((int) $row['user_id']);
            $notificationConfig->setForumId((int) $row['frm_id']);
            $notificationConfig->setThreadId((int) ($row['thread_id'] ?? 0));
            $notificationConfig->setContainerEnforcesNotification((bool) $row['container_enforces_noti']);
            $notificationConfig->setMemberMayDisableNotification((bool) $row['member_may_disable_noti']);
            $notificationConfig->setInterestedEvents((int) $row['interested_events']);
            $notificationConfig->setUserDeactivatedNotification((bool) (int) ($row['user_deactivated_noti'] ?? 0));

            self::$forced_events_cache[(int) $row['user_id']] = $notificationConfig;
        }

        return self::$forced_events_cache;
    }

    public function getForcedEventsObjectByUserId(int $user_id): self
    {
        if (!isset(self::$forced_events_cache[$user_id])) {
            $this->readAllForcedEvents();
        }

        if (!isset(self::$forced_events_cache[$user_id])) {
            self::$forced_events_cache[$user_id] = $this->createDisplayOnlyForumNotification($user_id);
        }

        return self::$forced_events_cache[$user_id];
    }

    private function createDisplayOnlyForumNotification(int $user_id): self
    {
        $new_object = new self($this->ref_id);
        $new_object->setNotificationId(0);
        $new_object->setUserId($user_id);
        $new_object->setForumId($this->forum_id);
        $new_object->setThreadId(0);

        $props = ilForumProperties::getInstance($this->forum_id);
        $new_object->setContainerEnforcesNotification($props->isContainerEnforcingForumNotification());
        $new_object->setMemberMayDisableNotification(true);
        $new_object->setInterestedEvents(ilForumNotificationEvents::DEACTIVATED);

        return $new_object;
    }

    /**
     * Aligns `frm_notification` rows for this forum with the effective notification mode and properties.
     *
     * @param list<int> $all_context_usr_ids A list of all user ids relevant for this context
     *                                       (moderators plus participants, only relevant for
     *                                       upstream course or group contexts)
     */
    public function applyTypeConfigurationFor(
        array $all_context_usr_ids,
        ilForumProperties $effective_properties,
        ?ilForumProperties $former_properties = null
    ): void {
        $forum_level_rows = $this->readForumLevelMembershipRows();
        $distinct_user_ids = $this->getDistinctForumNotificationUserIds();

        if ($effective_properties->getNotificationType() === NotificationType::DEFAULT) {
            foreach ($distinct_user_ids as $user_id) {
                $this->setUserId($user_id);
                $this->setContainerEnforcesNotification($effective_properties->isContainerEnforcingForumNotification());
                $row = $forum_level_rows[$user_id] ?? null;
                if ($row !== null) {
                    $this->setMemberMayDisableNotification($row->member_may_disable_noti);
                    $this->setInterestedEvents($row->interested_events);
                } else {
                    $this->setMemberMayDisableNotification($effective_properties->isMemberMayDeactivateForumNotification());
                    $this->setInterestedEvents($effective_properties->getInterestedEvents());
                }
                $this->update();
            }

            return;
        }

        if ($effective_properties->getNotificationType() === NotificationType::ALL_USERS) {
            if (!$effective_properties->isMemberMayDeactivateForumNotification()) {
                $this->db->manipulateF(
                    'UPDATE frm_notification SET user_deactivated_noti = %s WHERE frm_id = %s AND thread_id = %s',
                    ['integer', 'integer', 'integer'],
                    [0, $this->getForumId(), 0]
                );
            }

            foreach ($distinct_user_ids as $user_id) {
                $this->setUserId($user_id);
                $this->setContainerEnforcesNotification($effective_properties->isContainerEnforcingForumNotification());
                $this->setMemberMayDisableNotification($effective_properties->isMemberMayDeactivateForumNotification());
                $row = $forum_level_rows[$user_id] ?? null;

                if (!$effective_properties->isMemberMayDeactivateForumNotification()) {
                    $this->setInterestedEvents($effective_properties->getInterestedEvents());
                } else {
                    $this->setInterestedEvents(
                        $row !== null
                            ? $row->interested_events
                            : $effective_properties->getInterestedEvents()
                    );
                }

                $this->update();
            }

            foreach ($all_context_usr_ids as $user_id) {
                if (array_key_exists($user_id, $forum_level_rows)) {
                    continue;
                }

                $this->setUserId($user_id);
                $this->setContainerEnforcesNotification($effective_properties->isContainerEnforcingForumNotification());
                $this->setMemberMayDisableNotification($effective_properties->isMemberMayDeactivateForumNotification());
                $this->setInterestedEvents($effective_properties->getInterestedEvents());
                $this->insertContainerMembershipNotification();
            }

            return;
        }

        if ($effective_properties->getNotificationType() === NotificationType::PER_USER) {
            $user_ids_with_any_frm_noti_row = array_fill_keys($distinct_user_ids, true);

            foreach ($distinct_user_ids as $user_id) {
                $this->setUserId($user_id);
                $this->setContainerEnforcesNotification($effective_properties->isContainerEnforcingForumNotification());
                $row = $forum_level_rows[$user_id] ?? null;

                if ($row === null) {
                    $this->setMemberMayDisableNotification($effective_properties->isMemberMayDeactivateForumNotification());
                    $this->setInterestedEvents($effective_properties->getInterestedEvents());
                } else {
                    $this->setMemberMayDisableNotification($row->member_may_disable_noti);

                    if (!$row->member_may_disable_noti) {
                        $this->setInterestedEvents($effective_properties->getInterestedEvents());
                    } else {
                        $this->setInterestedEvents($row->interested_events);
                    }
                }

                $this->update();
            }

            foreach ($all_context_usr_ids as $user_id) {
                if (array_key_exists($user_id, $forum_level_rows)) {
                    continue;
                }

                if (!isset($user_ids_with_any_frm_noti_row[$user_id])) {
                    continue;
                }

                $this->setUserId($user_id);
                $this->setContainerEnforcesNotification($effective_properties->isContainerEnforcingForumNotification());
                $this->setMemberMayDisableNotification($effective_properties->isMemberMayDeactivateForumNotification());
                $this->setInterestedEvents($effective_properties->getInterestedEvents());
                $this->insertContainerMembershipNotification();
            }
        }
    }
}
