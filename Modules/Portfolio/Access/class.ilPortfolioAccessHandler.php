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

use ILIAS\Portfolio\Access\AccessSessionRepository;

/**
 * Access handler for portfolio
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPortfolioAccessHandler implements ilWACCheckingClass
{
    protected AccessSessionRepository $session_repo;
    protected ilLanguage $lng;
    protected ilObjUser $user;
    protected ilRbacReview $rbacreview;
    protected ilSetting $settings;
    protected ilDBInterface $db;
    protected ilAccessHandler $access;

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
        $this->session_repo = $DIC->portfolio()
            ->internal()
            ->repo()
            ->accessSession();
    }

    /**
     * check access for an object
     */
    public function checkAccess(
        string $a_permission,
        string $a_cmd,
        int $a_node_id,
        string $a_type = ""
    ): bool {
        $ilUser = $this->user;

        return $this->checkAccessOfUser($ilUser->getId(), $a_permission, $a_cmd, $a_node_id, $a_type);
    }

    /**
     * check access for an object
     */
    public function checkAccessOfUser(
        int $a_user_id,
        string $a_permission,
        string $a_cmd,
        int $a_node_id,
        string $a_type = ""
    ): bool {
        $rbacreview = $this->rbacreview;
        $ilUser = $this->user;
        $ilSetting = $this->settings;

        // #20310
        if (!$ilSetting->get("enable_global_profiles") && $ilUser->getId() === ANONYMOUS_USER_ID) {
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
        if ($pf->getOwner() === $a_user_id) {
            return true;
        }

        // #11921
        if (!$pf->isOnline()) {
            return false;
        }

        // other users can only read
        if ($a_permission === "read" || $a_permission === "visible") {
            // get all objects with explicit permission
            $objects = self::_getPermissions($a_node_id);
            if ($objects) {
                // check if given user is member of object or has role
                foreach ($objects as $obj_id) {
                    switch ($obj_id) {
                        case ilWorkspaceAccessGUI::PERMISSION_ALL:
                            return true;

                        case ilWorkspaceAccessGUI::PERMISSION_ALL_PASSWORD:
                            // check against input kept in session
                            if (self::getSharedNodePassword($a_node_id) === self::getSharedSessionPassword($a_node_id) ||
                                $a_permission === "visible") {
                                return true;
                            }
                            break;

                        case ilWorkspaceAccessGUI::PERMISSION_REGISTERED:
                            if ($ilUser->getId() !== ANONYMOUS_USER_ID) {
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
    public function setPermissions(
        int $a_parent_node_id,
        int $a_node_id
    ): void {
        // nothing to do as owner has irrefutable rights to any portfolio object
    }

    /**
     * Add permission to node for object
     */
    public function addPermission(
        int $a_node_id,
        int $a_object_id,
        string $a_extended_data = null
    ): void {
        $ilDB = $this->db;
        $ilUser = $this->user;

        // current owner must not be added
        if ($a_object_id === $ilUser->getId()) {
            return;
        }

        $ilDB->replace(
            "usr_portf_acl",
            [
                "node_id" => ["integer", $a_node_id],
                "object_id" => ["integer", $a_object_id]
            ],
            [
                "extended_data" => ["text", $a_extended_data],
                "tstamp" => ["integer", time()]
            ]
        );

        // portfolio as profile
        $this->syncProfile($a_node_id);
    }

    /**
     * Remove permission[s] (for object) to node
     */
    public function removePermission(
        int $a_node_id,
        int $a_object_id = null
    ): void {
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
     */
    public function getPermissions(int $a_node_id): array
    {
        return self::_getPermissions($a_node_id);
    }

    /**
     * Get all permissions to node
     */
    public static function _getPermissions(
        int $a_node_id
    ): array {
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

    public function hasRegisteredPermission(
        int $a_node_id
    ): bool {
        $ilDB = $this->db;

        $set = $ilDB->query("SELECT object_id FROM usr_portf_acl" .
            " WHERE node_id = " . $ilDB->quote($a_node_id, "integer") .
            " AND object_id = " . $ilDB->quote(ilWorkspaceAccessGUI::PERMISSION_REGISTERED, "integer"));
        return (bool) $ilDB->numRows($set);
    }

    public function hasGlobalPermission(
        int $a_node_id
    ): bool {
        $ilDB = $this->db;

        $set = $ilDB->query("SELECT object_id FROM usr_portf_acl" .
            " WHERE node_id = " . $ilDB->quote($a_node_id, "integer") .
            " AND object_id = " . $ilDB->quote(ilWorkspaceAccessGUI::PERMISSION_ALL, "integer"));
        return (bool) $ilDB->numRows($set);
    }

    public function hasGlobalPasswordPermission(
        int $a_node_id
    ): bool {
        $ilDB = $this->db;

        $set = $ilDB->query("SELECT object_id FROM usr_portf_acl" .
            " WHERE node_id = " . $ilDB->quote($a_node_id, "integer") .
            " AND object_id = " . $ilDB->quote(ilWorkspaceAccessGUI::PERMISSION_ALL_PASSWORD, "integer"));
        return (bool) $ilDB->numRows($set);
    }

    public function getObjectsIShare(
        bool $a_online_only = true
    ): array {
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

    public static function getPossibleSharedTargets(): array
    {
        global $DIC;

        $ilUser = $DIC->user();

        $grp_ids = ilParticipants::_getMembershipByType($ilUser->getId(), ["grp"]);
        $crs_ids = ilParticipants::_getMembershipByType($ilUser->getId(), ["crs"]);

        $obj_ids = array_merge($grp_ids, $crs_ids);
        $obj_ids[] = $ilUser->getId();
        $obj_ids[] = ilWorkspaceAccessGUI::PERMISSION_REGISTERED;
        $obj_ids[] = ilWorkspaceAccessGUI::PERMISSION_ALL;
        $obj_ids[] = ilWorkspaceAccessGUI::PERMISSION_ALL_PASSWORD;

        return $obj_ids;
    }

    public function getSharedOwners(): array
    {
        $ilUser = $this->user;
        $ilDB = $this->db;

        $obj_ids = self::getPossibleSharedTargets();

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

    public function getSharedObjects(
        int $a_owner_id
    ): array {
        $ilDB = $this->db;

        $obj_ids = self::getPossibleSharedTargets();

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

    public function getShardObjectsDataForUserIds(
        array $a_owner_ids
    ): array {
        $ilDB = $this->db;

        $obj_ids = self::getPossibleSharedTargets();

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

    public function findSharedObjects(
        array $a_filter = null,
        array $a_crs_ids = null,
        array $a_grp_ids = null
    ): array {
        $ilDB = $this->db;
        $ilUser = $this->user;
        $obj_ids = [];

        if (!$a_filter["acl_type"]) {
            $obj_ids = self::getPossibleSharedTargets();
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
            if (!count($usr_ids)) {
                return [];
            }
            $sql .= " AND " . $ilDB->in("obj.owner", $usr_ids, "", "integer");
        }

        if ($a_filter["acl_date"]) {
            $dt = $a_filter["acl_date"]->get(IL_CAL_DATE);
            $dt = new ilDateTime($dt . " 00:00:00", IL_CAL_DATETIME);
            $sql .= " AND acl.tstamp > " . $ilDB->quote($dt->get(IL_CAL_UNIX), "integer");
        }

        if ($a_filter["crsgrp"]) {
            $part = ilParticipants::getInstanceByObjId($a_filter['crsgrp']);
            $part = $part->getParticipants();
            if (!count($part)) {
                return [];
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

    public static function getSharedNodePassword(
        int $a_node_id
    ): string {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT extended_data FROM usr_portf_acl" .
            " WHERE node_id = " . $ilDB->quote($a_node_id, "integer") .
            " AND object_id = " . $ilDB->quote(ilWorkspaceAccessGUI::PERMISSION_ALL_PASSWORD, "integer"));
        $res = $ilDB->fetchAssoc($set);
        if ($res) {
            return $res["extended_data"];
        }
        return "";
    }

    public static function keepSharedSessionPassword(
        int $a_node_id,
        string $a_password
    ): void {
        global $DIC;
        $session_repo = $DIC->portfolio()
                                  ->internal()
                                  ->repo()
                                  ->accessSession();
        $session_repo->setSharedSessionPassword($a_node_id, $a_password);
    }

    public static function getSharedSessionPassword(
        int $a_node_id
    ): string {
        global $DIC;
        $session_repo = $DIC->portfolio()
                            ->internal()
                            ->repo()
                            ->accessSession();
        return $session_repo->getSharedSessionPassword($a_node_id);
    }

    protected function syncProfile(
        int $a_node_id
    ): void {
        $ilUser = $this->user;

        // #12845
        if (ilObjPortfolio::getDefaultPortfolio($ilUser->getId()) === $a_node_id) {
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
                if ($ilUser->getPref("public_profile") !== $new_pref) {
                    $ilUser->setPref("public_profile", $new_pref);
                    $ilUser->writePrefs();
                }
            }
        }
    }


    /**
     * WAC check
     */
    public function canBeDelivered(
        ilWACPath $ilWACPath
    ): bool {
        $ilUser = $this->user;
        $ilAccess = $this->access;
        if (preg_match("/\\/prtf_([\\d]+)\\//uim", $ilWACPath->getPath(), $results)) {
            // portfolio (custom)
            $obj_id = $results[1];
            if (ilObject::_lookupType($obj_id) === "prtf") {
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

    /**
     * Is portfolio editing (general feature) enabled
     */
    public function editPortfolios(): bool
    {
        return (bool) $this->settings->get('user_portfolios');
    }
}
