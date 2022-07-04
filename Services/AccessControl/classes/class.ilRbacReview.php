<?php declare(strict_types=1);
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
 * class ilRbacReview
 *  Contains Review functions of core Rbac.
 *  This class offers the possibility to view the contents of the user <-> role (UR) relation and
 *  the permission <-> role (PR) relation.
 *  For example, from the UA relation the administrator should have the facility to view all user assigned to a given role.
 * @author  Stefan Meyer <meyer@leifos.com>
 * @author  Sascha Hofmann <saschahofmann@gmx.de>
 * @version $Id$
 * @ingroup ServicesAccessControl
 */
class ilRbacReview
{
    public const FILTER_ALL = 1;
    public const FILTER_ALL_GLOBAL = 2;
    public const FILTER_ALL_LOCAL = 3;
    public const FILTER_INTERNAL = 4;
    public const FILTER_NOT_INTERNAL = 5;
    public const FILTER_TEMPLATES = 6;

    // Cache operation ids
    private static ?array $_opsCache = null;

    protected static array $assigned_users_cache = array();
    protected static array $is_assigned_cache = array();

    protected ilLogger $log;
    protected ilDBInterface $db;

    /**
     * Constructor
     * @access    public
     */
    public function __construct()
    {
        global $DIC;

        $this->log = ilLoggerFactory::getLogger('ac');
        $this->db = $DIC->database();
    }

    /**
     * Checks if a role already exists. Role title should be unique
     * @access    public
     * @param string    role title
     * @param    ?int    obj_id of role to exclude in the check. Commonly this is the current role you want to edit
     * @return  bool
     */
    public function roleExists(string $a_title, int $a_id = 0) : ?int
    {
        $clause = ($a_id) ? " AND obj_id != " . $this->db->quote($a_id, ilDBConstants::T_TEXT) . " " : "";

        $q = "SELECT DISTINCT(obj_id) obj_id FROM object_data " .
            "WHERE title =" . $this->db->quote($a_title, ilDBConstants::T_TEXT) . " " .
            "AND type IN('role','rolt')" .
            $clause . " ";
        $r = $this->db->query($q);
        while ($row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->obj_id;
        }
        return null;
    }

    /**
     * Note: This function performs faster than the new getParentRoles function,
     *       because it uses database indexes whereas getParentRoles needs
     *       a full table space scan.
     * Get parent roles in a path. If last parameter is set 'true'
     * it delivers also all templates in the path
     * @param array    array with path_ids
     * @param bool    true for role templates (default: false)
     * @return    array    array with all parent roles (obj_ids)
     */
    protected function __getParentRoles(array $a_path, bool $a_templates) : array
    {
        $parent_roles = [];
        $role_hierarchy = [];
        foreach ($a_path as $ref_id) {
            $roles = $this->getRoleListByObject($ref_id, $a_templates);
            foreach ($roles as $role) {
                $id = (int) $role["obj_id"];
                $role["parent"] = (int) $ref_id;
                $parent_roles[$id] = $role;

                if (!array_key_exists($role['obj_id'], $role_hierarchy)) {
                    $role_hierarchy[$id] = $ref_id;
                }
            }
        }
        return $this->__setProtectedStatus($parent_roles, $role_hierarchy, (int) reset($a_path));
    }

    /**
     * Get an array of parent role ids of all parent roles, if last parameter is set true
     * you get also all parent templates
     * @param int        ref_id of an object which is end node
     * @param bool        true for role templates (default: false)
     * @return    array       array(role_ids => role_data)
     * @todo move tree to construct. Currently this is not possible due to init sequence
     */
    public function getParentRoleIds(int $a_endnode_id, bool $a_templates = false) : array
    {
        global $DIC;

        $tree = $DIC->repositoryTree();

        $pathIds = $tree->getPathId($a_endnode_id);

        // add system folder since it may not in the path
        //$pathIds[0] = SYSTEM_FOLDER_ID;
        $pathIds[0] = ROLE_FOLDER_ID;
        return $this->__getParentRoles($pathIds, $a_templates);
    }

    /**
     * Returns a list of roles in an container
     */
    public function getRoleListByObject(int $a_ref_id, bool $a_templates = false) : array
    {
        $role_list = array();
        $where = $this->__setTemplateFilter($a_templates);

        $query = "SELECT * FROM object_data " .
            "JOIN rbac_fa ON obj_id = rol_id " .
            $where .
            "AND object_data.obj_id = rbac_fa.rol_id " .
            "AND rbac_fa.parent = " . $this->db->quote($a_ref_id, 'integer') . " ";

        $res = $this->db->query($query);
        while ($row = $this->db->fetchAssoc($res)) {
            $row["desc"] = $row["description"];
            $row["user_id"] = (int) $row["owner"];
            $row['obj_id'] = (int) $row['obj_id'];
            $row['rol_id'] = (int) $row['rol_id'];
            $row['parent'] = (int) $row['parent'];
            $role_list[] = $row;
        }

        return $this->__setRoleType($role_list);
    }

