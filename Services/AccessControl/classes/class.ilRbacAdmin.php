<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class ilRbacAdmin
*  Core functions for role based access control.
*  Creation and maintenance of Relations.
*  The main relations of Rbac are user <-> role (UR) assignment relation and the permission <-> role (PR) assignment relation.
*  This class contains methods to 'create' and 'delete' instances of the (UR) relation e.g.: assignUser(), deassignUser()
*  Required methods for the PR relation are grantPermission(), revokePermission()
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @ingroup ServicesAccessControl
*/
class ilRbacAdmin
{
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
     * Set blocked status
     * @param type $a_role_id
     * @param type $a_ref_id
     * @param type $a_blocked_status
     */
    public function setBlockedStatus($a_role_id, $a_ref_id, $a_blocked_status)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        ilLoggerFactory::getLogger('crs')->logStack();
        $query = 'UPDATE rbac_fa set blocked = ' . $ilDB->quote($a_blocked_status, 'integer') . ' ' .
                'WHERE rol_id = ' . $ilDB->quote($a_role_id, 'integer') . ' ' .
                'AND parent = ' . $ilDB->quote($a_ref_id, 'integer');
        $ilDB->manipulate($query);
    }

    /**
     * deletes a user from rbac_ua
     * all user <-> role relations are deleted
     * @access	public
     * @param	int	user_id
     * @return	boolean	true on success
     */
    public function removeUser($a_usr_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        $review = $DIC->rbac()->review();

        if (!isset($a_usr_id)) {
            $message = get_class($this) . "::removeUser(): No usr_id given!";
            $this->ilErr->raiseError($message, $this->ilErr->WARNING);
        }

        foreach ($review->assignedRoles($a_usr_id) as $role_id) {
            $this->deassignUser($role_id, $a_usr_id);
        }

        $query = "DELETE FROM rbac_ua WHERE usr_id = " . $ilDB->quote($a_usr_id, 'integer');
        $res = $ilDB->manipulate($query);

        return true;
    }

    /**
     * Deletes a role and deletes entries in object_data, rbac_pa, rbac_templates, rbac_ua, rbac_fa
     * @access	public
     * @param	integer		obj_id of role (role_id)
     * @param	integer		ref_id of role folder (ref_id)
     * @return	boolean     true on success
     */
    public function deleteRole($a_rol_id, $a_ref_id)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilDB = $DIC['ilDB'];

        if (!isset($a_rol_id) || !isset($a_ref_id)) {
            $message = get_class($this) . "::deleteRole(): Missing parameter! role_id: " . $a_rol_id . " ref_id of role folder: " . $a_ref_id;
            $this->ilErr->raiseError($message, $this->ilErr->WARNING);
        }

        // exclude system role from rbac
        if ($a_rol_id == SYSTEM_ROLE_ID) {
            $this->ilErr->raiseError($lng->txt("msg_sysrole_not_deletable"), $this->ilErr->MESSAGE);
        }

        include_once('Services/LDAP/classes/class.ilLDAPRoleGroupMapping.php');
        $mapping = ilLDAPRoleGroupMapping::_getInstance();
        $mapping->deleteRole($a_rol_id);


        // TODO: check assigned users before deletion
        // This is done in ilObjRole. Should be better moved to this place?

        // delete user assignements
        $query = "DELETE FROM rbac_ua " .
             "WHERE rol_id = " . $ilDB->quote($a_rol_id, 'integer');
        $res = $ilDB->manipulate($query);

        // delete permission assignments
        $query = "DELETE FROM rbac_pa " .
             "WHERE rol_id = " . $ilDB->quote($a_rol_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);

        //delete rbac_templates and rbac_fa
        $this->deleteLocalRole($a_rol_id);

        return true;
    }

    /**
     * Deletes a template from role folder and deletes all entries in rbac_templates, rbac_fa
     * @access	public
     * @param	integer		object_id of role template
     * @return	boolean
     */
    public function deleteTemplate($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (!isset($a_obj_id)) {
            $message = get_class($this) . "::deleteTemplate(): No obj_id given!";
            $this->ilErr->raiseError($message, $this->ilErr->WARNING);
        }

        $query = 'DELETE FROM rbac_templates ' .
             'WHERE rol_id = ' . $ilDB->quote($a_obj_id, 'integer');
        $res = $ilDB->manipulate($query);

        $query = 'DELETE FROM rbac_fa ' .
            'WHERE rol_id = ' . $ilDB->quote($a_obj_id, 'integer');
        $res = $ilDB->manipulate($query);

        return true;
    }

    /**
     * Deletes a local role and entries in rbac_fa and rbac_templates
     * @access	public
     * @param	integer	object_id of role
     * @param	integer	ref_id of role folder (optional)
     * @return	boolean true on success
     */
    public function deleteLocalRole($a_rol_id, $a_ref_id = 0)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (!isset($a_rol_id)) {
            $message = get_class($this) . "::deleteLocalRole(): Missing parameter! role_id: '" . $a_rol_id . "'";
            $this->ilErr->raiseError($message, $this->ilErr->WARNING);
        }

        // exclude system role from rbac
        if ($a_rol_id == SYSTEM_ROLE_ID) {
            return true;
        }

        if ($a_ref_id != 0) {
            $clause = 'AND parent = ' . $ilDB->quote($a_ref_id, 'integer') . ' ';
        }

        $query = 'DELETE FROM rbac_fa ' .
             'WHERE rol_id = ' . $ilDB->quote($a_rol_id, 'integer') . ' ' .
             $clause;
        $res = $ilDB->manipulate($query);

        $query = 'DELETE FROM rbac_templates ' .
             'WHERE rol_id = ' . $ilDB->quote($a_rol_id, 'integer') . ' ' .
             $clause;
        $res = $ilDB->manipulate($query);
        return true;
    }

    /**
     * Assign user limited
     * @param type $a_role_id
     * @param type $a_usr_id
     * @param type $a_limit
     */
    public function assignUserLimited($a_role_id, $a_usr_id, $a_limit, $a_limited_roles = [])
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $ilAtomQuery = $ilDB->buildAtomQuery();
        $ilAtomQuery->addTableLock('rbac_ua');

        $ilAtomQuery->addQueryCallable(
            function (ilDBInterface $ilDB) use (&$ret, $a_role_id, $a_usr_id, $a_limit, $a_limited_roles) {
                $ret = true;
                $limit_query = 'SELECT COUNT(*) num FROM rbac_ua ' .
                'WHERE ' . $ilDB->in('rol_id', (array) $a_limited_roles, false, 'integer');
                $res = $ilDB->query($limit_query);
                $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
                if ($row->num >= $a_limit) {
                    $ret = false;
                    return;
                }

                $query = "INSERT INTO rbac_ua (usr_id, rol_id) " .
                "VALUES (" .
                $ilDB->quote($a_usr_id, 'integer') . "," . $ilDB->quote($a_role_id, 'integer') .
                ")";
                $res = $ilDB->manipulate($query);
            }
        );

        $ilAtomQuery->run();

        if (!$ret) {
            return false;
        }

        $GLOBALS['DIC']['rbacreview']->setAssignedCacheEntry($a_role_id, $a_usr_id, true);

        include_once('Services/LDAP/classes/class.ilLDAPRoleGroupMapping.php');
        $mapping = ilLDAPRoleGroupMapping::_getInstance();
        $mapping->assign($a_role_id, $a_usr_id);
        return true;
    }


    /**
     * Assigns an user to a role. Update of table rbac_ua
     *
     * @param    int $a_rol_id Object-ID of role
     * @param    int $a_usr_id Object-ID of user
     *
     * @return    boolean
     */
    public function assignUser($a_rol_id, $a_usr_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $rbacreview = $DIC['rbacreview'];

        if (!isset($a_rol_id) || !isset($a_usr_id)) {
            $message = get_class($this) . "::assignUser(): Missing parameter! role_id: " . $a_rol_id . " usr_id: " . $a_usr_id;
            #$this->ilErr->raiseError($message,$this->ilErr->WARNING);
        }

        // check if already assigned user id and role_id
        $alreadyAssigned = $rbacreview->isAssigned($a_usr_id, $a_rol_id);

        // enhanced: only if we haven't had this role for this user
        if (!$alreadyAssigned) {
            $query = "INSERT INTO rbac_ua (usr_id, rol_id) " .
             "VALUES (" . $ilDB->quote($a_usr_id, 'integer') . "," . $ilDB->quote($a_rol_id, 'integer') . ")";
            $res = $ilDB->manipulate($query);

            $rbacreview->setAssignedCacheEntry($a_rol_id, $a_usr_id, true);
        }

        include_once('Services/LDAP/classes/class.ilLDAPRoleGroupMapping.php');
        $mapping = ilLDAPRoleGroupMapping::_getInstance();
        $mapping->assign($a_rol_id, $a_usr_id);


        $ref_id = $GLOBALS['DIC']['rbacreview']->getObjectReferenceOfRole($a_rol_id);
        $obj_id = ilObject::_lookupObjId($ref_id);
        $type = ilObject::_lookupType($obj_id);

        if (!$alreadyAssigned) {
            ilLoggerFactory::getInstance()->getLogger('ac')->debug('Raise event assign user');
            $GLOBALS['DIC']['ilAppEventHandler']->raise(
                'Services/AccessControl',
                'assignUser',
                array(
                        'obj_id' => $obj_id,
                        'usr_id' => $a_usr_id,
                        'role_id' => $a_rol_id,
                        'type' => $type
                    )
            );
        }
        return true;
    }


    /**
     * Deassigns a user from a role. Update of table rbac_ua
     *
     * @param    int $a_rol_id Object-ID of role
     * @param    int $a_usr_id Object-ID of user
     *
     * @return    boolean    true on success
     */
    public function deassignUser($a_rol_id, $a_usr_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $rbacreview = $DIC->rbac()->review();

        if (!isset($a_rol_id) || !isset($a_usr_id)) {
            $message = get_class($this) . "::deassignUser(): Missing parameter! role_id: " . $a_rol_id . " usr_id: " . $a_usr_id;
            $this->ilErr->raiseError($message, $this->ilErr->WARNING);
        }

        $query = "DELETE FROM rbac_ua " .
             "WHERE usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " " .
             "AND rol_id = " . $ilDB->quote($a_rol_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);

        $rbacreview->setAssignedCacheEntry($a_rol_id, $a_usr_id, false);

        include_once('Services/LDAP/classes/class.ilLDAPRoleGroupMapping.php');
        $mapping = ilLDAPRoleGroupMapping::_getInstance();
        $mapping->deassign($a_rol_id, $a_usr_id);

        if ($res) {
            $ref_id = $GLOBALS['DIC']['rbacreview']->getObjectReferenceOfRole($a_rol_id);
            $obj_id = ilObject::_lookupObjId($ref_id);
            $type = ilObject::_lookupType($obj_id);

            ilLoggerFactory::getInstance()->getLogger('ac')->debug('Raise event deassign user');
            $GLOBALS['DIC']['ilAppEventHandler']->raise('Services/AccessControl', 'deassignUser', array(
                    'obj_id' => $obj_id,
                    'usr_id' => $a_usr_id,
                    'role_id' => $a_rol_id,
                    'type' => $type,
            ));
        }

        return true;
    }

    /**
     * Grants a permission to an object and a specific role. Update of table rbac_pa
     * @access	public
     * @param	integer	object id of role
     * @param	array	array of operation ids
     * @param	integer	reference id of that object which is granted the permissions
     * @return	boolean
     */
    public function grantPermission($a_rol_id, $a_ops, $a_ref_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (!isset($a_rol_id) || !isset($a_ops) || !isset($a_ref_id)) {
            $this->ilErr->raiseError(get_class($this) . "::grantPermission(): Missing parameter! " .
                            "role_id: " . $a_rol_id . " ref_id: " . $a_ref_id . " operations: ", $this->ilErr->WARNING);
        }

        if (!is_array($a_ops)) {
            $this->ilErr->raiseError(
                get_class($this) . "::grantPermission(): Wrong datatype for operations!",
                $this->ilErr->WARNING
            );
        }

        // exclude system role from rbac
        if ($a_rol_id == SYSTEM_ROLE_ID) {
            return true;
        }

        // convert all values to integer
        foreach ($a_ops as $key => $operation) {
            $a_ops[$key] = (int) $operation;
        }

        // Serialization des ops_id Arrays
        $ops_ids = serialize($a_ops);

        $query = 'DELETE FROM rbac_pa ' .
            'WHERE rol_id = %s ' .
            'AND ref_id = %s';
        $res = $ilDB->queryF(
            $query,
            array('integer','integer'),
            array($a_rol_id,$a_ref_id)
        );

        if (!count($a_ops)) {
            return false;
        }

        $query = "INSERT INTO rbac_pa (rol_id, ops_id, ref_id) " .
             "VALUES " .
             "(" . $ilDB->quote($a_rol_id, 'integer') . ", " . $ilDB->quote($ops_ids, 'text') . ", " . $ilDB->quote($a_ref_id, 'integer') . ")";
        $res = $ilDB->manipulate($query);

        return true;
    }

    /**
     * Revokes permissions of an object of one role. Update of table rbac_pa.
     * Revokes all permission for all roles for that object (with this reference).
     * When a role_id is given this applies only to that role
     * @access	public
     * @param	integer	reference id of object where permissions should be revoked
     * @param	integer	role_id (optional: if you want to revoke permissions of object only for a specific role)
     * @return	boolean
     */
    public function revokePermission($a_ref_id, $a_rol_id = 0, $a_keep_protected = true)
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        $log = $DIC['log'];
        $ilDB = $DIC['ilDB'];
        $ilLog = $DIC['ilLog'];

        if (!isset($a_ref_id)) {
            $ilLog->logStack();
            $message = get_class($this) . "::revokePermission(): Missing parameter! ref_id: " . $a_ref_id;
            $this->ilErr->raiseError($message, $this->ilErr->WARNING);
        }

        // bypass protected status of roles
        if ($a_keep_protected != true) {
            // exclude system role from rbac
            if ($a_rol_id == SYSTEM_ROLE_ID) {
                return true;
            }

            if ($a_rol_id) {
                $and1 = " AND rol_id = " . $ilDB->quote($a_rol_id, 'integer') . " ";
            } else {
                $and1 = "";
            }

            $query = "DELETE FROM rbac_pa " .
                 "WHERE ref_id = " . $ilDB->quote($a_ref_id, 'integer') .
                 $and1;

            $res = $ilDB->manipulate($query);

            return true;
        }

        // consider protected status of roles

        // in any case, get all roles in scope first
        $roles_in_scope = $rbacreview->getParentRoleIds($a_ref_id);

        if (!$a_rol_id) {
            $role_ids = [];

            foreach ($roles_in_scope as $role) {
                if ($role['protected'] == true) {
                    continue;
                }

                $role_ids[] = $role['obj_id'];
            }

            // return if no role in array
            if (!$role_ids) {
                return true;
            }

            $query = 'DELETE FROM rbac_pa ' .
                'WHERE ' . $ilDB->in('rol_id', $role_ids, false, 'integer') . ' ' .
                'AND ref_id = ' . $ilDB->quote($a_ref_id, 'integer');
            $res = $ilDB->manipulate($query);
        } else {
            // exclude system role from rbac
            if ($a_rol_id == SYSTEM_ROLE_ID) {
                return true;
            }

            // exclude protected permission settings from revoking
            if ($roles_in_scope[$a_rol_id]['protected'] == true) {
                return true;
            }

            $query = "DELETE FROM rbac_pa " .
                 "WHERE ref_id = " . $ilDB->quote($a_ref_id, 'integer') . " " .
                 "AND rol_id = " . $ilDB->quote($a_rol_id, 'integer') . " ";
            $res = $ilDB->manipulate($query);
        }

        return true;
    }

    /**
     * Revoke subtree permissions
     * @param object $a_ref_id
     * @param object $a_role_id
     * @return
     */
    public function revokeSubtreePermissions($a_ref_id, $a_role_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'DELETE FROM rbac_pa ' .
                'WHERE ref_id IN ' .
                '( ' . $GLOBALS['DIC']['tree']->getSubTreeQuery($a_ref_id, array('child')) . ' ) ' .
                'AND rol_id = ' . $ilDB->quote($a_role_id, 'integer');

        $ilDB->manipulate($query);
        return true;
    }

    /**
     * Delete all template permissions of subtree nodes
     * @param object $a_ref_id
     * @param object $a_rol_id
     * @return
     */
    public function deleteSubtreeTemplates($a_ref_id, $a_rol_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'DELETE FROM rbac_templates ' .
                'WHERE parent IN ( ' .
                $GLOBALS['DIC']['tree']->getSubTreeQuery($a_ref_id, array('child')) . ' ) ' .
                'AND rol_id = ' . $ilDB->quote($a_rol_id, 'integer');

        $ilDB->manipulate($query);

        $query = 'DELETE FROM rbac_fa ' .
                'WHERE parent IN ( ' .
                $GLOBALS['DIC']['tree']->getSubTreeQuery($a_ref_id, array('child')) . ' ) ' .
                'AND rol_id = ' . $ilDB->quote($a_rol_id, 'integer');

        $ilDB->manipulate($query);

        return true;
    }

    /**
     * Revokes permissions of a LIST of objects of ONE role. Update of table rbac_pa.
     * @access	public
     * @param	array	list of reference_ids to revoke permissions
     * @param	integer	role_id
     * @return	boolean
     */
    public function revokePermissionList($a_ref_ids, $a_rol_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (!isset($a_ref_ids) || !is_array($a_ref_ids)) {
            $message = get_class($this) . "::revokePermissionList(): Missing parameter or parameter is not an array! reference_list: " . var_dump($a_ref_ids);
            $this->ilErr->raiseError($message, $this->ilErr->WARNING);
        }

        if (!isset($a_rol_id)) {
            $message = get_class($this) . "::revokePermissionList(): Missing parameter! rol_id: " . $a_rol_id;
            $this->ilErr->raiseError($message, $this->ilErr->WARNING);
        }

        // exclude system role from rbac
        if ($a_rol_id == SYSTEM_ROLE_ID) {
            return true;
        }

        $query = "DELETE FROM rbac_pa " .
             "WHERE " . $ilDB->in('ref_id', $a_ref_ids, false, 'integer') . ' ' .
             "AND rol_id = " . $ilDB->quote($a_rol_id, 'integer');
        $res = $ilDB->manipulate($query);

        return true;
    }

    /**
     * Copies template permissions and permission of one role to another.
     *
     * @access	public
     * @param	integer		$a_source_id		role_id source
     * @param	integer		$a_source_parent	parent_id source
     * @param	integer		$a_dest_parent		parent_id destination
     * @param	integer		$a_dest_id			role_id destination
     * @return	boolean
     */
    public function copyRolePermissions($a_source_id, $a_source_parent, $a_dest_parent, $a_dest_id, $a_consider_protected = true)
    {
        global $DIC;

        $tree = $DIC['tree'];
        $rbacreview = $DIC['rbacreview'];

        // Copy template permissions
        $this->copyRoleTemplatePermissions($a_source_id, $a_source_parent, $a_dest_parent, $a_dest_id, $a_consider_protected);

        $ops = $rbacreview->getRoleOperationsOnObject($a_source_id, $a_source_parent);

        $this->revokePermission($a_dest_parent, $a_dest_id);
        $this->grantPermission($a_dest_id, $ops, $a_dest_parent);
        return true;
    }

    /**
     * Copies template permissions of one role to another.
     * It's also possible to copy template permissions from/to RoleTemplateObject
     * @access	public
     * @param	integer		$a_source_id		role_id source
     * @param	integer		$a_source_parent	parent_id source
     * @param	integer		$a_dest_parent		parent_id destination
     * @param	integer		$a_dest_id			role_id destination
     * @return	boolean
     */
    public function copyRoleTemplatePermissions($a_source_id, $a_source_parent, $a_dest_parent, $a_dest_id, $a_consider_protected = true)
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        $ilDB = $DIC['ilDB'];

        if (!isset($a_source_id) || !isset($a_source_parent) || !isset($a_dest_id) || !isset($a_dest_parent)) {
            $message = __METHOD__ . ": Missing parameter! source_id: " . $a_source_id .
                       " source_parent_id: " . $a_source_parent .
                       " dest_id : " . $a_dest_id .
                       " dest_parent_id: " . $a_dest_parent;
            $this->ilErr->raiseError($message, $this->ilErr->WARNING);
        }

        // exclude system role from rbac
        if ($a_dest_id == SYSTEM_ROLE_ID) {
            return true;
        }

        // Read operations
        $query = 'SELECT * FROM rbac_templates ' .
             'WHERE rol_id = ' . $ilDB->quote($a_source_id, 'integer') . ' ' .
             'AND parent = ' . $ilDB->quote($a_source_parent, 'integer');
        $res = $ilDB->query($query);
        $operations = [];
        $rownum = 0;
        while ($row = $ilDB->fetchObject($res)) {
            $operations[$rownum]['type'] = $row->type;
            $operations[$rownum]['ops_id'] = $row->ops_id;
            $rownum++;
        }

        // Delete target permissions
        $query = 'DELETE FROM rbac_templates WHERE rol_id = ' . $ilDB->quote($a_dest_id, 'integer') . ' ' .
            'AND parent = ' . $ilDB->quote($a_dest_parent, 'integer');
        $res = $ilDB->manipulate($query);

        foreach ($operations as $row => $op) {
            $query = 'INSERT INTO rbac_templates (rol_id,type,ops_id,parent) ' .
                 'VALUES (' .
                 $ilDB->quote($a_dest_id, 'integer') . "," .
                 $ilDB->quote($op['type'], 'text') . "," .
                 $ilDB->quote($op['ops_id'], 'integer') . "," .
                 $ilDB->quote($a_dest_parent, 'integer') . ")";
            $ilDB->manipulate($query);
        }

        // copy also protection status if applicable
        if ($a_consider_protected == true) {
            if ($rbacreview->isProtected($a_source_parent, $a_source_id)) {
                $this->setProtected($a_dest_parent, $a_dest_id, 'y');
            }
        }

        return true;
    }
    /**
     * Copies the intersection of the template permissions of two roles to a
     * third role.
     *
     * @access	public
     * @param	integer		$a_source1_id		role_id source
     * @param	integer		$a_source1_parent	parent_id source
     * @param	integer		$a_source2_id		role_id source
     * @param	integer		$a_source2_parent	parent_id source
     * @param	integer		$a_dest_id			role_id destination
     * @param	integer		$a_dest_parent		parent_id destination
     * @return	boolean
     */
    public function copyRolePermissionIntersection($a_source1_id, $a_source1_parent, $a_source2_id, $a_source2_parent, $a_dest_parent, $a_dest_id)
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        $ilDB = $DIC['ilDB'];

        if (!isset($a_source1_id) || !isset($a_source1_parent)
            || !isset($a_source2_id) || !isset($a_source2_parent)
            || !isset($a_dest_id) || !isset($a_dest_parent)) {
            $message = get_class($this) . "::copyRolePermissionIntersection(): Missing parameter! source1_id: " . $a_source1_id .
                       " source1_parent: " . $a_source1_parent .
                       " source2_id: " . $a_source2_id .
                       " source2_parent: " . $a_source2_parent .
                       " dest_id: " . $a_dest_id .
                       " dest_parent_id: " . $a_dest_parent;
            $this->ilErr->raiseError($message, $this->ilErr->WARNING);
        }

        // exclude system role from rbac
        if ($a_dest_id == SYSTEM_ROLE_ID) {
            ilLoggerFactory::getLogger('ac')->debug('Ignoring system role.');
            return true;
        }

        if ($rbacreview->isProtected($a_source2_parent, $a_source2_id)) {
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Role is protected');
            return true;
        }

        $query = "SELECT s1.type, s1.ops_id " .
                        "FROM rbac_templates s1, rbac_templates s2 " .
                        "WHERE s1.rol_id = " . $ilDB->quote($a_source1_id, 'integer') . " " .
                        "AND s1.parent = " . $ilDB->quote($a_source1_parent, 'integer') . " " .
                        "AND s2.rol_id = " . $ilDB->quote($a_source2_id, 'integer') . " " .
                        "AND s2.parent = " . $ilDB->quote($a_source2_parent, 'integer') . " " .
                        "AND s1.type = s2.type " .
                        "AND s1.ops_id = s2.ops_id";

        ilLoggerFactory::getLogger('ac')->dump($query);

        $res = $ilDB->query($query);
        $operations = [];
        $rowNum = 0;
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $operations[$rowNum]['type'] = $row->type;
            $operations[$rowNum]['ops_id'] = $row->ops_id;

            $rowNum++;
        }

        // Delete template permissions of target
        $query = 'DELETE FROM rbac_templates WHERE rol_id = ' . $ilDB->quote($a_dest_id, 'integer') . ' ' .
            'AND parent = ' . $ilDB->quote($a_dest_parent, 'integer');
        $res = $ilDB->manipulate($query);

        $query = 'INSERT INTO rbac_templates (rol_id,type,ops_id,parent) ' .
            'VALUES (?,?,?,?)';
        $sta = $ilDB->prepareManip($query, array('integer','text','integer','integer'));
        foreach ($operations as $key => $set) {
            $ilDB->execute($sta, array(
                $a_dest_id,
                $set['type'],
                $set['ops_id'],
                $a_dest_parent));
        }
        return true;
    }

    /**
     *
     * @global <type> $ilDB
     * @param <type> $a_source1_id
     * @param <type> $a_source1_parent
     * @param <type> $a_source2_id
     * @param <type> $a_source2_parent
     * @param <type> $a_dest_id
     * @param <type> $a_dest_parent
     * @return <type>
     */
    public function copyRolePermissionUnion(
        $a_source1_id,
        $a_source1_parent,
        $a_source2_id,
        $a_source2_parent,
        $a_dest_id,
        $a_dest_parent
    ) {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $rbacreview = $DIC['rbacreview'];


        $s1_ops = $rbacreview->getAllOperationsOfRole($a_source1_id, $a_source1_parent);
        $s2_ops = $rbacreview->getAlloperationsOfRole($a_source2_id, $a_source2_parent);

        $this->deleteRolePermission($a_dest_id, $a_dest_parent);

        $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': ' . print_r($s1_ops, true));
        $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': ' . print_r($s2_ops, true));

        foreach ($s1_ops as $type => $ops) {
            foreach ($ops as $op) {
                // insert all permission of source 1
                // #15469
                $query = 'INSERT INTO rbac_templates (rol_id,type,ops_id,parent) ' .
                    'VALUES( ' .
                    $ilDB->quote($a_dest_id, 'integer') . ', ' .
                    $ilDB->quote($type, 'text') . ', ' .
                    $ilDB->quote($op, 'integer') . ', ' .
                    $ilDB->quote($a_dest_parent, 'integer') . ' ' .
                    ')';
                $ilDB->manipulate($query);
            }
        }

        // and the other direction...
        foreach ($s2_ops as $type => $ops) {
            foreach ($ops as $op) {
                if (!isset($s1_ops[$type]) || !in_array($op, $s1_ops[$type])) {
                    $query = 'INSERT INTO rbac_templates (rol_id,type,ops_id,parent) ' .
                        'VALUES( ' .
                        $ilDB->quote($a_dest_id, 'integer') . ', ' .
                        $ilDB->quote($type, 'text') . ', ' .
                        $ilDB->quote($op, 'integer') . ', ' .
                        $ilDB->quote($a_dest_parent, 'integer') . ' ' .
                        ')';
                    $ilDB->manipulate($query);
                }
            }
        }

        return true;
    }

    /**
     * Subtract role permissions
     * @param type $a_source_id
     * @param type $a_source_parent
     * @param type $a_dest_id
     * @param type $a_dest_parent
     */
    public function copyRolePermissionSubtract($a_source_id, $a_source_parent, $a_dest_id, $a_dest_parent)
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        $ilDB = $DIC['ilDB'];

        $s1_ops = $rbacreview->getAllOperationsOfRole($a_source_id, $a_source_parent);
        $d_ops = $rbacreview->getAllOperationsOfRole($a_dest_id, $a_dest_parent);

        foreach ($s1_ops as $type => $ops) {
            foreach ($ops as $op) {
                if (isset($d_ops[$type]) && in_array($op, $d_ops[$type])) {
                    $query = 'DELETE FROM rbac_templates ' .
                            'WHERE rol_id = ' . $ilDB->quote($a_dest_id, 'integer') . ' ' .
                            'AND type = ' . $ilDB->quote($type, 'text') . ' ' .
                            'AND ops_id = ' . $ilDB->quote($op, 'integer') . ' ' .
                            'AND parent = ' . $ilDB->quote($a_dest_parent, 'integer');
                    $ilDB->manipulate($query);
                }
            }
        }
        return true;
    }


    /**
     * Deletes all entries of a template. If an object type is given for third parameter only
     * the entries for that object type are deleted
     * Update of table rbac_templates.
     * @access	public
     * @param	integer		object id of role
     * @param	integer		ref_id of role folder
     * @param	string		object type (optional)
     * @return	boolean
     */
    public function deleteRolePermission($a_rol_id, $a_ref_id, $a_type = false)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (!isset($a_rol_id) || !isset($a_ref_id)) {
            $message = get_class($this) . "::deleteRolePermission(): Missing parameter! role_id: " . $a_rol_id . " ref_id: " . $a_ref_id;
            $this->ilErr->raiseError($message, $this->ilErr->WARNING);
        }

        // exclude system role from rbac
        if ($a_rol_id == SYSTEM_ROLE_ID) {
            return true;
        }

        if ($a_type !== false) {
            $and_type = " AND type=" . $ilDB->quote($a_type, 'text') . " ";
        }

        $query = 'DELETE FROM rbac_templates ' .
             'WHERE rol_id = ' . $ilDB->quote($a_rol_id, 'integer') . ' ' .
             'AND parent = ' . $ilDB->quote($a_ref_id, 'integer') . ' ' .
             $and_type;

        $res = $ilDB->manipulate($query);

        return true;
    }

    /**
     * Inserts template permissions in rbac_templates for an specific object type.
     *  Update of table rbac_templates
     * @access	public
     * @param	integer		role_id
     * @param	string		object type
     * @param	array		operation_ids
     * @param	integer		ref_id of role folder object
     * @return	boolean
     */
    public function setRolePermission($a_rol_id, $a_type, $a_ops, $a_ref_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (!isset($a_rol_id) || !isset($a_type) || !isset($a_ops) || !isset($a_ref_id)) {
            $message = get_class($this) . "::setRolePermission(): Missing parameter!" .
                       " role_id: " . $a_rol_id .
                       " type: " . $a_type .
                       " operations: " . $a_ops .
                       " ref_id: " . $a_ref_id;
            $this->ilErr->raiseError($message, $this->ilErr->WARNING);
        }

        if (!is_string($a_type) || empty($a_type)) {
            $message = get_class($this) . "::setRolePermission(): a_type is no string or empty!";
            $this->ilErr->raiseError($message, $this->ilErr->WARNING);
        }

        if (!is_array($a_ops) || empty($a_ops)) {
            $message = get_class($this) . "::setRolePermission(): a_ops is no array or empty!";
            $this->ilErr->raiseError($message, $this->ilErr->WARNING);
        }

        // exclude system role from rbac
        if ($a_rol_id == SYSTEM_ROLE_ID) {
            return true;
        }

        foreach ($a_ops as $op) {
            $ilDB->replace(
                'rbac_templates',
                [
                    'rol_id' => ['integer', $a_rol_id],
                    'type' => ['text', $a_type],
                    'ops_id' => ['integer', $op],
                    'parent' => ['integer', $a_ref_id]
                ],
                []
            );
        }
        return true;
    }

    /**
     * Assigns a role to an role folder
     * A role folder is an object to store roles.
     * Every role is assigned to minimum one role folder
     * If the inheritance of a role is stopped, a new role template will created, and the role is assigned to
     * minimum two role folders. All roles with stopped inheritance need the flag '$a_assign = false'
     *
     * @access	public
     * @param	integer		object id of role
     * @param	integer		ref_id of role folder
     * @param	string		assignable('y','n'); default: 'y'
     * @return	boolean
     */
    public function assignRoleToFolder($a_rol_id, $a_parent, $a_assign = "y")
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if (!isset($a_rol_id) || !isset($a_parent)) {
            $message = get_class($this) . "::assignRoleToFolder(): Missing Parameter!" .
                       " role_id: " . $a_rol_id .
                       " parent_id: " . $a_parent .
                       " assign: " . $a_assign;
            $this->ilErr->raiseError($message, $this->ilErr->WARNING);
        }

        // exclude system role from rbac
        if ($a_rol_id == SYSTEM_ROLE_ID) {
            return true;
        }

        // if a wrong value is passed, always set assign to "n"
        if ($a_assign != "y") {
            $a_assign = "n";
        }

        // check if already assigned
        $query = 'SELECT rol_id FROM rbac_fa ' .
            'WHERE rol_id = ' . $ilDB->quote($a_rol_id, 'integer') . ' ' .
            'AND parent = ' . $ilDB->quote($a_parent, 'integer');
        $res = $ilDB->query($query);
        if ($res->numRows()) {
            ilLoggerFactory::getLogger('ac')->info('Role already assigned to object');
            return false;
        }

        $query = sprintf(
            'INSERT INTO rbac_fa (rol_id, parent, assign, protected) ' .
            'VALUES (%s,%s,%s,%s)',
            $ilDB->quote($a_rol_id, 'integer'),
            $ilDB->quote($a_parent, 'integer'),
            $ilDB->quote($a_assign, 'text'),
            $ilDB->quote('n', 'text')
        );
        $res = $ilDB->manipulate($query);

        return true;
    }

    /**
     * Assign an existing operation to an object
     *  Update of rbac_ta.
     * @access	public
     * @param	integer	object type
     * @param	integer	operation_id
     * @return	boolean
     */
    public function assignOperationToObject($a_type_id, $a_ops_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (!isset($a_type_id) || !isset($a_ops_id)) {
            $message = get_class($this) . "::assignOperationToObject(): Missing parameter!" .
                       "type_id: " . $a_type_id .
                       "ops_id: " . $a_ops_id;
            $this->ilErr->raiseError($message, $this->ilErr->WARNING);
        }

        $query = "INSERT INTO rbac_ta (typ_id, ops_id) " .
             "VALUES(" . $ilDB->quote($a_type_id, 'integer') . "," . $ilDB->quote($a_ops_id, 'integer') . ")";
        $res = $ilDB->manipulate($query);
        return true;
    }

    /**
     * Deassign an existing operation from an object
     * Update of rbac_ta
     * @access	public
     * @param	integer	object type
     * @param	integer	operation_id
     * @return	boolean
     */
    public function deassignOperationFromObject($a_type_id, $a_ops_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (!isset($a_type_id) || !isset($a_ops_id)) {
            $message = get_class($this) . "::deassignPermissionFromObject(): Missing parameter!" .
                       "type_id: " . $a_type_id .
                       "ops_id: " . $a_ops_id;
            $this->ilErr->raiseError($message, $this->ilErr->WARNING);
        }

        $query = "DELETE FROM rbac_ta " .
             "WHERE typ_id = " . $ilDB->quote($a_type_id, 'integer') . " " .
             "AND ops_id = " . $ilDB->quote($a_ops_id, 'integer');
        $res = $ilDB->manipulate($query);

        return true;
    }

    /**
     * Set protected
     * @global  $ilDB
     * @param type $a_ref_id
     * @param type $a_role_id
     * @param type $a_value y or n
     * @return boolean
     */
    public function setProtected($a_ref_id, $a_role_id, $a_value)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        // ref_id not used yet. protected permission acts 'global' for each role,
        // regardless of any broken inheritance before
        $query = 'UPDATE rbac_fa ' .
            'SET protected = ' . $ilDB->quote($a_value, 'text') . ' ' .
            'WHERE rol_id = ' . $ilDB->quote($a_role_id, 'integer');
        $res = $ilDB->manipulate($query);
        return true;
    }

    /**
     * Copy local roles
     * This method creates a copy of all local role.
     * Note: auto generated roles are excluded
     *
     * @access public
     * @param int source id of object (not role folder)
     * @param int target id of object
     *
     */
    public function copyLocalRoles($a_source_id, $a_target_id)
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        $ilLog = $DIC['ilLog'];
        $ilObjDataCache = $DIC['ilObjDataCache'];

        $real_local = [];
        foreach ($rbacreview->getRolesOfRoleFolder($a_source_id, false) as $role_data) {
            $title = $ilObjDataCache->lookupTitle($role_data);
            if (substr($title, 0, 3) == 'il_') {
                continue;
            }
            $real_local[] = $role_data;
        }
        if (!count($real_local)) {
            return true;
        }
        // Create role folder
        foreach ($real_local as $role) {
            include_once("./Services/AccessControl/classes/class.ilObjRole.php");
            $orig = new ilObjRole($role);
            $orig->read();

            $ilLog->write(__METHOD__ . ': Start copying of role ' . $orig->getTitle());
            $roleObj = new ilObjRole();
            $roleObj->setTitle($orig->getTitle());
            $roleObj->setDescription($orig->getDescription());
            $roleObj->setImportId($orig->getImportId());
            $roleObj->create();

            $this->assignRoleToFolder($roleObj->getId(), $a_target_id, "y");
            $this->copyRolePermissions($role, $a_source_id, $a_target_id, $roleObj->getId(), true);
            $ilLog->write(__METHOD__ . ': Added new local role, id ' . $roleObj->getId());
        }
    }

    /**
     * Init intersection permissions.
     * @global type $rbacreview
     * @param type $a_ref_id
     * @param type $a_role_id
     * @param type $a_role_parent
     * @param type $a_template_id
     * @param type $a_template_parent
     * @return type
     */
    public function initIntersectionPermissions($a_ref_id, $a_role_id, $a_role_parent, $a_template_id, $a_template_parent)
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];

        if ($rbacreview->isProtected($a_role_parent, $a_role_id)) {
            // Assign object permissions
            $new_ops = $rbacreview->getOperationsOfRole(
                $a_role_id,
                ilObject::_lookupType($a_ref_id, true),
                $a_role_parent
            );

            // set new permissions for object
            $this->grantPermission(
                $a_role_id,
                (array) $new_ops,
                $a_ref_id
            );
            return;
        }
        if (!$a_template_id) {
            ilLoggerFactory::getLogger('ac')->info('No template id given. Aborting.');
            return;
        }
        // create template permission intersection
        $this->copyRolePermissionIntersection(
            $a_template_id,
            $a_template_parent,
            $a_role_id,
            $a_role_parent,
            $a_ref_id,
            $a_role_id
        );

        // assign role to folder
        $this->assignRoleToFolder(
            $a_role_id,
            $a_ref_id,
            'n'
        );

        // Assign object permissions
        $new_ops = $rbacreview->getOperationsOfRole(
            $a_role_id,
            ilObject::_lookupType($a_ref_id, true),
            $a_ref_id
        );

        // revoke existing permissions
        $this->revokePermission($a_ref_id, $a_role_id);

        // set new permissions for object
        $this->grantPermission(
            $a_role_id,
            (array) $new_ops,
            $a_ref_id
        );

        return;
    }

    /**
     * Apply didactic templates after object movement
     * @param int $a_ref_id
     * @param int $a_old_parent
     *
     * @deprecated since version 5.1.0 will be removed with 5.4 and implemented using event handler
     */
    protected function applyMovedObjectDidacticTemplates($a_ref_id, $a_old_parent)
    {
        include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateObjSettings.php';
        $tpl_id = ilDidacticTemplateObjSettings::lookupTemplateId($a_ref_id);
        if (!$tpl_id) {
            return;
        }
        include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateActionFactory.php';
        foreach (ilDidacticTemplateActionFactory::getActionsByTemplateId($tpl_id) as $action) {
            if ($action instanceof ilDidacticTemplateLocalRoleAction) {
                continue;
            }
            $action->setRefId($a_ref_id);
            $action->apply();
        }
        return;
    }


    /**
     * Adjust permissions of moved objects
     * - Delete permissions of parent roles that do not exist in new context
     * - Delete role templates of parent roles that do not exist in new context
     * - Add permissions for parent roles that did not exist in old context
     *
     * @access public
     * @param int ref id of moved object
     * @param int ref_id of old parent
     *
     */
    public function adjustMovedObjectPermissions($ref_id, $old_parent)
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        $tree = $DIC['tree'];
        $ilLog = $DIC['ilLog'];

        $new_parent = $tree->getParentId($ref_id);
        $old_context_roles = $rbacreview->getParentRoleIds($old_parent, false);
        $new_context_roles = $rbacreview->getParentRoleIds($new_parent, false);

        /**
         * 2023-08-15 sk: We need to switch off the cache here, as otherwise there
         * seems to be no way to get an adequate reading of the new path.
         * We switch it back on again at the end of this function.
         */
        $tree->useCache(false);

        $for_addition = $for_deletion = [];
        foreach ($new_context_roles as $new_role_id => $new_role) {
            if (!isset($old_context_roles[$new_role_id])) {
                $for_addition[] = $new_role_id;
            } elseif ($new_role['parent'] != $old_context_roles[$new_role_id]['parent']) {
                // handle stopped inheritance
                $for_deletion[] = $new_role_id;
                $for_addition[] = $new_role_id;
            }
        }
        foreach ($old_context_roles as $old_role_id => $old_role) {
            if (!isset($new_context_roles[$old_role_id])) {
                $for_deletion[] = $old_role_id;
            }
        }

        if (!count($for_deletion) && !count($for_addition)) {
            $this->applyMovedObjectDidacticTemplates($ref_id, $old_parent);
            return true;
        }

        $rbac_log_active = ilRbacLog::isActive();
        if ($rbac_log_active) {
            $role_ids = array_unique(array_merge(array_keys($for_deletion), array_keys($for_addition)));
        }

        foreach ($tree->getSubTree($tree->getNodeData($ref_id), true) as $node_data) {
            $node_id = $node_data['child'];

            if ($rbac_log_active) {
                $log_old = ilRbacLog::gatherFaPa($node_id, $role_ids);
            }

            // If $node_data['type'] is not set, this means there is a tree entry without
            // object_reference and/or object_data entry
            // Continue in this case
            if (!$node_data['type']) {
                $ilLog->write(__METHOD__ . ': No type give. Choosing next tree entry.');
                continue;
            }

            if (!$node_id) {
                $ilLog->write(__METHOD__ . ': Missing subtree node_id');
                continue;
            }

            foreach ($for_deletion as $role_id) {
                $this->deleteLocalRole($role_id, $node_id);
                $this->revokePermission($node_id, $role_id, false);
            }
            foreach ($for_addition as $role_id) {
                $role_parent_id = $rbacreview->getParentOfRole($role_id, $ref_id);
                switch ($node_data['type']) {
                    case 'grp':
                        $tpl_id = ilObjGroup::lookupGroupStatusTemplateId($node_data['obj_id']);
                        $this->initIntersectionPermissions(
                            $node_data['child'],
                            $role_id,
                            $role_parent_id,
                            $tpl_id,
                            ROLE_FOLDER_ID
                        );
                        break;

                    case 'crs':
                        $tpl_id = ilObjCourse::lookupCourseNonMemberTemplatesId();
                        $this->initIntersectionPermissions(
                            $node_data['child'],
                            $role_id,
                            $role_parent_id,
                            $tpl_id,
                            ROLE_FOLDER_ID
                        );
                        break;

                    default:
                        $this->grantPermission(
                            $role_id,
                            $rbacreview->getOperationsOfRole($role_id, $node_data['type'], $role_parent_id),
                            $node_id
                        );
                        break;

                }
            }

            if ($rbac_log_active) {
                $log_new = ilRbacLog::gatherFaPa($node_id, $role_ids);
                $log = ilRbacLog::diffFaPa($log_old, $log_new);
                ilRbacLog::add(ilRbacLog::MOVE_OBJECT, $node_id, $log);
            }
        }

        /**
         * We switch the cache back on again. See above.
         */
        $tree->useCache();

        $this->applyMovedObjectDidacticTemplates($ref_id, $old_parent);
    }
} // END class.ilRbacAdmin
