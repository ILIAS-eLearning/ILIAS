<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/AccessControl/classes/class.ilPermission2GUI.php';

/**
* New PermissionGUI (extends from old ilPermission2GUI) 
* RBAC related output
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @author Sascha Hofmann <saschahofmann@gmx.de>
*
* @version $Id$
*
* @ilCtrl_Calls ilPermissionGUI: ilObjRoleGUI, ilRepositorySearchGUI
*
* @ingroup	ServicesAccessControl
*/
class ilPermissionGUI extends ilPermission2GUI
{
	protected $current_obj = null;

	/**
	 * Constructor
	 * @param object $a_gui_obj
	 * @return 
	 */
	public function __construct($a_gui_obj)
	{
		parent::__construct($a_gui_obj);
	}
	
	/**
	 * Get current object
	 * @return ilObject
	 */
	public function getCurrentObject()
	{
		return $this->gui_obj->object;
	}

	/**
	 * Called after toolbar action applyTemplateSwitch
	 */
	protected function confirmTemplateSwitch()
	{
		include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateGUI.php';
		$this->ctrl->setReturn($this,'perm');
		$this->ctrl->setCmdClass('ildidactictemplategui');
		$dtpl_gui = new ilDidacticTemplateGUI($this->gui_obj);
		$this->ctrl->forwardCommand($dtpl_gui,'confirmTemplateSwitch');
	}

	
	/**
	 * show permission table
	 * @return 
	 */
	public function perm(ilTable2GUI $table = NULL )
	{
		global $objDefinition, $ilToolbar;

		include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateGUI.php';
		$dtpl = new ilDidacticTemplateGUI($this->gui_obj);
		if($dtpl->appendToolbarSwitch(
			$ilToolbar,
			$this->getCurrentObject()->getType(),
			$this->getCurrentObject()->getRefId()
		))
		{
			$ilToolbar->addSeparator();
		}
		
		if($objDefinition->hasLocalRoles($this->getCurrentObject()->getType()) and
			!$this->isAdminRoleFolder() and
			!$this->isAdministrationObject()
		)
		{
			// Show new role button
			$ilToolbar->setFormAction($this->ctrl->getFormAction($this));
			$ilToolbar->addButton($this->lng->txt('rbac_add_new_local_role'),$this->ctrl->getLinkTarget($this,'displayAddRoleForm'));
		}

		$this->__initSubTabs("perm");
		
		if(!$table instanceof ilTable2GUI)
		{
			include_once './Services/AccessControl/classes/class.ilObjectRolePermissionTableGUI.php';
			$table = new ilObjectRolePermissionTableGUI($this,'perm',$this->getCurrentObject()->getRefId());
		}
		$table->parse();
		$this->tpl->setContent($table->getHTML());
	}
	
	
	
	/**
	 * Check of current location is administration (main) role folder
	 * @return 
	 */
	protected function isAdminRoleFolder()
	{
		return $this->getCurrentObject()->getRefId() == ROLE_FOLDER_ID;
	}

	protected function isAdministrationObject()
	{
		return $this->getCurrentObject()->getType() == 'adm';
	}
	
	/**
	 * Apply filter
	 * @return 
	 */
	protected function applyFilter()
	{
		include_once './Services/AccessControl/classes/class.ilObjectRolePermissionTableGUI.php';
		$table = new ilObjectRolePermissionTableGUI($this,'perm',$this->getCurrentObject()->getRefId());
		$table->resetOffset();
		$table->writeFilterToSession();
		return $this->perm($table);
	}
	
	/**
	 * Reset filter
	 * @return 
	 */
	protected function resetFilter()
	{
		include_once './Services/AccessControl/classes/class.ilObjectRolePermissionTableGUI.php';
		$table = new ilObjectRolePermissionTableGUI($this,'perm',$this->getCurrentObject()->getRefId());
		$table->resetOffset();
		$table->resetFilter();
		
		return $this->perm($table);
	}
	
