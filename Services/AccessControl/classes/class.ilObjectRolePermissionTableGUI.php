<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once ('./Services/Table/classes/class.ilTable2GUI.php');
include_once './Services/AccessControl/classes/class.ilPermissionGUI.php';

/**
* Table for object role permissions
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ServicesAccessControl
*/
class ilObjectRolePermissionTableGUI extends ilTable2GUI
{
	const ROLE_FILTER_ALL = 1;
	const ROLE_FILTER_GLOBAL = 2;
	const ROLE_FILTER_LOCAL = 3;
	const ROLE_FILTER_LOCAL_POLICY = 4;
	const ROLE_FILTER_LOCAL_OBJECT = 5;
	
	private $ref_id = null;
	private $roles = array();

	private $tree_path_ids = array();
	
	private $activeOperations = array();
	private $visible_roles = array();

	/**
	 * Constructor
	 * @return 
	 */
	public function __construct($a_parent_obj,$a_parent_cmd, $a_ref_id)
	{
		global $ilCtrl,$rbacreview,$tpl,$tree;
		
		parent::__construct($a_parent_obj,$a_parent_cmd);
		
		$this->lng->loadLanguageModule('rbac');
		
		$this->ref_id = $a_ref_id;
		$this->tree_path_ids = $tree->getPathId($this->ref_id);
		
		$this->setId('objroleperm_'.$this->ref_id);

		$tpl->addJavaScript('./Services/AccessControl/js/ilPermSelect.js');

		$this->setTitle($this->lng->txt('permission_settings'));
		$this->setEnableHeader(true);
		$this->disable('sort');
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->disable('numinfo');
		$this->setRowTemplate("tpl.obj_role_perm_row.html", "Services/AccessControl");
		$this->setLimit(100);
		$this->setShowRowsSelector(false);
		$this->setDisableFilterHiding(true);
		$this->setNoEntriesText($this->lng->txt('msg_no_roles_of_type'));
		
		$this->addCommandButton('savePermissions', $this->lng->txt('save'));
		
		$this->initFilter();
	}
	

	/**
	 * Get tree path ids
	 * @return array
	 */
	public function getPathIds()
	{
		return (array) $this->tree_path_ids;
	}
	
	/**
	 * Get ref id of current object
	 * @return 
	 */
	public function getRefId()
	{
		return $this->ref_id;
	}
	
	/**
	 * Get obj id
	 * @return 
	 */
	public function getObjId()
	{
		return ilObject::_lookupObjId($this->getRefId());
	}
	
	/**
	 * get obj type
	 * @return 
	 */
	public function getObjType()
	{
		return ilObject::_lookupType($this->getObjId());
	}
	
	/**
	 * Add active operation
	 * @param int $a_ops_id
	 * @return 
	 */
	public function addActiveOperation($a_ops_id)
	{
		$this->activeOperations[] = $a_ops_id;
	}
	
	/**
	 * get active operations
	 * @return 
	 */
	public function getActiveOperations()
	{
		return (array) $this->activeOperations;
	}
	
	/**
	 * Set Visible roles
	 * @param object $a_ar
	 * @return 
	 */
	public function setVisibleRoles($a_ar)
	{
		$this->visible_roles = $a_ar; 
	}
	
	/**
	 * get visible roles
	 * @return 
	 */
	public function getVisibleRoles()
	{
		return $this->visible_roles;
	}
	
