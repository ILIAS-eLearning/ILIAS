<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

// BEGIN PATCH FileFolders: Set drop box and file exchange permissions.

/**
* Class ilPermissionHelper
*
* @author Simon Moor <simon.moor@hslu.ch>
*
* @version $Id: class.ilPermissionHelper.php 41039 2013-04-02 12:02:21Z smoor $
*
*/
class ilPermissionHelper
{
    /**
    * Returns the ID of the global user role.
    *
    * @return Role ID of the global user role.
    */
    public static function _getGlobalUserRoleId()
    {
        global $rbacreview;
        $globalRoleIds = $rbacreview->getGlobalRoles();
        $userRoleId = null;
        foreach ($globalRoleIds as $roleId) {
            $roleObj = ilObjectFactory::getInstanceByObjId($roleId);
            if ($roleObj->getTitle()=='User' || $roleObj->getTitle()=='BenutzerIn') {
                $userRoleId= $roleId;
            }
        }
        return $userRoleId;
    }
    /**
    * Finds the innermost member role of the specified object.
    *
    * @param int $a_ref_id Reference ID of an object.
    * @return Role ID of the innermost member role. Returns null if no
    * member role has been found.
    */
    public static function _getInnermostRoleId($a_ref_id, $a_title_fragment='member')
    {
        global $rbacsystem, $rbacadmin, $rbacreview, $tree, $ilias, $lng;

        // Get the innermost member role
        $roles = $rbacreview->getParentRoleIds($a_ref_id);
        $innermost_member_role = null;
        $innermost_member_parent_node = null;
        $innermost_member_role_depth == -1;
        foreach ($roles as $role)
        {
            if ($role['keep_protected'] == false &&
            strpos(strtolower($role['title']),$a_title_fragment) !== false
            )
            {
                $rolf = $rbacreview->getFoldersAssignedToRole($role["obj_id"],true);
                $parent_node = $tree->getParentNodeData($rolf[0]);
                if ($parent_node['depth'] > $innermost_member_role_depth)
                {
                    $innermost_member_role = $role;
                    $innermost_member_role_parent_node = $parent_node;
                }

                //echo var_export($role,true).'<br>';
            }
        }
        return ($innermost_member_role == null) ? null : $innermost_member_role['obj_id'];
    }
    /**
    * Set a local policy for the specified role using the specified role templates.
    *
    * @access   public
    * @param int $a_rol_id The object id of the role.
    * @param int $a_ref_id The ref id of the object.
    * @param string $a_folder_template_name The specified template is used to define
    *             the permission settings on the folder object.
    * @param string $a_subfolder_template_name The specified template is used to define the
    *              local policy for all objects contained in the folder and in subfolders.
    * @return Returns true on success. Returns a String with an error message in case of failure.
    */
    public static function _setLocalPolicy($a_role_id, $a_ref_id, $a_folder_template_name, $a_subfolder_template_name)
    {
        global $rbacsystem, $rbacadmin, $rbacreview, $tree, $ilias, $lng;

		
        $object =& ilObjectFactory::getInstanceByRefId($a_ref_id);

        // Permission check
        $access = $rbacsystem->checkAccess('edit_permission',$a_ref_id);
        if (!$access)
        {
            return $lng->txt('no_permission');
        }

        // Create a role folder if necessary
        // ------------------------------------
        //$rolf_data = $rbacreview->getRoleFolderOfObject($a_ref_id);
        //$rolf_id = $rolf_data["child"];
        //if (empty($rolf_id))
        //{
        //    // create a local role folder
        //    $rfoldObj = $object->createRoleFolder();
        //
        //    // set rolf_id again from new rolefolder object
        //    $rolf_id = $rfoldObj->getRefId();
        //}

        // Create a local policy for the innermost member role using the
        // role template
        // ------------------------------------
        $parentRoles = $rbacreview->getParentRoleIds($a_ref_id, true);
        $folder_template = null;
        $subfolder_template = null;
        foreach ($parentRoles as $par) {
            if ($par['type'] == 'rolt' && $par['title'] == $a_folder_template_name)
            {
                $folder_template = $par;
            }
            if ($par['type'] == 'rolt' && $par['title'] == $a_subfolder_template_name)
            {
                $subfolder_template = $par;
            }
        }
        if ($folder_template == null)
        {
            $ilias->raiseError(
                sprintf($lng->txt("msg_perm_no_template_found"),$a_folder_template_name),
                $ilias->error_obj->MESSAGE);
        }
        if ($subfolder_template == null)
        {
            $ilias->raiseError(
                sprintf($lng->txt("msg_perm_no_template_found"),$a_subfolder_template_name),
                $ilias->error_obj->MESSAGE);
        }
        //$rbacadmin->deleteRolePermission($a_role_id, $rolf_id);

        $rbacadmin->copyRolePermissions(
                $subfolder_template['obj_id'],
                $subfolder_template['parent'],
                $a_ref_id,
                $a_role_id
        );
        //hier das zersch lugen ob man assignen muss, sonst gibts mysql error
                $assignedFolders=$rbacreview->getFoldersAssignedToRole($a_role_id);
                if(!in_array($a_ref_id,$assignedFolders)) $rbacadmin->assignRoleToFolder($a_role_id,$a_ref_id,'n');

        // Apply the local policy to all existing objects under the parent node of the role folder.
        // But Don't change permissions of subtree objects if they have a local policy of their own.
        // ------------------------------------

        // Get all subnodes
        $node_data = $tree->getNodeData($a_ref_id);
        $subtree_nodes = $tree->getSubTree($node_data);

        // Get all objects that contain a role folder
        $all_parent_obj_of_rolf = $rbacreview->getObjectsWithStopedInheritance($a_role_id);

        // Delete actual role folder from array
        $key = array_keys($all_parent_obj_of_rolf,$a_ref_id);

        unset($all_parent_obj_of_rolf[$key[0]]);

        // associative array with key='type' and value=array of operations
        $operations_of_role_cache = array();
        $check = false;
        foreach ($subtree_nodes as $node)
        {
            if (!$check)
            {
                if (in_array($node["child"],$all_parent_obj_of_rolf))
                {
//BEGIN PATCH HSLU Tree use materialized path
                    $path = $node["path"].'.';
/*
                                $lft = $node["lft"];
                                $rgt = $node["rgt"];
*/
//END PATCH HSLU Tree use materialized path
                    $check = true;
                    continue;
                }

                $valid_nodes[] = $node;
            }
            else
            {
//BEGIN PATCH HSLU Tree use materialized path
                if ( substr($node["path"], 0, strlen($path)) == $path )
//              if (($node["lft"] > $lft) && ($node["rgt"] < $rgt))
//END PATCH HSLU Tree use materialized path
                {
                    continue;
                }
                else
                {
                    $check = false;

                    if (in_array($node["child"],$all_parent_obj_of_rolf))
                    {
//BEGIN PATCH HSLU Tree use materialized path
                    $path = $node["path"].'.';
/*
                        $lft = $node["lft"];
                        $rgt = $node["rgt"];
*/
//END PATCH HSLU Tree use materialized path
                        $check = true;
                        continue;
                    }

                    $valid_nodes[] = $node;
                }
            }
        }

        // Apply the folder template to the folder objects, and
        // apply the subfolder template to all objects contained within the
        // folder and within subfolders.
        foreach ($valid_nodes as $key => $node)
        {
            #if(!in_array($node["type"],$to_filter))
           {
                $node_ids[] = $node["child"];

                if ($node['obj_id'] == $object->getId())
                {
                    $valid_nodes[$key]["perms"] =
                        $rbacreview->getOperationsOfRole($folder_template['obj_id'],$node['type']);
                }
                else
                {
                    if (! array_key_exists($node['type'], $operations_of_role_cache))
                    {
                        $operations_of_role_cache[$node['type']] =
                            $rbacreview->getOperationsOfRole($a_role_id,$node['type'],$a_ref_id);
                    }
                    $valid_nodes[$key]["perms"] = $operations_of_role_cache[$node['type']];
                }
            }
        }

        // First revoke permissions from all valid objects
        $rbacadmin->revokePermissionList($node_ids,$a_role_id);

        // Now set all permissions
        foreach ($valid_nodes as $node)
        {
            if (is_array($node["perms"]))
            {
                $rbacadmin->grantPermission($a_role_id,$node["perms"],$node["child"]);
            }
        }

        return true;
    }
    /**
    * Removes a local policy for a role on the specified object.
    *
    * @access   public
    * @param int $a_rol_id The object id of the role.
    * @param int $a_ref_id The ref id of the object.
    * @return Returns true on success. Returns a String with an error message in case of failure.
    */
    public static function _removeLocalPolicy($a_role_id, $a_ref_id)
    {
        global $rbacsystem, $rbacadmin, $rbacreview, $tree, $ilias, $lng, $ilDB;

        $object =& ilObjectFactory::getInstanceByRefId($a_ref_id);

        // Permission check
        $access = $rbacsystem->checkAccess('edit_permission',$a_ref_id);
        if (!$access)
        {
            return $lng->txt('no_permission');
        }

        // Get the roles of the rolefolder
        // ------------------------------------
        $rolefolderRoles = $rbacreview->getRolesOfRoleFolder($a_ref_id, true);
        if (! in_array($a_role_id, $rolefolderRoles)) {
            // if there is no local policy for the role,
            // we have nothing to do.
            return true;
        }

        // Delete the local policy
        // -----------------------
        $rbacadmin->deleteLocalRole($a_role_id,$a_ref_id);

        // Apply the parent policy to all existing objects under the parent node of the role folder.
        // But Don't change permissions of subtree objects if they have a local policy of their own.
        // ------------------------------------

        // Get all subnodes
        $node_data = $tree->getNodeData($a_ref_id);
        $subtree_nodes = $tree->getSubTree($node_data);

		// Get all objects that contain a role folder
		$all_parent_obj_of_rolf = $rbacreview->getObjectsWithStopedInheritance($a_role_id);
		
		// associative array with key='type' and value=array of operations
        $operations_of_role_cache = array();
        $check = false;
		$valid_nodes=array();
        foreach ($subtree_nodes as $node)
        {
            if (!$check)
            {
                if (in_array($node["child"],$all_parent_obj_of_rolf))
                {
                    $path = $node["path"].'.';
                    $check = true;
                    continue;
                }

                $valid_nodes[] = $node;
            }
            else
            {
                if ( substr($node["path"], 0, strlen($path)) == $path )
                {
                    continue;
                }
                else
                {
                    $check = false;

                    if (in_array($node["child"],$all_parent_obj_of_rolf))
                    {
                        $path = $node["path"].'.';
                        $check = true;
                        continue;
                    }

                    $valid_nodes[] = $node;
                }
            }
        }

        // Apply the folder template to the folder objects, and
        // apply the subfolder template to all objects contained within the
        // folder and within subfolders.
        foreach ($valid_nodes as $key => $node)
        {
            #if(!in_array($node["type"],$to_filter))
           {
                $node_ids[] = $node["child"];

                if (! array_key_exists($node['type'], $operations_of_role_cache))
                {
                    $operations_of_role_cache[$node['type']] =
                        $rbacreview->getActiveOperationsOfRole($a_ref_id,$rolefolderRoles[0]);
                        
                }
                $valid_nodes[$key]["perms"] = $operations_of_role_cache[$node['type']];
            }
        }

        // First revoke permissions from all valid objects
        $rbacadmin->revokePermissionList($node_ids,$a_role_id);

        // Now set all permissions
        foreach ($valid_nodes as $node)
        {
            if (is_array($node["perms"]))
            {
                $rbacadmin->grantPermission($rolefolderRoles[0],$node["perms"],$node["child"]);
            }
        }

        return true;
    }

