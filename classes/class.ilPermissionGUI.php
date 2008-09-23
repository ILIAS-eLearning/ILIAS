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


/**
* Class ilPermissionGUI
* RBAC related output
*
* @author Sascha Hofmann <saschahofmann@gmx.de>
*
* @version $Id$
*
* @ilCtrl_Calls ilPermissionGUI: ilObjRoleGUI
*
* @ingroup	ServicesAccessControl
*/
class ilPermissionGUI
{
	/**
	* Constructor
	* @access	public
	* @param	array	??
	* @param	integer	object id
	* @param	boolean	call be reference
	*/
	function ilPermissionGUI(&$a_gui_obj)
	{
		global $ilias, $objDefinition, $tpl, $tree, $ilCtrl, $ilErr, $lng;

		if (!isset($ilErr))
		{
			$ilErr = new ilErrorHandling();
			$ilErr->setErrorHandling(PEAR_ERROR_CALLBACK,array($ilErr,'errorHandler'));
		}
		else
		{
			$this->ilErr =& $ilErr;
		}

		$this->ilias =& $ilias;
		$this->objDefinition =& $objDefinition;
		$this->tree =& $tree;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->lng->loadLanguageModule("rbac");

		$this->ctrl =& $ilCtrl;

		$this->gui_obj =& $a_gui_obj;
		
		$this->roles = array();
		$this->num_roles = 0;
	}
	

	function &executeCommand()
	{
		global $rbacsystem, $ilErr;

		// access to all functions in this class are only allowed if edit_permission is granted
		if (!$rbacsystem->checkAccess("edit_permission",$this->gui_obj->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt("permission_denied"),$ilErr->MESSAGE);
		}

		$next_class = $this->ctrl->getNextClass($this);

		switch($next_class)
		{
			case "ilobjrolegui":
				include_once("./Services/AccessControl/classes/class.ilObjRoleGUI.php");
				$this->gui_obj = new ilObjRoleGUI("",(int) $_GET["obj_id"], false, false);
				$this->gui_obj->setBackTarget($this->lng->txt("perm_settings"),
					$this->ctrl->getLinkTarget($this, "perm"));
				$ret =& $this->ctrl->forwardCommand($this->gui_obj);
				break;
				
			default:
				$cmd = $this->ctrl->getCmd();
				$this->$cmd();
				break;
		}

		return true;
	}

	/**
	* show permissions of current node
	*
	* @access	public
	*/
	function perm()
	{
		global $rbacsystem, $rbacreview;

		$this->getRolesData();

		/////////////////////
		// START DATA OUTPUT
		/////////////////////
		$this->__initSubTabs("perm");

		$this->gui_obj->getTemplateFile("perm");

		$this->num_roles = count($this->roles);

		// render filter form
	    $this->tpl->setCurrentBlock("filter");
	    $this->tpl->setVariable("FILTER_TXT_FILTER",$this->lng->txt('filter'));
	    $this->tpl->setVariable("SELECT_FILTER",$this->__buildRoleFilterSelect());
	    $this->tpl->setVariable("FILTER_ACTION",$this->ctrl->getFormAction($this)."&cmd=perm");
	    $this->tpl->setVariable("FILTER_NAME",'view');
	    $this->tpl->setVariable("FILTER_VALUE",$this->lng->txt('apply_filter'));
	    $this->tpl->parseCurrentBlock();

		// don't display table if no role in list
		if ($this->num_roles < 1)
		{
			ilUtil::sendInfo($this->lng->txt("msg_no_roles_of_type"),false);
			$this->__displayAddRoleForm();
			return true;
		}

		$this->tpl->addBlockFile("PERM_PERMISSIONS", "permissions", "tpl.obj_perm_permissions.html");

		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("permission_settings"));
		$this->tpl->setVariable("IMG_PERM", ilUtil::getImagePath("icon_perm.gif"));
		$this->tpl->setVariable("TXT_TITLE_INFO",
			sprintf($this->lng->txt("permission_settings_info"),
			$this->gui_obj->object->getTitle()
			));
		$this->tpl->setVariable("COLSPAN", $this->num_roles);
		$this->tpl->setVariable("FORMACTION",
			$this->gui_obj->getFormAction("permSave",$this->ctrl->getLinkTarget($this,"permSave")));
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		
		// needed for display correct role context of global roles
		$global_roles = $rbacreview->getGlobalRoles();