	/**
	 * Apply filter to roles
	 * @param int $a_filter_id
	 * @return 
	 */
	public function applyRoleFilter($a_roles, $a_filter_id)
	{
		global $rbacreview;
		
		// Always delete administrator role from view
		if(isset($a_roles[SYSTEM_ROLE_ID]))
		{
			unset($a_roles[SYSTEM_ROLE_ID]);
		}

		switch ($a_filter_id)
		{
			// all roles in context
			case ilObjectRolePermissionTableGUI::ROLE_FILTER_ALL:

				return $a_roles;
			
			// only global roles
			case ilObjectRolePermissionTableGUI::ROLE_FILTER_GLOBAL:
	
				$arr_global_roles = $rbacreview->getGlobalRoles();
				$arr_remove_roles = array_diff(array_keys($a_roles),$arr_global_roles);

				foreach ($arr_remove_roles as $role_id)
				{
					unset($a_roles[$role_id]);
				}
				return $a_roles;

			// only local roles (all local roles in context that are not defined at ROLE_FOLDER_ID)
			case ilObjectRolePermissionTableGUI::ROLE_FILTER_LOCAL:
				$arr_global_roles = $rbacreview->getGlobalRoles();

				foreach ($arr_global_roles as $role_id)
				{
					unset($a_roles[$role_id]);
				}
				
				return $a_roles;
				break;
				
			// only roles which use a local policy
			case ilObjectRolePermissionTableGUI::ROLE_FILTER_LOCAL_POLICY: 
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
				
			// only true local role defined at current position
			case ilObjectRolePermissionTableGUI::ROLE_FILTER_LOCAL_OBJECT:
				
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
				
			default:
				return $a_roles;
		}
	}
	
	/**
	 * Save permissions
	 * @return 
	 */
	protected function savePermissions()
	{
		global $rbacreview,$objDefinition,$rbacadmin;
		
		include_once './Services/AccessControl/classes/class.ilObjectRolePermissionTableGUI.php';
		$table = new ilObjectRolePermissionTableGUI($this,'perm',$this->getCurrentObject()->getRefId());
		
		$roles = $this->applyRoleFilter(
			$rbacreview->getParentRoleIds($this->getCurrentObject()->getRefId()),
			$table->getFilterItemByPostVar('role')->getValue()
		);
		
		// Log history
		include_once "Services/AccessControl/classes/class.ilRbacLog.php";
		$log_old = ilRbacLog::gatherFaPa($this->getCurrentObject()->getRefId(),array_keys((array) $roles));
		

		# all possible create permissions 
		$possible_ops_ids = $rbacreview->getOperationsByTypeAndClass(
			$this->getCurrentObject()->getType(), 
			'create'
		);
		
		# createable (activated) create permissions
		$create_types = $objDefinition->getCreatableSubObjects(
			$this->getCurrentObject()->getType()
		);
		$createable_ops_ids = ilRbacReview::lookupCreateOperationIds(array_keys((array) $create_types));

		foreach((array) $roles as $role => $role_data)
		{
			if($role_data['protected'])
			{
				continue;
			}

			$new_ops = array_keys((array) $_POST['perm'][$role]);
			$old_ops = $rbacreview->getRoleOperationsOnObject(
				$role,
				$this->getCurrentObject()->getRefId()
			);
			
			// Add operations which were enabled and are not activated.
			foreach($possible_ops_ids as $create_ops_id)
			{
				if(in_array($create_ops_id,$createable_ops_ids))
				{
					continue;
				}
				if(in_array($create_ops_id,$old_ops))
				{
					$new_ops[] = $create_ops_id;
				}
			}
			
			$rbacadmin->revokePermission(
				$this->getCurrentObject()->getRefId(),
				$role
			);
			
			$rbacadmin->grantPermission(
				$role,
				array_unique($new_ops),
				$this->getCurrentObject()->getRefId()
			);
		}
		
		// Handle local policies.
		$rolf_id = $this->initRoleFolder(count((array) $_POST['inherit']) ? true : false);
		$relevant_roles = array_intersect(
			$rbacreview->getRolesOfRoleFolder($rolf_id),
			array_keys($roles)
		);
		
		if(ilPermissionGUI::hasContainerCommands($this->getCurrentObject()->getType()))
		{
			foreach($roles as $role)
			{
				// No action for local roles
				if($role['parent'] == $rolf_id and $role['assign'] == 'y')
				{
					continue;
				}
				// Nothing for protected roles
				if($role['protected'])
				{
					continue;
				}
				// Stop local policy
				if($role['parent'] == $rolf_id and !isset($_POST['inherit'][$role['obj_id']]))
				{
					$role_obj = ilObjectFactory::getInstanceByObjId($role['obj_id']);
					$role_obj->setParent($rolf_id);
					$role_obj->delete();
					continue;
				}
				// Add local policy
				if($role['parent'] != $rolf_id and isset($_POST['inherit'][$role['obj_id']]))
				{
					$rbacadmin->copyRoleTemplatePermissions(
						$role['obj_id'], 
						$role['parent'],
						$rolf_id, 
						$role['obj_id']
					);
					$rbacadmin->assignRoleToFolder($role['obj_id'],$rolf_id,'n');
				}
			}
		}
		
		// Protect permissions
		if(ilPermissionGUI::hasContainerCommands($this->getCurrentObject()->getType()))
		{
			foreach($roles as $role)
			{
				if($rbacreview->isAssignable($role['obj_id'], $rolf_id))
				{
					if(isset($_POST['protect'][$role['obj_id']]) and 
						!$rbacreview->isProtected($rolf_id, $role['obj_id']))
					{
						$rbacadmin->setProtected($rolf_id, $role['obj_id'], 'y');
					}
					elseif(!isset($_POST['protect'][$role['obj_id']]) and 
						$rbacreview->isProtected($rolf_id, $role['obj_id']))
					{
						$rbacadmin->setProtected($rolf_id, $role['obj_id'], 'n');
					}
				}
			}
		}
		
		$log_new = ilRbacLog::gatherFaPa($this->getCurrentObject()->getRefId(),array_keys((array) $roles));
		$log = ilRbacLog::diffFaPa($log_old, $log_new);
		ilRbacLog::add(ilRbacLog::EDIT_PERMISSIONS, $this->getCurrentObject()->getRefId(), $log);
		
		if(count((array) $_POST['block']))
		{
			return $this->showConfirmBlockRole(array_keys($_POST['block']));
		}
		
		
		ilUtil::sendSuccess($this->lng->txt('settings_saved'),true);
		#$this->ctrl->redirect($this,'perm');
		$this->perm();
	}
	