	/**
	 * Init role filter
	 * @return 
	 */
	public function initFilter()
	{
		global $tree;
		
		$roles = $this->addFilterItemByMetaType(
			'role',
			ilTable2GUI::FILTER_SELECT
		);
		
		// Limit filter to local roles only for objects with group or course in path
		if(!$roles->getValue())
		{
			if ($tree->checkForParentType($this->getRefId(), 'crs') or
				$tree->checkForParentType($this->getRefId(), 'grp'))
			{
				$roles->setValue(self::ROLE_FILTER_LOCAL);
			}
			else
			{
				$roles->setValue(self::ROLE_FILTER_ALL);
			}
		}
		
		
		$roles->setOptions(
			array(
				self::ROLE_FILTER_ALL => $this->lng->txt('filter_all_roles'),
				self::ROLE_FILTER_GLOBAL => $this->lng->txt('filter_global_roles'),
				self::ROLE_FILTER_LOCAL => $this->lng->txt('filter_local_roles'),
				self::ROLE_FILTER_LOCAL_POLICY => $this->lng->txt('filter_roles_local_policy'),
				self::ROLE_FILTER_LOCAL_OBJECT => $this->lng->txt('filter_local_roles_object')
			)
		);
			
	}
	
	/**
	 * Fill one permission row
	 * @param object $row
	 * @return 
	 */
	public function fillRow($row)
	{
		global $objDefinition;
		
		
		// local policy
		if(isset($row['show_local_policy_row']))
		{
			foreach($row['roles'] as $role_id => $role_info)
			{
				$this->tpl->setCurrentBlock('role_option');
				$this->tpl->setVariable('INHERIT_ROLE_ID',$role_id);
				$this->tpl->setVariable('INHERIT_CHECKED',$role_info['local_policy'] ? 'checked=checked' : '');
				$this->tpl->setVariable('INHERIT_DISABLED',($role_info['protected'] or $role_info['isLocal']) ? 'disabled="disabled"' : '');
				$this->tpl->setVariable('TXT_INHERIT',$this->lng->txt('rbac_local_policy'));
				$this->tpl->setVariable('INHERIT_LONG',$this->lng->txt('perm_use_local_policy_desc'));
				$this->tpl->parseCurrentBlock();
			}
			return true;
		}
		// protected
		if(isset($row['show_protected_row']))
		{
			foreach($row['roles'] as $role_id => $role_info)
			{
				$this->tpl->setCurrentBlock('role_protect');
				$this->tpl->setVariable('PROTECT_ROLE_ID',$role_id);
				$this->tpl->setVariable('PROTECT_CHECKED',$role_info['protected_status'] ? 'checked=checked' : '');
				$this->tpl->setVariable('PROTECT_DISABLED',$role_info['protected_allowed'] ? '' : 'disabled="disabled"');
				$this->tpl->setVariable('TXT_PROTECT',$this->lng->txt('role_protect_permissions'));
				$this->tpl->setVariable('PROTECT_LONG',$this->lng->txt('role_protect_permissions_desc'));
				$this->tpl->parseCurrentBlock();
			}
			return true;
		}
		
		// block role
		if(isset($row['show_block_row']))
		{
			foreach($this->getVisibleRoles() as $counter => $role_info)
			{
				$this->tpl->setCurrentBlock('role_block');
				$this->tpl->setVariable('BLOCK_ROLE_ID',$role_info['obj_id']);
				$this->tpl->setVariable('TXT_BLOCK',$this->lng->txt('role_block_role'));
				$this->tpl->setVariable('BLOCK_LONG',$this->lng->txt('role_block_role_desc'));
				if($role_info['protected'] == 'y')
				{
					$this->tpl->setVariable('BLOCK_DISABLED','disabled="disabled');
				}
				
				$this->tpl->parseCurrentBlock();
			}
			return true;
		}

		// Select all
		if(isset($row['show_select_all']))
		{
			foreach($this->getVisibleRoles() as $role)
			{
				$this->tpl->setCurrentBlock('role_select_all');
				$this->tpl->setVariable('JS_ROLE_ID',$role['obj_id']);
				$this->tpl->setVariable('JS_SUBID',$row['subtype']);
				$this->tpl->setVariable('JS_ALL_PERMS',"['".implode("','",$row['ops'])."']");
				$this->tpl->setVariable('JS_FORM_NAME',$this->getFormName());
				$this->tpl->setVariable('TXT_SEL_ALL',$this->lng->txt('select_all'));
				$this->tpl->parseCurrentBlock();
			}
			return true;
		}

		// Object permissions
		if(isset($row['show_start_info']))
		{
			$this->tpl->setCurrentBlock('section_info');
			$this->tpl->setVariable('SECTION_TITLE',$this->lng->txt('perm_class_object'));
			$this->tpl->setVariable('SECTION_DESC',$this->lng->txt('perm_class_object_desc'));
			$this->tpl->parseCurrentBlock();
			
			return true;
		}

		if(isset($row['show_create_info']))
		{
			$this->tpl->setCurrentBlock('section_info');
			$this->tpl->setVariable('SECTION_TITLE',$this->lng->txt('perm_class_create'));
			$this->tpl->setVariable('SECTION_DESC',$this->lng->txt('perm_class_create_desc'));
			$this->tpl->parseCurrentBlock();
			
			return true;
		}

		foreach((array) $row['roles'] as $role_id => $role_info)
		{
			$this->tpl->setCurrentBlock('role_td');
			$this->tpl->setVariable('PERM_ROLE_ID',$role_id);
			$this->tpl->setVariable('PERM_PERM_ID',$row['perm']['ops_id']);
			
			
			if(substr($row['perm']['operation'],0,6) == 'create')
			{
				if ($objDefinition->isPlugin(substr($row['perm']['operation'],7)))
				{
					$perm = ilPlugin::lookupTxt("rep_robj", substr($row['perm']['operation'],7),
						"obj_".substr($row['perm']['operation'],7));
				}
				else
				{
					$perm = $this->lng->txt('obj_'.substr($row['perm']['operation'],7));
				}
			}
			else
			{
				if($this->lng->exists($this->getObjType().'_'.$row['perm']['operation'].'_short'))
				{
					$perm = $this->lng->txt($this->getObjType().'_'.$row['perm']['operation'].'_short');
				}
				else
				{
					$perm = $this->lng->txt($row['perm']['operation']);
				}
			}
			
			$this->tpl->setVariable('TXT_PERM',$perm);
			
			if ($objDefinition->isPlugin($this->getObjType()))
			{
				$this->tpl->setVariable('PERM_LONG',ilPlugin::lookupTxt("rep_robj", $this->getObjType(),
						$this->getObjType()."_".$row['perm']['operation']));
			}
			elseif(substr($row['perm']['operation'],0,6) == 'create')
			{
				$this->tpl->setVariable('PERM_LONG',$this->lng->txt('rbac_'.$row['perm']['operation']));
			}
			else
			{
				$this->tpl->setVariable('PERM_LONG',$this->lng->txt($this->getObjType().'_'.$row['perm']['operation']));
			}
			
			if($role_info['protected'])
			{
				$this->tpl->setVariable('PERM_DISABLED',$role_info['protected'] ? 'disabled="disabled"' : '');
			}
			if($role_info['permission_set'])
			{
				$this->tpl->setVariable('PERM_CHECKED','checked="checked"');
			}

			$this->tpl->parseCurrentBlock();
		}
	}
	
	
	/**
	 * Parse 
	 * @return 
	 */
	public function parse()
	{
		global $rbacreview,$objDefinition;
		
		$this->initColumns();

		$perms = array();
		$roles = array();
		
		if(!count($this->getVisibleRoles()))
		{
			return $this->setData(array());
		}
		
		// Read operations of role
		$operations = array();
		foreach($this->getVisibleRoles() as $role_data)
		{
			$operations[$role_data['obj_id']] = $rbacreview->getActiveOperationsOfRole($this->getRefId(), $role_data['obj_id']);
		}
		
		$counter = 0;
		
		// Local policy
		if(ilPermissionGUI::hasContainerCommands($this->getObjType()))
		{
			$roles = array();
			$local_roles = $rbacreview->getRolesOfObject($this->getRefId());
			foreach($this->getVisibleRoles() as $role_id => $role_data)
			{
				$roles[$role_data['obj_id']] = array(
					'protected' => $role_data['protected'],
					'local_policy' => in_array($role_data['obj_id'],$local_roles),
					'isLocal' => ($this->getRefId() == $role_data['parent']) && $role_data['assign'] == 'y'
				);
			}
			$perms[$counter]['roles'] = $roles;
			$perms[$counter]['show_local_policy_row'] = 1;
			
			$counter++;
		}
		
		// Protect permissions
		if(ilPermissionGUI::hasContainerCommands($this->getObjType()))
		{
			$roles = array();
			foreach($this->getVisibleRoles() as $role_id => $role_data)
			{
				$roles[$role_data['obj_id']] = array(
					'protected_allowed' => $rbacreview->isAssignable($role_data['obj_id'],$this->getRefId()),
					'protected_status' => $rbacreview->isProtected($role_data['parent'], $role_data['obj_id'])
				);
			}
			$perms[$counter]['roles'] = $roles;
			$perms[$counter]['show_protected_row'] = 1;
			
			$counter++;
		}
		// Block role
		if(ilPermissionGUI::hasContainerCommands($this->getObjType()))
		{
			$perms[$counter++]['show_block_row'] = 1;	
		}
		

		if(ilPermissionGUI::hasContainerCommands($this->getObjType()))
		{
			$perms[$counter++]['show_start_info'] = true;
		}

		// no creation permissions
		$no_creation_operations = array();
		foreach($rbacreview->getOperationsByTypeAndClass($this->getObjType(),'object') as $operation)
		{
			$this->addActiveOperation($operation);
			$no_creation_operations[] = $operation;

			$roles = array();
			foreach($this->getVisibleRoles() as $role_data)
			{
				
				$roles[$role_data['obj_id']] = 
					array(
						'protected' => $role_data['protected'],
						'permission_set' => in_array($operation,(array) $operations[$role_data['obj_id']])
					);
			}
			
			$op = $rbacreview->getOperation($operation);

			$perms[$counter]['roles'] = $roles;
			$perms[$counter]['perm'] =  $op;
			$counter++;
			
		}
		
		/*
		 * Select all
		 */
		if($no_creation_operations)
		{
			$perms[$counter]['show_select_all'] = 1;
			$perms[$counter]['ops'] = $no_creation_operations;
			$perms[$counter]['subtype'] = 'nocreation';
			$counter++;
		}
		
		
		if($objDefinition->isContainer($this->getObjType()))
		{
			$perms[$counter++]['show_create_info'] = true;
		}

		// Get creatable objects
		$objects = $objDefinition->getCreatableSubObjects($this->getObjType());
		$ops_ids = ilRbacReview::lookupCreateOperationIds(array_keys($objects));
		$creation_operations = array();
		foreach($objects as $type => $info)
		{
			$ops_id = $ops_ids[$type];
			
			if(!$ops_id)
			{
				continue;
			}
			
			$this->addActiveOperation($ops_id);
			$creation_operations[] = $ops_id;
			
			$roles = array();
			foreach($this->getVisibleRoles() as $role_data)
			{
				$roles[$role_data['obj_id']] = 
					array(
						'protected' => $role_data['protected'],
						'permission_set' => in_array($ops_id,(array) $operations[$role_data['obj_id']])
					);
			}
			
			$op = $rbacreview->getOperation($ops_id);

			$perms[$counter]['roles'] = $roles;
			$perms[$counter]['perm'] =  $op;
			$counter++;
			
		}
	
		
		
		// Select all
		if(count($creation_operations))
		{
			$perms[$counter]['show_select_all'] = 1;
			$perms[$counter]['ops'] = $creation_operations;
			$perms[$counter]['subtype'] = 'creation';
			$counter++;
		}

		$this->setData($perms);
	}
	
