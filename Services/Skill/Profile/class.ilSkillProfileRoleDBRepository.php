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

class ilSkillProfileRoleDBRepository
{
    protected ilDBInterface $db;
    protected ilLanguage $lng;
    protected ilRbacReview $review;

    public function __construct(ilDBInterface $db = null)
    {
        global $DIC;

        $this->db = ($db) ?: $DIC->database();
        $this->lng = $DIC->language();
        $this->review = $DIC->rbac()->review();
    }

    public function deleteProfileRoles(int $profile_id) : void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM skl_profile_role WHERE " .
            " profile_id = " . $ilDB->quote($profile_id, "integer")
        );
    }

    public function getAssignedRoles(int $profile_id) : array
    {
        $ilDB = $this->db;
        $lng = $this->lng;
        $review = $this->review;

        $set = $ilDB->query(
            "SELECT * FROM skl_profile_role " .
            " WHERE profile_id = " . $ilDB->quote($profile_id, "integer")
        );
        $roles = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $rec["role_id"] = (int) $rec["role_id"];
            $name = ilObjRole::_getTranslation(ilObjRole::_lookupTitle($rec["role_id"]));
            $type = $lng->txt("role");
            // get object of role
            $obj_id = ilObject::_lookupObjectId($review->getObjectReferenceOfRole($rec["role_id"]));
            // get title of object if course or group
            $obj_title = "";
            $obj_type = "";
            if (ilObject::_lookupType($obj_id) == "crs" || ilObject::_lookupType($obj_id) == "grp") {
                $obj_title = ilObject::_lookupTitle($obj_id);
                $obj_type = ilObject::_lookupType($obj_id);
            }

            $roles[] = [
                "type" => $type,
                "name" => $name,
                "id" => $rec["role_id"],
                "object_title" => $obj_title,
                "object_type" => $obj_type,
                "object_id" => $obj_id
            ];
        }

        return $roles;
    }

    public function addRoleToProfile(int $profile_id, int $role_id) : void
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

    public function removeRoleFromProfile(int $profile_id, int $role_id) : void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM skl_profile_role WHERE " .
            " profile_id = " . $ilDB->quote($profile_id, "integer") .
            " AND role_id = " . $ilDB->quote($role_id, "integer")
        );
    }

    public function removeRoleFromAllProfiles(int $role_id) : void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM skl_profile_role WHERE " .
            " role_id = " . $ilDB->quote($role_id, "integer")
        );
    }

    public function getAllProfilesOfRole(int $role_id) : array
    {
        $ilDB = $this->db;

        $profiles = [];
        $set = $ilDB->query(
            "SELECT p.id, p.title, p.description, p.image_id FROM skl_profile_role r JOIN skl_profile p " .
            " ON (r.profile_id = p.id) " .
            " WHERE r.role_id = " . $ilDB->quote($role_id, "integer") .
            " ORDER BY p.title ASC"
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $rec['id'] = (int) $rec['id'];
            $profiles[] = $rec;
        }
        return $profiles;
    }

    public function getGlobalProfilesOfRole(int $role_id) : array
    {
        $ilDB = $this->db;

        $profiles = [];
        $set = $ilDB->query(
            "SELECT p.id, p.title, p.description, p.image_id FROM skl_profile_role r JOIN skl_profile p " .
            " ON (r.profile_id = p.id) " .
            " WHERE r.role_id = " . $ilDB->quote($role_id, "integer") .
            " AND p.ref_id = 0" .
            " ORDER BY p.title ASC"
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $rec['id'] = (int) $rec['id'];
            $profiles[] = $rec;
        }

        return $profiles;
    }

    public function getLocalProfilesOfRole(int $role_id, int $ref_id) : array
    {
        $ilDB = $this->db;

        $profiles = [];
        $set = $ilDB->query(
            "SELECT p.id, p.title, p.description, p.image_id FROM skl_profile_role r JOIN skl_profile p " .
            " ON (r.profile_id = p.id) " .
            " WHERE r.role_id = " . $ilDB->quote($role_id, "integer") .
            " AND p.ref_id = " . $ilDB->quote($ref_id, "integer") .
            " ORDER BY p.title ASC"
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $rec['id'] = (int) $rec['id'];
            $profiles[] = $rec;
        }
        return $profiles;
    }

    public function countRoles(int $profile_id) : int
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
