<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjOrgUnitTree
 * Implements a singleton pattern for caching.
 *
 * @author: Oskar Truffer <ot@studer-raimann.ch>
 * @author: Martin Studer <ms@studer-raimann.ch>
 *
 */
class ilObjOrgUnitTree
{

    /**
     * @var null
     */
    protected static $temporary_table_name = null;
    /**
     * @var  ilObjOrgUnitTree
     */
    private static $instance;
    /**
     * @var  int[][] "employee" | "superior" => orgu ref id => role id
     */
    private $roles;
    /**
     * @var  int[][] "employee" | "superior" => role id => orgu ref id
     */
    private $role_to_orgu;
    /**
     * @var  int[][][] "employee" | "superior" => orgu ref id =>  array(obj_id of users)
     */
    private $staff;
    /**
     * @var  int[][] org_unit ref id => childrens org_unit ref ids.
     */
    private $tree_childs;
    /**
     * @var  int[] orgu_ref => parent_ref
     */
    private $parent;
    /**
     * @var ilDB
     */
    private $db;


    private function __construct()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $tree = $DIC['tree'];
        $this->db = $ilDB;
        $this->tree = $tree;
        $this->roles = array();
        $this->staff = array();
    }


    /**
     * @return \ilObjOrgUnitTree
     */
    public static function _getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    /**
     * @param $ref_id    int the reference id of the organisational unit.
     * @param $recursive bool if true you get the ids of the subsequent orgunits employees too
     *
     * @return int[] array of user ids.
     */
    public function getEmployees($ref_id, $recursive = false)
    {
        $arr_usr_ids = [];

        switch ($recursive) {
            case false:
                $arr_usr_ids = $this->getAssignements($ref_id, ilOrgUnitPosition::getCorePosition(ilOrgUnitPosition::CORE_POSITION_EMPLOYEE));
                break;
            case true:
                foreach ($this->getAllChildren($ref_id) as $ref_id) {
                    $arr_usr_ids = $arr_usr_ids
                        + $this->getAssignements($ref_id, ilOrgUnitPosition::getCorePosition(ilOrgUnitPosition::CORE_POSITION_EMPLOYEE));
                }
                break;
        }

        return $arr_usr_ids;
    }


    /**
     * @param          int       $ref_id
     * @param \ilOrgUnitPosition $ilOrgUnitPosition
     *
     * @return array
     */
    public function getAssignements($ref_id, ilOrgUnitPosition $ilOrgUnitPosition)
    {
        return ilOrgUnitUserAssignment::where(array(
            'orgu_id' => $ref_id,
            'position_id' => $ilOrgUnitPosition->getId(),
        ))->getArray('id', 'user_id');
    }


    /**
     * @param  $ref_id         int the reference id of the organisational unit.
     * @param  $recursive      bool if true you get the ids of the subsequent orgunits superiors too
     *
     * @return int[]  array of user ids.
     */
    public function getSuperiors($ref_id, $recursive = false)
    {
        $arr_usr_ids = [];

        switch ($recursive) {
            case false:
                $arr_usr_ids = $this->getAssignements($ref_id, ilOrgUnitPosition::getCorePosition(ilOrgUnitPosition::CORE_POSITION_SUPERIOR));
                break;
            case true:
                foreach ($this->getAllChildren($ref_id) as $ref_id) {
                    $arr_usr_ids = $arr_usr_ids
                        + $this->getAssignements($ref_id, ilOrgUnitPosition::getCorePosition(ilOrgUnitPosition::CORE_POSITION_SUPERIOR));
                }
                break;
        }

        return $arr_usr_ids;
    }


    /**
     * @param $title   "employee" or "superior"
     * @param $ref_ids int[] array of orgu object ref ids.
     *
     * @return int[] user_ids
     */
    private function loadArrayOfStaff($title, $ref_ids)
    {
        $this->loadRoles($title);
        $all_refs = $ref_ids;
        //take away ref_ids that are already loaded.
        foreach ($ref_ids as $id => $ref_id) {
            if (isset($this->staff[$title][$ref_id])) {
                unset($ref_ids[$id]);
            } else {
                $this->staff[$title][$ref_id] = array();
                $ref_ids[$id] = $this->roles[$title][$ref_id];
            }
        }

        //if there are still refs that need to be loaded, then do so.
        if (count($ref_ids)) {
            $q = "SELECT usr_id, rol_id FROM rbac_ua WHERE " . $this->db->in("rol_id", $ref_ids, false, "integer");
            $set = $this->db->query($q);
            while ($res = $this->db->fetchAssoc($set)) {
                $orgu_ref = $this->role_to_orgu[$title][$res["rol_id"]];
                $this->staff[$title][$orgu_ref][] = $res["usr_id"];
            }
        }

        //collect * users.
        $all_users = array();
        foreach ($all_refs as $ref) {
            $all_users = array_merge($all_users, $this->staff[$title][$ref]);
        }

        return $all_users;
    }


    /**
     * @param $ref_id
     *
     * @return array
     */
    public function getAllChildren($ref_id)
    {
        $open = array( $ref_id );
        $closed = array();
        while (count($open)) {
            $ref = array_pop($open);
            $closed[] = $ref;
            foreach ($this->getChildren($ref) as $child) {
                if (!in_array($child, $open) && !in_array($child, $closed)) {
                    $open[] = $child;
                }
            }
        }

        return $closed;
    }


    /**
     * If you want to have all orgunits where the current user has the write permission: use this
     * with the parameter "write".
     *
     * @param $operation string
     *
     * @return int[] ids of the org units.
     */
    public function getOrgusWhereUserHasPermissionForOperation($operation)
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        /*$q = "SELECT object_data.obj_id, object_reference.ref_id, object_data.title, object_data.type, rbac_pa.ops_id, rbac_operations.ops_id as op_id FROM object_data
        INNER JOIN rbac_operations ON rbac_operations.operation = ".$this->db->quote($operation, "text")."
        INNER JOIN rbac_ua ON rbac_ua.usr_id = ".$this->db->quote($ilUser->getId(), "integer")."
        INNER JOIN rbac_pa ON rbac_pa.rol_id = rbac_ua.rol_id AND rbac_pa.ops_id LIKE CONCAT('%', rbac_operations.ops_id, '%')
        INNER JOIN rbac_fa ON rbac_fa.rol_id = rbac_ua.rol_id
        INNER JOIN tree ON tree.child = rbac_fa.parent
        INNER JOIN object_reference ON object_reference.ref_id = tree.parent
        WHERE object_data.obj_id = object_reference.obj_id AND object_data.type = 'orgu'";*/

        $q = "SELECT object_data.obj_id, object_reference.ref_id, object_data.title, object_data.type, rbac_pa.ops_id, rbac_operations.ops_id as op_id FROM object_data
		INNER JOIN rbac_operations ON rbac_operations.operation = " . $this->db->quote($operation, "text") . "
		INNER JOIN rbac_ua ON rbac_ua.usr_id = " . $this->db->quote($ilUser->getId(), "integer") . "
		INNER JOIN rbac_pa ON rbac_pa.rol_id = rbac_ua.rol_id AND rbac_pa.ops_id LIKE CONCAT('%', rbac_operations.ops_id, '%')
		INNER JOIN object_reference ON object_reference.ref_id = rbac_pa.ref_id
		WHERE object_data.obj_id = object_reference.obj_id AND object_data.type = 'orgu'";

        $set = $this->db->query($q);
        $orgus = array();
        while ($res = $this->db->fetchAssoc($set)) {
            //this is needed as the table rbac_operations is not in the first normal form, thus this needs some additional checkings.
            $perm_check = unserialize($res['ops_id']);
            if (!in_array($res["op_id"], $perm_check)) {
                continue;
            }

            $orgus[] = $res["ref_id"];
        }

        return $orgus;
    }


    /**
     * If you want to have all orgunits where the current user has the write permission: use this
     * with the parameter 3 (3 is the "write" permission as in rbac_operations).
     *
     * @param $operation_id
     *
     * @return int[] ids of the org units.
     */
    public function getOrgusWhereUserHasPermissionForOperationId($operation_id)
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        $q = "SELECT object_data.obj_id, object_data.title, object_data.type, rbac_pa.ops_id FROM object_data
		INNER JOIN rbac_ua ON rbac_ua.usr_id = " . $this->db->quote($ilUser->getId(), "integer") . "
		INNER JOIN rbac_pa ON rbac_pa.rol_id = rbac_ua.rol_id AND rbac_pa.ops_id LIKE CONCAT('%', " . $this->db->quote($operation_id, "integer") . ", '%')
		INNER JOIN rbac_fa ON rbac_fa.rol_id = rbac_ua.rol_id
		INNER JOIN tree ON tree.child = rbac_fa.parent
		INNER JOIN object_reference ON object_reference.ref_id = tree.parent
		WHERE object_data.obj_id = object_reference.obj_id AND object_data.type = 'orgu'";

        $set = $this->db->query($q);
        $orgus = array();
        while ($res = $this->db->fetchAssoc($set)) {
            //this is needed as the table rbac_operations is not in the first normal form, thus this needs some additional checkings.
            $perm_check = unserialize($res['ops_id']);
            if (!in_array($res["ops_id"], $perm_check)) {
                continue;
            }

            $orgus[] = $res["obj_id"];
        }

        return $orgus;
    }


    /**
     * @param $ref_id
     *
     * @return int[]
     */
    private function getChildren($ref_id)
    {
        $this->loadChildren($ref_id);

        return $this->tree_childs[$ref_id];
    }


    /**
     * @param $ref_id
     */
    private function loadChildren($ref_id)
    {
        if (!$this->tree_childs[$ref_id]) {
            $children = array();
            foreach ($this->tree->getChilds($ref_id) as $child) {
                if ($child["type"] == "orgu") {
                    $children[] = $child["child"];
                }
            }
            $this->tree_childs[$ref_id] = $children;
        };
    }


    /**
     * @param $level
     *
     * @return array
     */
    public function getAllOrgunitsOnLevelX($level)
    {
        $levels = array( 0 => array( ilObjOrgUnit::getRootOrgRefId() ) );
        $current_level = 0;
        while ($current_level < $level) {
            $new_level = array();
            foreach ($levels[$current_level] as $orgu_ref) {
                $new_level = array_merge($this->getChildren($orgu_ref), $new_level);
            }
            $new_level = array_unique($new_level);
            $levels[$current_level + 1] = $new_level;
            $current_level++;
        }

        return $levels[$level];
    }


    /**
     * @param $user_id   int
     * @param $recursive bool if this is true subsequent orgunits of this users superior role get
     *                   searched as well.
     *
     * @return int[] returns an array of user_ids of the users which have an employee role in an
     *               orgunit of which this user's id has a superior role.
     */
    public function getEmployeesUnderUser($user_id, $recursive = true)
    {
        //querry for all orgu where user_id is superior.
        $q = "SELECT orgu.obj_id, refr.ref_id FROM object_data orgu
                INNER JOIN object_reference refr ON refr.obj_id = orgu.obj_id
				INNER JOIN object_data roles ON roles.title LIKE CONCAT('il_orgu_superior_',refr.ref_id)
				INNER JOIN rbac_ua rbac ON rbac.usr_id = " . $this->db->quote($user_id, "integer") . " AND roles.obj_id = rbac.rol_id
				WHERE orgu.type = 'orgu'";
        $set = $this->db->query($q);
        $orgu_ref_ids = array();
        while ($res = $this->db->fetchAssoc($set)) {
            $orgu_ref_ids[] = $res['ref_id'];
        }
        $employees = array();
        foreach ($orgu_ref_ids as $orgu_ref_id) {
            $employees = array_merge($employees, $this->getEmployees($orgu_ref_id, $recursive));
        }

        return $employees;
    }


    /**
     * @param $user_id   int
     * @param $recursive bool if this is true subsequent orgunits of this users superior role get
     *                   searched as well.
     *
     * @return int[] returns an array of user_ids of the users which have an employee role in an
     *               orgunit of which this user's id has a superior role.
     */
    public function getSuperiorsOfUser($user_id, $recursive = true)
    {
        //querry for all orgu where user_id is superior.
        $q = "SELECT orgu.obj_id, refr.ref_id FROM object_data orgu
                INNER JOIN object_reference refr ON refr.obj_id = orgu.obj_id
				INNER JOIN object_data roles ON roles.title LIKE CONCAT('il_orgu_employee_',refr.ref_id) OR roles.title LIKE CONCAT('il_orgu_superior_',refr.ref_id)
				INNER JOIN rbac_ua rbac ON rbac.usr_id = " . $this->db->quote($user_id, "integer") . " AND roles.obj_id = rbac.rol_id
				WHERE orgu.type = 'orgu'";
        $set = $this->db->query($q);
        $orgu_ref_ids = array();
        while ($res = $this->db->fetchAssoc($set)) {
            $orgu_ref_ids[] = $res['ref_id'];
        }
        $superiors = array();
        foreach ($orgu_ref_ids as $orgu_ref_id) {
            $superiors = array_merge($superiors, $this->getSuperiors($orgu_ref_id, $recursive));
        }

        return $superiors;
    }


    /**
     * for additional info see the other getLevelX method.
     *
     * @param $user_id
     * @param $level
     *
     * @return int[]
     */
    public function getLevelXOfUser($user_id, $level)
    {
        $q = "SELECT object_reference.ref_id FROM rbac_ua
				JOIN rbac_fa ON rbac_fa.rol_id = rbac_ua.rol_id
				JOIN object_reference ON rbac_fa.parent = object_reference.ref_id
				JOIN object_data ON object_data.obj_id = object_reference.obj_id
			WHERE rbac_ua.usr_id = " . $this->db->quote($user_id, 'integer') . " AND object_data.type = 'orgu';";

        $set = $this->db->query($q);
        $orgu_ref_ids = array();
        while ($res = $this->db->fetchAssoc($set)) {
            $orgu_ref_ids[] = $res['ref_id'];
        }
        $orgus_on_level_x = array();
        foreach ($orgu_ref_ids as $orgu_ref_id) {
            try {
                $orgus_on_level_x[] = $this->getLevelXOfTreenode($orgu_ref_id, $level);
            } catch (Exception $e) {
                // this means the user is assigned to a orgu above the given level. just dont add it to the list.
            }
        }

        return array_unique($orgus_on_level_x);
    }


    /**
     * getOrgUnitOfUser
     *
     * @param     $user_id
     * @param int $ref_id if given, only OrgUnits under this ID are returned (including $ref_id)
     *
     * @return int[]
     */
    public function getOrgUnitOfUser($user_id, $ref_id = 0)
    {
        $q = "SELECT object_reference.ref_id FROM rbac_ua
				JOIN rbac_fa ON rbac_fa.rol_id = rbac_ua.rol_id
				JOIN object_reference ON rbac_fa.parent = object_reference.ref_id
				JOIN object_data ON object_data.obj_id = object_reference.obj_id
			WHERE rbac_ua.usr_id = " . $this->db->quote($user_id, 'integer') . " AND object_data.type = 'orgu'";

        $set = $this->db->query($q);
        $orgu_ref_ids = array();
        while ($res = $this->db->fetchAssoc($set)) {
            $orgu_ref_ids[] = $res['ref_id'];
        }
        $orgu_ref_ids = array_unique($orgu_ref_ids);
        if ($ref_id) {
            $childernOrgIds = $this->getAllChildren($ref_id);
            foreach ($orgu_ref_ids as $k => $refId) {
                if (!in_array($refId, $childernOrgIds)) {
                    unset($orgu_ref_ids[$k]);
                }
            }
        }

        return $orgu_ref_ids;
    }


    /**
     * Creates a temporary table with all orgu/user assignements. there will be three columns in
     * the table orgu_usr_assignements (or specified table-name): ref_id: Reference-IDs of OrgUnits
     * user_id: Assigned User-IDs path: Path-representation of the OrgUnit
     *
     * Usage:
     * 1. Run ilObjOrgUnitTree::getInstance()->buildTempTableWithUsrAssignements(); in your code
     * 2. use the table orgu_usr_assignements for your JOINS ans SELECTS
     * 3. Run ilObjOrgUnitTree::getInstance()->dropTempTable(); to throw away the table
     *
     * @throws ilException
     *
     * @param string $temporary_table_name
     *
     * @return bool
     */
    public function buildTempTableWithUsrAssignements($temporary_table_name = 'orgu_usr_assignements')
    {
        if (self::$temporary_table_name == $temporary_table_name) {
            return true;
        }
        if (self::$temporary_table_name === null) {
            $this->dropTempTable($temporary_table_name);
            self::$temporary_table_name = $temporary_table_name;
        } elseif ($temporary_table_name != self::$temporary_table_name) {
            throw new ilException('there is already a temporary table for org-unit assignement: ' . self::$temporary_table_name);
        }

        $q = "CREATE TEMPORARY TABLE IF NOT EXISTS " . $temporary_table_name . " AS (
				SELECT DISTINCT object_reference.ref_id AS ref_id, il_orgu_ua.user_id AS user_id, orgu_path_storage.path AS path
					FROM il_orgu_ua
                    JOIN object_reference ON object_reference.ref_id = il_orgu_ua.orgu_id
					JOIN object_data ON object_data.obj_id = object_reference.obj_id
					JOIN orgu_path_storage ON orgu_path_storage.ref_id = object_reference.ref_id
				WHERE object_data.type = 'orgu' AND object_reference.deleted IS NULL
			);";
        $this->db->manipulate($q);

        return true;
    }


    /**
     * @param $temporary_table_name
     *
     * @return bool
     */
    public function dropTempTable($temporary_table_name)
    {
        if (self::$temporary_table_name === null
            || $temporary_table_name != self::$temporary_table_name) {
            return false;
        }
        $q = "DROP TABLE IF EXISTS " . $temporary_table_name;
        $this->db->manipulate($q);

        self::$temporary_table_name = null;

        return true;
    }


    /**
     * @param $org_refs
     *
     * @return array
     */
    public function getTitles($org_refs)
    {
        $names = array();
        foreach ($org_refs as $org_unit) {
            $names[$org_unit] = ilObject::_lookupTitle(ilObject::_lookupObjId($org_unit));
        }

        return $names;
    }


    /**
     * @return int[] returns an array of role_ids. orgu_ref => role_id
     */
    public function getEmployeeRoles()
    {
        $this->loadRoles("employee");

        return $this->roles["employee"];
    }


    /**
     * @return \int[]
     */
    public function getSuperiorRoles()
    {
        $this->loadRoles("superior");

        return $this->roles["superior"];
    }


    /**
     * @param $role
     */
    private function loadRoles($role)
    {
        if ($this->roles[$role] == null) {
            $this->loadRolesQuery($role);
        }
    }


    public function flushCache()
    {
        $this->roles = null;
    }


    /**
     * @param $role
     */
    private function loadRolesQuery($role)
    {
        $this->roles[$role] = array();
        $q = "SELECT obj_id, title FROM object_data WHERE type = 'role' AND title LIKE 'il_orgu_" . $role . "%'";
        $set = $this->db->query($q);
        while ($res = $this->db->fetchAssoc($set)) {
            $orgu_ref = $this->getRefIdFromRoleTitle($res["title"]);
            $this->roles[$role][$orgu_ref] = $res["obj_id"];
            $this->role_to_orgu[$role][$res["obj_id"]] = $orgu_ref;
        }
    }


    /**
     * @param $role_title
     *
     * @return int
     */
    private function getRefIdFromRoleTitle($role_title)
    {
        $array = explode("_", $role_title);

        return $array[count($array) - 1];
    }


    /**
     * Specify eg. level 1 and it will return on which orgunit on the first level after the root
     * node the specified orgu_ref is a subunit of. eg:
     *    0
     * -    -
     * 1    2
     * -   -  -
     * 3   4  5
     * -
     * 6
     *
     * (6, 1) = 1; (4, 1) = 2; (6, 2) = 3;
     * @param $orgu_ref
     * @param $level
     *
     * @throws Exception in case there's a thread of an infinite loop or if you try to fetch the
     *                   third level but there are only two (e.g. you want to fetch lvl 1 but give
     *                   the root node as reference).
     * @return int|bool ref_id of the orgu or false if not found.
     */
    public function getLevelXOfTreenode($orgu_ref, $level)
    {
        $line = array( $orgu_ref );
        $current_ref = $orgu_ref;
        while ($current_ref != ilObjOrgUnit::getRootOrgRefId()) {
            $current_ref = $this->getParent($current_ref);
            if ($current_ref) {
                $line[] = $current_ref;
            } else {
                break;
            }
            if (count($line) > 100) {
                throw new Exception("There's either a non valid call of the getLevelXOfTreenode in ilObjOrgUnitTree or your nesting of orgunits is higher than 100 units, which isn't encouraged");
            }
        }
        $line = array_reverse($line);
        if (count($line) > $level) {
            return $line[$level];
        } else {
            throw new Exception("you want to fetch level " . $level . " but the line to the length of the line is only " . count($line)
                . ". The line of the given org unit is: " . print_r($line, true));
        }
    }


    /**
     * @param $orgu_ref int
     *
     * @return int
     */
    public function getParent($orgu_ref)
    {
        if (!$this->parent[$orgu_ref]) {
            $this->parent[$orgu_ref] = $this->tree->getParentId($orgu_ref);
        }

        return $this->parent[$orgu_ref];
    }
}