	/**
	 * init Columns
	 * @return 
	 */
	protected function initColumns()
	{
		global $rbacreview,$ilCtrl;
		
		$roles = $rbacreview->getParentRoleIds($this->getRefId());
		$roles = $this->getParentObject()->applyRoleFilter(
			$roles,
			$this->getFilterItemByPostVar('role')->getValue()
		);

		if(count($roles))
		{
			$column_width = 100/count($roles);
			$column_width .= '%';
		}
		else
		{
			$column_widht = "0%";
		}
		
		$all_roles = array();
		foreach($roles as $role)
		{
			if($role['obj_id'] == SYSTEM_ROLE_ID)
			{
				continue;
			}
			
			$role['role_type'] = $rbacreview->isGlobalRole($role['obj_id']) ? 'global' : 'local';
			
			// TODO check filter
			$this->addColumn(
				$this->createTitle($role),
				$role['obj_id'],
				'',
				'',
				false,
				$this->createTooltip($role)
			);
			$all_roles[] = $role;
		}

		$this->setVisibleRoles($all_roles);
		return true;
	}
	
	/**
	 * Create a linked title for roles with local policy
	 * @param object $role
	 * @return 
	 */
	protected function createTooltip($role)
	{
		global $rbacreview,$tree;
		
		#vd($role);
		$protected_status = $rbacreview->isProtected($role['parent'], $role['obj_id']) ? 'protected_' : '';
		if($role['role_type'] == 'global')
		{
			$tp = $this->lng->txt('perm_'.$protected_status.'global_role');
		}
		else
		{
			$tp = $this->lng->txt('perm_'.$protected_status.'local_role');
		}

		$inheritance_seperator = ': ';
		
		// Show create at info
		if(
			($role['assign'] == 'y' and $role['role_type'] != 'global') or
			($role['assign'] == 'n' and $role['role_type'] != 'global')
		)
		{
			$tp .= ': ';

			$obj = $rbacreview->getObjectOfRole($role['obj_id']);
			if($obj)
			{
				$tp .= sprintf(
					$this->lng->txt('perm_role_path_info_created'),
					$this->lng->txt('obj_'.ilObject::_lookupType($obj)),ilObject::_lookupTitle($obj)
				);
				$inheritance_seperator = ', ';
			}
		}

		$path_hierarchy = $rbacreview->getObjectsWithStopedInheritance(
			$role['obj_id'],
			$tree->getPathId($this->getRefId())
		);

		$reduced_path_hierarchy = (array) array_diff(
			$path_hierarchy,
			array(
				$this->getRefId(),
				$rbacreview->getObjectReferenceOfRole($role['obj_id'])
			)
		);


		// Inheritance
		if($role['assign'] == 'n' and count($reduced_path_hierarchy))
		{
			$tp .= $inheritance_seperator;

			$parent = end($reduced_path_hierarchy);
			$p_type = ilObject::_lookupType(ilObject::_lookupObjId($parent));
			$p_title = ilObject::_lookupTitle(ilObject::_lookupObjId($parent));
			$tp .= sprintf($this->lng->txt('perm_role_path_info_inheritance'),$this->lng->txt('obj_'.$p_type),$p_title);
		}
		
		return $tp;
	}
	
	/**
	 * Create (linked) title
	 * @param array $role
	 * @return 
	 */
	protected function createTitle($role)
	{
		global $ilCtrl;
		
		include_once './Services/AccessControl/classes/class.ilObjRole.php';
		$role['title'] = ilObjRole::_getTranslation($role['title']);
		
		// No local policies
		if($role['parent'] != $this->getRefId())
		{
			return $role['title'];
		} 
		$ilCtrl->setParameterByClass('ilobjrolegui', 'obj_id', $role['obj_id']);
		
		return '<a class="tblheader" href="'.$ilCtrl->getLinkTargetByClass('ilobjrolegui','').'" >'.$role['title'].'</a>';
	}
}
?>