    /**
     * Returns a list of all assignable roles
     */
    public function getAssignableRoles(
        bool $a_templates = false,
        bool $a_internal_roles = false,
        string $title_filter = ''
    ) : array {
        $role_list = array();
        $where = $this->__setTemplateFilter($a_templates);
        $query = "SELECT * FROM object_data " .
            "JOIN rbac_fa ON obj_id = rol_id " .
            $where .
            "AND rbac_fa.assign = 'y' ";

        if (strlen($title_filter)) {
            $query .= (' AND ' . $this->db->like(
                'title',
                'text',
                $title_filter . '%'
            ));
        }
        $res = $this->db->query($query);

        while ($row = $this->db->fetchAssoc($res)) {
            $row["description"] = (string) $row["description"];
            $row["desc"] = $row["description"];
            $row["user_id"] = (int) $row["owner"];
            $row['obj_id'] = (int) $row['obj_id'];
            $row['parent'] = (int) $row['parent'];
            $role_list[] = $row;
        }
        return $this->__setRoleType($role_list);
    }

    /**
     * Returns a list of assignable roles in a subtree of the repository
     * @todo move tree to construct. Currently this is not possible due to init sequence
     */
    public function getAssignableRolesInSubtree(int $ref_id) : array
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        $query = 'SELECT rol_id FROM rbac_fa fa ' .
            'JOIN tree t1 ON t1.child = fa.parent ' .
            'JOIN object_data obd ON fa.rol_id = obd.obj_id ' .
            'WHERE assign = ' . $this->db->quote('y', 'text') . ' ' .
            'AND obd.type = ' . $this->db->quote('role', 'text') . ' ' .
            'AND t1.child IN (' .
            $tree->getSubTreeQuery($ref_id, array('child')) . ' ' .
            ') ';

        $res = $this->db->query($query);

