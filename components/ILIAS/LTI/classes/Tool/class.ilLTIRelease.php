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

    public function __construct(private readonly int $ref_id, private readonly int $platform_id)
    {
        global $DIC;

        $db = $DIC->database();
        $row = $db->fetchAssoc($db->queryF(
            'SELECT admin, tutor, member FROM ' . self::TABLE_NAME . ' WHERE ref_id = %s AND ext_consumer_id = %s',
            ['integer', 'integer'],
            [$ref_id, $platform_id]
        ));
        if ($row !== null) {
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
    }

    /**
     * The local roles an LTI user gets, as their LTI roles tell. celtic/lti knows the role names of
     * every LTI version, both the short ones and the full URIs.
     *
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