	/**
	 * Show block role confirmation screen
	 * @param array $a_roles
	 * @return 
	 */
	protected function showConfirmBlockRole($a_roles)
	{
		ilUtil::sendInfo($this->lng->txt('role_confirm_block_role_info'));
		
		include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
		$confirm = new ilConfirmationGUI();
		$confirm->setFormAction($this->ctrl->getFormAction($this));
		$confirm->setHeaderText($this->lng->txt('role_confirm_block_role_header'));
		$confirm->setConfirm($this->lng->txt('role_block_role'), 'blockRoles');
		$confirm->setCancel($this->lng->txt('cancel'), 'perm');
		
		foreach($a_roles as $role_id)
		{
			include_once './Services/AccessControl/classes/class.ilObjRole.php';
			$confirm->addItem(
				'roles[]',
				$role_id, 
				ilObjRole::_getTranslation(ilObject::_lookupTitle($role_id)));
		}
		
		$this->tpl->setContent($confirm->getHTML());
		
	}
	
	/**
	 * Block role
	 * @return 
	 */
	protected function blockRoles()
	{
		global $rbacadmin,$rbacreview;
		
		$rolf = $rbacreview->getRoleFolderIdOfObject($this->getCurrentObject()->getRefId());
		
		$p_roles = $rbacreview->getParentRoleIds($this->getCurrentObject()->getRefId());
		
		$roles = $_POST['roles'];
		foreach($roles as $role)
		{
			// Set assign to 'y' only if it is a local role
			$assign = $rbacreview->isAssignable($role, $rolf) ? 'y' : 'n';

			// Delete permissions
			$rbacadmin->revokeSubtreePermissions($this->getCurrentObject()->getRefId(), $role);
			
			// Delete template permissions
			$rbacadmin->deleteSubtreeTemplates($this->getCurrentObject()->getRefId(), $role);

			
			$rbacadmin->assignRoleToFolder(
				$role,
				$rolf,
				$assign
			);
		}
		
		ilUtil::sendInfo($this->lng->txt('settings_saved'));
		$this->ctrl->redirect($this,'perm');
	}
	
	/**
	 * Init role folder of object
	 * @param object $a_create [optional]
	 * @return 
	 */
	protected function initRoleFolder($a_create = false)
	{
		global $rbacreview;
		
		$rolf_id = $rbacreview->getRoleFolderIdOfObject($this->getCurrentObject()->getRefId());
		
		if($rolf_id)
		{
			return $rolf_id;
		}
		$rolf = $this->getCurrentObject()->createRoleFolder();
		return $rolf->getRefId();
	}
	
	/**
	 * Check if container commands are possible for the current object type
	 * @param object $a_type
	 * @return 
	 */
	public static function hasContainerCommands($a_type)
	{
		global $objDefinition;
		
		return $objDefinition->isContainer($a_type) and $a_type != 'root' and $a_type != 'adm' and $a_type != 'rolf';
	}
}
?>