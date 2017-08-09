<?php
/**
 * Created by PhpStorm.
 * User: fschmid
 * Date: 09.08.17
 * Time: 15:06
 */

/**
 * class ilRbacReview
 *  Contains Review functions of core Rbac.
 *  This class offers the possibility to view the contents of the user <-> role (UR) relation and
 *  the permission <-> role (PR) relation.
 *  For example, from the UA relation the administrator should have the facility to view all user
 *  assigned to a given role.
 *
 *
 * @author  Stefan Meyer <meyer@leifos.com>
 * @author  Sascha Hofmann <saschahofmann@gmx.de>
 *
 * @version $Id$
 *
 * @ingroup ServicesAccessControl
 */
interface ilRBACRelatedAccessChecks {

	/**
	 * Checks if a role already exists. Role title should be unique
	 *
	 * @access    public
	 *
	 * @param    string     role title
	 * @param    integer    obj_id of role to exclude in the check. Commonly this is the current
	 *                             role you want to edit
	 *
	 * @return    boolean    true if exists
	 * @todo      refactor rolf => DONE
	 */
	public function roleExists($a_title, $a_id = 0);


	/**
	 * get an array of parent role ids of all parent roles, if last parameter is set true
	 * you get also all parent templates
	 *
	 * @access    public
	 *
	 * @param    integer        ref_id of an object which is end node
	 * @param    boolean        true for role templates (default: false)
	 *
	 * @return    array       array(role_ids => role_data)
	 * @todo      refactor rolf => DONE
	 */
	public function getParentRoleIds($a_endnode_id, $a_templates = false);


	/**
	 * Returns a list of roles in an container
	 *
	 * @access    public
	 *
	 * @param    integer    ref_id of object
	 * @param    boolean    if true fetch template roles too
	 *
	 * @return    array    set ids
	 * @todo      refactor rolf => DONE
	 */
	public function getRoleListByObject($a_ref_id, $a_templates = false);


	/**
	 * Returns a list of all assignable roles
	 *
	 * @access    public
	 *
	 * @param    boolean    if true fetch template roles too
	 *
	 * @return    array    set ids
	 * @todo      refactor rolf => DONE
	 */
	function getAssignableRoles($a_templates = false, $a_internal_roles = false, $title_filter = '');


	/**
	 * Returns a list of assignable roles in a subtree of the repository
	 *
	 * @access    public
	 *
	 * @param    ref_id Root node of subtree
	 *
	 * @return    array    set ids
	 * @todo      refactor rolf => DONE
	 */
	public function getAssignableRolesInSubtree($ref_id);


	/**
	 * Get all assignable roles directly under a specific node
	 *
	 * @access    public
	 *
	 * @param ref_id
	 *
	 * @return    array    set ids
	 * @todo      refactor rolf => Find a better name; reduce sql fields
	 */
	public function getAssignableChildRoles($a_ref_id);


	/**
	 * Get the number of assigned users to roles
	 *
	 * @global ilDB $ilDB
	 *
	 * @param array $a_roles
	 *
	 * @return int
	 * @todo refactor rolf => DONE
	 */
	public function getNumberOfAssignedUsers(Array $a_roles);


	/**
	 * get all assigned users to a given role
	 *
	 * @access    public
	 *
	 * @param    integer    role_id
	 *
	 * @return    array    all users (id) assigned to role
	 */
	public function assignedUsers($a_rol_id);


	/**
	 * check if a specific user is assigned to specific role
	 *
	 * @access    public
	 *
	 * @param    integer        usr_id
	 * @param    integer        role_id
	 *
	 * @return    boolean
	 * @todo      refactor rolf =>  DONE
	 */
	public function isAssigned($a_usr_id, $a_role_id);