        $role_list = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $role_list[] = (int) $row->rol_id;
        }
        return $role_list;
    }

    /**
     * Get all assignable roles directly under a specific node
     */
    public function getAssignableChildRoles(int $a_ref_id) : array
    {
        $query = "SELECT fa.*, rd.* " .
            "FROM object_data rd " .
            "JOIN rbac_fa fa ON rd.obj_id = fa.rol_id " .
            "WHERE fa.assign = 'y' " .
            "AND fa.parent = " . $this->db->quote($a_ref_id, 'integer') . " ";

        $res = $this->db->query($query);
        $roles_data = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $row['rol_id'] = (int) $row['rol_id'];
            $row['obj_id'] = (int) $row['obj_id'];

            $roles_data[] = $row;
        }

        return $roles_data;
    }

    /**
     * get roles and templates or only roles; returns string for where clause
     */
    protected function __setTemplateFilter(bool $a_templates) : string
    {
        if ($a_templates) {
            $where = "WHERE " . $this->db->in('object_data.type', array('role', 'rolt'), false, 'text') . " ";
        } else {
            $where = "WHERE " . $this->db->in('object_data.type', array('role'), false, 'text') . " ";
        }
        return $where;
    }

    /**
     * computes role type in role list array:
     * global: roles in ROLE_FOLDER_ID
     * local: assignable roles in other role folders
     * linked: roles with stoppped inheritance
     * template: role templates
     */
    protected function __setRoleType(array $a_role_list) : array
    {
        foreach ($a_role_list as $key => $val) {
            // determine role type
            if ($val["type"] == "rolt") {
                $a_role_list[$key]["role_type"] = "template";
            } else {
                if ($val["assign"] == "y") {
                    if ($val["parent"] == ROLE_FOLDER_ID) {
                        $a_role_list[$key]["role_type"] = "global";
                    } else {
                        $a_role_list[$key]["role_type"] = "local";
                    }
                } else {
                    $a_role_list[$key]["role_type"] = "linked";
                }
            }

            if ($val["protected"] == "y") {
                $a_role_list[$key]["protected"] = true;
            } else {
                $a_role_list[$key]["protected"] = false;
            }
        }
        return $a_role_list;
    }

    /**
     * Get the number of assigned users to roles (not properly deleted user accounts are not counted)
     * @param int[] $a_roles
     */
    public function getNumberOfAssignedUsers(array $a_roles) : int
    {
        $query = 'select count(distinct(ua.usr_id)) as num from rbac_ua ua ' .
            'join object_data on ua.usr_id = obj_id ' .
            'join usr_data ud on ua.usr_id = ud.usr_id ' .
            'where ' . $this->db->in('rol_id', $a_roles, false, 'integer');

        $res = $this->db->query($query);
        if ($res->numRows() > 0) {
            $row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT);
            return isset($row->num) && is_numeric($row->num) ? (int) $row->num : 0;
        }
        return 0;
    }

    /**
     * get all assigned users to a given role
     * @access    public
     * @param int    role_id
     * @return    array    all users (id) assigned to role
     */
    public function assignedUsers(int $a_rol_id) : array
    {
        if (isset(self::$assigned_users_cache[$a_rol_id])) {
            return self::$assigned_users_cache[$a_rol_id];
        }

        $result_arr = array();
        $query = "SELECT usr_id FROM rbac_ua WHERE rol_id= " . $this->db->quote($a_rol_id, 'integer');
        $res = $this->db->query($query);
        while ($row = $this->db->fetchAssoc($res)) {
            $result_arr[] = (int) $row["usr_id"];
        }
        self::$assigned_users_cache[$a_rol_id] = $result_arr;
        return $result_arr;
    }

    /**
     * check if a specific user is assigned to specific role
     */
    public function isAssigned(int $a_usr_id, int $a_role_id) : bool
    {
        if (isset(self::$is_assigned_cache[$a_role_id][$a_usr_id])) {
            return self::$is_assigned_cache[$a_role_id][$a_usr_id];
        }
        // Quickly determine if user is assigned to a role
        $this->db->setLimit(1, 0);
        $query = "SELECT usr_id FROM rbac_ua WHERE " .
            "rol_id= " . $this->db->quote($a_role_id, 'integer') . " " .
            "AND usr_id= " . $this->db->quote($a_usr_id, ilDBConstants::T_INTEGER);
        $res = $this->db->query($query);
        $is_assigned = $res->numRows() == 1;
        self::$is_assigned_cache[$a_role_id][$a_usr_id] = $is_assigned;
        return $is_assigned;
    }

    /**
     * check if a specific user is assigned to at least one of the
     * given role ids.
     * This function is used to quickly check whether a user is member
     * of a course or a group.
     * @param int        usr_id
     * @param int[]        role_ids
     */
    public function isAssignedToAtLeastOneGivenRole(int $a_usr_id, array $a_role_ids) : bool
    {
        global $DIC;

        $this->db = $DIC['ilDB'];

        $this->db->setLimit(1, 0);
        $query = "SELECT usr_id FROM rbac_ua WHERE " .
            $this->db->in('rol_id', $a_role_ids, false, 'integer') .
            " AND usr_id= " . $this->db->quote($a_usr_id, ilDBConstants::T_INTEGER);
        $res = $this->db->query($query);

        return $this->db->numRows($res) == 1;
    }

    /**
     * get all assigned roles to a given user
     * @param int        usr_id
     * @return    int[]    all roles (id) the user is assigned to
     */
    public function assignedRoles(int $a_usr_id) : array
    {
        $query = "SELECT rol_id FROM rbac_ua WHERE usr_id = " . $this->db->quote($a_usr_id, 'integer');

        $res = $this->db->query($query);
        $role_arr = [];
        while ($row = $this->db->fetchObject($res)) {
            $role_arr[] = (int) $row->rol_id;
        }
        return $role_arr;
    }

    /**
     * Get assigned global roles for an user
     */
    public function assignedGlobalRoles(int $a_usr_id) : array
    {
        $query = "SELECT ua.rol_id FROM rbac_ua ua " .
            "JOIN rbac_fa fa ON ua.rol_id = fa.rol_id " .
            "WHERE usr_id = " . $this->db->quote($a_usr_id, 'integer') . ' ' .
            "AND parent = " . $this->db->quote(ROLE_FOLDER_ID, ilDBConstants::T_INTEGER) . " " .
            "AND assign = 'y' ";

        $res = $this->db->query($query);
        $role_arr = [];
        while ($row = $this->db->fetchObject($res)) {
            $role_arr[] = $row->rol_id;
        }
        return $role_arr !== [] ? $role_arr : array();
    }

    /**
     * Check if its possible to assign users
     */
    public function isAssignable(int $a_rol_id, int $a_ref_id) : bool
    {
        // exclude system role from rbac
        if ($a_rol_id == SYSTEM_ROLE_ID) {
            return true;
        }

        $query = "SELECT * FROM rbac_fa " .
            "WHERE rol_id = " . $this->db->quote($a_rol_id, 'integer') . " " .
            "AND parent = " . $this->db->quote($a_ref_id, 'integer') . " ";
        $res = $this->db->query($query);
        while ($row = $this->db->fetchObject($res)) {
            return $row->assign == 'y';
        }
        return false;
    }

    public function hasMultipleAssignments(int $a_role_id) : bool
    {
        $query = "SELECT * FROM rbac_fa WHERE rol_id = " . $this->db->quote($a_role_id, 'integer') . ' ' .
            "AND assign = " . $this->db->quote('y', 'text');
        $res = $this->db->query($query);
        return $res->numRows() > 1;
    }

    /**
     * Returns an array of objects assigned to a role. A role with stopped inheritance
     * may be assigned to more than one objects.
     * To get only the original location of a role, set the second parameter to true
     * @access    public
     * @param int    role id
     * @param bool        get only rolefolders where role is assignable (true)
     * @return    int[]        reference IDs of role folders
     */
    public function getFoldersAssignedToRole(int $a_rol_id, bool $a_assignable = false) : array
    {
        $where = '';
        if ($a_assignable) {
            $where = " AND assign ='y'";
        }

        $query = "SELECT DISTINCT parent FROM rbac_fa " .
            "WHERE rol_id = " . $this->db->quote($a_rol_id, 'integer') . " " . $where . " ";

        $res = $this->db->query($query);
        $folders = [];
        while ($row = $this->db->fetchObject($res)) {
            $folders[] = (int) $row->parent;
        }
        return $folders;
    }

    /**
     * Get roles of object
     */
    public function getRolesOfObject(int $a_ref_id, bool $a_assignable_only = false) : array
    {
        $and = '';
        if ($a_assignable_only === true) {
            $and = 'AND assign = ' . $this->db->quote('y', 'text');
        }
        $query = "SELECT rol_id FROM rbac_fa " .
            "WHERE parent = " . $this->db->quote($a_ref_id, 'integer') . " " .
            $and;

        $res = $this->db->query($query);

        $role_ids = array();
        while ($row = $this->db->fetchObject($res)) {
            $role_ids[] = (int) $row->rol_id;
        }
        return $role_ids;
    }

    /**
     * get all roles of a role folder including linked local roles that are created due to stopped inheritance
     * returns an array with role ids
     * @access     public
     * @param int        ref_id of object
     * @param bool if false only get true local roles
     * @return    int[] Array with rol_ids
     * @deprecated since version 4.5.0
     * @todo       refactor rolf => RENAME
     */
    public function getRolesOfRoleFolder(int $a_ref_id, bool $a_nonassignable = true) : array
    {
        $and = '';
        if ($a_nonassignable === false) {
            $and = " AND assign='y'";
        }

        $query = "SELECT rol_id FROM rbac_fa " .
            "WHERE parent = " . $this->db->quote($a_ref_id, 'integer') . " " .
            $and;

        $res = $this->db->query($query);
        $rol_id = [];
        while ($row = $this->db->fetchObject($res)) {
            $rol_id[] = (int) $row->rol_id;
        }

        return $rol_id;
    }

    /**
     * get only 'global' roles
     * @return    int[]        Array with rol_ids
     * @todo refactor rolf => DONE
     */
    public function getGlobalRoles() : array
    {
        return $this->getRolesOfRoleFolder(ROLE_FOLDER_ID, false);
    }

    /**
     * Get local roles of object
     */
    public function getLocalRoles(int $a_ref_id) : array
    {
        $lroles = array();
        foreach ($this->getRolesOfRoleFolder($a_ref_id) as $role_id) {
            if ($this->isAssignable($role_id, $a_ref_id)) {
                $lroles[] = $role_id;
            }
        }
        return $lroles;
    }

    /**
     * Get all roles with local policies
     * @return int[]
     */
    public function getLocalPolicies(int $a_ref_id) : array
    {
        $lroles = array();
        foreach ($this->getRolesOfRoleFolder($a_ref_id) as $role_id) {
            $lroles[] = $role_id;
        }
        return $lroles;
    }

    /**
     * get only 'global' roles
     */
    public function getGlobalRolesArray() : array
    {
        $ga = [];
        foreach ($this->getRolesOfRoleFolder(ROLE_FOLDER_ID, false) as $role_id) {
            $ga[] = array('obj_id' => $role_id,
                          'role_type' => 'global'
            );
        }
        return $ga;
    }

    /**
     * get only 'global' roles (with flag 'assign_users')
     */
    public function getGlobalAssignableRoles() : array
    {
        $ga = [];
        foreach ($this->getGlobalRoles() as $role_id) {
            if (ilObjRole::_getAssignUsersStatus($role_id)) {
                $ga[] = array('obj_id' => $role_id,
                              'role_type' => 'global'
                );
            }
        }
        return $ga;
    }

    /**
     * Check if role is assigned to an object
     */
    public function isRoleAssignedToObject(int $a_role_id, int $a_parent_id) : bool
    {
        $query = 'SELECT * FROM rbac_fa ' .
            'WHERE rol_id = ' . $this->db->quote($a_role_id, 'integer') . ' ' .
            'AND parent = ' . $this->db->quote($a_parent_id, 'integer');
        $res = $this->db->query($query);
        return (bool) $res->numRows();
    }

    /**
     * get all possible operations
     */
    public function getOperations() : array
    {
        $query = 'SELECT * FROM rbac_operations ORDER BY ops_id ';
        $res = $this->db->query($query);
        $ops = [];
        while ($row = $this->db->fetchObject($res)) {
            $ops[] = array('ops_id' => (int) $row->ops_id,
                           'operation' => $row->operation,
                           'description' => $row->description
            );
        }
        return $ops;
    }

    /**
     * get one operation by operation id
     */
    public function getOperation(int $ops_id) : array
    {
        $query = 'SELECT * FROM rbac_operations WHERE ops_id = ' . $this->db->quote($ops_id, 'integer');
        $res = $this->db->query($query);
        $ops = [];
        while ($row = $this->db->fetchObject($res)) {
            $ops = array('ops_id' => (int) $row->ops_id,
                         'operation' => $row->operation,
                         'description' => $row->description
            );
        }
        return $ops;
    }

    /**
     * get all possible operations of a specific role
     * The ref_id of the role folder (parent object) is necessary to distinguish local roles
     */
    public function getAllOperationsOfRole(int $a_rol_id, int $a_parent = 0) : array
    {
        if (!$a_parent) {
            $a_parent = ROLE_FOLDER_ID;
        }
        $query = "SELECT ops_id,type FROM rbac_templates " .
            "WHERE rol_id = " . $this->db->quote($a_rol_id, 'integer') . " " .
            "AND parent = " . $this->db->quote($a_parent, 'integer');
        $res = $this->db->query($query);

        $ops_arr = [];
        while ($row = $this->db->fetchObject($res)) {
            $ops_arr[$row->type][] = (int) $row->ops_id;
        }
        return $ops_arr;
    }

    /**
     * Get active operations for a role
     */
    public function getActiveOperationsOfRole(int $a_ref_id, int $a_role_id) : array
    {
        $query = 'SELECT * FROM rbac_pa ' .
            'WHERE ref_id = ' . $this->db->quote($a_ref_id, 'integer') . ' ' .
            'AND rol_id = ' . $this->db->quote($a_role_id, 'integer') . ' ';

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            return unserialize($row['ops_id']);
        }
        return [];
    }

    /**
     * get all possible operations of a specific role
     * The ref_id of the role folder (parent object) is necessary to distinguish local roles
     */
    public function getOperationsOfRole(int $a_rol_id, string $a_type, int $a_parent = 0) : array
    {
        $ops_arr = [];
        // if no rolefolder id is given, assume global role folder as target
        if ($a_parent == 0) {
            $a_parent = ROLE_FOLDER_ID;
        }

        $query = "SELECT ops_id FROM rbac_templates " .
            "WHERE type =" . $this->db->quote($a_type, 'text') . " " .
            "AND rol_id = " . $this->db->quote($a_rol_id, 'integer') . " " .
            "AND parent = " . $this->db->quote($a_parent, 'integer');
        $res = $this->db->query($query);
        while ($row = $this->db->fetchObject($res)) {
            $ops_arr[] = $row->ops_id;
        }
        return $ops_arr;
    }

    public function getRoleOperationsOnObject(int $a_role_id, int $a_ref_id) : array
    {
        $query = "SELECT * FROM rbac_pa " .
            "WHERE rol_id = " . $this->db->quote($a_role_id, 'integer') . " " .
            "AND ref_id = " . $this->db->quote($a_ref_id, 'integer') . " ";

        $res = $this->db->query($query);
        $ops = [];
        while ($row = $this->db->fetchObject($res)) {
            $ops = (array) unserialize($row->ops_id);
        }
        return $ops;
    }

    /**
     * all possible operations of a type
     */
    public function getOperationsOnType(int $a_typ_id) : array
    {
        $query = 'SELECT * FROM rbac_ta ta JOIN rbac_operations o ON ta.ops_id = o.ops_id ' .
            'WHERE typ_id = ' . $this->db->quote($a_typ_id, 'integer') . ' ' .
            'ORDER BY op_order';

        $res = $this->db->query($query);
        $ops_id = [];
        while ($row = $this->db->fetchObject($res)) {
            $ops_id[] = (int) $row->ops_id;
        }
        return $ops_id;
    }

    /**
     * all possible operations of a type
     */
    public function getOperationsOnTypeString(string $a_type) : array
    {
        $query = "SELECT * FROM object_data WHERE type = 'typ' AND title = " . $this->db->quote($a_type, 'text') . " ";
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $this->getOperationsOnType((int) $row->obj_id);
        }
        return [];
    }

    /**
     * Get operations by type and class
     */
    public function getOperationsByTypeAndClass(string $a_type, string $a_class) : array
    {
        if ($a_class != 'create') {
            $condition = "AND class != " . $this->db->quote('create', 'text');
        } else {
            $condition = "AND class = " . $this->db->quote('create', 'text');
        }

        $query = "SELECT ro.ops_id FROM rbac_operations ro " .
            "JOIN rbac_ta rt ON  ro.ops_id = rt.ops_id " .
            "JOIN object_data od ON rt.typ_id = od.obj_id " .
            "WHERE type = " . $this->db->quote('typ', 'text') . " " .
            "AND title = " . $this->db->quote($a_type, 'text') . " " .
            $condition . " " .
            "ORDER BY op_order ";

        $res = $this->db->query($query);
        $ops = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ops[] = (int) $row->ops_id;
        }
        return $ops;
    }

    /**
     * get all objects in which the inheritance of role with role_id was stopped
     * the function returns all reference ids of objects containing a role folder.
     */
    public function getObjectsWithStopedInheritance(int $a_rol_id, array $a_filter = array()) : array
    {
        $query = 'SELECT parent p FROM rbac_fa ' .
            'WHERE assign = ' . $this->db->quote('n', 'text') . ' ' .
            'AND rol_id = ' . $this->db->quote($a_rol_id, 'integer') . ' ';

        if ($a_filter !== []) {
            $query .= ('AND ' . $this->db->in('parent', (array) $a_filter, false, 'integer'));
        }

        $res = $this->db->query($query);
        $parent = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $parent[] = (int) $row->p;
        }
        return $parent;
    }

    /**
     * Checks if a rolefolder is set as deleted (negative tree_id)
     * @todo delete this method
     */
    public function isDeleted(int $a_node_id) : bool
    {
        $q = "SELECT tree FROM tree WHERE child =" . $this->db->quote($a_node_id, ilDBConstants::T_INTEGER) . " ";
        $r = $this->db->query($q);
        $row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

        if (!$row) {
            $message = sprintf(
                '%s::isDeleted(): Role folder with ref_id %s not found!',
                get_class($this),
                $a_node_id
            );
            $this->log->warning($message);
            return true;
        }
        return $row->tree < 0;
    }

    /**
     * Check if role is a global role
     */
    public function isGlobalRole(int $a_role_id) : bool
    {
        return in_array($a_role_id, $this->getGlobalRoles());
    }

    public function getRolesByFilter(int $a_filter = 0, int $a_user_id = 0, string $title_filter = '') : array
    {
        $assign = "y";
        switch ($a_filter) {
            // all (assignable) roles
            case self::FILTER_ALL:
                return $this->getAssignableRoles(true, true, $title_filter);

            // all (assignable) global roles
            case self::FILTER_ALL_GLOBAL:
                $where = 'WHERE ' . $this->db->in('rbac_fa.rol_id', $this->getGlobalRoles(), false, 'integer') . ' ';
                break;

            // all (assignable) local roles
            case self::FILTER_ALL_LOCAL:
            case self::FILTER_INTERNAL:
            case self::FILTER_NOT_INTERNAL:
                $where = 'WHERE ' . $this->db->in('rbac_fa.rol_id', $this->getGlobalRoles(), true, 'integer');
                break;

            // all role templates
            case self::FILTER_TEMPLATES:
                $where = "WHERE object_data.type = 'rolt'";
                $assign = "n";
                break;

            // only assigned roles, handled by ilObjUserGUI::roleassignmentObject()
            case 0:
            default:
                if (!$a_user_id) {
                    return array();
                }

                $where = 'WHERE ' . $this->db->in(
                    'rbac_fa.rol_id',
                    $this->assignedRoles($a_user_id),
                    false,
                    'integer'
                ) . ' ';
                break;
        }

        $roles = array();

        $query = "SELECT * FROM object_data " .
            "JOIN rbac_fa ON obj_id = rol_id " .
            $where .
            "AND rbac_fa.assign = " . $this->db->quote($assign, 'text') . " ";

        if (strlen($title_filter)) {
            $query .= (' AND ' . $this->db->like(
                'title',
                'text',
                '%' . $title_filter . '%'
            ));
        }

        $res = $this->db->query($query);
        while ($row = $this->db->fetchAssoc($res)) {
            $prefix = substr($row["title"], 0, 3) == "il_";

            // all (assignable) internal local roles only
            if ($a_filter == 4 && !$prefix) {
                continue;
            }

            // all (assignable) non internal local roles only
            if ($a_filter == 5 && $prefix) {
                continue;
            }

            $row['title'] = (string) $row['title'];
            $row['description'] = (string) $row['description'];
            $row["desc"] = $row["description"];
            $row["user_id"] = (int) $row["owner"];
            $row['obj_id'] = (int) $row['obj_id'];
            $row['rol_id'] = (int) $row['rol_id'];
            $row['parent'] = (int) $row['parent'];
            $roles[] = $row;
        }
        return $this->__setRoleType($roles);
    }

    public function getTypeId(string $a_type) : int
    {
        $q = "SELECT obj_id FROM object_data " .
            "WHERE title=" . $this->db->quote($a_type, 'text') . " AND type='typ'";
        $r = $this->db->query($q);
        while ($row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->obj_id;
        }
        return 0;
    }

    /**
     * get ops_id's by name.
     * Example usage: $rbacadmin->grantPermission($roles,ilRbacReview::_getOperationIdsByName(array('visible','read'),$ref_id));
     */
    public static function _getOperationIdsByName(array $operations) : array
    {
        global $DIC;

        $ilDB = $DIC->database();
        if ($operations === []) {
            return array();
        }

        $query = 'SELECT ops_id FROM rbac_operations ' .
            'WHERE ' . $ilDB->in('operation', $operations, false, 'text');

        $res = $ilDB->query($query);
        $ops_ids = [];
        while ($row = $ilDB->fetchObject($res)) {
            $ops_ids[] = (int) $row->ops_id;
        }
        return $ops_ids;
    }

    /**
     * get operation id by name of operation
     */
    public static function _getOperationIdByName(string $a_operation) : int
    {
        global $DIC;

        $ilDB = $DIC->database();

        // Cache operation ids
        if (!is_array(self::$_opsCache)) {
            self::$_opsCache = array();

            $q = "SELECT ops_id, operation FROM rbac_operations";
            $r = $ilDB->query($q);
            while ($row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                self::$_opsCache[$row->operation] = (int) $row->ops_id;
            }
        }

        // Get operation ID by name from cache
        if (array_key_exists($a_operation, self::$_opsCache)) {
            return self::$_opsCache[$a_operation];
        }
        return 0;
    }

    /**
     * Lookup operation ids
     * @param array $a_type_arr e.g array('cat','crs','grp'). The operation name (e.g. 'create_cat') is generated automatically
     * @return int[] Array with operation ids
     */
    public static function lookupCreateOperationIds(array $a_type_arr) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $operations = array();
        foreach ($a_type_arr as $type) {
            $operations[] = ('create_' . $type);
        }

        if ($operations === []) {
            return array();
        }

        $query = 'SELECT ops_id, operation FROM rbac_operations ' .
            'WHERE ' . $ilDB->in('operation', $operations, false, 'text');

        $res = $ilDB->query($query);

        $ops_ids = array();
        while ($row = $ilDB->fetchObject($res)) {
            $type_arr = explode('_', $row->operation);
            $type = $type_arr[1];

            $ops_ids[$type] = (int) $row->ops_id;
        }
        return $ops_ids;
    }

    public function isProtected(int $a_ref_id, int $a_role_id) : bool
    {
        // ref_id not used yet. protected permission acts 'global' for each role,
        $query = "SELECT protected FROM rbac_fa " .
            "WHERE rol_id = " . $this->db->quote($a_role_id, 'integer') . " ";
        $res = $this->db->query($query);
        $row = $this->db->fetchAssoc($res);
        return ilUtil::yn2tf($row['protected']);
    }

    public function isBlockedAtPosition(int $a_role_id, int $a_ref_id) : bool
    {
        $query = 'SELECT blocked from rbac_fa ' .
            'WHERE rol_id = ' . $this->db->quote($a_role_id, 'integer') . ' ' .
            'AND parent = ' . $this->db->quote($a_ref_id, 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (bool) $row->blocked;
        }
        return false;
    }

    /**
     * Check if role is blocked in upper context
     * @todo move tree to construct. Currently this is not possible due to init sequence
     */
    public function isBlockedInUpperContext(int $a_role_id, int $a_ref_id) : bool
    {
        global $DIC;

        $tree = $DIC['tree'];

        if ($this->isBlockedAtPosition($a_role_id, $a_ref_id)) {
            return false;
        }
        $query = 'SELECT parent from rbac_fa ' .
            'WHERE rol_id = ' . $this->db->quote($a_role_id, 'integer') . ' ' .
            'AND blocked = ' . $this->db->quote(1, 'integer');
        $res = $this->db->query($query);

        $parent_ids = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $parent_ids[] = (int) $row->parent;
        }

        foreach ($parent_ids as $parent_id) {
            if ($tree->isGrandChild($parent_id, $a_ref_id)) {
                return true;
            }
        }
        return false;
    }

    // this method alters the protected status of role regarding the current user's role assignment
    // and current postion in the hierarchy.
    protected function __setProtectedStatus(array $a_parent_roles, array $a_role_hierarchy, int $a_ref_id) : array
    {
        global $DIC;

        $rbacsystem = $DIC->rbac()->system();
        $ilUser = $DIC->user();
        if (in_array(SYSTEM_ROLE_ID, $this->assignedRoles($ilUser->getId()))) {
            $leveladmin = true;
        } else {
            $leveladmin = false;
        }
        foreach ($a_role_hierarchy as $role_id => $rolf_id) {
            if ($leveladmin == true) {
                $a_parent_roles[$role_id]['protected'] = false;
                continue;
            }

            if ($a_parent_roles[$role_id]['protected'] == true) {
                $arr_lvl_roles_user = array_intersect(
                    $this->assignedRoles($ilUser->getId()),
                    array_keys($a_role_hierarchy, $rolf_id)
                );

                foreach ($arr_lvl_roles_user as $lvl_role_id) {
                    // check if role grants 'edit_permission' to parent
                    $rolf = $a_parent_roles[$role_id]['parent'];
                    if ($rbacsystem->checkPermission($rolf, $lvl_role_id, 'edit_permission')) {
                        $a_parent_roles[$role_id]['protected'] = false;
                    }
                }
            }
        }
        return $a_parent_roles;
    }

    /**
     * get operation list by object type
     */
    public static function _getOperationList(string $a_type = '') : array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $arr = array();
        if ($a_type) {
            $query = sprintf(
                'SELECT * FROM rbac_operations ' .
                'JOIN rbac_ta ON rbac_operations.ops_id = rbac_ta.ops_id ' .
                'JOIN object_data ON rbac_ta.typ_id = object_data.obj_id ' .
                'WHERE object_data.title = %s ' .
                'AND object_data.type = %s ' .
                'ORDER BY op_order ASC',
                $ilDB->quote($a_type, 'text'),
                $ilDB->quote('typ', 'text')
            );
        } else {
            $query = 'SELECT * FROM rbac_operations ORDER BY op_order ASC';
        }
        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($res)) {
            $arr[] = array(
                "ops_id" => (int) $row['ops_id'],
                "operation" => $row['operation'],
                "desc" => $row['description'],
                "class" => $row['class'],
                "order" => (int) $row['op_order']
            );
        }
        return $arr;
    }

    public static function _groupOperationsByClass(array $a_ops_arr) : array
    {
        $arr = array();
        foreach ($a_ops_arr as $ops) {
            $arr[$ops['class']][] = array('ops_id' => (int) $ops['ops_id'],
                                          'name' => $ops['operation']
            );
        }
        return $arr;
    }

    /**
     * Get object id of objects a role is assigned to
     * @todo refactor rolf (due to performance reasons the new version does not check for deleted roles only in object reference)
     */
    public function getObjectOfRole(int $a_role_id) : int
    {
        // internal cache
        static $obj_cache = array();

        if (isset($obj_cache[$a_role_id]) && $obj_cache[$a_role_id]) {
            return $obj_cache[$a_role_id];
        }

        $query = 'SELECT obr.obj_id FROM rbac_fa rfa ' .
            'JOIN object_reference obr ON rfa.parent = obr.ref_id ' .
            'WHERE assign = ' . $this->db->quote('y', 'text') . ' ' .
            'AND rol_id = ' . $this->db->quote($a_role_id, 'integer') . ' ' .
            'AND deleted IS NULL';

        $res = $this->db->query($query);
        $obj_cache[$a_role_id] = 0;
        while ($row = $this->db->fetchObject($res)) {
            $obj_cache[$a_role_id] = (int) $row->obj_id;
        }
        return $obj_cache[$a_role_id];
    }

    public function getObjectReferenceOfRole(int $a_role_id) : int
    {
        $query = 'SELECT parent p_ref FROM rbac_fa ' .
            'WHERE rol_id = ' . $this->db->quote($a_role_id, 'integer') . ' ' .
            'AND assign = ' . $this->db->quote('y', 'text');

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->p_ref;
        }
        return 0;
    }

    /**
     * return if role is only attached to deleted role folders
     */
    public function isRoleDeleted(int $a_role_id) : bool
    {
        $rolf_list = $this->getFoldersAssignedToRole($a_role_id, false);
        $deleted = true;
        if ($rolf_list !== []) {
            foreach ($rolf_list as $rolf) {
                // only list roles that are not set to status "deleted"
                if (!$this->isDeleted($rolf)) {
                    $deleted = false;
                    break;
                }
            }
        }
        return $deleted;
    }

    public function getRolesForIDs(array $role_ids, bool $use_templates) : array
    {
        $where = $this->__setTemplateFilter($use_templates);
        $query = "SELECT * FROM object_data " .
            "JOIN rbac_fa ON object_data.obj_id = rbac_fa.rol_id " .
            $where .
            "AND rbac_fa.assign = 'y' " .
            'AND ' . $this->db->in('object_data.obj_id', $role_ids, false, 'integer');

        $res = $this->db->query($query);
        $role_list = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $row["desc"] = $row["description"];
            $row["user_id"] = (int) $row["owner"];
            $role_list[] = $row;
        }
        return $this->__setRoleType($role_list);
    }

    /**
     * get operation assignments
     * @return array array(array('typ_id' => $typ_id,'title' => $title,'ops_id => '$ops_is,'operation' => $operation),...
     */
    public function getOperationAssignment() : array
    {
        global $DIC;

        $this->db = $DIC['ilDB'];

        $query = 'SELECT ta.typ_id, obj.title, ops.ops_id, ops.operation FROM rbac_ta ta ' .
            'JOIN object_data obj ON obj.obj_id = ta.typ_id ' .
            'JOIN rbac_operations ops ON ops.ops_id = ta.ops_id ';
        $res = $this->db->query($query);

        $counter = 0;
        $info = [];
        while ($row = $this->db->fetchObject($res)) {
            $info[$counter]['typ_id'] = (int) $row->typ_id;
            $info[$counter]['type'] = $row->title;
            $info[$counter]['ops_id'] = (int) $row->ops_id;
            $info[$counter]['operation'] = $row->operation;
            $counter++;
        }
        return $info;
    }

    /**
     * Check if role is deleteable at a specific position
     */
    public function isDeleteable(int $a_role_id, int $a_rolf_id) : bool
    {
        if (!$this->isAssignable($a_role_id, $a_rolf_id)) {
            return false;
        }
        if ($a_role_id == SYSTEM_ROLE_ID or $a_role_id == ANONYMOUS_ROLE_ID) {
            return false;
        }
        if (substr(ilObject::_lookupTitle($a_role_id), 0, 3) == 'il_') {
            return false;
        }
        return true;
    }

    /**
     * Check if the role is system generate role or role template
     */
    public function isSystemGeneratedRole(int $a_role_id) : bool
    {
        $title = ilObject::_lookupTitle($a_role_id);
        return substr($title, 0, 3) == 'il_';
    }

    public function getRoleFolderOfRole(int $a_role_id) : int
    {
        if (ilObject::_lookupType($a_role_id) == 'role') {
            $and = ('AND assign = ' . $this->db->quote('y', 'text'));
        } else {
            $and = '';
        }

        $query = 'SELECT * FROM rbac_fa ' .
            'WHERE rol_id = ' . $this->db->quote($a_role_id, 'integer') . ' ' .
            $and;
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->parent;
        }
        return 0;
    }

    /**
     * Get all user permissions on an object
     */
    public function getUserPermissionsOnObject(int $a_user_id, int $a_ref_id) : array
    {
        $query = "SELECT ops_id FROM rbac_pa JOIN rbac_ua " .
            "ON (rbac_pa.rol_id = rbac_ua.rol_id) " .
            "WHERE rbac_ua.usr_id = " . $this->db->quote($a_user_id, 'integer') . " " .
            "AND rbac_pa.ref_id = " . $this->db->quote($a_ref_id, 'integer') . " ";

        $res = $this->db->query($query);
        $all_ops = array();
        while ($row = $this->db->fetchObject($res)) {
            $ops = unserialize($row->ops_id);
            $all_ops = array_merge($all_ops, $ops);
        }
        $all_ops = array_unique($all_ops);

        $set = $this->db->query("SELECT operation FROM rbac_operations " .
            " WHERE " . $this->db->in("ops_id", $all_ops, false, "integer"));
        $perms = array();
        while ($rec = $this->db->fetchAssoc($set)) {
            $perms[] = $rec["operation"];
        }
        return $perms;
    }

    /**
     * set entry of assigned_chache
     */
    public function setAssignedCacheEntry(int $a_role_id, int $a_user_id, bool $a_value) : void
    {
        self::$is_assigned_cache[$a_role_id][$a_user_id] = $a_value;
    }

    public function getAssignedCacheEntry(int $a_role_id, int $a_user_id) : bool
    {
        return self::$is_assigned_cache[$a_role_id][$a_user_id];
    }

    /**
     * Clear assigned users caches
     */
    public function clearCaches() : void
    {
        self::$is_assigned_cache = array();
        self::$assigned_users_cache = array();
    }

    public static function _getCustomRBACOperationId(string $operation) : ?int
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $sql =
            "SELECT ops_id" . PHP_EOL
            . "FROM rbac_operations" . PHP_EOL
            . "WHERE operation = " . $ilDB->quote($operation, "text") . PHP_EOL
        ;

        $res = $ilDB->query($sql);
        if ($ilDB->numRows($res) == 0) {
            return null;
        }

        $row = $ilDB->fetchAssoc($res);
        return (int) $row["ops_id"] ?? null;
    }

    public static function _isRBACOperation(int $type_id, int $ops_id) : bool
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $sql =
            "SELECT typ_id" . PHP_EOL
            . "FROM rbac_ta" . PHP_EOL
            . "WHERE typ_id = " . $ilDB->quote($type_id, "integer") . PHP_EOL
            . "AND ops_id = " . $ilDB->quote($ops_id, "integer") . PHP_EOL
        ;

        return (bool) $ilDB->numRows($ilDB->query($sql));
    }
} // END class.ilRbacReview