		foreach ($this->roles as $role)
		{
			$tmp_role_folder = $rbacreview->getRoleFolderOfObject($this->gui_obj->object->getRefId());
			$tmp_local_roles = array();

			if ($tmp_role_folder)
			{
				$tmp_local_roles = $rbacreview->getRolesOfRoleFolder($tmp_role_folder["ref_id"]);
			}
			
			// Is it a real or linked lokal role
			if ($role['protected'] == false and in_array($role['obj_id'],$tmp_local_roles))
			{
				$role_folder_data = $rbacreview->getRoleFolderOfObject($_GET['ref_id']);
				$role_folder_id = $role_folder_data['ref_id'];


				$this->tpl->setCurrentBlock("rolelink_open");

				$up_path = defined('ILIAS_MODULE') ? "../" : "";
				$this->ctrl->setParameterByClass("ilobjrolegui", "obj_id",
					$role['obj_id']);
				$this->ctrl->setParameterByClass("ilobjrolegui", "rolf_ref_id",
					$role_folder_id);
				$this->tpl->setVariable("LINK_ROLE_RULESET",
					$this->ctrl->getLinkTargetByClass("ilobjrolegui", "perm"));
				
				$this->tpl->setVariable("TXT_ROLE_RULESET",$this->lng->txt("edit_perm_ruleset"));
				$this->tpl->parseCurrentBlock();

				$this->tpl->touchBlock("rolelink_close");
			}

			$this->tpl->setCurrentBlock("role_infos");
			
			// display human readable role names for autogenerated roles
			include_once ('./Services/AccessControl/classes/class.ilObjRole.php');
			$this->tpl->setVariable("ROLE_NAME",str_replace(" ","&nbsp;",ilObjRole::_getTranslation($role["title"])));
			//var_dump("<pre>",$role,"</pre>");
			
			// display role context
			if (in_array($role["obj_id"],$global_roles))
			{
				$this->tpl->setVariable("ROLE_CONTEXT_TYPE","global");
			}
			else
			{
				$rolf = $rbacreview->getFoldersAssignedToRole($role["obj_id"],true);
				$parent_node = $this->tree->getParentNodeData($rolf[0]);
				//$this->tpl->setVariable("ROLE_CONTEXT_TYPE",$this->lng->txt("obj_".$parent_node["type"])."&nbsp;(#".$parent_node["obj_id"].")");
				//$this->tpl->setVariable("ROLE_CONTEXT",$parent_node["title"]);
				$this->tpl->setVariable("ROLE_CONTEXT_TYPE",$parent_node["title"]);
			}
			
			$this->tpl->parseCurrentBlock();
		}
		$this->ctrl->clearParametersByClass("ilobjrolegui");
		
// show permission settings

		// general section
		$this->__showPermissionsGeneralSection();
		
		// object section
		$this->__showPermissionsObjectSection();

		// rbac section
		$this->__showPermissionsRBACSection();
		
		// create section
		$this->__showPermissionsCreateSection();

		$this->tpl->setVariable("COLSPAN", $this->num_roles);