    /**
    * Set a local policy for a role using the specified role templates.
    *
    * @access   public
    * @param int $a_rol_id The id of a role.
    * @param int $a_ref_id The ref id of the object.
    * @param string $a_type_array An array of object types which need to be matched.
    *              Specify null, if no matching shall be performed.
    * @param string $a_title_array An array of folder titles which need to be matched.
    *              Specify null, if no matching shall be performed.
    * @param string $a_folder_template_name The specified template is used to define
    *             the permission settings on the folder object.
    * @param string $a_subfolder_template_name The specified template is used to define the
    *              local policy for all objects contained in the folder and in subfolders.
    * @return Returns the number of processed folders. Raises an error, in case of error.
    */
    //public static function _setLocalPolicyInSubtree($a_role_id, $a_ref_id, $a_type_array, $a_title_array, $a_folder_template_name, $a_subfolder_template_name)
    //{
    //    global $tree;
    //    //require_once 'classes/class.ilPermissionHelper.php';
    //
    //    $subtree_root_node_data = $tree->getNodeData($a_ref_id);
    //    $nodes_data = $tree->getSubtree($subtree_root_node_data);
    //    $count = 0;
    //    foreach ($nodes_data as $node_data)
    //    {
    //        if (($a_type_array == null || in_array($node_data['type'], $a_type_array)) &&
    //            ($a_title_array == null || in_array($node_data['title'], $a_title_array)))
    //        {
	//			//error_log('ilPermissionHelper::localSubtree '.$a_role_id.' node_ref:'.$node_data['ref_id'].' node_role_id:'.$a_role_id.' '.$node_data['title']);
    //            ilPermissionHelper::_removeLocalPolicy($a_role_id,$node_data['ref_id']);
    //                            if (ilPermissionHelper::_setLocalPolicy($a_role_id, $node_data['ref_id'], $a_folder_template_name,$a_subfolder_template_name) !== true)
    //            {
    //                $this->ilias->raiseError(
    //                    sprintf($this->lng->txt("msg_perm_no_template_found"),$a_folder_template_name),
    //                    $this->ilias->error_obj->MESSAGE);
    //                break;
    //            }
    //            $count++;
    //        }
    //    }
    //    return $count;
    //}
    /**
    * Set a local policy for a role using the specified role templates.
    *
    * @access   public
    * @param int $a_rol_id The id of a role.
    * @param int $a_ref_id The ref id of the object.
    * @param string $a_type_array An array of object types which need to be matched.
    *              Specify null, if no matching shall be performed.
    * @param string $a_title_array An array of folder titles which need to be matched.
    *              Specify null, if no matching shall be performed.
    * @param string $a_folder_template_name The specified template is used to define
    *             the permission settings on the folder object.
    * @param string $a_subfolder_template_name The specified template is used to define the
    *              local policy for all objects contained in the folder and in subfolders.
    * @return Returns the number of processed folders. Raises an error, in case of error.
    */
    //public static function _setInnermostLocalPolicyInSubtree($a_title_fragment, $a_ref_id, $a_type_array, $a_title_array, $a_folder_template_name, $a_subfolder_template_name)
    //{
    //    global $tree;
    //    //require_once 'classes/class.ilPermissionHelper.php';
    //
    //    $subtree_root_node_data = $tree->getNodeData($a_ref_id);
    //    $nodes_data = $tree->getSubtree($subtree_root_node_data);
    //    $count = 0;
    //    foreach ($nodes_data as $node_data)
    //    {
    //        if (($a_type_array == null || in_array($node_data['type'], $a_type_array)) &&
    //            ($a_title_array == null || in_array($node_data['title'], $a_title_array)))
    //        {
    //            $a_role_id = ilPermissionHelper::_getInnermostRoleId($node_data['ref_id'], $a_title_fragment);
    //            if ($a_role_id !=null) {
    //                //ilPermissionHelper::_removeLocalPolicy($a_role_id,$node_data['ref_id']);
    //                                    if (ilPermissionHelper::_setLocalPolicy($a_role_id, $node_data['ref_id'], $a_folder_template_name,$a_subfolder_template_name) !== true)
    //                {
    //                    $this->ilias->raiseError(
    //                        sprintf($this->lng->txt("msg_perm_no_template_found"),$a_folder_template_name),
    //                        $this->ilias->error_obj->MESSAGE);
    //                    break;
    //                }
    //                $count++;
    //            }
    //        }
    //    }
    //    return $count;
    //}
}
// END PATCH FileFolders: Set drop box and file exchange permissions.
?>