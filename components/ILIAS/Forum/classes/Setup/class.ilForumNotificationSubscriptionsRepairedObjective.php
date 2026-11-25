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

use ILIAS\Setup\Environment;
use ILIAS\Setup\NullConfig;
use ILIAS\Setup\CLI\IOWrapper;

/**
 * Creates missing forced notification subscriptions for forums which notify all participants
 * of the parent course/group and do not allow participants to unsubscribe.
 *
 * Copying such a forum did not create subscriptions for the participants of the target
 * container (see https://mantis.ilias.de/view.php?id=47579), so only participants added
 * after the copy received notifications.
 *
 * Forums which allow participants to unsubscribe are not touched, because a missing
 * subscription might be an intended opt-out there.
 */
class ilForumNotificationSubscriptionsRepairedObjective extends ilSetupObjective
{
    private const int SYSTEM_USER_ID = 6;
    private const int ANONYMOUS_USER_ID = 13;
    private const int MAIN_TREE_ID = 1;

    private ilDBInterface $db;
    /** @var array<int, array{parent: int, type: string}|null> */
    private array $node_cache = [];
    /** @var array<string, int[]> */
    private array $role_users_cache = [];

    public function __construct()
    {
        parent::__construct(new NullConfig());
    }

    public function getHash(): string
    {
        return hash('sha256', self::class);
    }

    public function getLabel(): string
    {
        return 'Missing forced notification subscriptions of forums are created';
    }

