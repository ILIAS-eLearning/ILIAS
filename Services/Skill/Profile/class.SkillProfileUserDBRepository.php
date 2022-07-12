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

namespace ILIAS\Skill\Profile;

class SkillProfileUserDBRepository
{
    protected \ilDBInterface $db;
    protected \ilLanguage $lng;

    public function __construct(\ilDBInterface $db = null)
    {
        global $DIC;

        $this->db = ($db) ?: $DIC->database();
        $this->lng = $DIC->language();
    }

    public function getAssignedUsers(int $profile_id) : array
    {
        $ilDB = $this->db;
        $lng = $this->lng;

        $set = $ilDB->query(
            "SELECT * FROM skl_profile_user " .
            " WHERE profile_id = " . $ilDB->quote($profile_id, "integer")
        );
        $users = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $rec["user_id"] = (int) $rec["user_id"];
            $name = \ilUserUtil::getNamePresentation($rec["user_id"]);
            $type = $lng->txt("user");
            $users[] = [
                "type" => $type,
                "name" => $name,
                "id" => $rec["user_id"],
                "object_title" => ""
            ];
        }
        return $users;
    }

    public function addUserToProfile(int $profile_id, int $user_id) : void
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

    public function removeUserFromProfile(int $profile_id, int $user_id) : void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM skl_profile_user WHERE " .
            " profile_id = " . $ilDB->quote($profile_id, "integer") .
            " AND user_id = " . $ilDB->quote($user_id, "integer")
        );
    }

    public function removeUserFromAllProfiles(int $user_id) : void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM skl_profile_user WHERE " .
            " user_id = " . $ilDB->quote($user_id, "integer")
        );
    }

    public function deleteProfileUsers(int $profile_id) : void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM skl_profile_user WHERE " .
            " profile_id = " . $ilDB->quote($profile_id, "integer")
        );
    }

    public function getProfilesOfUser(int $user_id) : array
    {
        $ilDB = $this->db;

        $user_profiles = [];
        $set = $ilDB->query(
            "SELECT p.id, p.title, p.description, p.image_id FROM skl_profile_user u JOIN skl_profile p " .
            " ON (u.profile_id = p.id) " .
            " WHERE user_id = " . $ilDB->quote($user_id, "integer") .
            " ORDER BY p.title ASC"
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $rec['id'] = (int) $rec['id'];
            $user_profiles[] = $rec;
        }

        return $user_profiles;
    }

    public function countUsers(int $profile_id) : int
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
