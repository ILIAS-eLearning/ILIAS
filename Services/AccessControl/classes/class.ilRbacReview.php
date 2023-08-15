<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * class ilRbacReview
 *  Contains Review functions of core Rbac.
 *  This class offers the possibility to view the contents of the user <-> role (UR) relation and
 *  the permission <-> role (PR) relation.
 *  For example, from the UA relation the administrator should have the facility to view all user assigned to a given role.
 *
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @author Sascha Hofmann <saschahofmann@gmx.de>
 *
 * @version $Id$
 *
 * @ingroup ServicesAccessControl
 */
class ilRbacReview
{
    const FILTER_ALL = 1;
    const FILTER_ALL_GLOBAL = 2;
    const FILTER_ALL_LOCAL = 3;
    const FILTER_INTERNAL = 4;
    const FILTER_NOT_INTERNAL = 5;
    const FILTER_TEMPLATES = 6;

    // Cache operation ids
    private static $_opsCache = null;

    /**
     * @var array
     */
    protected static $assigned_users_cache = [];

    /**
     * @var array
     */
    protected static $is_assigned_cache = [];

    /**
     * @var ilLogger
     */
    protected $log;

    /**
     * Constructor
     * @access	public
     */
    public function __construct()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilErr = $DIC['ilErr'];
        $ilias = $DIC['ilias'];

        $this->log = ilLoggerFactory::getLogger('ac');

        // set db & error handler
        (isset($ilDB)) ? $this->ilDB = &$ilDB : $this->ilDB = &$ilias->db;

