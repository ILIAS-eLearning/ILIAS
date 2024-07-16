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

    public function getRoleAssignmentFromRecord(array $rec): SkillProfileRoleAssignment
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
     * @return SkillRoleProfile[]
     */
    public function getAllProfilesOfRole(int $role_id): array
    {
        $ilDB = $this->db;

        $profiles = [];
        $set = $ilDB->query(
            "SELECT spr.profile_id, spr.role_id, sp.title, sp.description, sp.ref_id, sp.skill_tree_id, sp.image_id " .
            " FROM skl_profile_role spr INNER JOIN skl_profile sp ON (spr.profile_id = sp.id) " .
            " WHERE spr.role_id = " . $ilDB->quote($role_id, "integer") .
            " ORDER BY sp.title ASC"
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $profiles[(int) $rec["profile_id"]] = $this->getRoleProfileFromRecord($rec);
        }
        return $profiles;
    }

    /**
     * @return SkillRoleProfile[]
     */
    public function getGlobalProfilesOfRole(int $role_id): array
    {
        $ilDB = $this->db;

        $profiles = [];
        $set = $ilDB->query(
            "SELECT spr.profile_id, spr.role_id, sp.title, sp.description, sp.ref_id, sp.skill_tree_id, sp.image_id " .
            " FROM skl_profile_role spr INNER JOIN skl_profile sp ON (spr.profile_id = sp.id) " .
            " WHERE spr.role_id = " . $ilDB->quote($role_id, "integer") .
            " AND sp.ref_id = 0" .
            " ORDER BY sp.title ASC"
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $profiles[(int) $rec["profile_id"]] = $this->getRoleProfileFromRecord($rec);
        }

        return $profiles;
    }

    /**
     * @return SkillRoleProfile[]
     */
    public function getLocalProfilesOfRole(int $role_id): array
    {
        $ilDB = $this->db;

        $profiles = [];
        $set = $ilDB->query(
            "SELECT spr.profile_id, spr.role_id, sp.title, sp.description, sp.ref_id, sp.skill_tree_id, sp.image_id " .
            " FROM skl_profile_role spr INNER JOIN skl_profile sp ON (spr.profile_id = sp.id) " .
            " WHERE spr.role_id = " . $ilDB->quote($role_id, "integer") .
            " AND sp.ref_id <> 0" .
            " ORDER BY sp.title ASC"
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $profiles[(int) $rec["profile_id"]] = $this->getRoleProfileFromRecord($rec);
        }
        return $profiles;
    }

    protected function getProfileFromRecord(array $rec): SkillProfile
    {
        $rec["id"] = (int) $rec["id"];
        $rec["title"] = (string) $rec["title"];
        $rec["description"] = (string) $rec["description"];
        $rec["skill_tree_id"] = (int) $rec["skill_tree_id"];
        $rec["image_id"] = (string) $rec["image_id"];
        $rec["ref_id"] = (int) $rec["ref_id"];

        return $this->factory_service->profile()->profile(
            $rec["id"],
            $rec["title"],
            $rec["description"],
            $rec["skill_tree_id"],
            $rec["image_id"],
            $rec["ref_id"]
        );
    }

    protected function getRoleProfileFromRecord(array $rec): SkillRoleProfile
    {
        $rec["role_id"] = (int) $rec["role_id"];
        $rec["profile_id"] = (int) $rec["profile_id"];
        $rec["title"] = (string) $rec["title"];
        $rec["description"] = (string) $rec["description"];
        $rec["skill_tree_id"] = (int) $rec["skill_tree_id"];
        $rec["image_id"] = (string) $rec["image_id"];
        $rec["ref_id"] = (int) $rec["ref_id"];

        return $this->factory_service->profile()->roleProfile(
            $rec["role_id"],
            $rec["profile_id"],
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
