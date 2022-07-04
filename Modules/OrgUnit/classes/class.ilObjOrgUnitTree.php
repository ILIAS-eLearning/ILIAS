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
 * Class ilObjOrgUnitTree
 * Implements a singleton pattern for caching.
 * @author: Oskar Truffer <ot@studer-raimann.ch>
 * @author: Martin Studer <ms@studer-raimann.ch>
 */
class ilObjOrgUnitTree
{
    protected static ?string $temporary_table_name_getOrgUnitOfUser = null;
    protected static ?string $temporary_table_name = null;
    protected static ?ilObjOrgUnitTree $instance = null;
    /** @var int[][] "employee" | "superior" => orgu_ref_id => role_id */
    private array $roles;
    /** @var int[][] "employee" | "superior" => role_id => orgu_ref_id id */
    private array $role_to_orgu;
    /** @var int[][][] "employee" | "superior" => orgu ref id =>  array(obj_id of users) */
    private $staff;
    /** @var int[][] org_unit ref id => childrens org_unit ref ids. */
    private array $tree_childs = [];
    /** @var int[] orgu_ref => parent_ref */
    private array $parent = [];
    private ilDBInterface $db;
    private ilObjUser $ilUser;
    private \ilTree $tree;

    private function __construct()
    {
        global $DIC;
        $this->db = $DIC->database();
        $this->tree = $DIC->repositoryTree();
        $this->roles = array();
        $this->staff = array();
        $this->ilUser = $DIC->user();
    }

    public static function _getInstance() : \ilObjOrgUnitTree
    {
        if (static::$instance === null) {
            static::$instance = new self();
        }

        return static::$instance;
    }

    /**
     * @param int  $ref_id    the reference id of the organisational unit.
     * @param bool $recursive if true you get the ids of the subsequent orgunits employees too
     */
    public function getEmployees(int $ref_id, bool $recursive = false) : array
    {
        $arr_usr_ids = [];

        switch ($recursive) {
            case false:
                $arr_usr_ids = $this->getAssignements($ref_id,
                    ilOrgUnitPosition::getCorePosition(ilOrgUnitPosition::CORE_POSITION_EMPLOYEE));
                break;
            case true:
                $assignment_query = ilOrgUnitUserAssignmentQueries::getInstance();
                $arr_usr_ids = $assignment_query->getUserIdsOfOrgUnitsInPosition($this->getAllChildren($ref_id),
                    ilOrgUnitPosition::CORE_POSITION_EMPLOYEE);
                break;
        }

        return $arr_usr_ids;
    }

    public function getAssignements(int $ref_id, ilOrgUnitPosition $ilOrgUnitPosition) : array
    {
        return ilOrgUnitUserAssignment::where(array(
            'orgu_id' => $ref_id,
            'position_id' => $ilOrgUnitPosition->getId(),
        ))->getArray('id', 'user_id');
    }

    /**
     * @param int  $ref_id    the reference id of the organisational unit.
     * @param bool $recursive if true you get the ids of the subsequent orgunits superiors too
     * @return int[] array of user ids.
     */
    public function getSuperiors(int $ref_id, bool $recursive = false) : array
    {
        if ($recursive === false) {
            return $this->getAssignements($ref_id,
                ilOrgUnitPosition::getCorePosition(ilOrgUnitPosition::CORE_POSITION_SUPERIOR));
        }

        $arr_usr_ids = [];
        foreach ($this->getAllChildren($ref_id) as $ref_id_child) {
            $arr_usr_ids += $this->getAssignements($ref_id_child,
                ilOrgUnitPosition::getCorePosition(ilOrgUnitPosition::CORE_POSITION_SUPERIOR));
        }
        return $arr_usr_ids;
    }

