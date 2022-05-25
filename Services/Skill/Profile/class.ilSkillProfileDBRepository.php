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
 ********************************************************************
 */

use ILIAS\Skill\Service;

class ilSkillProfileDBRepository
{
    protected ilDBInterface $db;
    protected Service\SkillInternalFactoryService $factory_service;

    public function __construct(ilDBInterface $db = null, Service\SkillInternalFactoryService $factory_service = null)
    {
        global $DIC;

        $this->db = ($db) ?: $DIC->database();
        $this->factory_service = ($factory_service) ?: $DIC->skills()->internal()->factory();
    }

    public function getById(int $profile_id) : ilSkillProfile
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM skl_profile " .
            " WHERE id = " . $ilDB->quote($profile_id, "integer")
        );

        if ($rec = $ilDB->fetchAssoc($set)) {
            return $this->getProfileFromRecord($rec);
        }
        throw new ilSkillProfileNotFoundException("Profile with ID $profile_id not found.");
    }

    protected function getProfileFromRecord(array $rec) : ilSkillProfile
    {
        $rec["id"] = (int) $rec["id"];
        $rec["ref_id"] = (int) $rec["ref_id"];
        $rec["skill_tree_id"] = (int) $rec["skill_tree_id"];

        return $this->factory_service->profile(
            $rec["id"],
            $rec["title"],
            $rec["description"],
            $rec["skill_tree_id"],
            $rec["image_id"],
            $rec["ref_id"]
        );
    }

    public function getNextId() : int
    {
        $ilDB = $this->db;

        $next_id = $ilDB->nextId("skl_profile");
        return $next_id;
    }

    public function createProfile(
        ilSkillProfile $profile
    ) : ilSkillProfile {
        $ilDB = $this->db;

        $new_profile_id = $this->getNextId();
        $ilDB->manipulate("INSERT INTO skl_profile " .
            "(id, title, description, skill_tree_id, image_id, ref_id) VALUES (" .
            $ilDB->quote($new_profile_id, "integer") . "," .
            $ilDB->quote($profile->getTitle(), "text") . "," .
            $ilDB->quote($profile->getDescription(), "text") . "," .
            $ilDB->quote($profile->getSkillTreeId(), "integer") . "," .
            $ilDB->quote($profile->getImageId(), "text") . "," .
            $ilDB->quote($profile->getRefId(), "integer") .
            ")");

        return $this->getById($new_profile_id);
    }

    public function updateProfile(
        ilSkillProfile $profile
    ) : ilSkillProfile {
        $ilDB = $this->db;

        // profile
        $ilDB->manipulate(
            "UPDATE skl_profile SET " .
            " title = " . $ilDB->quote($profile->getTitle(), "text") . "," .
            " description = " . $ilDB->quote($profile->getDescription(), "text") . "," .
            " image_id = " . $ilDB->quote($profile->getImageId(), "text") .
            " WHERE id = " . $ilDB->quote($profile->getId(), "integer") .
            " AND ref_id = " . $ilDB->quote($profile->getRefId(), "integer")
        );

        return $this->getById($profile->getId());
    }

    public function deleteProfile(int $profile_id) : void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM skl_profile WHERE " .
            " id = " . $ilDB->quote($profile_id, "integer")
        );
    }

    public function deleteProfilesFromObject(int $ref_id) : void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM skl_profile WHERE " .
            " ref_id = " . $ilDB->quote($ref_id, "integer")
        );
    }

    public function getProfilesForAllSkillTrees() : array
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM skl_profile " .
            " ORDER BY title "
        );
        $profiles = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $profiles[$rec["id"]] = $rec;
        }

        return $profiles;
    }

    public function getProfilesForSkillTree(int $skill_tree_id) : array
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM skl_profile " .
            " WHERE skill_tree_id = " . $ilDB->quote($skill_tree_id, "integer") .
            " ORDER BY title "
        );
        $profiles = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $profiles[$rec["id"]] = $rec;
        }

        return $profiles;
    }

    public function getAllGlobalProfiles() : array
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM skl_profile " .
            " WHERE ref_id = 0 " .
            " ORDER BY title "
        );
        $profiles = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $profiles[$rec["id"]] = $rec;
        }

        return $profiles;
    }

    public function getLocalProfilesForObject(int $ref_id) : array
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM skl_profile " .
            " WHERE ref_id = " . $ref_id .
            " ORDER BY title "
        );
        $profiles = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $profiles[$rec["id"]] = $rec;
        }

        return $profiles;
    }

    public function lookup(int $id, string $field) : ?string
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT " . $field . " FROM skl_profile " .
            " WHERE id = " . $ilDB->quote($id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);

        return isset($rec[$field]) ? (string) $rec[$field] : null;
    }

    public function updateRefIdAfterImport(int $profile_id, int $new_ref_id) : void
    {
        $ilDB = $this->db;

        $ilDB->update(
            "skl_profile",
            array(
                "ref_id" => array("integer", $new_ref_id)),
            array(
                "id" => array("integer", $profile_id))
        );
    }

    public function getTreeId(int $profile_id) : int
    {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT * FROM skl_profile " .
            " WHERE id = %s ",
            ["integer"],
            [$profile_id]
        );
        $rec = $db->fetchAssoc($set);
        return (int) $rec["skill_tree_id"] ?? 0;
    }
}
