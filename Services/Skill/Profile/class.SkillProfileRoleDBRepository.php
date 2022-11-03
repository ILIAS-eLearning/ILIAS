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
 ********************************************************************
 */

namespace ILIAS\Skill\Profile;

use ILIAS\Skill\Service;

class SkillProfileRoleDBRepository
{
    protected \ilDBInterface $db;
    protected Service\SkillInternalFactoryService $factory_service;

    public function __construct(
        \ilDBInterface $db = null,
        Service\SkillInternalFactoryService $factory_service = null
    ) {
        global $DIC;

        $this->db = ($db) ?: $DIC->database();
        $this->factory_service = ($factory_service) ?: $DIC->skills()->internal()->factory();
    }

    public function deleteProfileRoles(int $profile_id): void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM skl_profile_role WHERE " .
            " profile_id = " . $ilDB->quote($profile_id, "integer")
        );
    }

    public function get(int $profile_id): array
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM skl_profile_role " .
            " WHERE profile_id = " . $ilDB->quote($profile_id, "integer")
        );
        $roles = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $roles[] = $rec;
        }

        return $roles;
    }

    public function getFromRecord(array $rec): SkillProfileRoleAssignment
    {
        return $this->factory_service->profile()->profileRoleAssignment(
            $rec["name"],
            $rec["id"],
            $rec["object_title"],
            $rec["object_type"],
            $rec["object_id"]
        );
    }

    public function addRoleToProfile(int $profile_id, int $role_id): void
    {
        $ilDB = $this->db;

        $ilDB->replace(
            "skl_profile_role",
            array("profile_id" => array("integer", $profile_id),
                  "role_id" => array("integer", $role_id),
            ),
            []
        );
    }

    public function removeRoleFromProfile(int $profile_id, int $role_id): void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM skl_profile_role WHERE " .
            " profile_id = " . $ilDB->quote($profile_id, "integer") .
            " AND role_id = " . $ilDB->quote($role_id, "integer")
        );
    }

    public function removeRoleFromAllProfiles(int $role_id): void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM skl_profile_role WHERE " .
            " role_id = " . $ilDB->quote($role_id, "integer")
        );
    }

    /**
     * @return SkillProfile[]
     */
    public function getAllProfilesOfRole(int $role_id): array
    {
        $ilDB = $this->db;

        $profiles = [];
        $set = $ilDB->query(
            "SELECT p.id, p.title, p.description, p.ref_id, p.skill_tree_id, p.image_id " .
            " FROM skl_profile_role r JOIN skl_profile p ON (r.profile_id = p.id) " .
            " WHERE r.role_id = " . $ilDB->quote($role_id, "integer") .
            " ORDER BY p.title ASC"
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $profiles[] = $this->getProfileFromRecord($rec);
        }
        return $profiles;
    }

    /**
     * @return SkillProfile[]
     */
    public function getGlobalProfilesOfRole(int $role_id): array
    {
        $ilDB = $this->db;

        $profiles = [];
        $set = $ilDB->query(
            "SELECT p.id, p.title, p.description, p.ref_id, p.skill_tree_id, p.image_id " .
            " FROM skl_profile_role r JOIN skl_profile p ON (r.profile_id = p.id) " .
            " WHERE r.role_id = " . $ilDB->quote($role_id, "integer") .
            " AND p.ref_id = 0" .
            " ORDER BY p.title ASC"
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $profiles[] = $this->getProfileFromRecord($rec);
        }

        return $profiles;
    }

    /**
     * @return SkillProfile[]
     */
    public function getLocalProfilesOfRole(int $role_id, int $ref_id): array
    {
        $ilDB = $this->db;

        $profiles = [];
        $set = $ilDB->query(
            "SELECT p.id, p.title, p.description, p.ref_id, p.skill_tree_id, p.image_id " .
            " FROM skl_profile_role r JOIN skl_profile p ON (r.profile_id = p.id) " .
            " WHERE r.role_id = " . $ilDB->quote($role_id, "integer") .
            " AND p.ref_id = " . $ilDB->quote($ref_id, "integer") .
            " ORDER BY p.title ASC"
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $profiles[] = $this->getProfileFromRecord($rec);
        }
        return $profiles;
    }

    protected function getProfileFromRecord(array $rec): SkillProfile
    {
        $rec["id"] = (int) $rec["id"];
        $rec["ref_id"] = (int) $rec["ref_id"];
        $rec["skill_tree_id"] = (int) $rec["skill_tree_id"];

        return $this->factory_service->profile()->profile(
            $rec["id"],
            $rec["title"],
            $rec["description"],
            $rec["skill_tree_id"],
            $rec["image_id"],
            $rec["ref_id"]
        );
    }

    public function countRoles(int $profile_id): int
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT count(*) rcnt FROM skl_profile_role " .
            " WHERE profile_id = " . $ilDB->quote($profile_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        return (int) $rec["rcnt"];
    }
}
