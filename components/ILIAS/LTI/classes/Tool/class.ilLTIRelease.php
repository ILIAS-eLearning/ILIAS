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

use ceLTIc\LTI\User;

/**
 * The release of an ILIAS object to a platform: which local roles of the object the users of the
 * platform get, depending on their LTI role. Stored in lti_int_provider_obj.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTIRelease
{
    private const string TABLE_NAME = 'lti_int_provider_obj';

    private int $admin_role = 0;
    private int $tutor_role = 0;
    private int $member_role = 0;
    private bool $released = false;

    public function __construct(private readonly int $ref_id, private readonly int $platform_id)
    {
        global $DIC;

        $db = $DIC->database();
        // earlier releases kept the row of an object they stopped releasing, and disabled its registration
        $row = $db->fetchAssoc($db->queryF(
            'SELECT o.admin, o.tutor, o.member FROM ' . self::TABLE_NAME . ' o WHERE o.ref_id = %s AND o.ext_consumer_id = %s'
            . ' AND NOT EXISTS (SELECT 1 FROM lti2_consumer c WHERE c.ref_id = o.ref_id'
            . ' AND c.ext_consumer_id = o.ext_consumer_id AND c.enabled = 0)',
            ['integer', 'integer'],
            [$ref_id, $platform_id]
        ));
        if ($row !== null) {
            $this->released = true;
            $this->admin_role = (int) $row['admin'];
            $this->tutor_role = (int) $row['tutor'];
            $this->member_role = (int) $row['member'];
        }
    }

    /**
     * @return array the titles of the active platforms the given object type may be released to, by id
     */
    public static function getPlatformsForType(string $type): array
    {
        global $DIC;

        $db = $DIC->database();
        $result = $db->queryF(
            'SELECT c.id, c.title FROM lti_ext_consumer c'
            . ' JOIN lti_ext_consumer_otype t ON t.consumer_id = c.id'
            . ' WHERE c.active = %s AND t.object_type = %s ORDER BY c.title',
            ['integer', 'text'],
            [1, $type]
        );

        $platforms = [];
        while ($row = $db->fetchAssoc($result)) {
            $platforms[(int) $row['id']] = (string) $row['title'];
        }

        return $platforms;
    }

    /**
     * The objects a registration of lti2_consumer may launch, while its platform is active: the one object of
     * a registration for a single object (ref_id > 0), else the objects released to the platform.
     *
     * @return array the ref ids of the objects that still exist, by title
     */
    public static function lookupLaunchableRefIds(int $record_id): array
    {
        global $DIC;

        $db = $DIC->database();
        $row = $db->fetchAssoc($db->queryF(
            'SELECT c.ref_id, c.ext_consumer_id FROM lti2_consumer c'
            . ' JOIN lti_ext_consumer e ON e.id = c.ext_consumer_id'
            . ' WHERE c.consumer_pk = %s AND c.enabled = %s AND e.active = %s',
            ['integer', 'integer', 'integer'],
            [$record_id, 1, 1]
        ));
        if ($row === null) {
            return [];
        }

        $ref_ids = [(int) $row['ref_id']];
        if ($ref_ids[0] === 0) {
            $result = $db->queryF(
                'SELECT ref_id FROM ' . self::TABLE_NAME . ' WHERE ext_consumer_id = %s',
                ['integer'],
                [(int) $row['ext_consumer_id']]
            );
            $ref_ids = [];
            while ($released = $db->fetchAssoc($result)) {
                $ref_ids[] = (int) $released['ref_id'];
            }
        }

        $titles = [];
        foreach ($ref_ids as $ref_id) {
            if ($ref_id > 0 && ilObject::_exists($ref_id, true) && !ilObject::_isInTrash($ref_id)) {
                $titles[$ref_id] = ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id));
            }
        }
        asort($titles);

        return array_keys($titles);
    }

    /**
     * True when the object is released to the platform.
     */
    public function isReleased(): bool
    {
        return $this->released;
    }

    public function getAdminRole(): int
    {
        return $this->admin_role;
    }

    public function getTutorRole(): int
    {
        return $this->tutor_role;
    }

    public function getMemberRole(): int
    {
        return $this->member_role;
    }

    public function save(int $admin_role, int $tutor_role, int $member_role): void
    {
        global $DIC;

        $this->admin_role = $admin_role;
        $this->tutor_role = $tutor_role;
        $this->member_role = $member_role;

        $DIC->database()->replace(
            self::TABLE_NAME,
            ['ref_id' => ['integer', $this->ref_id], 'ext_consumer_id' => ['integer', $this->platform_id]],
            [
                'admin' => ['integer', $admin_role],
                'tutor' => ['integer', $tutor_role],
                'member' => ['integer', $member_role],
            ]
        );
        // a registration an earlier release disabled for the object would keep it withdrawn
        $DIC->database()->manipulateF(
            'UPDATE lti2_consumer SET enabled = %s WHERE ref_id = %s AND ext_consumer_id = %s AND enabled = %s',
            ['integer', 'integer', 'integer', 'integer'],
            [1, $this->ref_id, $this->platform_id, 0]
        );
        $this->released = true;
    }

    /**
     * Withdraws the object from an LTI Advantage platform. An LTI 1.1 platform keeps the row: its
     * registration for the object is disabled instead.
     */
    public function delete(): void
    {
        global $DIC;

        $DIC->database()->manipulateF(
            'DELETE FROM ' . self::TABLE_NAME . ' WHERE ref_id = %s AND ext_consumer_id = %s',
            ['integer', 'integer'],
            [$this->ref_id, $this->platform_id]
        );
        $this->released = false;
    }

    /**
     * The local roles an LTI user gets, as their LTI roles tell. celtic/lti knows the role names of
     * every LTI version, both the short ones and the full URIs.
     *
     * @param User $user
     * @return array
     */
    public function getLocalRoles(User $user): array
    {
        $roles = [
            $user->isAdmin() || $user->isManager() ? $this->admin_role : 0,
            $user->isStaff() ? $this->tutor_role : 0,
            $user->isLearner() || $user->isMember() ? $this->member_role : 0,
        ];

        return array_values(array_unique(array_filter($roles)));
    }

    /**
     * The roles a user may get from this release, so that they can be taken away before a new launch.
     *
     * @return array
     */
    public function getAllRoles(): array
    {
        return array_values(array_unique(array_filter([$this->admin_role, $this->tutor_role, $this->member_role])));
    }
}
