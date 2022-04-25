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

/**
 * Skill profile
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillProfile implements ilSkillUsageInfo
{
    protected ilDBInterface $db;
    protected ilLanguage $lng;
    protected ilRbacReview $review;

    protected int $id = 0;
    protected string $title = "";
    protected string $description = "";
    protected int $ref_id = 0;
    protected string $image_id = "";
    protected int $skill_tree_id = 0;

    /**
     * @var array{base_skill_id: int, tref_id: int, level_id: int, order_nr: int}[]
     */
    protected array $skill_level = [];

    public function __construct(int $a_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->lng = $DIC->language();
        $this->review = $DIC->rbac()->review();
        if ($a_id > 0) {
            $this->setId($a_id);
            $this->read();
        }
    }

    public function setId(int $a_val) : void
    {
        $this->id = $a_val;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function setTitle(string $a_val) : void
    {
        $this->title = $a_val;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function setDescription(string $a_val) : void
    {
        $this->description = $a_val;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function setRefId(int $a_val) : void
    {
        $this->ref_id = $a_val;
    }

    public function getRefId() : int
    {
        return $this->ref_id;
    }

    public function setImageId(string $a_val) : void
    {
        $this->image_id = $a_val;
    }

    public function getImageId() : string
    {
        return $this->image_id;
    }

    public function setSkillTreeId(int $a_val) : void
    {
        $this->skill_tree_id = $a_val;
    }

    public function getSkillTreeId() : int
    {
        return $this->skill_tree_id;
    }

    public function addSkillLevel(int $a_base_skill_id, int $a_tref_id, int $a_level_id, int $a_order_nr) : void
    {
        $this->skill_level[] = array(
            "base_skill_id" => $a_base_skill_id,
            "tref_id" => $a_tref_id,
            "level_id" => $a_level_id,
            "order_nr" => $a_order_nr
            );
    }

    public function removeSkillLevel(int $a_base_skill_id, int $a_tref_id, int $a_level_id, int $a_order_nr) : void
    {
        foreach ($this->skill_level as $k => $sl) {
            if ((int) $sl["base_skill_id"] == $a_base_skill_id &&
                (int) $sl["tref_id"] == $a_tref_id &&
                (int) $sl["level_id"] == $a_level_id &&
                (int) $sl["order_nr"] == $a_order_nr) {
                unset($this->skill_level[$k]);
            }
        }
    }

    /**
     * @return array{base_skill_id: int, tref_id: int, level_id: int, order_nr: int}[]
     */
    public function getSkillLevels() : array
    {
        usort($this->skill_level, static function (array $level_a, array $level_b) : int {
            return $level_a['order_nr'] <=> $level_b['order_nr'];
        });

        return $this->skill_level;
    }

    public function read() : void
    {
        $ilDB = $this->db;
        
        $set = $ilDB->query(
            "SELECT * FROM skl_profile " .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        $this->setTitle($rec["title"]);
        $this->setDescription($rec["description"]);
        $this->setRefId($rec["ref_id"]);
        $this->setImageId($rec["image_id"]);
        $this->setSkillTreeId($rec["skill_tree_id"]);
        
        $set = $ilDB->query(
            "SELECT * FROM skl_profile_level " .
            " WHERE profile_id = " . $ilDB->quote($this->getId(), "integer")
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->addSkillLevel(
                (int) $rec["base_skill_id"],
                (int) $rec["tref_id"],
                (int) $rec["level_id"],
                (int) $rec["order_nr"]
            );
        }
    }

    public function create() : void
    {
        $ilDB = $this->db;
        
        // profile
        $this->setId($ilDB->nextId("skl_profile"));
        $ilDB->manipulate("INSERT INTO skl_profile " .
            "(id, title, description, ref_id, image_id, skill_tree_id) VALUES (" .
            $ilDB->quote($this->getId(), "integer") . "," .
            $ilDB->quote($this->getTitle(), "text") . "," .
            $ilDB->quote($this->getDescription(), "text") . "," .
            $ilDB->quote($this->getRefId(), "integer") . "," .
            $ilDB->quote($this->getImageId(), "text") . "," .
            $ilDB->quote($this->getSkillTreeId(), "integer") .
            ")");
        
        // profile levels
        foreach ($this->skill_level as $level) {
            $ilDB->replace(
                "skl_profile_level",
                array("profile_id" => array("integer", $this->getId()),
                    "tref_id" => array("integer", (int) $level["tref_id"]),
                    "base_skill_id" => array("integer", (int) $level["base_skill_id"])
                    ),
                array("order_nr" => array("integer", (int) $level["order_nr"]),
                    "level_id" => array("integer", (int) $level["level_id"])
                    )
            );
        }
    }

    public function update() : void
    {
        $ilDB = $this->db;
        
        // profile
        $ilDB->manipulate(
            "UPDATE skl_profile SET " .
            " title = " . $ilDB->quote($this->getTitle(), "text") . "," .
            " description = " . $ilDB->quote($this->getDescription(), "text") . "," .
            " image_id = " . $ilDB->quote($this->getImageId(), "text") .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer") .
            " AND ref_id = " . $ilDB->quote($this->getRefId(), "integer")
        );
        
        // profile levels
        $ilDB->manipulate(
            "DELETE FROM skl_profile_level WHERE " .
            " profile_id = " . $ilDB->quote($this->getId(), "integer")
        );
        foreach ($this->skill_level as $level) {
            $ilDB->replace(
                "skl_profile_level",
                array("profile_id" => array("integer", $this->getId()),
                    "tref_id" => array("integer", (int) $level["tref_id"]),
                    "base_skill_id" => array("integer", (int) $level["base_skill_id"])
                    ),
                array("order_nr" => array("integer", (int) $level["order_nr"]),
                      "level_id" => array("integer", (int) $level["level_id"])
                )
            );

            /*$ilDB->manipulate("INSERT INTO skl_profile_level ".
                "(profile_id, base_skill_id, tref_id, level_id) VALUES (".
                $ilDB->quote($this->getId(), "integer").",".
                $ilDB->quote((int) $level["base_skill_id"], "integer").",".
                $ilDB->quote((int) $level["tref_id"], "integer").",".
                $ilDB->quote((int) $level["level_id"], "integer").
                ")");*/
        }
    }

    public function delete() : void
    {
        $ilDB = $this->db;

        // TODO: Split the deletions when refactoring to repository pattern
        
        // profile levels
        $ilDB->manipulate(
            "DELETE FROM skl_profile_level WHERE " .
            " profile_id = " . $ilDB->quote($this->getId(), "integer")
        );

        // profile users
        $ilDB->manipulate(
            "DELETE FROM skl_profile_user WHERE " .
            " profile_id = " . $ilDB->quote($this->getId(), "integer")
        );

        // profile roles
        $ilDB->manipulate(
            "DELETE FROM skl_profile_role WHERE " .
            " profile_id = " . $ilDB->quote($this->getId(), "integer")
        );
        
        // profile
        $ilDB->manipulate(
            "DELETE FROM skl_profile WHERE " .
            " id = " . $ilDB->quote($this->getId(), "integer")
        );
    }

    public static function deleteProfilesFromObject(int $a_ref_id) : void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->manipulate(
            "DELETE FROM skl_profile WHERE " .
            " ref_id = " . $ilDB->quote($a_ref_id, "integer")
        );
    }

    public function updateSkillOrder(array $order) : void
    {
        $ilDB = $this->db;

        asort($order);

        $cnt = 1;
        foreach ($order as $id => $o) {
            $id_arr = explode("_", $id);
            $ilDB->manipulate(
                "UPDATE skl_profile_level SET " .
                " order_nr = " . $ilDB->quote(($cnt * 10), "integer") .
                " WHERE base_skill_id = " . $ilDB->quote($id_arr[0], "integer") .
                " AND tref_id = " . $ilDB->quote($id_arr[1], "integer") .
                " AND profile_id = " . $ilDB->quote($this->getId(), "integer")
            );
            $cnt++;
        }
    }

    public function fixSkillOrderNumbering() : void
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT profile_id, base_skill_id, tref_id, order_nr FROM skl_profile_level WHERE " .
            " profile_id = " . $ilDB->quote($this->getId(), "integer") .
            " ORDER BY order_nr ASC"
        );
        $cnt = 1;
        while ($rec = $ilDB->fetchAssoc($set)) {
            $ilDB->manipulate(
                "UPDATE skl_profile_level SET " .
                " order_nr = " . $ilDB->quote(($cnt * 10), "integer") .
                " WHERE profile_id = " . $ilDB->quote($rec["profile_id"], "integer") .
                " AND base_skill_id = " . $ilDB->quote($rec["base_skill_id"], "integer") .
                " AND tref_id = " . $ilDB->quote($rec["tref_id"], "integer")
            );
            $cnt++;
        }
    }

    public function getMaxLevelOrderNr() : int
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT MAX(order_nr) mnr FROM skl_profile_level WHERE " .
            " profile_id = " . $ilDB->quote($this->getId(), "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        return (int) $rec["mnr"];
    }

    public static function getProfilesForAllSkillTrees() : array
    {
        global $DIC;

        $ilDB = $DIC->database();
        
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

    public static function getProfilesForSkillTree(int $a_skill_tree_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT * FROM skl_profile " .
            " WHERE skill_tree_id = " . $ilDB->quote($a_skill_tree_id, "integer") .
            " ORDER BY title "
        );
        $profiles = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $profiles[$rec["id"]] = $rec;
        }

        return $profiles;
    }

    public static function getAllGlobalProfiles() : array
    {
        global $DIC;

        $ilDB = $DIC->database();

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

    /**
     * Get local profiles of object
     */
    public static function getLocalProfilesForObject(int $a_ref_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT * FROM skl_profile " .
            " WHERE ref_id = " . $a_ref_id .
            " ORDER BY title "
        );
        $profiles = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $profiles[$rec["id"]] = $rec;
        }

        return $profiles;
    }

    protected static function lookup(int $a_id, string $a_field) : ?string
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $set = $ilDB->query(
            "SELECT " . $a_field . " FROM skl_profile " .
            " WHERE id = " . $ilDB->quote($a_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);

        return isset($rec[$a_field]) ? (string) $rec[$a_field] : null;
    }

    public static function lookupTitle(int $a_id) : string
    {
        return self::lookup($a_id, "title");
    }

    public static function lookupRefId(int $a_id) : int
    {
        return (int) self::lookup($a_id, "ref_id");
    }

    /**
     * Update the old ref id with the new ref id after import
     */
    public function updateRefIdAfterImport(int $a_new_ref_id) : void
    {
        $ilDB = $this->db;

        $ilDB->update(
            "skl_profile",
            array(
                "ref_id" => array("integer", $a_new_ref_id)),
            array(
                "id" => array("integer", $this->getId()))
        );
    }
    
    ////
    //// Skill user assignment
    ////

    /**
     * Get all assignments (users and roles)
     */
    public function getAssignments() : array
    {
        $assignments = [];

        $users = $this->getAssignedUsers();
        $roles = $this->getAssignedRoles();
        $assignments = $users + $roles;
        ksort($assignments);

        return $assignments;
    }

    public function getAssignedUsers() : array
    {
        $ilDB = $this->db;
        $lng = $this->lng;
        
        $set = $ilDB->query(
            "SELECT * FROM skl_profile_user " .
            " WHERE profile_id = " . $ilDB->quote($this->getId(), "integer")
        );
        $users = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $name = ilUserUtil::getNamePresentation($rec["user_id"]);
            $type = $lng->txt("user");
            $users[$rec["user_id"]] = array(
                "type" => $type,
                "name" => $name,
                "id" => $rec["user_id"],
                "object_title" => "",
                );
        }
        return $users;
    }

    public function addUserToProfile(int $a_user_id) : void
    {
        $ilDB = $this->db;
        
        $ilDB->replace(
            "skl_profile_user",
            array("profile_id" => array("integer", $this->getId()),
                "user_id" => array("integer", $a_user_id),
                ),
            []
        );
    }

    public function removeUserFromProfile(int $a_user_id) : void
    {
        $ilDB = $this->db;
        
        $ilDB->manipulate(
            "DELETE FROM skl_profile_user WHERE " .
            " profile_id = " . $ilDB->quote($this->getId(), "integer") .
            " AND user_id = " . $ilDB->quote($a_user_id, "integer")
        );
    }

    public static function removeUserFromAllProfiles(int $a_user_id) : void
    {
        global $DIC;
        $ilDB = $DIC->database();

        $ilDB->manipulate(
            "DELETE FROM skl_profile_user WHERE " .
            " user_id = " . $ilDB->quote($a_user_id, "integer")
        );
    }

    /**
     * @return array{id: int, title: string, description: string, image_id: string}[]
     */
    public static function getProfilesOfUser(int $a_user_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $rbacreview = $DIC->rbac()->review();

        $all_profiles = [];

        // competence profiles coming from user assignments
        $user_profiles = [];
        $set = $ilDB->query(
            "SELECT p.id, p.title, p.description, p.image_id FROM skl_profile_user u JOIN skl_profile p " .
            " ON (u.profile_id = p.id) " .
            " WHERE user_id = " . $ilDB->quote($a_user_id, "integer") .
            " ORDER BY p.title ASC"
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $rec['id'] = (int) $rec['id'];
            $user_profiles[] = $rec;
        }

        // competence profiles coming from role assignments
        $role_profiles = [];
        $user_roles = $rbacreview->assignedRoles($a_user_id);
        foreach ($user_roles as $role) {
            $profiles = self::getGlobalProfilesOfRole($role);
            foreach ($profiles as $profile) {
                $role_profiles[] = $profile;
            }
        }

        // merge competence profiles and remove multiple occurrences
        $all_profiles = array_merge($user_profiles, $role_profiles);
        $temp_profiles = [];
        foreach ($all_profiles as $v) {
            if (!isset($temp_profiles[$v["id"]])) {
                $temp_profiles[$v["id"]] = $v;
            }
        }
        $all_profiles = array_values($temp_profiles);
        return $all_profiles;
    }

    public static function countUsers(int $a_profile_id) : int
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $set = $ilDB->query(
            "SELECT count(*) ucnt FROM skl_profile_user " .
            " WHERE profile_id = " . $ilDB->quote($a_profile_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        return (int) $rec["ucnt"];
    }

    public function getAssignedRoles() : array
    {
        $ilDB = $this->db;
        $lng = $this->lng;
        $review = $this->review;

        $set = $ilDB->query(
            "SELECT * FROM skl_profile_role " .
            " WHERE profile_id = " . $ilDB->quote($this->getId(), "integer")
        );
        $roles = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
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

            $roles[$rec["role_id"]] = array(
                "type" => $type,
                "name" => $name,
                "id" => $rec["role_id"],
                "object_title" => $obj_title,
                "object_type" => $obj_type,
                "object_id" => $obj_id
            );
        }

        return $roles;
    }

    public function addRoleToProfile(int $a_role_id) : void
    {
        $ilDB = $this->db;

        $ilDB->replace(
            "skl_profile_role",
            array("profile_id" => array("integer", $this->getId()),
                  "role_id" => array("integer", $a_role_id),
            ),
            []
        );
    }

    public function removeRoleFromProfile(int $a_role_id) : void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM skl_profile_role WHERE " .
            " profile_id = " . $ilDB->quote($this->getId(), "integer") .
            " AND role_id = " . $ilDB->quote($a_role_id, "integer")
        );
    }

    public static function removeRoleFromAllProfiles(int $a_role_id) : void
    {
        global $DIC;
        $ilDB = $DIC->database();

        $ilDB->manipulate(
            "DELETE FROM skl_profile_role WHERE " .
            " role_id = " . $ilDB->quote($a_role_id, "integer")
        );
    }

    /**
     * Get global and local profiles of a role
     * @return array{id: int, title: string, description: string, image_id: string}[]
     */
    public static function getAllProfilesOfRole(int $a_role_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $profiles = [];
        $set = $ilDB->query(
            "SELECT p.id, p.title, p.description, p.image_id FROM skl_profile_role r JOIN skl_profile p " .
            " ON (r.profile_id = p.id) " .
            " WHERE r.role_id = " . $ilDB->quote($a_role_id, "integer") .
            " ORDER BY p.title ASC"
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $rec['id'] = (int) $rec['id'];
            $profiles[] = $rec;
        }
        return $profiles;
    }

    /**
     * @return array{id: int, title: string, description: string, image_id: string}[]
     */
    public static function getGlobalProfilesOfRole(int $a_role_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $profiles = [];
        $set = $ilDB->query(
            "SELECT p.id, p.title, p.description, p.image_id FROM skl_profile_role r JOIN skl_profile p " .
            " ON (r.profile_id = p.id) " .
            " WHERE r.role_id = " . $ilDB->quote($a_role_id, "integer") .
            " AND p.ref_id = 0" .
            " ORDER BY p.title ASC"
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $rec['id'] = (int) $rec['id'];
            $profiles[] = $rec;
        }

        return $profiles;
    }

    public static function getLocalProfilesOfRole(int $a_role_id, int $a_ref_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $profiles = [];
        $set = $ilDB->query(
            "SELECT p.id, p.title, p.description, p.image_id FROM skl_profile_role r JOIN skl_profile p " .
            " ON (r.profile_id = p.id) " .
            " WHERE r.role_id = " . $ilDB->quote($a_role_id, "integer") .
            " AND p.ref_id = " . $ilDB->quote($a_ref_id, "integer") .
            " ORDER BY p.title ASC"
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $profiles[] = $rec;
        }
        return $profiles;
    }

    public static function countRoles(int $a_profile_id) : int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT count(*) rcnt FROM skl_profile_role " .
            " WHERE profile_id = " . $ilDB->quote($a_profile_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        return (int) $rec["rcnt"];
    }

    /**
     * @param array{skill_id: int, tref_id: int}[] $a_cskill_ids
     *
     * @return array<string, array<string, array{key: string}[]>>
     */
    public static function getUsageInfo(array $a_cskill_ids) : array
    {
        return ilSkillUsage::getUsageInfoGeneric(
            $a_cskill_ids,
            ilSkillUsage::PROFILE,
            "skl_profile_level",
            "profile_id",
            "base_skill_id"
        );
    }

    public function getTreeIdForProfileId(int $a_profile_id) : int
    {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT * FROM skl_profile " .
            " WHERE id = %s ",
            ["integer"],
            [$a_profile_id]
        );
        $rec = $db->fetchAssoc($set);
        return $rec["skill_tree_id"] ?? 0;
    }
}