	/**
	 * check if a specific user is assigned to at least one of the
	 * given role ids.
	 * This function is used to quickly check whether a user is member
	 * of a course or a group.
	 *
	 * @access    public
	 *
	 * @param    integer        usr_id
	 * @param    array          [integer]        role_ids
	 *
	 * @return    boolean
	 * @todo      refactor rolf =>  DONE
	 */
	public function isAssignedToAtLeastOneGivenRole($a_usr_id, $a_role_ids);


	/**
	 * get all assigned roles to a given user
	 *
	 * @access    public
	 *
	 * @param    integer        usr_id
	 *
	 * @return    array        all roles (id) the user have
	 * @todo      refactor rolf =>  DONE
	 */
	public function assignedRoles($a_usr_id);


	/**
	 * Get assigned global roles for an user
	 *
	 * @param int $a_usr_id Id of user account
	 *
	 * @todo refactor rolf =>  DONE
	 */
	public function assignedGlobalRoles($a_usr_id);


	/**
	 * Check if its possible to assign users
	 *
	 * @access    public
	 *
	 * @param    integer    object id of role
	 * @param    integer    ref_id of object in question
	 *
	 * @return    boolean
	 * @todo      refactor rolf (expects object reference id instead of rolf) => DONE
	 */
	public function isAssignable($a_rol_id, $a_ref_id);


	/**
	 * Temporary bugfix
	 *
	 * @todo refactor rolf => DONE
	 *
	 */
	public function hasMultipleAssignments($a_role_id);


	/**
	 * Returns an array of objects assigned to a role. A role with stopped inheritance
	 * may be assigned to more than one objects.
	 * To get only the original location of a role, set the second parameter to true
	 *
	 * @access    public
	 *
	 * @param    integer        role id
	 * @param    boolean        get only rolefolders where role is assignable (true)
	 *
	 * @return    array        reference IDs of role folders
	 * @todo      refactor rolf  => RENAME (rest done)
	 */
	public function getFoldersAssignedToRole($a_rol_id, $a_assignable = false);


	/**
	 * Get roles of object
	 *
	 * @param type $a_ref_id
	 * @param type $a_assignable
	 *
	 * @throws InvalidArgumentException
	 * @todo refactor rolf => DONE
	 */
	public function getRolesOfObject($a_ref_id, $a_assignable_only = false);


	/**
	 * get all roles of a role folder including linked local roles that are created due to stopped
	 * inheritance returns an array with role ids
	 *
	 * @access     public
	 *
	 * @param    integer        ref_id of object
	 * @param    boolean        if false only get true local roles
	 *
	 * @return    array        Array with rol_ids
	 * @deprecated since version 4.5.0
	 * @todo       refactor rolf => RENAME
	 */
	public function getRolesOfRoleFolder($a_ref_id, $a_nonassignable = true);


	/**
	 * get only 'global' roles
	 *
	 * @access    public
	 * @return    array        Array with rol_ids
	 * @todo      refactor rolf => DONE
	 */
	public function getGlobalRoles();


	/**
	 * Get local roles of object
	 *
	 * @param int $a_ref_id
	 *
	 * @todo refactor rolf => DONE
	 */
	public function getLocalRoles($a_ref_id);


	/**
	 * Get all roles with local policies
	 *
	 * @param type $a_ref_id
	 *
	 * @return type
	 */
	public function getLocalPolicies($a_ref_id);


	/**
	 * get only 'global' roles
	 *
	 * @access    public
	 * @return    array        Array with rol_ids
	 * @todo      refactor rolf => DONE
	 */
	public function getGlobalRolesArray();


	/**
	 * get only 'global' roles (with flag 'assign_users')
	 *
	 * @access    public
	 * @return    array        Array with rol_ids
	 * @todo      refactor rolf => DONE
	 */
	public function getGlobalAssignableRoles();


	/**
	 * Check if role is assigned to an object
	 *
	 * @todo refactor rolf => DONE (renamed)
	 */
	public function isRoleAssignedToObject($a_role_id, $a_parent_id);


	/**
	 * get all possible operations
	 *
	 * @access    public
	 * @return    array    array of operation_id
	 * @todo      refactor rolf => DONE
	 */
	public function getOperations();


