<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Modules/Portfolio/classes/class.ilObjPortfolio.php";
include_once "Modules/Group/classes/class.ilGroupParticipants.php";
include_once "Modules/Course/classes/class.ilCourseParticipants.php";
include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessGUI.php";
require_once('./Services/WebAccessChecker/interfaces/interface.ilWACCheckingClass.php');

/**
 * Access handler for portfolio
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.ilPersonalDesktopGUI.php 26976 2010-12-16 13:24:38Z akill $
 *
 * @ingroup ModulesPortfolio
 */
class ilPortfolioAccessHandler implements ilWACCheckingClass
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilRbacReview
     */
    protected $rbacreview;

    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var ilDB
     */
    protected $db;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->rbacreview = $DIC->rbac()->review();
        $this->settings = $DIC->settings();
        $this->db = $DIC->database();
        $this->access = $DIC->access();
        $lng = $DIC->language();
        $lng->loadLanguageModule("wsp");
    }

    /**
     * check access for an object
     *
     * @param	string		$a_permission
     * @param	string		$a_cmd
     * @param	int			$a_node_id
     * @param	string		$a_type (optional)
     * @return	bool
     */
    public function checkAccess($a_permission, $a_cmd, $a_node_id, $a_type = "")
    {
        $ilUser = $this->user;

        return $this->checkAccessOfUser($ilUser->getId(), $a_permission, $a_cmd, $a_node_id, $a_type);
    }

    /**
     * check access for an object
     *
     * @param	integer		$a_user_id
     * @param	string		$a_permission
     * @param	string		$a_cmd
     * @param	int			$a_node_id
     * @param	string		$a_type (optional)
     * @return	bool
     */
    public function checkAccessOfUser($a_user_id, $a_permission, $a_cmd, $a_node_id, $a_type = "")
    {
        $rbacreview = $this->rbacreview;
        $ilUser = $this->user;
        $ilSetting = $this->settings;

        // #20310
        if (!$ilSetting->get("enable_global_profiles") && $ilUser->getId() == ANONYMOUS_USER_ID) {
            return false;
        }

        // #12059
        if (!$ilSetting->get('user_portfolios')) {
            return false;
        }

        // :TODO: create permission for parent node with type ?!
        
        $pf = new ilObjPortfolio($a_node_id, false);
        if (!$pf->getId()) {
            return false;
        }
        
        // portfolio owner has all rights
        if ($pf->getOwner() == $a_user_id) {
            return true;
        }
        
        // #11921
        if (!$pf->isOnline()) {
            return false;
        }

        // other users can only read
        if ($a_permission == "read" || $a_permission == "visible") {
            // get all objects with explicit permission
            $objects = self::_getPermissions($a_node_id);
            if ($objects) {
                include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessGUI.php";
                
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
     *
     * @param int $a_parent_node_id
     * @param int $a_node_id
     */
    public function setPermissions($a_parent_node_id, $a_node_id)
    {
        // nothing to do as owner has irrefutable rights to any portfolio object
    }

    /**
     * Add permission to node for object
     *
     * @param int $a_node_id
     * @param int $a_object_id
     * @param string $a_extended_data
     */
    public function addPermission($a_node_id, $a_object_id, $a_extended_data = null)
    {
        $ilDB = $this->db;
        $ilUser = $this->user;

        // current owner must not be added
        if ($a_object_id == $ilUser->getId()) {
            return;
        }

        $ilDB->manipulate("INSERT INTO usr_portf_acl (node_id, object_id, extended_data, tstamp)" .
            " VALUES (" . $ilDB->quote($a_node_id, "integer") . ", " .
            $ilDB->quote($a_object_id, "integer") . "," .
            $ilDB->quote($a_extended_data, "text") . "," .
            $ilDB->quote(time(), "integer") . ")");
        
        // portfolio as profile
        $this->syncProfile($a_node_id);
    }

    /**
     * Remove permission[s] (for object) to node
     *
     * @param int $a_node_id
     * @param int $a_object_id
     */
    public function removePermission($a_node_id, $a_object_id = null)
    {
        $ilDB = $this->db;
        
        $query = "DELETE FROM usr_portf_acl" .
            " WHERE node_id = " . $ilDB->quote($a_node_id, "integer");

        if ($a_object_id) {
            $query .= " AND object_id = " . $ilDB->quote($a_object_id, "integer");
        }

        $ilDB->manipulate($query);
        
        // portfolio as profile
        $this->syncProfile($a_node_id);
    }

    /**
     * Get all permissions to node
     *
     * @param int $a_node_id
     * @return array
     */
    public function getPermissions($a_node_id)
    {
        return self::_getPermissions($a_node_id);
    }

    /**
     * Get all permissions to node
     *
     * @param int $a_node_id
     * @return array
     */
    public static function _getPermissions($a_node_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT object_id FROM usr_portf_acl" .
            " WHERE node_id = " . $ilDB->quote($a_node_id, "integer"));
        $res = array();
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[] = $row["object_id"];
        }
        return $res;
    }
    
    public function hasRegisteredPermission($a_node_id)
    {
        $ilDB = $this->db;

        $set = $ilDB->query("SELECT object_id FROM usr_portf_acl" .
            " WHERE node_id = " . $ilDB->quote($a_node_id, "integer") .
            " AND object_id = " . $ilDB->quote(ilWorkspaceAccessGUI::PERMISSION_REGISTERED, "integer"));
        return (bool) $ilDB->numRows($set);
    }
    
    public function hasGlobalPermission($a_node_id)
    {
        $ilDB = $this->db;

        $set = $ilDB->query("SELECT object_id FROM usr_portf_acl" .
            " WHERE node_id = " . $ilDB->quote($a_node_id, "integer") .
            " AND object_id = " . $ilDB->quote(ilWorkspaceAccessGUI::PERMISSION_ALL, "integer"));
        return (bool) $ilDB->numRows($set);
    }
    
    public function hasGlobalPasswordPermission($a_node_id)
    {
        $ilDB = $this->db;

        $set = $ilDB->query("SELECT object_id FROM usr_portf_acl" .
            " WHERE node_id = " . $ilDB->quote($a_node_id, "integer") .
            " AND object_id = " . $ilDB->quote(ilWorkspaceAccessGUI::PERMISSION_ALL_PASSWORD, "integer"));
        return (bool) $ilDB->numRows($set);
    }
    
    public function getObjectsIShare($a_online_only = true)
    {
        $ilDB = $this->db;
        $ilUser = $this->user;
        
        $res = array();
        
        $sql = "SELECT obj.obj_id" .
            " FROM object_data obj" .
            " JOIN usr_portfolio prtf ON (prtf.id = obj.obj_id)" .
            " JOIN usr_portf_acl acl ON (acl.node_id = obj.obj_id)" .
            " WHERE obj.owner = " . $ilDB->quote($ilUser->getId(), "integer");
        
        if ($a_online_only) {
            $sql .= " AND prtf.is_online = " . $ilDB->quote(1, "integer");
        }
        
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[] = $row["obj_id"];
        }
        
        return $res;
    }
    
    public static function getPossibleSharedTargets()
    {
        global $DIC;

        $ilUser = $DIC->user();
        
        include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessGUI.php";
        include_once "Services/Membership/classes/class.ilParticipants.php";
        $grp_ids = ilParticipants::_getMembershipByType($ilUser->getId(), "grp");
        $crs_ids = ilParticipants::_getMembershipByType($ilUser->getId(), "crs");
        
        $obj_ids = array_merge($grp_ids, $crs_ids);
        $obj_ids[] = $ilUser->getId();
        $obj_ids[] = ilWorkspaceAccessGUI::PERMISSION_REGISTERED;
        $obj_ids[] = ilWorkspaceAccessGUI::PERMISSION_ALL;
        $obj_ids[] = ilWorkspaceAccessGUI::PERMISSION_ALL_PASSWORD;

        return $obj_ids;
    }
    
    public function getSharedOwners()
    {
        $ilUser = $this->user;
        $ilDB = $this->db;
        
        $obj_ids = $this->getPossibleSharedTargets();
        
        $user_ids = array();
        $set = $ilDB->query("SELECT DISTINCT(obj.owner), u.lastname, u.firstname, u.title" .
            " FROM object_data obj" .
            " JOIN usr_portfolio prtf ON (prtf.id = obj.obj_id)" .
            " JOIN usr_portf_acl acl ON (acl.node_id = obj.obj_id)" .
            " JOIN usr_data u on (u.usr_id = obj.owner)" .
            " WHERE " . $ilDB->in("acl.object_id", $obj_ids, "", "integer") .
            " AND obj.owner <> " . $ilDB->quote($ilUser->getId(), "integer") .
            " AND prtf.is_online = " . $ilDB->quote(1, "integer") .
            " ORDER BY u.lastname, u.firstname, u.title");
        while ($row = $ilDB->fetchAssoc($set)) {
            $user_ids[$row["owner"]] = $row["lastname"] . ", " . $row["firstname"];
            if ($row["title"]) {
                $user_ids[$row["owner"]] .= ", " . $row["title"];
            }
        }
        
        return $user_ids;
    }
    
    public function getSharedObjects($a_owner_id)
    {
        $ilDB = $this->db;
        
        $obj_ids = $this->getPossibleSharedTargets();
        
        $res = array();
        $set = $ilDB->query("SELECT obj.obj_id, obj.owner" .
            " FROM object_data obj" .
            " JOIN usr_portfolio prtf ON (prtf.id = obj.obj_id)" .
            " JOIN usr_portf_acl acl ON (acl.node_id = obj.obj_id)" .
            " WHERE " . $ilDB->in("acl.object_id", $obj_ids, "", "integer") .
            " AND obj.owner = " . $ilDB->quote($a_owner_id, "integer") .
            " AND prtf.is_online = " . $ilDB->quote(1, "integer"));
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[$row["obj_id"]] = $row["obj_id"];
        }
    
        return $res;
    }
    
    public function getShardObjectsDataForUserIds(array $a_owner_ids)
    {
        $ilDB = $this->db;
        
        $obj_ids = $this->getPossibleSharedTargets();
        
        $res = array();
        
        $set = $ilDB->query("SELECT obj.obj_id, obj.owner, obj.title" .
            " FROM object_data obj" .
            " JOIN usr_portfolio prtf ON (prtf.id = obj.obj_id)" .
            " JOIN usr_portf_acl acl ON (acl.node_id = obj.obj_id)" .
            " WHERE " . $ilDB->in("acl.object_id", $obj_ids, "", "integer") .
            " AND " . $ilDB->in("obj.owner", $a_owner_ids, "", "integer") .
            " AND prtf.is_online = " . $ilDB->quote(1, "integer"));
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[$row["owner"]][$row["obj_id"]] = $row["title"];
        }
    
        return $res;
    }
    
    public function findSharedObjects(array $a_filter = null, array $a_crs_ids = null, array $a_grp_ids = null)
    {
        $ilDB = $this->db;
        $ilUser = $this->user;
        if (!$a_filter["acl_type"]) {
            $obj_ids = $this->getPossibleSharedTargets();
        } else {
            include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessGUI.php";
            
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
        
        $sql = "SELECT obj.obj_id,obj.title,obj.owner" .
            ",acl.object_id acl_type, acl.tstamp acl_date" .
            " FROM object_data obj" .
            " JOIN usr_portfolio prtf ON (prtf.id = obj.obj_id)" .
            " JOIN usr_portf_acl acl ON (acl.node_id = obj.obj_id)" .
            " WHERE " . $ilDB->in("acl.object_id", $obj_ids, "", "integer") .
            " AND obj.owner <> " . $ilDB->quote($ilUser->getId(), "integer") .
            " AND obj.type = " . $ilDB->quote("prtf", "text") .
            " AND prtf.is_online = " . $ilDB->quote(1, "integer");
        
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
                return;
            }
            $sql .= " AND " . $ilDB->in("obj.owner", $usr_ids, "", "integer");
        }
        
        if ($a_filter["acl_date"]) {
            $dt = $a_filter["acl_date"]->get(IL_CAL_DATE);
            $dt = new ilDateTime($dt . " 00:00:00", IL_CAL_DATETIME);
            $sql .= " AND acl.tstamp > " . $ilDB->quote($dt->get(IL_CAL_UNIX), "integer");
        }
        
        if ($a_filter["crsgrp"]) {
            include_once "Services/Membership/classes/class.ilParticipants.php";
            $part = ilParticipants::getInstanceByObjId($a_filter['crsgrp']);
            $part = $part->getParticipants();
            if (!sizeof($part)) {
                return;
            }
            $sql .= " AND " . $ilDB->in("obj.owner", $part, "", "integer");
        }
    
        // we use the oldest share date
        $sql .= " ORDER BY acl.tstamp";
            
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            if (!isset($res[$row["obj_id"]])) {
                $row["acl_type"] = array($row["acl_type"]);
                $res[$row["obj_id"]] = $row;
            } else {
                $res[$row["obj_id"]]["acl_type"][] = $row["acl_type"];
            }
        }
    
        return $res;
    }
    
    public static function getSharedNodePassword($a_node_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessGUI.php";
        
        $set = $ilDB->query("SELECT extended_data FROM usr_portf_acl" .
            " WHERE node_id = " . $ilDB->quote($a_node_id, "integer") .
            " AND object_id = " . $ilDB->quote(ilWorkspaceAccessGUI::PERMISSION_ALL_PASSWORD, "integer"));
        $res = $ilDB->fetchAssoc($set);
        if ($res) {
            return $res["extended_data"];
        }
    }
    
    public static function keepSharedSessionPassword($a_node_id, $a_password)
    {
        $_SESSION["ilshpw_" . $a_node_id] = $a_password;
    }
    
    public static function getSharedSessionPassword($a_node_id)
    {
        return $_SESSION["ilshpw_" . $a_node_id];
    }
    
    protected function syncProfile($a_node_id)
    {
        $ilUser = $this->user;
        
        // #12845
        include_once "Modules/Portfolio/classes/class.ilObjPortfolio.php";
        if (ilObjPortfolio::getDefaultPortfolio($ilUser->getId()) == $a_node_id) {
            $has_registered = $this->hasRegisteredPermission($a_node_id);
            $has_global = $this->hasGlobalPermission($a_node_id);
            
            // not published anymore - remove portfolio as profile
            if (!$has_registered && !$has_global) {
                $ilUser->setPref("public_profile", "n");
                $ilUser->writePrefs();
                ilObjPortfolio::setUserDefault($ilUser->getId());
            }
            // adapt profile setting
            else {
                $new_pref = "y";
                if ($has_global) {
                    $new_pref = "g";
                }
                if ($ilUser->getPref("public_profile") != $new_pref) {
                    $ilUser->setPref("public_profile", $new_pref);
                    $ilUser->writePrefs();
                }
            }
        }
    }


    /**
     * @param ilWACPath $ilWACPath
     *
     * @return bool
     */
    public function canBeDelivered(ilWACPath $ilWACPath)
    {
        $ilUser = $this->user;
        $ilAccess = $this->access;
                
        if (preg_match("/\\/prtf_([\\d]*)\\//uism", $ilWACPath->getPath(), $results)) {
            // portfolio (custom)
            $obj_id = $results[1];
            if (ilObject::_lookupType($obj_id) == "prtf") {
                if ($this->checkAccessOfUser($ilUser->getId(), "read", "view", $obj_id, "prtf")) {
                    return true;
                }
            }
            // portfolio template (RBAC)
            else {
                $ref_ids = ilObject::_getAllReferences($obj_id);
                foreach ($ref_ids as $ref_id) {
                    if ($ilAccess->checkAccessOfUser($ilUser->getId(), "read", "view", $ref_id, "prtt", $obj_id)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