        if (!isset($ilErr)) {
            $ilErr = new ilErrorHandling();
            $ilErr->setErrorHandling(PEAR_ERROR_CALLBACK, array($ilErr,'errorHandler'));
        } else {
            $this->ilErr = &$ilErr;
        }
    }

    /**
     * Checks if a role already exists. Role title should be unique
     * @access	public
     * @param	string	role title
     * @param	integer	obj_id of role to exclude in the check. Commonly this is the current role you want to edit
     * @return	boolean	true if exists
     * @todo refactor rolf => DONE
     */
    public function roleExists($a_title, $a_id = 0)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (empty($a_title)) {
            $message = get_class($this) . "::roleExists(): No title given!";
            $this->ilErr->raiseError($message, $this->ilErr->WARNING);
        }

        $clause = ($a_id) ? " AND obj_id != " . $ilDB->quote($a_id) . " " : "";

        $q = "SELECT DISTINCT(obj_id) obj_id FROM object_data " .
             "WHERE title =" . $ilDB->quote($a_title) . " " .
             "AND type IN('role','rolt')" .
             $clause . " ";
        $r = $this->ilDB->query($q);

        while ($row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->obj_id;
        }
        return false;
    }

    /**
     * Note: This function performs faster than the new getParentRoles function,
     *       because it uses database indexes whereas getParentRoles needs
     *       a full table space scan.
     *
     * Get parent roles in a path. If last parameter is set 'true'
     * it delivers also all templates in the path
     * @access	protected
     * @param	array	array with path_ids
     * @param	boolean	true for role templates (default: false)
     * @return	array	array with all parent roles (obj_ids)
     * @todo refactor rolf => DONE
     */
    protected function __getParentRoles($a_path, $a_templates)
    {
        if (!isset($a_path) or !is_array($a_path)) {
            $message = get_class($this) . "::getParentRoles(): No path given or wrong datatype!";
            $this->ilErr->raiseError($message, $this->ilErr->WARNING);
        }

        $parent_roles = [];
        $role_hierarchy = [];

        foreach ($a_path as $ref_id) {
            $roles = $this->getRoleListByObject($ref_id, $a_templates);
            foreach ($roles as $role) {
                $id = $role["obj_id"];
                $role["parent"] = $ref_id;
                $parent_roles[$id] = $role;

                if (!array_key_exists($role['obj_id'], $role_hierarchy)) {
                    $role_hierarchy[$id] = $ref_id;
                }
            }
        }
        return $this->__setProtectedStatus($parent_roles, $role_hierarchy, reset($a_path));
    }

    /**
     * get an array of parent role ids of all parent roles, if last parameter is set true
     * you get also all parent templates
     * @access	public
     * @param	integer		ref_id of an object which is end node
     * @param	boolean		true for role templates (default: false)
     * @return	array       array(role_ids => role_data)
     * @todo refactor rolf => DONE
     */
    public function getParentRoleIds($a_endnode_id, $a_templates = false)
    {
        global $DIC;

        $tree = $DIC['tree'];

        if (!isset($a_endnode_id)) {
            $GLOBALS['DIC']['ilLog']->logStack();
            $message = get_class($this) . "::getParentRoleIds(): No node_id (ref_id) given!";
            $this->ilErr->raiseError($message, $this->ilErr->WARNING);
        }

        $pathIds = $tree->getPathId($a_endnode_id);

        // add system folder since it may not in the path
        $pathIds[0] = ROLE_FOLDER_ID;
        return $this->__getParentRoles($pathIds, $a_templates);
    }

    /**
     * Returns a list of roles in an container
     * @access	public
     * @param	integer	ref_id of object
     * @param	boolean	if true fetch template roles too
     * @return	array	set ids
     * @todo refactor rolf => DONE
     */
    public function getRoleListByObject($a_ref_id, $a_templates = false)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (!isset($a_ref_id) or !isset($a_templates)) {
            $message = get_class($this) . "::getRoleListByObject(): Missing parameter!" .
                       "ref_id: " . $a_ref_id .
                       "tpl_flag: " . $a_templates;
            $this->ilErr->raiseError($message, $this->ilErr->WARNING);
        }

        $role_list = [];

        $where = $this->__setTemplateFilter($a_templates);

        $query = "SELECT * FROM object_data " .
             "JOIN rbac_fa ON obj_id = rol_id " .
             $where .
             "AND object_data.obj_id = rbac_fa.rol_id " .
             "AND rbac_fa.parent = " . $ilDB->quote($a_ref_id, 'integer') . " ";

        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($res)) {
            $row["desc"] = $row["description"];
            $row["user_id"] = $row["owner"];
            $role_list[] = $row;
        }

        $role_list = $this->__setRoleType($role_list);

        return $role_list;
    }

    /**
     * Returns a list of all assignable roles
     * @access	public
     * @param	boolean	if true fetch template roles too
     * @return	array	set ids
     * @todo refactor rolf => DONE
     */
    public function getAssignableRoles($a_templates = false, $a_internal_roles = false, $title_filter = '')
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $role_list = [];

        $where = $this->__setTemplateFilter($a_templates);

        $query = "SELECT * FROM object_data " .
             "JOIN rbac_fa ON obj_id = rol_id " .
             $where .
             "AND rbac_fa.assign = 'y' ";

        if (strlen($title_filter)) {
            $query .= (' AND ' . $ilDB->like(
                'title',
                'text',
                $title_filter . '%'
            ));
        }
        $res = $ilDB->query($query);

        while ($row = $ilDB->fetchAssoc($res)) {
            $row["desc"] = $row["description"];
            $row["user_id"] = $row["owner"];
            $role_list[] = $row;
        }

        $role_list = $this->__setRoleType($role_list);

        return $role_list;
    }

    /**
     * Returns a list of assignable roles in a subtree of the repository
     * @access	public
     * @param	ref_id Root node of subtree
     * @return	array	set ids
     * @todo refactor rolf => DONE
     */
    public function getAssignableRolesInSubtree($ref_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'SELECT rol_id FROM rbac_fa fa ' .
                'JOIN tree t1 ON t1.child = fa.parent ' .
                'JOIN object_data obd ON fa.rol_id = obd.obj_id ' .
                'WHERE assign = ' . $ilDB->quote('y', 'text') . ' ' .
                'AND obd.type = ' . $ilDB->quote('role', 'text') . ' ' .
                'AND t1.child IN (' .
                $GLOBALS['DIC']['tree']->getSubTreeQuery($ref_id, array('child')) . ' ' .
                ') ';


        $res = $ilDB->query($query);

        $role_list = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $role_list[] = $row->rol_id;
        }
        return $role_list;
    }

    /**
     * Get all assignable roles directly under a specific node
     * @access	public
     * @param ref_id
     * @return	array	set ids
     * @todo refactor rolf => Find a better name; reduce sql fields
     */
    public function getAssignableChildRoles($a_ref_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT fa.*, rd.* " .
             "FROM object_data rd " .
             "JOIN rbac_fa fa ON rd.obj_id = fa.rol_id " .
             "WHERE fa.assign = 'y' " .
             "AND fa.parent = " . $this->ilDB->quote($a_ref_id, 'integer') . " "
            ;

        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($res)) {
            $roles_data[] = $row;
        }
        return $roles_data ? $roles_data : [];
    }

    /**
     * get roles and templates or only roles; returns string for where clause
     * @access	private
     * @param	boolean	true: with templates
     * @return	string	where clause
     * @todo refactor rolf => DONE
     */
    protected function __setTemplateFilter($a_templates)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if ($a_templates === true) {
            $where = "WHERE " . $ilDB->in('object_data.type', array('role','rolt'), false, 'text') . " ";
        } else {
            $where = "WHERE " . $ilDB->in('object_data.type', array('role'), false, 'text') . " ";
        }

        return $where;
    }

    /**
     * computes role type in role list array:
     * global: roles in ROLE_FOLDER_ID
     * local: assignable roles in other role folders
     * linked: roles with stoppped inheritance
     * template: role templates
     *
     * @access	private
     * @param	array	role list
     * @return	array	role list with additional entry for role_type
     * @todo refactor rolf => DONE
     */
    protected function __setRoleType($a_role_list)
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
     * @return int
     * @todo refactor rolf => DONE
     */
    public function getNumberOfAssignedUsers(array $a_roles)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'select count(distinct(ua.usr_id)) as num from rbac_ua ua ' .
            'join object_data on ua.usr_id = obj_id ' .
            'join usr_data ud on ua.usr_id = ud.usr_id ' .
            'where ' . $ilDB->in('rol_id', $a_roles, false, 'integer');

        $res = $ilDB->query($query);
        if ($res->numRows()) {
            $row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT);
            return $row->num;
        }
        return 0;
    }


    /**
     * get all assigned users to a given role
     * @access	public
     * @param	integer	role_id
     * @return	array	all users (id) assigned to role
     */
    public function assignedUsers($a_rol_id)
    {
        global $DIC;

        $ilBench = $DIC['ilBench'];
        $ilDB = $DIC['ilDB'];

        if (!isset($a_rol_id)) {
            $message = get_class($this) . "::assignedUsers(): No role_id given!";
            $this->ilErr->raiseError($message, $this->ilErr->WARNING);
        }
        if (isset(self::$assigned_users_cache[$a_rol_id])) {
            return self::$assigned_users_cache[$a_rol_id];
        }

        $result_arr = [];

        $query = "SELECT usr_id FROM rbac_ua WHERE rol_id= " . $ilDB->quote($a_rol_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($res)) {
            array_push($result_arr, $row["usr_id"]);
        }

        self::$assigned_users_cache[$a_rol_id] = $result_arr;

        return $result_arr;
    }


    /**
     * check if a specific user is assigned to specific role
     * @access	public
     * @param	integer		usr_id
     * @param	integer		role_id
     * @return	boolean
     * @todo refactor rolf =>  DONE
     */
    public function isAssigned($a_usr_id, $a_role_id)
    {
        if (isset(self::$is_assigned_cache[$a_role_id][$a_usr_id])) {
            return self::$is_assigned_cache[$a_role_id][$a_usr_id];
        }
        // Quickly determine if user is assigned to a role
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $ilDB->setLimit(1, 0);
        $query = "SELECT usr_id FROM rbac_ua WHERE " .
                    "rol_id= " . $ilDB->quote($a_role_id, 'integer') . " " .
                    "AND usr_id= " . $ilDB->quote($a_usr_id);
        $res = $ilDB->query($query);

        $is_assigned = $res->numRows() == 1;
        self::$is_assigned_cache[$a_role_id][$a_usr_id] = $is_assigned;

        return $is_assigned;
    }

    /**
     * check if a specific user is assigned to at least one of the
     * given role ids.
     * This function is used to quickly check whether a user is member
     * of a course or a group.
     *
     * @access	public
     * @param	integer		usr_id
     * @param	array[integer]		role_ids
     * @return	boolean
     * @todo refactor rolf =>  DONE
     */
    public function isAssignedToAtLeastOneGivenRole($a_usr_id, $a_role_ids)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $ilDB->setLimit(1, 0);
        $query = "SELECT usr_id FROM rbac_ua WHERE " .
                    $ilDB->in('rol_id', $a_role_ids, false, 'integer') .
                    " AND usr_id= " . $ilDB->quote($a_usr_id);
        $res = $ilDB->query($query);

        return $ilDB->numRows($res) == 1;
    }

    /**
     * get all assigned roles to a given user
     * @access	public
     * @param	int		usr_id
     * @return	int[]	all roles (id) the user is assigned to
     * @todo refactor rolf =>  DONE
     */
    public function assignedRoles($a_usr_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $role_arr = [];
        $query = "SELECT rol_id FROM rbac_ua WHERE usr_id = " . $ilDB->quote($a_usr_id, 'integer');

        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchObject($res)) {
            $role_arr[] = $row->rol_id;
        }
        return $role_arr;
    }

    /**
     * Get assigned global roles for an user
     * @param int	$a_usr_id	Id of user account
     * @todo refactor rolf =>  DONE
     */
    public function assignedGlobalRoles($a_usr_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT ua.rol_id FROM rbac_ua ua " .
            "JOIN rbac_fa fa ON ua.rol_id = fa.rol_id " .
            "WHERE usr_id = " . $ilDB->quote($a_usr_id, 'integer') . ' ' .
            "AND parent = " . $ilDB->quote(ROLE_FOLDER_ID) . " " .
            "AND assign = 'y' ";

        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchObject($res)) {
            $role_arr[] = $row->rol_id;
        }
        return $role_arr ? $role_arr : [];
    }

    /**
     * Check if its possible to assign users
     * @access	public
     * @param	integer	object id of role
     * @param	integer	ref_id of object in question
     * @return	boolean
     * @todo refactor rolf (expects object reference id instead of rolf) => DONE
     */
    public function isAssignable($a_rol_id, $a_ref_id)
    {
        global $DIC;

        $ilBench = $DIC['ilBench'];
        $ilDB = $DIC['ilDB'];

        $ilBench->start("RBAC", "review_isAssignable");

        // exclude system role from rbac
        if ($a_rol_id == SYSTEM_ROLE_ID) {
            $ilBench->stop("RBAC", "review_isAssignable");
            return true;
        }

        if (!isset($a_rol_id) or !isset($a_ref_id)) {
            $message = get_class($this) . "::isAssignable(): Missing parameter!" .
                       " role_id: " . $a_rol_id . " ,ref_id: " . $a_ref_id;
            $this->ilErr->raiseError($message, $this->ilErr->WARNING);
        }
        $query = "SELECT * FROM rbac_fa " .
             "WHERE rol_id = " . $ilDB->quote($a_rol_id, 'integer') . " " .
             "AND parent = " . $ilDB->quote($a_ref_id, 'integer') . " ";
        $res = $ilDB->query($query);
        $row = $ilDB->fetchObject($res);

        $ilBench->stop("RBAC", "review_isAssignable");
        return $row->assign == 'y' ? true : false;
    }

    /**
     * Temporary bugfix
     * @todo refactor rolf => DONE
     *
     */
    public function hasMultipleAssignments($a_role_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM rbac_fa WHERE rol_id = " . $ilDB->quote($a_role_id, 'integer') . ' ' .
            "AND assign = " . $ilDB->quote('y', 'text');
        $res = $ilDB->query($query);
        return $res->numRows() > 1;
    }

    /**
     * Returns an array of objects assigned to a role. A role with stopped inheritance
     * may be assigned to more than one objects.
     * To get only the original location of a role, set the second parameter to true
     *
     * @access	public
     * @param	integer		role id
     * @param	boolean		get only rolefolders where role is assignable (true)
     * @return	array		reference IDs of role folders
     * @todo refactor rolf  => RENAME (rest done)
     */
    public function getFoldersAssignedToRole($a_rol_id, $a_assignable = false)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (!isset($a_rol_id)) {
            $message = get_class($this) . "::getFoldersAssignedToRole(): No role_id given!";
            $this->ilErr->raiseError($message, $this->ilErr->WARNING);
        }

        if ($a_assignable) {
            $where = " AND assign ='y'";
        }

        $query = "SELECT DISTINCT parent FROM rbac_fa " .
             "WHERE rol_id = " . $ilDB->quote($a_rol_id, 'integer') . " " . $where . " ";

        $res = $ilDB->query($query);
        $folders = [];
        while ($row = $ilDB->fetchObject($res)) {
            $folders[] = $row->parent;
        }
        return $folders;
    }

    /**
     * Get roles of object
     * @param type $a_ref_id
     * @param type $a_assignable
     * @throws InvalidArgumentException
     * @todo refactor rolf => DONE
     */
    public function getRolesOfObject($a_ref_id, $a_assignable_only = false)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (!isset($a_ref_id)) {
            $GLOBALS['DIC']['ilLog']->logStack();
            throw new InvalidArgumentException(__METHOD__ . ': No ref_id given!');
        }
        if ($a_assignable_only === true) {
            $and = 'AND assign = ' . $ilDB->quote('y', 'text');
        }
        $query = "SELECT rol_id FROM rbac_fa " .
             "WHERE parent = " . $ilDB->quote($a_ref_id, 'integer') . " " .
             $and;

        $res = $ilDB->query($query);

        $role_ids = [];
        while ($row = $ilDB->fetchObject($res)) {
            $role_ids[] = $row->rol_id;
        }
        return $role_ids;
    }




    /**
     * get all roles of a role folder including linked local roles that are created due to stopped inheritance
     * returns an array with role ids
     * @access	public
     * @param	integer		ref_id of object
     * @param	boolean		if false only get true local roles
     * @return	array		Array with rol_ids
     * @deprecated since version 4.5.0
     * @todo refactor rolf => RENAME
     */
    public function getRolesOfRoleFolder($a_ref_id, $a_nonassignable = true)
    {
        global $DIC;

        $ilBench = $DIC['ilBench'];
        $ilDB = $DIC['ilDB'];
        $ilLog = $DIC['ilLog'];

        $ilBench->start("RBAC", "review_getRolesOfRoleFolder");

        if (!isset($a_ref_id)) {
            $message = get_class($this) . "::getRolesOfRoleFolder(): No ref_id given!";
            ilLoggerFactory::getLogger('ac')->logStack();
            $this->ilErr->raiseError($message, $this->ilErr->WARNING);
        }

        if ($a_nonassignable === false) {
            $and = " AND assign='y'";
        }

        $query = "SELECT rol_id FROM rbac_fa " .
             "WHERE parent = " . $ilDB->quote($a_ref_id, 'integer') . " " .
             $and;

        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchObject($res)) {
            $rol_id[] = $row->rol_id;
        }

        $ilBench->stop("RBAC", "review_getRolesOfRoleFolder");

        return $rol_id ? $rol_id : [];
    }

    /**
     * get only 'global' roles
     * @access	public
     * @return	array		Array with rol_ids
     * @todo refactor rolf => DONE
     */
    public function getGlobalRoles()
    {
        return $this->getRolesOfRoleFolder(ROLE_FOLDER_ID, false);
    }

    /**
     * Get local roles of object
     * @param int $a_ref_id
     * @todo refactor rolf => DONE
     */
    public function getLocalRoles($a_ref_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $lroles = [];
        foreach ($this->getRolesOfRoleFolder($a_ref_id) as $role_id) {
            if ($this->isAssignable($role_id, $a_ref_id)) {
                $lroles[] = $role_id;
            }
        }
        return $lroles;
    }

    /**
     * Get all roles with local policies
     * @param type $a_ref_id
     * @return type
     */
    public function getLocalPolicies($a_ref_id)
    {
        $lroles = [];
        foreach ($this->getRolesOfRoleFolder($a_ref_id) as $role_id) {
            $lroles[] = $role_id;
        }
        return $lroles;
    }

    /**
     * get only 'global' roles
     * @access	public
     * @return	array		Array with rol_ids
     * @todo refactor rolf => DONE
     */
    public function getGlobalRolesArray()
    {
        foreach ($this->getRolesOfRoleFolder(ROLE_FOLDER_ID, false) as $role_id) {
            $ga[] = array('obj_id' => $role_id,
                          'role_type' => 'global');
        }
        return $ga ? $ga : [];
    }

    /**
     * get only 'global' roles (with flag 'assign_users')
     * @access	public
     * @return	array		Array with rol_ids
     * @todo refactor rolf => DONE
     */
    public function getGlobalAssignableRoles()
    {
        include_once './Services/AccessControl/classes/class.ilObjRole.php';

        foreach ($this->getGlobalRoles() as $role_id) {
            if (ilObjRole::_getAssignUsersStatus($role_id)) {
                $ga[] = array('obj_id' => $role_id,
                              'role_type' => 'global');
            }
        }
        return $ga ? $ga : [];
    }


    /**
     * Check if role is assigned to an object
     * @todo refactor rolf => DONE (renamed)
     */
    public function isRoleAssignedToObject($a_role_id, $a_parent_id)
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        $ilDB = $DIC['ilDB'];

        $query = 'SELECT * FROM rbac_fa ' .
            'WHERE rol_id = ' . $ilDB->quote($a_role_id, 'integer') . ' ' .
            'AND parent = ' . $ilDB->quote($a_parent_id, 'integer');
        $res = $ilDB->query($query);
        return $res->numRows() ? true : false;
    }

    /**
     * get all possible operations
     * @access	public
     * @return	array	array of operation_id
     * @todo refactor rolf => DONE
     */
    public function getOperations()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'SELECT * FROM rbac_operations ORDER BY ops_id ';
        $res = $this->ilDB->query($query);
        while ($row = $ilDB->fetchObject($res)) {
            $ops[] = array('ops_id' => $row->ops_id,
                           'operation' => $row->operation,
                           'description' => $row->description);
        }

        return $ops ? $ops : [];
    }

    /**
     * get one operation by operation id
     * @access	public
     * @return	array data of operation_id
     * @todo refactor rolf => DONE
     */
    public function getOperation($ops_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'SELECT * FROM rbac_operations WHERE ops_id = ' . $ilDB->quote($ops_id, 'integer');
        $res = $this->ilDB->query($query);
        while ($row = $ilDB->fetchObject($res)) {
            $ops = array('ops_id' => $row->ops_id,
                         'operation' => $row->operation,
                         'description' => $row->description);
        }

        return $ops ? $ops : [];
    }

    /**
     * get all possible operations of a specific role
     * The ref_id of the role folder (parent object) is necessary to distinguish local roles
     * @access	public
     * @param	integer	role_id
     * @param	integer	role folder id
     * @return	array	array of operation_id and types
     * @todo refactor rolf => DONE
     */
    public function getAllOperationsOfRole($a_rol_id, $a_parent = 0)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (!$a_parent) {
            $a_parent = ROLE_FOLDER_ID;
        }

        $query = "SELECT ops_id,type FROM rbac_templates " .
            "WHERE rol_id = " . $ilDB->quote($a_rol_id, 'integer') . " " .
            "AND parent = " . $ilDB->quote($a_parent, 'integer');
        $res = $ilDB->query($query);

        $ops_arr = [];
        while ($row = $ilDB->fetchObject($res)) {
            $ops_arr[$row->type][] = $row->ops_id;
        }
        return (array) $ops_arr;
    }

    /**
     * Get active operations for a role
     * @param object $a_ref_id
     * @param object $a_role_id
     * @return
     * @todo refactor rolf => DONE
     */
    public function getActiveOperationsOfRole($a_ref_id, $a_role_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'SELECT * FROM rbac_pa ' .
            'WHERE ref_id = ' . $ilDB->quote($a_ref_id, 'integer') . ' ' .
            'AND rol_id = ' . $ilDB->quote($a_role_id, 'integer') . ' ';

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            return unserialize($row['ops_id']);
        }
        return [];
    }


    /**
     * get all possible operations of a specific role
     * The ref_id of the role folder (parent object) is necessary to distinguish local roles
     * @access	public
     * @param	integer	role_id
     * @param	string	object type
     * @param	integer	role folder id
     * @return	array	array of operation_id
     * @todo refactor rolf => DONE
     */
    public function getOperationsOfRole($a_rol_id, $a_type, $a_parent = 0)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilLog = $DIC['ilLog'];

        if (!isset($a_rol_id) or !isset($a_type)) {
            $message = get_class($this) . "::getOperationsOfRole(): Missing Parameter!" .
                       "role_id: " . $a_rol_id .
                       "type: " . $a_type .
                       "parent_id: " . $a_parent;
            $ilLog->logStack("Missing parameter! ");
            $this->ilErr->raiseError($message, $this->ilErr->WARNING);
        }

        $ops_arr = [];

        // if no rolefolder id is given, assume global role folder as target
        if ($a_parent == 0) {
            $a_parent = ROLE_FOLDER_ID;
        }

        $query = "SELECT ops_id FROM rbac_templates " .
             "WHERE type =" . $ilDB->quote($a_type, 'text') . " " .
             "AND rol_id = " . $ilDB->quote($a_rol_id, 'integer') . " " .
             "AND parent = " . $ilDB->quote($a_parent, 'integer');
        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchObject($res)) {
            $ops_arr[] = $row->ops_id;
        }

        return $ops_arr;
    }

    /**
     * @global ilDB $ilDB
     * @param type $a_role_id
     * @param type $a_ref_id
     * @return type
     * @todo rafactor rolf => DONE
     */
    public function getRoleOperationsOnObject($a_role_id, $a_ref_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM rbac_pa " .
            "WHERE rol_id = " . $ilDB->quote($a_role_id, 'integer') . " " .
            "AND ref_id = " . $ilDB->quote($a_ref_id, 'integer') . " ";

        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchObject($res)) {
            $ops = unserialize($row->ops_id);
        }

        return $ops ? $ops : [];
    }

    /**
     * all possible operations of a type
     * @access	public
     * @param	integer		object_ID of type
     * @return	array		valid operation_IDs
     * @todo rafactor rolf => DONE
     */
    public function getOperationsOnType($a_typ_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (!isset($a_typ_id)) {
            $message = get_class($this) . "::getOperationsOnType(): No type_id given!";
            $this->ilErr->raiseError($message, $this->ilErr->WARNING);
        }

        #$query = "SELECT * FROM rbac_ta WHERE typ_id = ".$ilDB->quote($a_typ_id,'integer');

        $query = 'SELECT * FROM rbac_ta ta JOIN rbac_operations o ON ta.ops_id = o.ops_id ' .
            'WHERE typ_id = ' . $ilDB->quote($a_typ_id, 'integer') . ' ' .
            'ORDER BY op_order';

        $res = $ilDB->query($query);

        while ($row = $ilDB->fetchObject($res)) {
            $ops_id[] = $row->ops_id;
        }

        return $ops_id ? $ops_id : [];
    }

    /**
     * all possible operations of a type
     * @access	public
     * @param	integer		object_ID of type
     * @return	array		valid operation_IDs
     * @todo rafactor rolf => DONE
     *
     */
    public function getOperationsOnTypeString($a_type)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM object_data WHERE type = 'typ' AND title = " . $ilDB->quote($a_type, 'text') . " ";


        $res = $this->ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $this->getOperationsOnType($row->obj_id);
        }
        return false;
    }

    /**
     * Get operations by type and class
     * @param string $a_type Type is "object" or
     * @param string $a_class
     * @return
     * @todo refactor rolf => DONE
     */
    public function getOperationsByTypeAndClass($a_type, $a_class)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if ($a_class != 'create') {
            $condition = "AND class != " . $ilDB->quote('create', 'text');
        } else {
            $condition = "AND class = " . $ilDB->quote('create', 'text');
        }

        $query = "SELECT ro.ops_id FROM rbac_operations ro " .
            "JOIN rbac_ta rt ON  ro.ops_id = rt.ops_id " .
            "JOIN object_data od ON rt.typ_id = od.obj_id " .
            "WHERE type = " . $ilDB->quote('typ', 'text') . " " .
            "AND title = " . $ilDB->quote($a_type, 'text') . " " .
            $condition . " " .
            "ORDER BY op_order ";

        $res = $ilDB->query($query);

        $ops = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ops[] = $row->ops_id;
        }
        return $ops;
    }


    /**
     * get all objects in which the inheritance of role with role_id was stopped
     * the function returns all reference ids of objects containing a role folder.
     * @access	public
     * @param	integer	role_id
     * @param	array   filter ref_ids
     * @return	array	with ref_ids of objects
     * @todo refactor rolf => DONE
     */
    public function getObjectsWithStopedInheritance($a_rol_id, $a_filter = [])
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        #$query = 'SELECT t.parent p FROM tree t JOIN rbac_fa fa ON fa.parent = child '.
        #	'WHERE assign = '.$ilDB->quote('n','text').' '.
        #	'AND rol_id = '.$ilDB->quote($a_rol_id,'integer').' ';

        $query = 'SELECT parent p FROM rbac_fa ' .
                'WHERE assign = ' . $ilDB->quote('n', 'text') . ' ' .
                'AND rol_id = ' . $ilDB->quote($a_rol_id, 'integer') . ' ';

        if ($a_filter) {
            $query .= ('AND ' . $ilDB->in('parent', (array) $a_filter, false, 'integer'));
        }

        $res = $ilDB->query($query);
        $parent = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $parent[] = $row->p;
        }
        return $parent;
    }

    /**
     * Checks if a rolefolder is set as deleted (negative tree_id)
     * @access	public
     * @param	integer	ref_id of rolefolder
     * @return	boolean	true if rolefolder is set as deleted
     * @todo refactor rolf => DELETE method
     */
    public function isDeleted($a_node_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $q = "SELECT tree FROM tree WHERE child =" . $ilDB->quote($a_node_id) . " ";
        $r = $this->ilDB->query($q);

        $row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

        if (!$row) {
            $message = sprintf(
                '%s::isDeleted(): Role folder with ref_id %s not found!',
                get_class($this),
                $a_node_id
            );
            $this->log->write($message, $this->log->FATAL);

            return true;
        }

        // rolefolder is deleted
        if ($row->tree < 0) {
            return true;
        }

        return false;
    }

    /**
     * Check if role is a global role
     * @param type $a_role_id
     * @return type
     * @todo refactor rolf => DONE
     */
    public function isGlobalRole($a_role_id)
    {
        return in_array($a_role_id, $this->getGlobalRoles());
    }

    /**
     *
     * @global ilDB $ilDB
     * @param type $a_filter
     * @param type $a_user_id
     * @param type $title_filter
     * @return type
     * @todo refactor rolf => DONE
     */
    public function getRolesByFilter($a_filter = 0, $a_user_id = 0, $title_filter = '')
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $assign = "y";

        switch ($a_filter) {
            // all (assignable) roles
            case self::FILTER_ALL:
                return $this->getAssignableRoles(true, true, $title_filter);
                break;

            // all (assignable) global roles
            case self::FILTER_ALL_GLOBAL:
                $where = 'WHERE ' . $ilDB->in('rbac_fa.rol_id', $this->getGlobalRoles(), false, 'integer') . ' ';
                break;

            // all (assignable) local roles
            case self::FILTER_ALL_LOCAL:
            case self::FILTER_INTERNAL:
            case self::FILTER_NOT_INTERNAL:
                $where = 'WHERE ' . $ilDB->in('rbac_fa.rol_id', $this->getGlobalRoles(), true, 'integer');
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
                    return [];
                }

                $where = 'WHERE ' . $ilDB->in('rbac_fa.rol_id', $this->assignedRoles($a_user_id), false, 'integer') . ' ';
                break;
        }

        $roles = [];

        $query = "SELECT * FROM object_data " .
             "JOIN rbac_fa ON obj_id = rol_id " .
             $where .
             "AND rbac_fa.assign = " . $ilDB->quote($assign, 'text') . " ";

        if (strlen($title_filter)) {
            $query .= (' AND ' . $ilDB->like(
                'title',
                'text',
                '%' . $title_filter . '%'
            ));
        }

        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($res)) {
            $prefix = (substr($row["title"], 0, 3) == "il_") ? true : false;

            // all (assignable) internal local roles only
            if ($a_filter == 4 and !$prefix) {
                continue;
            }

            // all (assignable) non internal local roles only
            if ($a_filter == 5 and $prefix) {
                continue;
            }

            $row["desc"] = $row["description"];
            $row["user_id"] = $row["owner"];
            $roles[] = $row;
        }

        $roles = $this->__setRoleType($roles);

        return $roles ? $roles : [];
    }

    /**
     * Get type id of object
     * @global ilDB $ilDB
     * @param type $a_type
     * @return type
     * @todo refactor rolf => DONE
     */
    public function getTypeId($a_type)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $q = "SELECT obj_id FROM object_data " .
             "WHERE title=" . $ilDB->quote($a_type, 'text') . " AND type='typ'";
        $r = $ilDB->query($q);

        $row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
        return $row->obj_id;
    }

    /**
     * get ops_id's by name.
     *
     * Example usage: $rbacadmin->grantPermission($roles,ilRbacReview::_getOperationIdsByName(array('visible','read'),$ref_id));
     *
     * @access	public
     * @param	array	string name of operation. see rbac_operations
     * @return	array   integer ops_id's
     * @todo refactor rolf => DONE
     */
    public static function _getOperationIdsByName($operations)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (!count($operations)) {
            return [];
        }

        $query = 'SELECT ops_id FROM rbac_operations ' .
            'WHERE ' . $ilDB->in('operation', $operations, false, 'text');

        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchObject($res)) {
            $ops_ids[] = $row->ops_id;
        }
        return $ops_ids ? $ops_ids : [];
    }

    /**
     * get operation id by name of operation
     * @access	public
     * @access	static
     * @param	string	operation name
     * @return	integer	operation id
     * @todo refactor rolf => DONE
     */
    public static function _getOperationIdByName($a_operation)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilErr = $DIC['ilErr'];

        if (!isset($a_operation)) {
            $message = "perm::getOperationId(): No operation given!";
            $ilErr->raiseError($message, $ilErr->WARNING);
        }

        // Cache operation ids
        if (!is_array(self::$_opsCache)) {
            self::$_opsCache = [];

            $q = "SELECT ops_id, operation FROM rbac_operations";
            $r = $ilDB->query($q);
            while ($row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                self::$_opsCache[$row->operation] = $row->ops_id;
            }
        }

        // Get operation ID by name from cache
        if (array_key_exists($a_operation, self::$_opsCache)) {
            return self::$_opsCache[$a_operation];
        }
        return null;
    }

    /**
     * Lookup operation ids
     * @param array $a_type_arr e.g array('cat','crs','grp'). The operation name (e.g. 'create_cat') is generated automatically
     * @return array int Array with operation ids
     * @todo refactor rolf => DONE
     */
    public static function lookupCreateOperationIds($a_type_arr)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $operations = [];
        foreach ($a_type_arr as $type) {
            $operations[] = ('create_' . $type);
        }

        if (!count($operations)) {
            return [];
        }

        $query = 'SELECT ops_id, operation FROM rbac_operations ' .
            'WHERE ' . $ilDB->in('operation', $operations, false, 'text');

        $res = $ilDB->query($query);

        $ops_ids = [];
        while ($row = $ilDB->fetchObject($res)) {
            $type_arr = explode('_', $row->operation);
            $type = $type_arr[1];

            $ops_ids[$type] = $row->ops_id;
        }
        return $ops_ids;
    }



    /**
     * @todo refactor rolf => search calls
     * @global ilDB $ilDB
     * @param type $a_ref_id
     * @param type $a_role_id
     * @return type
     * @todo refactor rolf => DONE
     */
    public function isProtected($a_ref_id, $a_role_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        // ref_id not used yet. protected permission acts 'global' for each role,
        $query = "SELECT protected FROM rbac_fa " .
             "WHERE rol_id = " . $ilDB->quote($a_role_id, 'integer') . " ";
        $res = $ilDB->query($query);
        $row = $ilDB->fetchAssoc($res);

        return ilUtil::yn2tf($row['protected']);
    }

    /**
     * Check if role is blocked at position
     * @global ilDB $ilDB
     * @param type $a_role_id
     * @param type $a_ref_id
     * @return boolean
     */
    public function isBlockedAtPosition($a_role_id, $a_ref_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'SELECT blocked from rbac_fa ' .
                'WHERE rol_id = ' . $ilDB->quote($a_role_id, 'integer') . ' ' .
                'AND parent = ' . $ilDB->quote($a_ref_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (bool) $row->blocked;
        }
        return false;
    }

    /**
     * Check if role is blocked in upper context
     * @param type $a_role_id
     * @param type $a_ref_id
     */
    public function isBlockedInUpperContext($a_role_id, $a_ref_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $tree = $DIC['tree'];

        if ($this->isBlockedAtPosition($a_role_id, $a_ref_id)) {
            return false;
        }
        $query = 'SELECT parent from rbac_fa ' .
                'WHERE rol_id = ' . $ilDB->quote($a_role_id, 'integer') . ' ' .
                'AND blocked = ' . $ilDB->quote(1, 'integer');
        $res = $ilDB->query($query);

        $parent_ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $parent_ids[] = $row->parent;
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

    /**
     * Set protected status
     * @global type $rbacsystem
     * @global type $ilUser
     * @global type $log
     * @param type $a_parent_roles
     * @param type $a_role_hierarchy
     * @param type $a_ref_id
     * @return boolean
     * @todo refactor rolf => DONE
     */
    protected function __setProtectedStatus($a_parent_roles, $a_role_hierarchy, $a_ref_id)
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $ilUser = $DIC['ilUser'];
        $log = $DIC['log'];

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
                $arr_lvl_roles_user = array_intersect($this->assignedRoles($ilUser->getId()), array_keys($a_role_hierarchy, $rolf_id));

                foreach ($arr_lvl_roles_user as $lvl_role_id) {
                    // check if role grants 'edit_permission' to parent
                    $rolf = $a_parent_roles[$role_id]['parent'];
                    if ($rbacsystem->checkPermission($rolf, $lvl_role_id, 'edit_permission')) {
                        // user may change permissions of that higher-ranked role
                        $a_parent_roles[$role_id]['protected'] = false;
                    }
                }
            }
        }
        return $a_parent_roles;
    }

    /**
     * get operation list by object type
     * @access	public
     * @access 	static
     * @param	string	object type you want to have the operation list
     * @param	string	order column
     * @param	string	order direction (possible values: ASC or DESC)
     * @return	array	returns array of operations
     * @todo refactor rolf => DONE
     */
    public static function _getOperationList($a_type = null)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $arr = [];

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
                        "ops_id" => $row['ops_id'],
                        "operation" => $row['operation'],
                        "desc" => $row['description'],
                        "class" => $row['class'],
                        "order" => $row['op_order']
                        );
        }
        return $arr;
    }

    /**
     *
     * @param type $a_ops_arr
     * @return type
     * @todo refactor rolf => DONE
     */
    public static function _groupOperationsByClass($a_ops_arr)
    {
        $arr = [];

        foreach ($a_ops_arr as $ops) {
            $arr[$ops['class']][] = array('ops_id' => $ops['ops_id'],
                                           'name' => $ops['operation']
                                         );
        }
        return $arr;
    }

    /**
     * Get object id of objects a role is assigned to
     *
     * @todo refactor rolf (due to performance reasons the new version does not check for deleted roles only in object reference)
     *
     * @access public
     * @param int role id
     *
     */
    public function getObjectOfRole($a_role_id)
    {
        // internal cache
        static $obj_cache = [];

        global $DIC;

        $ilDB = $DIC['ilDB'];


        if (isset($obj_cache[$a_role_id]) and $obj_cache[$a_role_id]) {
            return $obj_cache[$a_role_id];
        }

        $query = 'SELECT obr.obj_id FROM rbac_fa rfa ' .
                'JOIN object_reference obr ON rfa.parent = obr.ref_id ' .
                'WHERE assign = ' . $ilDB->quote('y', 'text') . ' ' .
                'AND rol_id = ' . $ilDB->quote($a_role_id, 'integer') . ' ' .
                'AND deleted IS NULL';

        #$query = "SELECT obr.obj_id FROM rbac_fa rfa ".
        #	"JOIN tree ON rfa.parent = tree.child ".
        #	"JOIN object_reference obr ON tree.parent = obr.ref_id ".
        #	"WHERE tree.tree = 1 ".
        #	"AND assign = 'y' ".
        #	"AND rol_id = ".$ilDB->quote($a_role_id,'integer')." ";
        $res = $ilDB->query($query);

        $obj_cache[$a_role_id] = 0;
        while ($row = $ilDB->fetchObject($res)) {
            $obj_cache[$a_role_id] = $row->obj_id;
        }
        return $obj_cache[$a_role_id];
    }

    /**
     * Get reference of role
     * @param object $a_role_id
     * @return int
     * @todo refactor rolf (no deleted check)
     */
    public function getObjectReferenceOfRole($a_role_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'SELECT parent p_ref FROM rbac_fa ' .
                'WHERE rol_id = ' . $ilDB->quote($a_role_id, 'integer') . ' ' .
                'AND assign = ' . $ilDB->quote('y', 'text');

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->p_ref;
        }
        return 0;
    }

    /**
     * return if role is only attached to deleted role folders
     *
     * @param int $a_role_id
     * @return boolean
     * @todo refactor rolf => DONE
     */
    public function isRoleDeleted($a_role_id)
    {
        $rolf_list = $this->getFoldersAssignedToRole($a_role_id, false);
        $deleted = true;
        if (count($rolf_list)) {
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


    /**
     * @global ilDB $ilDB
     * @param type $role_ids
     * @param type $use_templates
     * @return type
     * @todo refactor rolf => DONE
     */
    public function getRolesForIDs($role_ids, $use_templates)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $role_list = [];

        $where = $this->__setTemplateFilter($use_templates);

        $query = "SELECT * FROM object_data " .
             "JOIN rbac_fa ON object_data.obj_id = rbac_fa.rol_id " .
             $where .
             "AND rbac_fa.assign = 'y' " .
             'AND ' . $ilDB->in('object_data.obj_id', $role_ids, false, 'integer');

        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($res)) {
            $row["desc"] = $row["description"];
            $row["user_id"] = $row["owner"];
            $role_list[] = $row;
        }

        $role_list = $this->__setRoleType($role_list);
        return $role_list;
    }

    /**
     * get operation assignments
     * @return array array(array('typ_id' => $typ_id,'title' => $title,'ops_id => '$ops_is,'operation' => $operation),...
     * @todo refactor rolf => DONE
     */
    public function getOperationAssignment()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'SELECT ta.typ_id, obj.title, ops.ops_id, ops.operation FROM rbac_ta ta ' .
             'JOIN object_data obj ON obj.obj_id = ta.typ_id ' .
             'JOIN rbac_operations ops ON ops.ops_id = ta.ops_id ';
        $res = $ilDB->query($query);

        $counter = 0;
        while ($row = $ilDB->fetchObject($res)) {
            $info[$counter]['typ_id'] = $row->typ_id;
            $info[$counter]['type'] = $row->title;
            $info[$counter]['ops_id'] = $row->ops_id;
            $info[$counter]['operation'] = $row->operation;
            $counter++;
        }
        return $info ? $info : [];
    }

    /**
     * Check if role is deleteable at a specific position
     * @param object $a_role_id
     * @param int rolf_id
     * @return
     * @todo refactor rolf => DONE
     */
    public function isDeleteable($a_role_id, $a_rolf_id)
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
     * @param int $a_role_id
     * @return bool
     * @todo refactor rolf => DONE
     */
    public function isSystemGeneratedRole($a_role_id)
    {
        $title = ilObject::_lookupTitle($a_role_id);
        return substr($title, 0, 3) == 'il_' ? true : false;
    }


    public function getParentOfRole(int $role_id, ?int $object_ref = null) : ?int
    {
        global $DIC;
        /** @var ilTree $tree */
        $tree = $DIC['tree'];

        if ($object_ref === null || $object_ref === ROLE_FOLDER_ID) {
            return $this->getRoleFolderOfRole($role_id);
        }


        $path_ids = $tree->getPathId($object_ref);
        array_unshift($path_ids, ROLE_FOLDER_ID);

        while ($ref_id = array_pop($path_ids)) {
            $roles = $this->getRoleListByObject($ref_id, false);
            foreach ($roles as $role) {
                if ((int) $role['obj_id'] === $role_id) {
                    return $ref_id;
                }
            }
        }

        return null;
    }


    /**
     * Get role folder of role
     * @global ilDB $ilDB
     * @param int $a_role_id
     * @return int
     * @todo refactor rolf => RENAME
     */
    public function getRoleFolderOfRole($a_role_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (ilObject::_lookupType($a_role_id) == 'role') {
            $and = ('AND assign = ' . $ilDB->quote('y', 'text'));
        } else {
            $and = '';
        }

        $query = 'SELECT * FROM rbac_fa ' .
            'WHERE rol_id = ' . $ilDB->quote($a_role_id, 'integer') . ' ' .
            $and;
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->parent;
        }
        return 0;
    }

    /**
     * Get all user permissions on an object
     *
     * @param int $a_user_id user id
     * @param int $a_ref_id ref id
     * @todo refactor rolf => DONE
     */
    public function getUserPermissionsOnObject($a_user_id, $a_ref_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT ops_id FROM rbac_pa JOIN rbac_ua " .
            "ON (rbac_pa.rol_id = rbac_ua.rol_id) " .
            "WHERE rbac_ua.usr_id = " . $ilDB->quote($a_user_id, 'integer') . " " .
            "AND rbac_pa.ref_id = " . $ilDB->quote($a_ref_id, 'integer') . " ";

        $res = $ilDB->query($query);
        $all_ops = [];
        while ($row = $ilDB->fetchObject($res)) {
            $ops = unserialize($row->ops_id);
            $all_ops = array_merge($all_ops, $ops);
        }
        $all_ops = array_unique($all_ops);

        $set = $ilDB->query("SELECT operation FROM rbac_operations " .
            " WHERE " . $ilDB->in("ops_id", $all_ops, false, "integer"));
        $perms = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $perms[] = $rec["operation"];
        }

        return $perms;
    }

    /**
     * set entry of assigned_chache
     * @param int $a_role_id
     * @param int $a_user_id
     * @param bool $a_value
     */
    public function setAssignedCacheEntry($a_role_id, $a_user_id, $a_value)
    {
        self::$is_assigned_cache[$a_role_id][$a_user_id] = $a_value;
    }

    /**
     * get entry of assigned_chache
     * @param int $a_role_id
     * @param int $a_user_id

     */
    public function getAssignedCacheEntry($a_role_id, $a_user_id)
    {
        return self::$is_assigned_cache[$a_role_id][$a_user_id];
    }

    /**
     * Clear assigned users caches
     */
    public function clearCaches()
    {
        self::$is_assigned_cache = [];
        self::$assigned_users_cache = [];
    }
} // END class.ilRbacReview
