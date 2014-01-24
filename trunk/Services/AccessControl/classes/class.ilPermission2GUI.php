<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class ilPermissionGUI
* RBAC related output
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @author Sascha Hofmann <saschahofmann@gmx.de>
*
* @version $Id: class.ilPermissionGUI.php 20310 2009-06-23 12:57:19Z smeyer $
*
*
* @ingroup	ServicesAccessControl
*/
class ilPermission2GUI
{
	protected $gui_obj = null;
	protected $ilErr = null;
	protected $ctrl = null;
	protected $lng = null;
	
	public function __construct($a_gui_obj)
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

		$this->objDefinition =& $objDefinition;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->lng->loadLanguageModule("rbac");

		$this->ctrl =& $ilCtrl;

		$this->gui_obj = $a_gui_obj;
		
		$this->roles = array();
		$this->num_roles = 0;
	}
	



	/**
	* save permissions
	*
	* @access	public
	*/
	function permSave()
	{
		global $rbacreview, $rbacadmin, $rbacsystem;

		$this->getRolesData();

		include_once "Services/AccessControl/classes/class.ilRbacLog.php";
		$log_old = ilRbacLog::gatherFaPa($this->gui_obj->object->getRefId(), array_keys($this->roles));

		// only revoke permission of roles that are not filtered
		foreach($this->roles as $role_id => $data)
		{
			$rbacadmin->revokePermission($this->gui_obj->object->getRefId(),$role_id);
		}

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

			// Only delete local policies for filtered roles
			$linked_roles_to_remove = (array) array_intersect(
				(array) $linked_roles_to_remove,
				(array) array_keys($this->roles));

			// remove roles where stopped inheritance is cancelled and purge rolefolder if empty
			foreach ($linked_roles_to_remove as $role_id)
			{
				if ($rbacreview->isProtected($rolf_id,$role_id))
				{
					continue;
				}
				
				$role_obj = ilObjectFactory::getInstanceByObjId($role_id);
				$role_obj->setParent($rolf_id);
				$role_obj->delete();
				unset($role_obj);
			}
		}

		$log_new = ilRbacLog::gatherFaPa($this->gui_obj->object->getRefId(), array_keys($this->roles));
		$log = ilRbacLog::diffFaPa($log_old, $log_new);
		ilRbacLog::add(ilRbacLog::EDIT_PERMISSIONS, $this->gui_obj->object->getRefId(), $log);

		ilUtil::sendSuccess($this->lng->txt("saved_successfully"),true);
		
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
		global $rbacadmin, $rbacreview, $rbacsystem,$ilErr,$ilCtrl;

		$form = $this->initRoleForm();
		if($form->checkInput())
		{
			$new_title = $form->getInput("title");
			$rolf_data = $rbacreview->getRoleFolderOfObject($this->gui_obj->object->getRefId());
			if($rolf_data['child'])
			{
				foreach($rbacreview->getRolesOfRoleFolder($rolf_data['child']) as $role_id)
				{
					if(trim($new_title) == ilObject::_lookupTitle($role_id))
					{
						$ilErr->raiseError($this->lng->txt('rbac_role_exists_alert'),$ilErr->MESSAGE);
					}
				}
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
						ilUtil::sendFailure($this->lng->txt("msg_no_rolf_allowed1")." '".$this->gui_obj->object->getTitle()."' ".
								$this->lng->txt("msg_no_rolf_allowed2"), true);
						$ilCtrl->redirect($this, "perm");
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
				$roleObj = $this->gui_obj->object->createRole($new_title, $form->getInput("desc"));
			}
			else
			{
				$rfoldObj = ilObjectFactory::getInstanceByRefId($rolf_id);
				$roleObj = $rfoldObj->createRole($new_title, $form->getInput("desc"));
			}

			// protect
			$rbacadmin->setProtected(
				$rolf_id,
				$roleObj->getId(),
				$form->getInput('pro') ? 'y' : 'n'
			);

			// copy rights 
			$right_id_to_copy = $form->getInput("rights");
			if($right_id_to_copy)
			{
				$parentRoles = $rbacreview->getParentRoleIds($rolf_id,true);
				$rbacadmin->copyRoleTemplatePermissions(
					$right_id_to_copy,
					$parentRoles[$right_id_to_copy]["parent"],
					$rolf_id,
					$roleObj->getId(),
					false);

				if($form->getInput('existing'))
				{
					if($form->getInput('pro'))
					{
						$roleObj->changeExistingObjects(
							$this->gui_obj->object->getRefId(),
							ilObjRole::MODE_PROTECTED_KEEP_LOCAL_POLICIES,
							array('all')
						);
					}
					else
					{
						$roleObj->changeExistingObjects(
							$this->gui_obj->object->getRefId(),
							ilObjRole::MODE_UNPROTECTED_KEEP_LOCAL_POLICIES,
							array('all')
						);
					}
				}
			}

			// add to desktop items
			if($form->getInput("desktop"))
			{
				include_once 'Services/AccessControl/classes/class.ilRoleDesktopItem.php';
				$role_desk_item_obj =& new ilRoleDesktopItem($roleObj->getId());
				$role_desk_item_obj->add($this->gui_obj->object->getRefId(),ilObject::_lookupType($this->gui_obj->object->getRefId(),true));
			}		

			ilUtil::sendSuccess($this->lng->txt("role_added"),true);
			$this->ctrl->redirect($this,'perm');
		}
		else
		{
			$form->setValuesByPost();
			$this->tpl->setContent($form->getHTML());
		}
	}

	function &__initTableGUI()
	{
		include_once "Services/Table/classes/class.ilTableGUI.php";

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
		$this->__initSubTabs("owner");

		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this, "owner"));
		$form->setTitle($this->lng->txt("info_owner_of_object"));
		
		$login = new ilTextInputGUI($this->lng->txt("username"), "owner");
		$login->setDataSource($this->ctrl->getLinkTargetByClass(array(get_class($this),
			'ilRepositorySearchGUI'), 'doUserAutoComplete', '', true));		
		$login->setRequired(true);
		$login->setSize(50);
		$login->setInfo($this->lng->txt("chown_warning"));
		$login->setValue(ilObjUser::_lookupLogin($this->gui_obj->object->getOwner()));
		$form->addItem($login);
		
		$form->addCommandButton("changeOwner", $this->lng->txt("change_owner"));
		
		$this->tpl->setContent($form->getHTML());
	}
	
	function changeOwner()
	{
		global $rbacsystem,$ilObjDataCache;

		if(!$user_id = ilObjUser::_lookupId($_POST['owner']))
		{
			ilUtil::sendFailure($this->lng->txt('user_not_known'));
			$this->owner();
			return true;
		}
		
		// no need to change?
		if($user_id != $this->gui_obj->object->getOwner())
		{
			$this->gui_obj->object->setOwner($user_id);
			$this->gui_obj->object->updateOwner();
			$ilObjDataCache->deleteCachedEntry($this->gui_obj->object->getId());			

			include_once "Services/AccessControl/classes/class.ilRbacLog.php";
			if(ilRbacLog::isActive())
			{
				ilRbacLog::add(ilRbacLog::CHANGE_OWNER, $this->gui_obj->object->getRefId(), array($user_id));
			}
		}
		
		ilUtil::sendSuccess($this->lng->txt('owner_updated'),true);

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

		include_once('./Services/AccessControl/classes/class.ilObjectStatusGUI.php');
		
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
		$log = ($a_cmd == 'log') ? true : false;

		$ilTabs->addSubTabTarget("permission_settings", $this->ctrl->getLinkTarget($this, "perm"),
								 "", "", "", $perm);
								 
		#$ilTabs->addSubTabTarget("permission_settings", $this->ctrl->getLinkTarget($this, "perm2"),
		#							 "", "", "", $perm);
								 
		$ilTabs->addSubTabTarget("info_status_info", $this->ctrl->getLinkTarget($this, "info"),
								 "", "", "", $info);
		$ilTabs->addSubTabTarget("owner", $this->ctrl->getLinkTarget($this, "owner"),
								 "", "", "", $owner);

		include_once "Services/AccessControl/classes/class.ilRbacLog.php";
		if(ilRbacLog::isActive())
		{
			$ilTabs->addSubTabTarget("log", $this->ctrl->getLinkTarget($this, "log"),
									 "", "", "", $log);
		}
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
	
	function __showPermissionsGeneralSection()
	{
		global $objDefinition;
		
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
				if ($objDefinition->isPlugin($this->gui_obj->object->getType()))
				{
					$this->tpl->setVariable("PERM_TOOLTIP",
						ilPlugin::lookupTxt("rep_robj", $this->gui_obj->object->getType(),
						$this->gui_obj->object->getType()."_".$perm['name']));
				}
				else
				{
					$this->tpl->setVariable("PERM_TOOLTIP",$this->lng->txt($this->gui_obj->object->getType()."_".$perm['name']));
				}
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
		global $objDefinition;
		
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
					if ($objDefinition->isPlugin($this->gui_obj->object->getType()))
					{
						$this->tpl->setVariable("PERM_TOOLTIP",
							ilPlugin::lookupTxt("rep_robj", $this->gui_obj->object->getType(),
							$this->gui_obj->object->getType()."_".$perm['name']));
					}
					else
					{
						$this->tpl->setVariable("PERM_TOOLTIP",$this->lng->txt($this->gui_obj->object->getType()."_".$perm['name']));
					}
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
		global $objDefinition;
		
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
				if ($objDefinition->isPlugin($this->gui_obj->object->getType()))
				{
					$this->tpl->setVariable("PERM_TOOLTIP",
						ilPlugin::lookupTxt("rep_robj", $this->gui_obj->object->getType(),
						$this->gui_obj->object->getType()."_".$perm['name']));
				}
				else
				{
					$this->tpl->setVariable("PERM_TOOLTIP",$this->lng->txt($this->gui_obj->object->getType()."_".$perm['name']));
				}
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
		global $objDefinition,$ilSetting;
		
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
					if ($perm["name"] == "create_icrs" and !$ilSetting->get("ilinc_active"))
					{
						continue;
					}

					$box = ilUtil::formCheckBox($perm['checked'],"perm[".$role["obj_id"]."][]",$perm["ops_id"],$role["protected"]);
	
					$this->tpl->setCurrentBlock("perm_item");
					$this->tpl->setVariable("PERM_CHECKBOX",$box);
					if ($objDefinition->isPlugin(substr($perm['name'],7)))
					{
						$this->tpl->setVariable("PERM_NAME",
							ilPlugin::lookupTxt("rep_robj", substr($perm['name'],7),
							"obj_".substr($perm['name'],7)));
						$this->tpl->setVariable("PERM_TOOLTIP",
							ilPlugin::lookupTxt("rep_robj", substr($perm['name'],7),
							$this->gui_obj->object->getType()."_".$perm['name']));
					}
					else
					{
						$this->tpl->setVariable("PERM_NAME",$this->lng->txt("obj".substr($perm['name'],6)));
						$this->tpl->setVariable("PERM_TOOLTIP",$this->lng->txt($this->gui_obj->object->getType()."_".$perm['name']));
					}
					
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

	function log()
	{
		include_once "Services/AccessControl/classes/class.ilRbacLog.php";
		if(!ilRbacLog::isActive())
		{
			$this->ctrl->redirect($this, "perm");
		}

		$this->__initSubTabs("log");

		include_once "Services/AccessControl/classes/class.ilRbacLogTableGUI.php";
		$table = new ilRbacLogTableGUI($this, "log", $this->gui_obj->object->getRefId());
		$this->tpl->setContent($table->getHTML());
	}

	function applyLogFilter()
    {
		include_once "Services/AccessControl/classes/class.ilRbacLogTableGUI.php";
		$table = new ilRbacLogTableGUI($this, "log", $this->gui_obj->object->getRefId());
		$table->resetOffset();
		$table->writeFilterToSession();
		$this->log();
    }

	function resetLogFilter()
    {
		include_once "Services/AccessControl/classes/class.ilRbacLogTableGUI.php";
		$table = new ilRbacLogTableGUI($this, "log", $this->gui_obj->object->getRefId());
		$table->resetOffset();
		$table->resetFilter();
		$this->log();
    }

} // END class.ilPermissionGUI
?>