    /**
     * @param string $title   "employee" or "superior"
     * @param int[]  $ref_ids array of orgu object ref ids.
     * @return int[] user_ids
     */
    private function loadArrayOfStaff(string $title, array $ref_ids) : array
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
        $all_users = [];
        foreach ($all_refs as $ref) {
            $all_users = array_merge($all_users, $this->staff[$title][$ref]);
        }
        return $all_users;
    }

    public function getAllChildren(int $ref_id) : array
    {
        $open = array($ref_id);
        $closed = array();
        while (count($open)) {
            $ref = array_pop($open);
            $closed[] = $ref;
            foreach ($this->getChildren($ref) as $child) {
                if (in_array($child, $open, true) === false && in_array($child, $closed, true) === false) {
                    $open[] = $child;
                }
            }
        }

        return $closed;
    }

    /**
     * If you want to have all orgunits where the current user has the write permission: use this
     * with the parameter "write".
     * @return int[] ids of the org units.
     */
    public function getOrgusWhereUserHasPermissionForOperation($operation) : array
    {

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
		INNER JOIN rbac_ua ON rbac_ua.usr_id = " . $this->db->quote($this->ilUser->getId(), "integer") . "
		INNER JOIN rbac_pa ON rbac_pa.rol_id = rbac_ua.rol_id AND rbac_pa.ops_id LIKE CONCAT('%', rbac_operations.ops_id, '%')
		INNER JOIN object_reference ON object_reference.ref_id = rbac_pa.ref_id
		WHERE object_data.obj_id = object_reference.obj_id AND object_data.type = 'orgu'";

        $set = $this->db->query($q);
        $orgus = [];
        while ($res = $this->db->fetchAssoc($set)) {
            //this is needed as the table rbac_operations is not in the first normal form, thus this needs some additional checkings.
            $perm_check = unserialize($res['ops_id'], ['allowed_classes' => true]);
            if (in_array($res["op_id"], $perm_check, true) === false) {
                continue;
            }

            $orgus[] = $res["ref_id"];
        }

        return $orgus;
    }

    /**
     * If you want to have all orgunits where the current user has the write permission: use this
     * with the parameter 3 (3 is the "write" permission as in rbac_operations).
     * @return int[] ids of the org units.
     */
    public function getOrgusWhereUserHasPermissionForOperationId(string $operation_id) : array
    {
        $q = "SELECT object_data.obj_id, object_data.title, object_data.type, rbac_pa.ops_id FROM object_data
		INNER JOIN rbac_ua ON rbac_ua.usr_id = " . $this->db->quote($this->ilUser->getId(), "integer") . "
		INNER JOIN rbac_pa ON rbac_pa.rol_id = rbac_ua.rol_id AND rbac_pa.ops_id LIKE CONCAT('%', " . $this->db->quote($operation_id,
                "integer") . ", '%')
		INNER JOIN rbac_fa ON rbac_fa.rol_id = rbac_ua.rol_id
		INNER JOIN tree ON tree.child = rbac_fa.parent
		INNER JOIN object_reference ON object_reference.ref_id = tree.parent
		WHERE object_data.obj_id = object_reference.obj_id AND object_data.type = 'orgu'";

        $set = $this->db->query($q);
        $orgus = array();
        while ($res = $this->db->fetchAssoc($set)) {
            //this is needed as the table rbac_operations is not in the first normal form, thus this needs some additional checkings.
            $perm_check = unserialize($res['ops_id'], ['allowed_classes' => true]);
            if (in_array($res["ops_id"], $perm_check, true) === false) {
                continue;
            }

            $orgus[] = $res["obj_id"];
        }

        return $orgus;
    }

    private function getChildren(int $ref_id) : array
    {
        $this->loadChildren($ref_id);

        return $this->tree_childs[$ref_id];
    }

    private function loadChildren(int $ref_id) : void
    {
        if (!$this->tree_childs[$ref_id]) {
            $children = array();
            foreach ($this->tree->getChilds($ref_id) as $child) {
                if ($child["type"] == "orgu") {
                    $children[] = $child["child"];
                }
            }
            $this->tree_childs[$ref_id] = $children;
        }
    }

    public function getAllOrgunitsOnLevelX(int $level) : array
    {
        $levels = array(0 => array(ilObjOrgUnit::getRootOrgRefId()));
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
     * @param bool $recursive if this is true subsequent orgunits of this users superior role get
     *                        searched as well.
     * @return int[] returns an array of user_ids of the users which have an employee role in an
     *                        orgunit of which this user's id has a superior role.
     */
    public function getEmployeesUnderUser(int $user_id, bool $recursive = true) : array
    {
        $assignment_query = ilOrgUnitUserAssignmentQueries::getInstance();
        $orgu_ref_ids = $assignment_query->getOrgUnitIdsOfUsersPosition(
            ilOrgUnitPosition::CORE_POSITION_SUPERIOR,
            $user_id
        );

        switch ($recursive) {
            case true:
                $orgu_ref_id_with_children = [];
                foreach ($orgu_ref_ids as $orgu_ref_id) {
                    $orgu_ref_id_with_children = array_merge($orgu_ref_ids, $this->getAllChildren($orgu_ref_id));
                }
                return $assignment_query->getUserIdsOfOrgUnitsInPosition($orgu_ref_id_with_children,
                    ilOrgUnitPosition::CORE_POSITION_EMPLOYEE);
            default:
                return $assignment_query->getUserIdsOfOrgUnitsInPosition($orgu_ref_ids,
                    ilOrgUnitPosition::CORE_POSITION_EMPLOYEE);
        }
    }

    /**
     * @param bool $recursive if this is true subsequent orgunits of this users superior role get
     *                        searched as well.
     * @return int[] returns an array of user_ids of the users which have an employee role in an
     *                        orgunit of which this user's id has a superior role.
     */
    public function getSuperiorsOfUser(int $user_id, bool $recursive = true) : array
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
     * @param int $user_id
     * @return int[]
     */
    public function getLevelXOfUser(int $user_id, int $level) : array
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
     * @param int $ref_id if given, only OrgUnits under this ID are returned (including $ref_id)
     * @return int[]
     */
    public function getOrgUnitOfUser(int $user_id) : array
    {
        $orgu_ref_ids = [];
        $orgu_query = ilOrgUnitUserAssignmentQueries::getInstance();

        $orgus = $orgu_query->getAssignmentsOfUserId($user_id);
        foreach ($orgus as $orgu) {
            $orgu_ref_ids[] = $orgu->getOrguId();
        }
        return $orgu_ref_ids;
    }

    /**
     * Creates a temporary table with all orgu/user assignements. there will be three columns in
     * the table orgu_usr_assignements (or specified table-name): ref_id: Reference-IDs of OrgUnits
     * user_id: Assigned User-IDs path: Path-representation of the OrgUnit
     * Usage:
     * 1. Run ilObjOrgUnitTree::getInstance()->buildTempTableWithUsrAssignements(); in your code
     * 2. use the table orgu_usr_assignements for your JOINS ans SELECTS
     * 3. Run ilObjOrgUnitTree::getInstance()->dropTempTable(); to throw away the table
     * @param string $temporary_table_name
     * @return bool
     * @throws ilException
     */
    public function buildTempTableWithUsrAssignements(string $temporary_table_name = 'orgu_usr_assignements') : bool
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

    public function dropTempTable(string $temporary_table_name) : bool
    {
        if (self::$temporary_table_name === null
            || $temporary_table_name !== self::$temporary_table_name
        ) {
            return false;
        }
        $q = "DROP TABLE IF EXISTS " . $temporary_table_name;
        $this->db->manipulate($q);

        self::$temporary_table_name = null;

        return true;
    }

    public function getTitles(array $org_refs) : array
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
    public function getEmployeeRoles() : array
    {
        $this->loadRoles("employee");
        return $this->roles["employee"];
    }

    /**
     * @return int[]
     */
    public function getSuperiorRoles() : array
    {
        $this->loadRoles("superior");

        return $this->roles["superior"];
    }

    private function loadRoles(string $role)
    {
        if ($this->roles[$role] == null) {
            $this->loadRolesQuery($role);
        }
    }

    public function flushCache() : void
    {
        $this->roles = null;
    }

    private function loadRolesQuery(string $role) : void
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

    private function getRefIdFromRoleTitle(string $role_title) : int
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
     * (6, 1) = 1; (4, 1) = 2; (6, 2) = 3;
     * @return int|bool ref_id of the orgu or false if not found.
     * @throws Exception in case there's a thread of an infinite loop or if you try to fetch the
     *                   third level but there are only two (e.g. you want to fetch lvl 1 but give
     *                   the root node as reference).
     */
    public function getLevelXOfTreenode(int $orgu_ref, int $level)
    {
        $line = array($orgu_ref);
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

    public function getParent(int $orgu_ref) : int
    {
        if (array_key_exists($orgu_ref,$this->parent) === false) {
            $this->parent[$orgu_ref] = $this->tree->getParentId($orgu_ref);
        }

        return $this->parent[$orgu_ref];
    }
}