    public function isNotable(): bool
    {
        return true;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilDatabaseInitializedObjective()
        ];
    }

    public function achieve(Environment $environment): Environment
    {
        $this->db = $environment->getResource(Environment::RESOURCE_DATABASE);
        /** @var IOWrapper|null $io */
        $io = $environment->getResource(Environment::RESOURCE_ADMIN_INTERACTION);

        $repaired_forums = 0;
        $created_subscriptions = 0;

        foreach ($this->getAffectedForums() as $forum) {
            $participants = $this->getParticipants($forum['ref_id']);
            if ($participants === []) {
                continue;
            }

            $missing = array_diff($participants, $this->getSubscribedUserIds($forum['obj_id']));
            if ($missing === []) {
                continue;
            }

            foreach ($missing as $usr_id) {
                $this->db->insert('frm_notification', [
                    'notification_id' => [ilDBConstants::T_INTEGER, $this->db->nextId('frm_notification')],
                    'user_id' => [ilDBConstants::T_INTEGER, $usr_id],
                    'frm_id' => [ilDBConstants::T_INTEGER, $forum['obj_id']],
                    'admin_force_noti' => [ilDBConstants::T_INTEGER, 1],
                    'user_toggle_noti' => [ilDBConstants::T_INTEGER, 1],
                    'interested_events' => [ilDBConstants::T_INTEGER, $forum['interested_events']],
                    'user_id_noti' => [ilDBConstants::T_INTEGER, self::SYSTEM_USER_ID]
                ]);
            }

            ++$repaired_forums;
            $created_subscriptions += count($missing);
        }

        $io?->inform(sprintf(
            'Created %d missing forum notification subscription(s) in %d forum(s).',
            $created_subscriptions,
            $repaired_forums
        ));

        return $environment;
    }

    public function isApplicable(Environment $environment): bool
    {
        return true;
    }

    /**
     * @return list<array{ref_id: int, obj_id: int, interested_events: int}>
     */
    private function getAffectedForums(): array
    {
        $res = $this->db->queryF(
            'SELECT r.ref_id, s.obj_id, s.interested_events
            FROM frm_settings s
            INNER JOIN object_reference r ON r.obj_id = s.obj_id AND r.deleted IS NULL
            INNER JOIN tree t ON t.child = r.ref_id AND t.tree = %s
            WHERE s.notification_type = %s AND s.admin_force_noti = %s AND s.user_toggle_noti = %s',
            [ilDBConstants::T_INTEGER, ilDBConstants::T_TEXT, ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER],
            [self::MAIN_TREE_ID, 'all_users', 1, 1]
        );

        $forums = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $forums[] = [
                'ref_id' => (int) $row['ref_id'],
                'obj_id' => (int) $row['obj_id'],
                'interested_events' => (int) $row['interested_events'],
            ];
        }

        return $forums;
    }

    /**
     * Mirrors `ilObjForum::getAllForumParticipants()`: moderators of the forum and administrators,
     * tutors and members of the closest group in the path, or of the closest course if there is no group.
     *
     * @return int[]
     */
    private function getParticipants(int $forum_ref_id): array
    {
        $container = $this->findMembershipContainer($forum_ref_id);
        if ($container === null) {
            return [];
        }

        [$container_ref_id, $container_type] = $container;
        $role_titles = $container_type === 'grp'
            ? ['il_grp_admin_' . $container_ref_id, 'il_grp_member_' . $container_ref_id]
            : [
                'il_crs_admin_' . $container_ref_id,
                'il_crs_tutor_' . $container_ref_id,
                'il_crs_member_' . $container_ref_id
            ];
        $role_titles[] = 'il_frm_moderator_' . $forum_ref_id;

        return array_values(array_diff(
            array_unique($this->getUsersOfRoles($role_titles)),
            [self::ANONYMOUS_USER_ID]
        ));
    }

    /**
     * @return array{0: int, 1: string}|null
     */
    private function findMembershipContainer(int $ref_id): ?array
    {
        $closest_course = null;
        $node = $this->getNode($ref_id);
        $visited = [];
        while ($node !== null && $node['parent'] > 0 && !isset($visited[$node['parent']])) {
            $parent_ref_id = $node['parent'];
            $visited[$parent_ref_id] = true;
            $node = $this->getNode($parent_ref_id);
            if ($node === null) {
                break;
            }

            if ($node['type'] === 'grp') {
                return [$parent_ref_id, 'grp'];
            }

            if ($node['type'] === 'crs' && $closest_course === null) {
                $closest_course = [$parent_ref_id, 'crs'];
            }
        }

        return $closest_course;
    }

    /**
     * @return array{parent: int, type: string}|null
     */
    private function getNode(int $ref_id): ?array
    {
        if (!array_key_exists($ref_id, $this->node_cache)) {
            $res = $this->db->queryF(
                'SELECT t.parent, od.type
                FROM tree t
                INNER JOIN object_reference r ON r.ref_id = t.child
                INNER JOIN object_data od ON od.obj_id = r.obj_id
                WHERE t.child = %s AND t.tree = %s',
                [ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER],
                [$ref_id, self::MAIN_TREE_ID]
            );
            $row = $this->db->fetchAssoc($res);
            $this->node_cache[$ref_id] = $row === null
                ? null
                : ['parent' => (int) $row['parent'], 'type' => (string) $row['type']];
        }

        return $this->node_cache[$ref_id];
    }

    /**
     * @param string[] $role_titles
     * @return int[]
     */
    private function getUsersOfRoles(array $role_titles): array
    {
        $usr_ids = [];
        $uncached = array_diff($role_titles, array_keys($this->role_users_cache));
        if ($uncached !== []) {
            foreach ($uncached as $title) {
                $this->role_users_cache[$title] = [];
            }

            $res = $this->db->query(
                'SELECT od.title, ua.usr_id
                FROM object_data od
                INNER JOIN rbac_ua ua ON ua.rol_id = od.obj_id
                INNER JOIN usr_data ud ON ud.usr_id = ua.usr_id
                WHERE od.type = ' . $this->db->quote('role', ilDBConstants::T_TEXT) . '
                AND ' . $this->db->in('od.title', array_values($uncached), false, ilDBConstants::T_TEXT)
            );
            while ($row = $this->db->fetchAssoc($res)) {
                $this->role_users_cache[(string) $row['title']][] = (int) $row['usr_id'];
            }
        }

        foreach ($role_titles as $title) {
            $usr_ids = array_merge($usr_ids, $this->role_users_cache[$title]);
        }

        return $usr_ids;
    }

    /**
     * @return int[]
     */
    private function getSubscribedUserIds(int $forum_obj_id): array
    {
        $res = $this->db->queryF(
            'SELECT DISTINCT user_id FROM frm_notification WHERE frm_id = %s',
            [ilDBConstants::T_INTEGER],
            [$forum_obj_id]
        );

        $usr_ids = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $usr_ids[] = (int) $row['user_id'];
        }

        return $usr_ids;
    }
}