	/**
	 * get one operation by operation id
	 *
	 * @access    public
	 * @return    array data of operation_id
	 * @todo      refactor rolf => DONE
	 */
	public function getOperation($ops_id);


	/**
	 * get all possible operations of a specific role
	 * The ref_id of the role folder (parent object) is necessary to distinguish local roles
	 *
	 * @access    public
	 *
	 * @param    integer    role_id
	 * @param    integer    role folder id
	 *
	 * @return    array    array of operation_id and types
	 * @todo      refactor rolf => DONE
	 */
	public function getAllOperationsOfRole($a_rol_id, $a_parent = 0);


	/**
	 * Get active operations for a role
	 *
	 * @param object $a_ref_id
	 * @param object $a_role_id
	 *
	 * @return
	 * @todo refactor rolf => DONE
	 */
	public function getActiveOperationsOfRole($a_ref_id, $a_role_id);


	/**
	 * get all possible operations of a specific role
	 * The ref_id of the role folder (parent object) is necessary to distinguish local roles
	 *
	 * @access    public
	 *
	 * @param    integer    role_id
	 * @param    string     object type
	 * @param    integer    role folder id
	 *
	 * @return    array    array of operation_id
	 * @todo      refactor rolf => DONE
	 */
	public function getOperationsOfRole($a_rol_id, $a_type, $a_parent = 0);


	/**
	 * @global ilDB $ilDB
	 *
	 * @param type  $a_role_id
	 * @param type  $a_ref_id
	 *
	 * @return type
	 * @todo rafactor rolf => DONE
	 */
	public function getRoleOperationsOnObject($a_role_id, $a_ref_id);


	/**
	 * all possible operations of a type
	 *
	 * @access    public
	 *
	 * @param    integer        object_ID of type
	 *
	 * @return    array        valid operation_IDs
	 * @todo      rafactor rolf => DONE
	 */
	public function getOperationsOnType($a_typ_id);


	/**
	 * all possible operations of a type
	 *
	 * @access    public
	 *
	 * @param    integer        object_ID of type
	 *
	 * @return    array        valid operation_IDs
	 * @todo      rafactor rolf => DONE
	 *
	 */
	public function getOperationsOnTypeString($a_type);


	/**
	 * Get operations by type and class
	 *
	 * @param string $a_type Type is "object" or
	 * @param string $a_class
	 *
	 * @return
	 * @todo refactor rolf => DONE
	 */
	public function getOperationsByTypeAndClass($a_type, $a_class);


	/**
	 * get all objects in which the inheritance of role with role_id was stopped
	 * the function returns all reference ids of objects containing a role folder.
	 *
	 * @access    public
	 *
	 * @param    integer    role_id
	 * @param    array      filter ref_ids
	 *
	 * @return    array    with ref_ids of objects
	 * @todo      refactor rolf => DONE
	 */
	public function getObjectsWithStopedInheritance($a_rol_id, $a_filter = array());


	/**
	 * Checks if a rolefolder is set as deleted (negative tree_id)
	 *
	 * @access    public
	 *
	 * @param    integer    ref_id of rolefolder
	 *
	 * @return    boolean    true if rolefolder is set as deleted
	 * @todo      refactor rolf => DELETE method
	 */
	public function isDeleted($a_node_id);


	/**
	 * Check if role is a global role
	 *
	 * @param type $a_role_id
	 *
	 * @return type
	 * @todo refactor rolf => DONE
	 */
	public function isGlobalRole($a_role_id);


	/**
	 *
	 * @global ilDB $ilDB
	 *
	 * @param type  $a_filter
	 * @param type  $a_user_id
	 * @param type  $title_filter
	 *
	 * @return type
	 * @todo refactor rolf => DONE
	 */
	public function getRolesByFilter($a_filter = 0, $a_user_id = 0, $title_filter = '');


