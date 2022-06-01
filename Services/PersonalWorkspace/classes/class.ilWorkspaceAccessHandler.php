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

/**
 * Access handler for personal workspace
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilWorkspaceAccessHandler
{
    protected ilObjUser $user;
    protected ilLanguage $lng;
    protected ilRbacReview $rbacreview;
    protected ilSetting $settings;
    protected ilDBInterface $db;
    /**
     * @var ilTree|ilWorkspaceTree|null
     */
    protected ?ilTree $tree;

    public function __construct(
        ilTree $a_tree = null
    ) {
        global $DIC;

        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->rbacreview = $DIC->rbac()->review();
        $this->settings = $DIC->settings();
        $this->db = $DIC->database();
        $ilUser = $DIC->user();
        $lng = $DIC->language();

        $lng->loadLanguageModule("wsp");
        
        if (!$a_tree) {
            $a_tree = new ilWorkspaceTree($ilUser->getId());
        }
        $this->tree = $a_tree;
    }
    
    /**
     * Get workspace tree
     *
     * @return ilWorkspaceTree
     */
    public function getTree() : ilTree
    {
        return $this->tree;
    }

    public function checkAccess(
        string $a_permission,
        string $a_cmd,
        int $a_node_id,
        string $a_type = ""
    ) : bool {
        $ilUser = $this->user;
        return $this->checkAccessOfUser($this->tree, $ilUser->getId(), $a_permission, $a_cmd, $a_node_id, $a_type);
    }

    /**
     * check access for an object
     */
    public function checkAccessOfUser(
        ilTree $a_tree,
        int $a_user_id,
        string $a_permission,
        string $a_cmd,
        int $a_node_id,
        string $a_type = ""
    ) : bool {
        $rbacreview = $this->rbacreview;
        $ilUser = $this->user;
        $ilSetting = $this->settings;

        // :TODO: create permission for parent node with type ?!

        // #20310
        if (!$ilSetting->get("enable_global_profiles") && $ilUser->getId() == ANONYMOUS_USER_ID) {
            return false;
        }

        // tree root is read-only
        if ($a_permission == "write") {
            if ($a_tree->readRootId() == $a_node_id) {
                return false;
            }
        }
        
        // node owner has all rights
        if ($a_tree->lookupOwner($a_node_id) == $a_user_id) {
            return true;
        }
        
        // other users can only read
        if ($a_permission == "read" || $a_permission == "visible") {
            // get all objects with explicit permission
            $objects = $this->getPermissions($a_node_id);
            if ($objects) {
                // check if given user is member of object or has role
                foreach ($objects as $obj_id) {
                    switch ($obj_id) {
                        case ilWorkspaceAccessGUI::PERMISSION_ALL:
                            return true;
                                
                        case ilWorkspaceAccessGUI::PERMISSION_ALL_PASSWORD:
                            // check against input kept in session
                            if (self::getSharedNodePassword($a_node_id) == self::getSharedSessionPassword($a_node_id) ||
                                $a_permission == "visible") {
                                return true;
                            }
                            break;
                    
                        case ilWorkspaceAccessGUI::PERMISSION_REGISTERED:
                            if ($ilUser->getId() != ANONYMOUS_USER_ID) {
                                return true;
                            }
                            break;
                        
                        default:
                            switch (ilObject::_lookupType($obj_id)) {
                                case "grp":
                                    // member of group?
                                    if (ilGroupParticipants::_getInstanceByObjId($obj_id)->isAssigned($a_user_id)) {
                                        return true;
                                    }
                                    break;

                                case "crs":
                                    // member of course?
                                    if (ilCourseParticipants::_getInstanceByObjId($obj_id)->isAssigned($a_user_id)) {
                                        return true;
                                    }
                                    break;

                                case "role":
                                    // has role?
                                    if ($rbacreview->isAssigned($a_user_id, $obj_id)) {
                                        return true;
                                    }
                                    break;

                                case "usr":
                                    // direct assignment
                                    if ($a_user_id == $obj_id) {
                                        return true;
                                    }
                                    break;
                            }
                            break;
                    }
                }
            }
        }
        
        return false;
    }

    /**
     * Set permissions after creating node/object
     */
    public function setPermissions(int $a_parent_node_id, int $a_node_id) : void
    {
        // nothing to do as owner has irrefutable rights to any workspace object
    }

    /**
     * Add permission to node for object
     */
    public function addPermission(
        int $a_node_id,
        int $a_object_id,
        string $a_extended_data = null
    ) : bool {
        $ilDB = $this->db;
        $ilUser = $this->user;

        // tree owner must not be added
        if ($this->tree->getTreeId() == $ilUser->getId() &&
            $a_object_id == $ilUser->getId()) {
            return false;
        }

        $ilDB->manipulate("INSERT INTO acl_ws (node_id, object_id, extended_data, tstamp)" .
            " VALUES (" . $ilDB->quote($a_node_id, "integer") . ", " .
            $ilDB->quote($a_object_id, "integer") . "," .
            $ilDB->quote($a_extended_data, "text") . "," .
            $ilDB->quote(time(), "integer") . ")");
        return true;
    }

    /**
     * Remove permission[s] (for object) to node
     */
    public function removePermission(
        int $a_node_id,
        int $a_object_id = null
    ) : int {
        $ilDB = $this->db;
        
        $query = "DELETE FROM acl_ws" .
            " WHERE node_id = " . $ilDB->quote($a_node_id, "integer");

        if ($a_object_id) {
            $query .= " AND object_id = " . $ilDB->quote($a_object_id, "integer");
        }

        return $ilDB->manipulate($query);
    }

    /**
     * Get all permissions of node
     * @return int[]
     */
    public function getPermissions(int $a_node_id) : array
    {
        return self::_getPermissions($a_node_id);
    }
    
    /**
     * Get all permissions to node
     * @return int[]
     */
    public static function _getPermissions(int $a_node_id) : array // PHP8-Review: Method return type has no value type specified in iterable type array.
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilSetting = $DIC->settings();

        $publish_enabled = $ilSetting->get("enable_global_profiles");
        $publish_perm = array(ilWorkspaceAccessGUI::PERMISSION_ALL,
            ilWorkspaceAccessGUI::PERMISSION_ALL_PASSWORD);

        $set = $ilDB->query("SELECT object_id FROM acl_ws" .
            " WHERE node_id = " . $ilDB->quote($a_node_id, "integer"));
        $res = array();
        while ($row = $ilDB->fetchAssoc($set)) {
            if ($publish_enabled || !in_array($row["object_id"], $publish_perm)) {
                $res[] = (int) $row["object_id"];
            }
        }
        return $res;
    }

    public function hasRegisteredPermission(int $a_node_id) : bool
    {
        $ilDB = $this->db;

        $set = $ilDB->query("SELECT object_id FROM acl_ws" .
            " WHERE node_id = " . $ilDB->quote($a_node_id, "integer") .
            " AND object_id = " . $ilDB->quote(ilWorkspaceAccessGUI::PERMISSION_REGISTERED, "integer"));
        return (bool) $ilDB->numRows($set);
    }

    public function hasGlobalPermission(int $a_node_id) : bool
    {
        $ilDB = $this->db;
        $ilSetting = $this->settings;

        if (!$ilSetting->get("enable_global_profiles")) {
            return false;
        }

        $set = $ilDB->query("SELECT object_id FROM acl_ws" .
            " WHERE node_id = " . $ilDB->quote($a_node_id, "integer") .
            " AND object_id = " . $ilDB->quote(ilWorkspaceAccessGUI::PERMISSION_ALL, "integer"));
        return (bool) $ilDB->numRows($set);
    }

    public function hasGlobalPasswordPermission(int $a_node_id) : bool
    {
        $ilDB = $this->db;
        $ilSetting = $this->settings;

        if (!$ilSetting->get("enable_global_profiles")) {
            return false;
        }

        $set = $ilDB->query("SELECT object_id FROM acl_ws" .
            " WHERE node_id = " . $ilDB->quote($a_node_id, "integer") .
            " AND object_id = " . $ilDB->quote(ilWorkspaceAccessGUI::PERMISSION_ALL_PASSWORD, "integer"));
        return (bool) $ilDB->numRows($set);
    }

    public static function getPossibleSharedTargets() : array // PHP8-Review: Method return type has no value type specified in iterable type array.
    {
        global $DIC;

        $ilUser = $DIC->user();
        $ilSetting = $DIC->settings();

        $grp_ids = ilParticipants::_getMembershipByType($ilUser->getId(), ["grp"]);
        $crs_ids = ilParticipants::_getMembershipByType($ilUser->getId(), ["crs"]);

        $obj_ids = array_merge($grp_ids, $crs_ids);
        $obj_ids[] = $ilUser->getId();
        $obj_ids[] = ilWorkspaceAccessGUI::PERMISSION_REGISTERED;

        if ($ilSetting->get("enable_global_profiles")) {
            $obj_ids[] = ilWorkspaceAccessGUI::PERMISSION_ALL;
            $obj_ids[] = ilWorkspaceAccessGUI::PERMISSION_ALL_PASSWORD;
        }

        return $obj_ids;
    }

    public function getSharedOwners() : array // PHP8-Review: Method return type has no value type specified in iterable type array.
    {
        $ilUser = $this->user;
        $ilDB = $this->db;

        $obj_ids = $this->getPossibleSharedTargets();

        $user_ids = array();
        $set = $ilDB->query("SELECT DISTINCT(obj.owner), u.lastname, u.firstname, u.title" .
            " FROM object_data obj" .
            " JOIN object_reference_ws ref ON (obj.obj_id = ref.obj_id)" .
            " JOIN tree_workspace tree ON (tree.child = ref.wsp_id)" .
            " JOIN acl_ws acl ON (acl.node_id = tree.child)" .
            " JOIN usr_data u on (u.usr_id = obj.owner)" .
            " WHERE " . $ilDB->in("acl.object_id", $obj_ids, "", "integer") . // PHP8-Review: Parameter #3 $negate of method ilDBInterface::in() expects bool, string given.
            " AND obj.owner <> " . $ilDB->quote($ilUser->getId(), "integer") .
            " ORDER BY u.lastname, u.firstname, u.title");
        while ($row = $ilDB->fetchAssoc($set)) {
            $user_ids[$row["owner"]] = $row["lastname"] . ", " . $row["firstname"];
            if ($row["title"]) {
                $user_ids[$row["owner"]] .= ", " . $row["title"];
            }
        }

        return $user_ids;
    }

    public function getSharedObjects(int $a_owner_id) : array // PHP8-Review: Method return type has no value type specified in iterable type array.
    {
        $ilDB = $this->db;

        $obj_ids = $this->getPossibleSharedTargets();

        $res = array();
        $set = $ilDB->query("SELECT ref.wsp_id,obj.obj_id" .
            " FROM object_data obj" .
            " JOIN object_reference_ws ref ON (obj.obj_id = ref.obj_id)" .
            " JOIN tree_workspace tree ON (tree.child = ref.wsp_id)" .
            " JOIN acl_ws acl ON (acl.node_id = tree.child)" .
            " WHERE " . $ilDB->in("acl.object_id", $obj_ids, "", "integer") . // PHP8-Review: Parameter #3 $negate of method ilDBInterface::in() expects bool, string given.
            " AND obj.owner = " . $ilDB->quote($a_owner_id, "integer"));
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[$row["wsp_id"]] = $row["obj_id"];
        }

        return $res;
    }

    public function findSharedObjects(// PHP8-Review: Method return type and parameters have no value type specified in iterable type array.
        array $a_filter = null,
        array $a_crs_ids = null,
        array $a_grp_ids = null
    ) : array {
        $ilDB = $this->db;
        $ilUser = $this->user;
        $obj_ids = [];
        if (!$a_filter["acl_type"]) {
            $obj_ids = $this->getPossibleSharedTargets();
        } else {
            switch ($a_filter["acl_type"]) {
                case "all":
                    $obj_ids = array(ilWorkspaceAccessGUI::PERMISSION_ALL);
                    break;

                case "password":
                    $obj_ids = array(ilWorkspaceAccessGUI::PERMISSION_ALL_PASSWORD);
                    break;

                case "registered":
                    $obj_ids = array(ilWorkspaceAccessGUI::PERMISSION_REGISTERED);
                    break;

                case "course":
                    $obj_ids = $a_crs_ids;
                    break;

                case "group":
                    $obj_ids = $a_grp_ids;
                    break;

                case "user":
                    $obj_ids = array($ilUser->getId());
                    break;
            }
        }

        $res = array();

        $sql = "SELECT ref.wsp_id,obj.obj_id,obj.type,obj.title,obj.owner," .
            "acl.object_id acl_type, acl.tstamp acl_date" .
            " FROM object_data obj" .
            " JOIN object_reference_ws ref ON (obj.obj_id = ref.obj_id)" .
            " JOIN tree_workspace tree ON (tree.child = ref.wsp_id)" .
            " JOIN acl_ws acl ON (acl.node_id = tree.child)" .
            " WHERE " . $ilDB->in("acl.object_id", $obj_ids, "", "integer") .
            " AND obj.owner <> " . $ilDB->quote($ilUser->getId(), "integer");

        if ($a_filter["obj_type"]) {
            $sql .= " AND obj.type = " . $ilDB->quote($a_filter["obj_type"], "text");
        }
        if ($a_filter["title"] && strlen($a_filter["title"]) >= 3) {
            $sql .= " AND " . $ilDB->like("obj.title", "text", "%" . $a_filter["title"] . "%");
        }
        if ($a_filter["user"] && strlen($a_filter["user"]) >= 3) {
            $usr_ids = array();
            $set = $ilDB->query("SELECT usr_id FROM usr_data" .
                " WHERE (" . $ilDB->like("login", "text", "%" . $a_filter["user"] . "%") . " " .
                "OR " . $ilDB->like("firstname", "text", "%" . $a_filter["user"] . "%") . " " .
                "OR " . $ilDB->like("lastname", "text", "%" . $a_filter["user"] . "%") . " " .
                "OR " . $ilDB->like("email", "text", "%" . $a_filter["user"] . "%") . ")");
            while ($row = $ilDB->fetchAssoc($set)) {
                $usr_ids[] = $row["usr_id"];
            }
            if (!sizeof($usr_ids)) {
                return [];
            }
            $sql .= " AND " . $ilDB->in("obj.owner", $usr_ids, "", "integer"); // PHP8-Review: Parameter #3 $negate of method ilDBInterface::in() expects bool, string given.
        }

        if ($a_filter["acl_date"]) {
            $dt = $a_filter["acl_date"]->get(IL_CAL_DATE);
            $dt = new ilDateTime($dt . " 00:00:00", IL_CAL_DATETIME);
            $sql .= " AND acl.tstamp > " . $ilDB->quote($dt->get(IL_CAL_UNIX), "integer");
        }

        if ($a_filter["crsgrp"]) {
            $part = ilParticipants::getInstanceByObjId($a_filter['crsgrp']);
            $part = $part->getParticipants();
            if (!sizeof($part)) {
                return [];
            }
            $sql .= " AND " . $ilDB->in("obj.owner", $part, "", "integer");
        }

        // we use the oldest share date
        $sql .= " ORDER BY acl.tstamp";

        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            if (!isset($res[$row["wsp_id"]])) {
                $row["acl_type"] = array($row["acl_type"]);
                $res[$row["wsp_id"]] = $row;
            } else {
                $res[$row["wsp_id"]]["acl_type"][] = $row["acl_type"];
            }
        }

        return $res;
    }

    public static function getSharedNodePassword(int $a_node_id) : string
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT * FROM acl_ws" .
            " WHERE node_id = " . $ilDB->quote($a_node_id, "integer") .
            " AND object_id = " . $ilDB->quote(ilWorkspaceAccessGUI::PERMISSION_ALL_PASSWORD, "integer"));
        $res = $ilDB->fetchAssoc($set);
        if ($res) {
            return $res["extended_data"];
        }
        return "";
    }

    public static function keepSharedSessionPassword(int $a_node_id, string $a_password) : void
    {
        ilSession::set("ilshpw_" . $a_node_id, $a_password);
    }

    public static function getSharedSessionPassword(int $a_node_id) : string
    {
        return ilSession::get("ilshpw_" . $a_node_id);
    }

    public static function getGotoLink(int $a_node_id, int $a_obj_id, string $a_additional = "") : string
    {
        return ilLink::_getStaticLink($a_node_id, ilObject::_lookupType($a_obj_id), true, $a_additional . "_wsp");
    }

    public function getObjectsIShare() : array // PHP8-Review: Method return type has no value type specified in iterable type array.
    {
        $ilDB = $this->db;
        $ilUser = $this->user;

        $res = array();
        $set = $ilDB->query("SELECT ref.wsp_id,obj.obj_id" .
            " FROM object_data obj" .
            " JOIN object_reference_ws ref ON (obj.obj_id = ref.obj_id)" .
            " JOIN tree_workspace tree ON (tree.child = ref.wsp_id)" .
            " JOIN acl_ws acl ON (acl.node_id = tree.child)" .
            " WHERE obj.owner = " . $ilDB->quote($ilUser->getId(), "integer"));
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[$row["wsp_id"]] = $row["obj_id"];
        }

        return $res;
    }

    public static function getObjectDataFromNode(int $a_node_id) : ?array  // PHP8-Review: Method return type has no value type specified in iterable type array.
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT obj.obj_id, obj.type, obj.title" .
            " FROM object_reference_ws ref" .
            " JOIN tree_workspace tree ON (tree.child = ref.wsp_id)" .
            " JOIN object_data obj ON (ref.obj_id = obj.obj_id)" .
            " WHERE ref.wsp_id = " . $ilDB->quote($a_node_id, "integer"));
        return $ilDB->fetchAssoc($set);
    }
}