		// ADD LOCAL ROLE		
		$this->__displayAddRoleForm();
	}


	/**
	* save permissions
	*
	* @access	public
	*/
	function permSave()
	{
		global $rbacreview, $rbacadmin, $rbacsystem;

		// first save the new permission settings for all roles
		$rbacadmin->revokePermission($this->gui_obj->object->getRefId());

		if (is_array($_POST["perm"]))
		{
			foreach ($_POST["perm"] as $key => $new_role_perms) // $key enthaelt die aktuelle Role_Id
			{
				$rbacadmin->grantPermission($key,$new_role_perms,$this->gui_obj->object->getRefId());
			}
		}

		// update object data entry (to update last modification date)
		$this->gui_obj->object->update();

		// Wenn die Vererbung der Rollen Templates unterbrochen werden soll,
		// muss folgendes geschehen:
		// - existiert kein RoleFolder, wird er angelegt und die Rechte aus den Permission Templates ausgelesen
		// - existiert die Rolle im aktuellen RoleFolder werden die Permission Templates dieser Rolle angezeigt
		// - existiert die Rolle nicht im aktuellen RoleFolder wird sie dort angelegt
		//   und das Permission Template an den Wert des nihst hher gelegenen Permission Templates angepasst

		// get rolefolder data if a rolefolder already exists
		$rolf_data = $rbacreview->getRoleFolderOfObject($this->gui_obj->object->getRefId());
		$rolf_id = $rolf_data["child"];
		
		$stop_inherit_roles = $_POST["stop_inherit"] ? $_POST["stop_inherit"] : array();

		if ($stop_inherit_roles)
		{
			// rolefolder does not exist, so create one
			if (empty($rolf_id))
			{
				// create a local role folder
				$rfoldObj = $this->gui_obj->object->createRoleFolder();

				// set rolf_id again from new rolefolder object
				$rolf_id = $rfoldObj->getRefId();
			}

			$roles_of_folder = $rbacreview->getRolesOfRoleFolder($rolf_id);
			
			foreach ($stop_inherit_roles as $stop_inherit)
			{
				// create role entries for roles with stopped inheritance
				if (!in_array($stop_inherit,$roles_of_folder))
				{
					$parentRoles = $rbacreview->getParentRoleIds($rolf_id);
					$rbacadmin->copyRoleTemplatePermissions($stop_inherit,$parentRoles[$stop_inherit]["parent"],
												   $rolf_id,$stop_inherit);
					$rbacadmin->assignRoleToFolder($stop_inherit,$rolf_id,'n');
				}
			}// END FOREACH
		}// END STOP INHERIT
		
		if ($rolf_id  and $rolf_id != ROLE_FOLDER_ID)
		{
			// get roles where inheritance is stopped was cancelled
			$linked_roles = $rbacreview->getLinkedRolesOfRoleFolder($rolf_id);
			$linked_roles_to_remove = array_diff($linked_roles,$stop_inherit_roles);
				
			// remove roles where stopped inheritance is cancelled and purge rolefolder if empty
			foreach ($linked_roles_to_remove as $role_id)
			{
				if ($rbacreview->isProtected($rolf_id,$role_id))
				{
					continue;
				}
				
				$role_obj =& $this->ilias->obj_factory->getInstanceByObjId($role_id);
				$role_obj->setParent($rolf_id);
				$role_obj->delete();
				unset($role_obj);
			}
		}
		
		ilUtil::sendInfo($this->lng->txt("saved_successfully"),true);
		
		// redirect to default page if user revokes himself access to the permission panel
		if (!$rbacsystem->checkAccess("edit_permission",$this->gui_obj->object->getRefId()))
		{
			$this->ctrl->redirect($this->gui_obj);
		}
		
		$this->ctrl->redirect($this,'perm');
	}



	/**
	* adds a local role
	* This method is only called when choose the option 'you may add local roles'. This option
	* is displayed in the permission settings dialogue for an object
	* TODO: this will be changed
	* @access	public
	*/
	function addRole()
	{
		global $rbacadmin, $rbacreview, $rbacsystem;

		// check if role title has il_ prefix
		if (substr($_POST["Fobject"]["title"],0,3) == "il_")
		{
			$this->ilias->raiseError($this->lng->txt("msg_role_reserved_prefix"),$this->ilias->error_obj->MESSAGE);
		}
		if(!strlen($_POST["Fobject"]["title"]))
		{
			$this->ilias->raiseError($this->lng->txt("fill_out_all_required_fields"),$this->ilias->error_obj->MESSAGE);
		}

		// if the current object is no role folder, create one
		if ($this->gui_obj->object->getType() != "rolf")
		{
			$rolf_data = $rbacreview->getRoleFolderOfObject($this->gui_obj->object->getRefId());

			// is there already a rolefolder?
			if (!($rolf_id = $rolf_data["child"]))
			{
				// can the current object contain a rolefolder?
				$subobjects = $this->objDefinition->getSubObjects($this->gui_obj->object->getType());

				if (!isset($subobjects["rolf"]))
				{
					$this->ilias->raiseError($this->lng->txt("msg_no_rolf_allowed1")." '".$this->gui_obj->object->getTitle()."' ".
											$this->lng->txt("msg_no_rolf_allowed2"),$this->ilias->error_obj->WARNING);
				}

				// create a rolefolder
				$rolfObj = $this->gui_obj->object->createRoleFolder();
				$rolf_id = $rolfObj->getRefId();
			}
		}
		else
		{
			// Current object is already a rolefolder. To create the role we take its reference id
			$rolf_id = $this->gui_obj->object->getRefId();
		}

		// create role
		if ($this->gui_obj->object->getType() == "rolf")
		{
			$roleObj = $this->gui_obj->object->createRole($_POST["Fobject"]["title"],$_POST["Fobject"]["desc"]);
		}
		else
		{
			$rfoldObj = $this->ilias->obj_factory->getInstanceByRefId($rolf_id);
			$roleObj = $rfoldObj->createRole($_POST["Fobject"]["title"],$_POST["Fobject"]["desc"]);
		}

		ilUtil::sendInfo($this->lng->txt("role_added"),true);
		
		// in administration jump to deault perm settings screen
		// alex, ILIAS 3.6.5, 1.9.2006: this does not work and leads to errors in
		// a) administration
		//    -> repository trash & permissions -> item -> permissions ->
		//    "you may add role" screen -> save
		// b) other modules like learning modules
		//    -> permissions -> "you may add role" screen
		// deactivated for 3.6.6
		//if ($this->ctrl->getTargetScript() != "repository.php")
		//{
		//	$this->ctrl->setParameter($this,"obj_id",$roleObj->getId());
		//	$this->ctrl->setParameter($this,"ref_id",$rolf_id);
		//	$this->ctrl->redirect($this,'perm');
		//}

		$this->ctrl->redirect($this,'perm');
	}

	function &__initTableGUI()
	{
		include_once "./Services/Table/classes/class.ilTableGUI.php";

		return new ilTableGUI(0,false);
	}
	
	/**
	 * standard implementation for tables
	 * use 'from' variable use different initial setting of table 
	 * 
	 */
	function __setTableGUIBasicData(&$tbl,&$result_set,$a_from = "")
	{
		switch ($a_from)
		{
			case "clipboardObject":
				$offset = $_GET["offset"];
				$order = $_GET["sort_by"];
				$direction = $_GET["sort_order"];
				$tbl->disable("footer");
				break;

			default:
				$offset = $_GET["offset"];
				$order = $_GET["sort_by"];
				$direction = $_GET["sort_order"];
				break;
		}

		$tbl->setOrderColumn($order);
		$tbl->setOrderDirection($direction);
		$tbl->setOffset($offset);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->setData($result_set);
	}
	

	function __buildRoleFilterSelect()
	{
		$action[1] = $this->lng->txt('filter_all_roles');
		$action[2] = $this->lng->txt('filter_global_roles');
		$action[3] = $this->lng->txt('filter_local_roles');
		$action[4] = $this->lng->txt('filter_roles_local_policy');
		$action[5] = $this->lng->txt('filter_local_roles_object');
		return ilUtil::formSelect($_SESSION['perm_filtered_roles'], "filter",$action,false,true);
	}
	
	function __filterRoles($a_roles,$a_filter)
	{
		global $rbacreview;

		switch ($a_filter)
		{
			case 1:	// all roles in context
				return $a_roles;
				break;
			
			case 2:	// only global roles
				$arr_global_roles = $rbacreview->getGlobalRoles();
				$arr_remove_roles = array_diff(array_keys($a_roles),$arr_global_roles);

				foreach ($arr_remove_roles as $role_id)
				{
					unset($a_roles[$role_id]);
				}
				
				return $a_roles;
				break;			

			case 3:	// only local roles (all local roles in context that are not defined at ROLE_FOLDER_ID)
				$arr_global_roles = $rbacreview->getGlobalRoles();

				foreach ($arr_global_roles as $role_id)
				{
					unset($a_roles[$role_id]);
				}
				
				return $a_roles;
				break;
				
			case 4:	// only roles which use a local policy 
				$role_folder = $rbacreview->getRoleFolderOfObject($this->gui_obj->object->getRefId());
		
				if (!$role_folder)
				{
					return array();
				}
				
				$arr_local_roles = $rbacreview->getRolesOfRoleFolder($role_folder["ref_id"]);
				$arr_remove_roles = array_diff(array_keys($a_roles),$arr_local_roles);

				foreach ($arr_remove_roles as $role_id)
				{
					unset($a_roles[$role_id]);
				}

				return $a_roles;
				break;
				
			case 5:	// only true local role defined at current position
				
				$role_folder = $rbacreview->getRoleFolderOfObject($this->gui_obj->object->getRefId());
		
				if (!$role_folder)
				{
					return array();
				}
				
				$arr_local_roles = $rbacreview->getRolesOfRoleFolder($role_folder["ref_id"],false);
				$arr_remove_roles = array_diff(array_keys($a_roles),$arr_local_roles);

				foreach ($arr_remove_roles as $role_id)
				{
					unset($a_roles[$role_id]);
				}

				return $a_roles;
				break;
		}

		return $a_roles;
	}

	// show owner sub tab
	function owner()
	{
		global $ilObjDataCache,$ilUser;

		$this->__initSubTabs("owner");

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.obj_owner.html');

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("USERNAME",ilObjUser::_lookupLogin($this->gui_obj->object->getOwner()));
		$this->tpl->setVariable("TBL_TITLE_IMG",ilUtil::getImagePath('icon_usr.gif'));
		$this->tpl->setVariable("TBL_TITLE_IMG_ALT",$this->lng->txt('owner'));
		$this->tpl->setVariable("TBL_TITLE",$this->lng->txt('info_owner_of_object'));
		$this->tpl->setVariable("BTN_CHOWN",$this->lng->txt('change_owner'));
		$this->tpl->setVariable("TXT_USERNAME",$this->lng->txt('username'));
		$this->tpl->setVariable("CHOWN_WARNING",$this->lng->txt('chown_warning'));
	}
	
	function changeOwner()
	{
		global $rbacsystem,$ilErr,$ilObjDataCache;

		if(!$user_id = ilObjUser::_lookupId($_POST['owner']))
		{
			ilUtil::sendInfo($this->lng->txt('user_not_known'));
			$this->owner();
			return true;
		}

		$this->gui_obj->object->setOwner($user_id);
		$this->gui_obj->object->updateOwner();
		$ilObjDataCache->deleteCachedEntry($this->gui_obj->object->getId());
		ilUtil::sendInfo($this->lng->txt('owner_updated'),true);

		if (!$rbacsystem->checkAccess("edit_permission",$this->gui_obj->object->getRefId()))
		{
			$this->ctrl->redirect($this->gui_obj);
			return true;
		}

		$this->ctrl->redirect($this,'owner');
		return true;

	}
	
	// init permission query feature
	function info()
	{
		$this->__initSubTabs("info");

		include_once('classes/class.ilObjectStatusGUI.php');
		
		$ilInfo = new ilObjectStatusGUI($this->gui_obj->object);
		
		$this->tpl->setVariable("ADM_CONTENT",$ilInfo->getHTML());
	}
	
	// init sub tabs
	function __initSubTabs($a_cmd)
	{
		global $ilTabs;

		$perm = ($a_cmd == 'perm') ? true : false;
		$info = ($a_cmd == 'info') ? true : false;
		$owner = ($a_cmd == 'owner') ? true : false;

		$ilTabs->addSubTabTarget("permission_settings", $this->ctrl->getLinkTarget($this, "perm"),
								 "", "", "", $perm);
		$ilTabs->addSubTabTarget("info_status_info", $this->ctrl->getLinkTarget($this, "info"),
								 "", "", "", $info);
		$ilTabs->addSubTabTarget("owner", $this->ctrl->getLinkTarget($this, "owner"),
								 "", "", "", $owner);
	}
	
	function getRolesData()
	{
		global $rbacsystem, $rbacreview, $tree;

		// first get all roles in
		$roles = $rbacreview->getParentRoleIds($this->gui_obj->object->getRefId());

		// filter roles
		$_SESSION['perm_filtered_roles'] = isset($_POST['filter']) ? $_POST['filter'] : $_SESSION['perm_filtered_roles'];

		// set default filter (all roles) if no filter is set
		if ($_SESSION['perm_filtered_roles'] == 0)
        {
            if ($tree->checkForParentType($this->gui_obj->object->getRefId(),'crs') || $tree->checkForParentType($this->gui_obj->object->getRefId(),'grp'))
                $_SESSION['perm_filtered_roles'] = 3;
            else
                $_SESSION['perm_filtered_roles'] = 1;
        }
        
        
  		// remove filtered roles from array
      	$roles = $this->__filterRoles($roles,$_SESSION["perm_filtered_roles"]);

		// determine status of each role (local role, changed policy, protected)

		$role_folder = $rbacreview->getRoleFolderOfObject($this->gui_obj->object->getRefId());
		
		$local_roles = array();

		if (!empty($role_folder))
		{
			$local_roles = $rbacreview->getRolesOfRoleFolder($role_folder["ref_id"]);
		}

		foreach ($roles as $key => $role)
		{
			// exclude system admin role from list
			if ($role["obj_id"] == SYSTEM_ROLE_ID)
			{
				unset($roles[$key]);
				continue;
			}
			
			$this->roles[$role['obj_id']] = $role;

			// don't allow local policies for protected roles
			$this->roles[$role['obj_id']]['keep_protected'] = $rbacreview->isProtected($role['parent'],$role['obj_id']);

			if (!in_array($role["obj_id"],$local_roles))
			{
				$this->roles[$role['obj_id']]['local_policy_enabled'] = false;
				$this->roles[$role['obj_id']]['local_policy_allowed'] = true;
			}
			else
			{
				// no checkbox for local roles
				if ($rbacreview->isAssignable($role["obj_id"],$role_folder["ref_id"]))
				{
					$this->roles[$role['obj_id']]['local_policy_allowed'] = false;
				}
				else
				{
					$this->roles[$role['obj_id']]['local_policy_enabled'] = true;
					$this->roles[$role['obj_id']]['local_policy_allowed'] = true;
				}
			}

			// compute permission settings for each role
			$grouped_ops = ilRbacReview::_groupOperationsByClass(ilRbacReview::_getOperationList($this->gui_obj->object->getType()));
			foreach ($grouped_ops as $ops_group => $ops_data)
			{
				foreach ($ops_data as $key => $operation)
				{
					$grouped_ops[$ops_group][$key]['checked'] = $rbacsystem->checkPermission($this->gui_obj->object->getRefId(), $role['obj_id'], $operation['name']);
				}
			}
			
			$this->roles[$role['obj_id']]['permissions'] = $grouped_ops;
			unset($grouped_ops);
		}
	}
	
	function __displayAddRoleForm()
	{
		// do not display this option for admin section and root node
		$object_types_exclude = array("adm","root","mail","objf","lngf","trac","taxf","auth", "assf","svyf",'seas','extt','adve');

		if (!in_array($this->gui_obj->object->getType(),$object_types_exclude) and $this->gui_obj->object->getRefId() != ROLE_FOLDER_ID)
		{
			$this->tpl->addBlockFile("PERM_ADD_ROLE", "add_local_roles", "tpl.obj_perm_add_role.html");

			// fill in saved values in case of error
			$data = array();
			$data["fields"] = array();
			$data["fields"]["title"] = $_SESSION["error_post_vars"]["Fobject"]["title"];
			$data["fields"]["desc"] = $_SESSION["error_post_vars"]["Fobject"]["desc"];

			foreach ($data["fields"] as $key => $val)
			{
				$this->tpl->setVariable("TXT_LR_".strtoupper($key), $this->lng->txt($key));
				$this->tpl->setVariable(strtoupper($key), $val);
			}

			$this->tpl->setVariable("FORMACTION_LR",$this->gui_obj->getFormAction("addRole", $this->ctrl->getLinkTarget($this, "addRole")));
			$this->tpl->setVariable("TXT_LR_HEADER", $this->lng->txt("you_may_add_local_roles"));
			$this->tpl->setVariable("TXT_ADD_ROLE", $this->lng->txt("role_add_local"));
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		}
	}
	
	function __showPermissionsGeneralSection()
	{
		$this->tpl->setCurrentBlock("perm_subtitle");
		$this->tpl->setVariable("TXT_PERM_CLASS",$this->lng->txt('perm_class_general'));
		$this->tpl->setVariable("TXT_PERM_CLASS_DESC",$this->lng->txt('perm_class_general_desc'));
		$this->tpl->setVariable("COLSPAN", $this->num_roles);
		$this->tpl->parseCurrentBlock();

		foreach ($this->roles as $role)
		{
			foreach ($role['permissions']['general'] as $perm)
			{
				// exclude delete permission for all role_folders expect main ROLE_FOLDER_ID
				if ($perm['name'] == 'delete' and $this->gui_obj->object->getType() == 'rolf' and $this->gui_obj->object->getRefId() != ROLE_FOLDER_ID)
				{
					continue;
				}
				
				$box = ilUtil::formCheckBox($perm['checked'],"perm[".$role["obj_id"]."][]",$perm["ops_id"],$role["protected"]);

				$this->tpl->setCurrentBlock("perm_item");
				$this->tpl->setVariable("PERM_CHECKBOX",$box);
				$this->tpl->setVariable("PERM_NAME",$this->lng->txt($perm['name']));
				$this->tpl->setVariable("PERM_TOOLTIP",$this->lng->txt($this->gui_obj->object->getType()."_".$perm['name']));
				$this->tpl->setVariable("PERM_LABEL",'perm_'.$role['obj_id'].'_'.$perm['ops_id']);
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("perm_table");
			$this->tpl->parseCurrentBlock();	
		}

		$this->tpl->setCurrentBlock("perm_settings");
		$this->tpl->parseCurrentBlock();
	}
	
	function __showPermissionsObjectSection()
	{
		// create pointer to first role (only the permission list is needed)
		reset($this->roles);
		$first_role =& current($this->roles);

		if (count($first_role['permissions']['object'])) // check if object type has special operations
		{
			$this->tpl->setCurrentBlock("perm_subtitle");
			$this->tpl->setVariable("TXT_PERM_CLASS",$this->lng->txt('perm_class_object'));
			$this->tpl->setVariable("TXT_PERM_CLASS_DESC",$this->lng->txt('perm_class_object_desc'));
			$this->tpl->setVariable("COLSPAN", $this->num_roles);
			$this->tpl->parseCurrentBlock();
	
			foreach ($this->roles as $role)
			{
				foreach ($role['permissions']['object'] as $perm)
				{
					$box = ilUtil::formCheckBox($perm['checked'],"perm[".$role["obj_id"]."][]",$perm["ops_id"],$role["protected"]);
	
					$this->tpl->setCurrentBlock("perm_item");
					$this->tpl->setVariable("PERM_CHECKBOX",$box);
					$this->tpl->setVariable("PERM_NAME",$this->lng->txt($this->gui_obj->object->getType()."_".$perm['name']));
					$this->tpl->setVariable("PERM_TOOLTIP",$this->lng->txt($this->gui_obj->object->getType()."_".$perm['name']));
					$this->tpl->setVariable("PERM_LABEL",'perm_'.$role['obj_id'].'_'.$perm['ops_id']);
					$this->tpl->parseCurrentBlock();
				}
	
				$this->tpl->setCurrentBlock("perm_table");
				$this->tpl->parseCurrentBlock();	
			}								
	
			$this->tpl->setCurrentBlock("perm_settings");
			$this->tpl->parseCurrentBlock();
		}
	}
	
	function __showPermissionsRBACSection()
	{
		$this->tpl->setCurrentBlock("perm_subtitle");
		$this->tpl->setVariable("TXT_PERM_CLASS",$this->lng->txt('perm_class_rbac'));
		$this->tpl->setVariable("TXT_PERM_CLASS_DESC",$this->lng->txt('perm_class_rbac_desc'));
		$this->tpl->setVariable("COLSPAN", $this->num_roles);
		$this->tpl->parseCurrentBlock();

		foreach ($this->roles as $role)
		{
			foreach ($role['permissions']['rbac'] as $perm)
			{
				$box = ilUtil::formCheckBox($perm['checked'],"perm[".$role["obj_id"]."][]",$perm["ops_id"],$role["protected"]);

				$this->tpl->setCurrentBlock("perm_item");
				$this->tpl->setVariable("PERM_CHECKBOX",$box);
				$this->tpl->setVariable("PERM_NAME",$this->lng->txt('perm_administrate'));
				$this->tpl->setVariable("PERM_TOOLTIP",$this->lng->txt($this->gui_obj->object->getType()."_".$perm['name']));
				$this->tpl->setVariable("PERM_LABEL",'perm_'.$role['obj_id'].'_'.$perm['ops_id']);
				$this->tpl->parseCurrentBlock();
			}

			// use local policy flag
			// offer option 'use local policy' only to those objects where this option is permitted
			if ($this->objDefinition->stopInheritance($this->gui_obj->object->getType()))
			{
				if ($role['local_policy_allowed'])
				{
					$box = ilUtil::formCheckBox($role['local_policy_enabled'],'stop_inherit[]',$role['obj_id'],$role['keep_protected']);
					$lang = $this->lng->txt("perm_use_local_policy")." (".
						$this->lng->txt("stop_inheritance").")";
					$lang_desc = $this->lng->txt("perm_use_local_policy_desc");
				}
				else
				{
					$box = '&nbsp;';
					$lang = $this->lng->txt("perm_local_role");
					$lang_desc = $this->lng->txt("perm_local_role_desc");
				}
				
				$this->tpl->setCurrentBlock("perm_item");
				$this->tpl->setVariable("PERM_CHECKBOX",$box);
				$this->tpl->setVariable("PERM_NAME",$lang);
				$this->tpl->setVariable("PERM_TOOLTIP",$lang_desc);
				$this->tpl->setVariable("PERM_LABEL",'stop_inherit_'.$role['obj_id']);
				$this->tpl->parseCurrentBlock();
			}
	
				$this->tpl->setCurrentBlock("perm_table");
				$this->tpl->parseCurrentBlock();	
		}

		$this->tpl->setCurrentBlock("perm_settings");
		$this->tpl->parseCurrentBlock();
	}
	
	function __showPermissionsCreateSection()
	{
		// no create operation for roles/role templates in local role folders
		// access is controlled by 'administrate' (change permission settings) only
		if ($this->gui_obj->object->getType() == 'rolf' and $this->gui_obj->object->getRefId() != ROLE_FOLDER_ID)
		{
			return;
		}
		
		// create pointer to first role (only the permission list is needed)
		reset($this->roles);
		$first_role =& current($this->roles);

		if (count($first_role['permissions']['create'])) // check if object type has create operations
		{
			$this->tpl->setCurrentBlock("perm_subtitle");
			$this->tpl->setVariable("TXT_PERM_CLASS",$this->lng->txt('perm_class_create'));
			$this->tpl->setVariable("TXT_PERM_CLASS_DESC",$this->lng->txt('perm_class_create_desc'));
			$this->tpl->setVariable("COLSPAN", $this->num_roles);
			$this->tpl->parseCurrentBlock();
			
			// add a checkbox 'select all' for create permissions of the following object types
			$container_arr = array('cat','grp','crs','fold');
			
			if (in_array($this->gui_obj->object->getType(),$container_arr))
			{
				$chk_toggle_create = true;
			}
	
			foreach ($this->roles as $role)
			{
				$ops_ids = array();
				
				foreach ($role['permissions']['create'] as $perm)
				{
					$ops_ids[] = $perm['ops_id'];
				}
				
				if ($chk_toggle_create)
				{
					$this->tpl->setCurrentBlock('chk_toggle_create');
					$this->tpl->setVariable('PERM_NAME',$this->lng->txt('check_all')."/".$this->lng->txt('uncheck_all'));
					$this->tpl->setVariable('PERM_TOOLTIP',$this->lng->txt('check_all'));
					$this->tpl->setVariable('ROLE_ID',$role['obj_id']);
					$this->tpl->setVariable('JS_VARNAME','perm_'.$role['obj_id']);
					$this->tpl->setVariable('JS_ONCLICK',ilUtil::array_php2js($ops_ids));
					$this->tpl->parseCurrentBlock();
				}				
				
				foreach ($role['permissions']['create'] as $perm)
				{
					if ($perm["name"] == "create_icrs" and !$this->ilias->getSetting("ilinc_active"))
					{
						continue;
					}

					$box = ilUtil::formCheckBox($perm['checked'],"perm[".$role["obj_id"]."][]",$perm["ops_id"],$role["protected"]);
	
					$this->tpl->setCurrentBlock("perm_item");
					$this->tpl->setVariable("PERM_CHECKBOX",$box);
					$this->tpl->setVariable("PERM_NAME",$this->lng->txt("obj".substr($perm['name'],6)));
					$this->tpl->setVariable("PERM_TOOLTIP",$this->lng->txt($this->gui_obj->object->getType()."_".$perm['name']));
					$this->tpl->setVariable("PERM_LABEL",'perm_'.$role['obj_id'].'_'.$perm['ops_id']);
					$this->tpl->parseCurrentBlock();
				}
	
				$this->tpl->setCurrentBlock("perm_table");
				$this->tpl->parseCurrentBlock();	
			}
	
			$this->tpl->setCurrentBlock("perm_settings");
			$this->tpl->parseCurrentBlock();
		}
	}
} // END class.ilPermissionGUI
?>
