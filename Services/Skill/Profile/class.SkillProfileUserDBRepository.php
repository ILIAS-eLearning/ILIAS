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

class SkillProfileUserDBRepository
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

    public function get(int $profile_id): array
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM skl_profile_user " .
            " WHERE profile_id = " . $ilDB->quote($profile_id, "integer")
        );
        $users = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $users[] = $rec;
        }

        return $users;
    }

    public function getFromRecord(array $rec): SkillProfileUserAssignment
    {
        return $this->factory_service->profile()->profileUserAssignment(
            $rec["name"],
            $rec["id"]
        );
    }

    public function addUserToProfile(int $profile_id, int $user_id): void
    {
        $ilDB = $this->db;

        $ilDB->replace(
            "skl_profile_user",
            array("profile_id" => array("integer", $profile_id),
                  "user_id" => array("integer", $user_id),
            ),
            []
        );
    }

    public function removeUserFromProfile(int $profile_id, int $user_id): void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM skl_profile_user WHERE " .
            " profile_id = " . $ilDB->quote($profile_id, "integer") .
            " AND user_id = " . $ilDB->quote($user_id, "integer")
        );
    }

    public function removeUserFromAllProfiles(int $user_id): void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM skl_profile_user WHERE " .
            " user_id = " . $ilDB->quote($user_id, "integer")
        );
    }

    public function deleteProfileUsers(int $profile_id): void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM skl_profile_user WHERE " .
            " profile_id = " . $ilDB->quote($profile_id, "integer")
        );
    }

    /**
     * @return SkillProfile[]
     */
    public function getProfilesOfUser(int $user_id): array
    {
        $ilDB = $this->db;

        $user_profiles = [];
        $set = $ilDB->query(
            "SELECT p.id, p.title, p.description, p.ref_id, p.skill_tree_id, p.image_id " .
            " FROM skl_profile_user u JOIN skl_profile p ON (u.profile_id = p.id) " .
            " WHERE u.user_id = " . $ilDB->quote($user_id, "integer") .
            " ORDER BY p.title ASC"
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $user_profiles[] = $this->getProfileFromRecord($rec);
        }

        return $user_profiles;
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

    public function countUsers(int $profile_id): int
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT count(*) ucnt FROM skl_profile_user " .
            " WHERE profile_id = " . $ilDB->quote($profile_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        return (int) $rec["ucnt"];
    }
}