	/**
	 * Get type id of object
	 *
	 * @global ilDB $ilDB
	 *
	 * @param type  $a_type
	 *
	 * @return type
	 * @todo refactor rolf => DONE
	 */
	public function getTypeId($a_type);


	/**
	 * @todo refactor rolf => search calls
	 * @global ilDB $ilDB
	 *
	 * @param type  $a_ref_id
	 * @param type  $a_role_id
	 *
	 * @return type
	 * @todo refactor rolf => DONE
	 */
	public function isProtected($a_ref_id, $a_role_id);


	/**
	 * Check if role is blocked at position
	 *
	 * @global ilDB $ilDB
	 *
	 * @param type  $a_role_id
	 * @param type  $a_ref_id
	 *
	 * @return boolean
	 */
	public function isBlockedAtPosition($a_role_id, $a_ref_id);


	/**
	 * Check if role is blocked in upper context
	 *
	 * @param type $a_role_id
	 * @param type $a_ref_id
	 */
	public function isBlockedInUpperContext($a_role_id, $a_ref_id);


	/**
	 * Get object id of objects a role is assigned to
	 *
	 * @todo   refactor rolf (due to performance reasons the new version does not check for deleted
	 *         roles only in object reference)
	 *
	 * @access public
	 *
	 * @param int role id
	 *
	 */
	public function getObjectOfRole($a_role_id);


	/**
	 * Get reference of role
	 *
	 * @param object $a_role_id
	 *
	 * @return int
	 * @todo refactor rolf (no deleted check)
	 */
	public function getObjectReferenceOfRole($a_role_id);


	/**
	 * return if role is only attached to deleted role folders
	 *
	 * @param int $a_role_id
	 *
	 * @return boolean
	 * @todo refactor rolf => DONE
	 */
	public function isRoleDeleted($a_role_id);


	/**
	 * @global ilDB $ilDB
	 *
	 * @param type  $role_ids
	 * @param type  $use_templates
	 *
	 * @return type
	 * @todo refactor rolf => DONE
	 */
	public function getRolesForIDs($role_ids, $use_templates);


	/**
	 * get operation assignments
	 *
	 * @return array array(array('typ_id' => $typ_id,'title' => $title,'ops_id =>
	 *               '$ops_is,'operation' => $operation),...
	 * @todo refactor rolf => DONE
	 */
	public function getOperationAssignment();


	/**
	 * Check if role is deleteable at a specific position
	 *
	 * @param object $a_role_id
	 * @param        int rolf_id
	 *
	 * @return
	 * @todo refactor rolf => DONE
	 */
	public function isDeleteable($a_role_id, $a_rolf_id);


	/**
	 * Check if the role is system generate role or role template
	 *
	 * @param int $a_role_id
	 *
	 * @return bool
	 * @todo refactor rolf => DONE
	 */
	public function isSystemGeneratedRole($a_role_id);


	/**
	 * Get role folder of role
	 *
	 * @global ilDB $ilDB
	 *
	 * @param int   $a_role_id
	 *
	 * @return int
	 * @todo refactor rolf => RENAME
	 */
	public function getRoleFolderOfRole($a_role_id);


	/**
	 * Get all user permissions on an object
	 *
	 * @param int $a_user_id user id
	 * @param int $a_ref_id  ref id
	 *
	 * @todo refactor rolf => DONE
	 */
	public function getUserPermissionsOnObject($a_user_id, $a_ref_id);


	/**
	 * set entry of assigned_chache
	 *
	 * @param int  $a_role_id
	 * @param int  $a_user_id
	 * @param bool $a_value
	 */
	public function setAssignedCacheEntry($a_role_id, $a_user_id, $a_value);


	/**
	 * get entry of assigned_chache
	 *
	 * @param int $a_role_id
	 * @param int $a_user_id
	 */
	public function getAssignedCacheEntry($a_role_id, $a_user_id);


	/**
	 * Clear assigned users caches
	 */
	public function clearCaches();
}