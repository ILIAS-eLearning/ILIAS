<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Object/classes/class.ilObjectGUI.php";
include_once './Services/AccessControl/classes/class.ilObjRole.php';

/**
* Class ilObjRoleGUI
*
* @author Stefan Meyer <smeyer@ilias@gmx.de>
* @author Sascha Hofmann <saschahofmann@gmx.de>
*
* @version $Id$
*
* @ilCtrl_Calls ilObjRoleGUI: ilRepositorySearchGUI, ilExportGUI
*
* @ingroup	ServicesAccessControl
*/
class ilObjRoleGUI extends ilObjectGUI
{
	const MODE_GLOBAL_UPDATE = 1;
	const MODE_GLOBAL_CREATE = 2;
	const MODE_LOCAL_UPDATE = 3;
	const MODE_LOCAL_CREATE = 4;

	/**
	* ILIAS3 object type abbreviation
	* @var		string
	* @access	public
	*/
	var $type;

	
	protected $obj_ref_id = 0;
	protected $obj_obj_id = 0;
	protected $obj_obj_type = '';
	protected $container_type = '';


	var $ctrl;
 
	/**
	* Constructor
	* @access public
	*/
	function __construct($a_data,$a_id,$a_call_by_reference = false,$a_prepare_output = true)
	{
		global $tree,$lng;
		
		$lng->loadLanguageModule('rbac');

		//TODO: move this to class.ilias.php
		define("USER_FOLDER_ID",7);
		
		// Add ref_id of object that contains this role folder
		
		$this->obj_ref_id = 
				((int) $_REQUEST['rolf_ref_id'] ?
				(int) $_REQUEST['rolf_ref_id'] :
				(int) $_REQUEST['ref_id']
		);
		
		$this->obj_obj_id = ilObject::_lookupObjId($this->getParentRefId());
		$this->obj_obj_type = ilObject::_lookupType($this->getParentObjId());
		
		$this->container_type = ilObject::_lookupType(ilObject::_lookupObjId($this->obj_ref_id));

		$this->type = "role";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,false);
		$this->ctrl->saveParameter($this, array('obj_id', 'rolf_ref_id'));
	}


	function &executeCommand()
	{
		global $rbacsystem;

		$this->prepareOutput();
		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			case 'ilrepositorysearchgui':
				include_once('./Services/Search/classes/class.ilRepositorySearchGUI.php');
				$rep_search =& new ilRepositorySearchGUI();
				$rep_search->setTitle($this->lng->txt('role_add_user'));
				$rep_search->setCallback($this,'addUserObject');

				// Set tabs
				$this->tabs_gui->setTabActive('user_assignment');
				$this->ctrl->setReturn($this,'userassignment');
				$ret =& $this->ctrl->forwardCommand($rep_search);
				break;
			
			case 'ilexportgui':
					
				$this->tabs_gui->setTabActive('export');
				
				include_once './Services/Export/classes/class.ilExportOptions.php';
				$eo = ilExportOptions::newInstance(ilExportOptions::allocateExportId());
				$eo->addOption(ilExportOptions::KEY_ROOT,0,$this->object->getId(),$this->obj_ref_id);
				
				include_once './Services/Export/classes/class.ilExportGUI.php';
				$exp = new ilExportGUI($this, new ilObjRole($this->object->getId()));
				$exp->addFormat('xml');
				$this->ctrl->forwardCommand($exp);
				break;

			default:
				if(!$cmd)
				{
					if($this->showDefaultPermissionSettings())
					{
						$cmd = "perm";
					}
					else
					{
						$cmd = 'userassignment';
					}
				}
				$cmd .= "Object";
				$this->$cmd();
					
				break;
		}

		return true;
	}
	
	/**
	 * Get ref id of current object (not role folder id)
	 * @return 
	 */
	public function getParentRefId()
	{
		return $this->obj_ref_id;
	}
	
	/**
	 * Get obj_id of current object
	 * @return 
	 */
	public function getParentObjId()
	{
		return $this->obj_obj_id;
	}
	
	/**
	 * get type of current object (not role folder)
	 * @return 
	 */
	public function getParentType()
	{
		return $this->obj_obj_type;
	}
	
	/**
	* set back tab target
	*/
	function setBackTarget($a_text, $a_link)
	{
		$this->back_target = array("text" => $a_text,
			"link" => $a_link);
	}
	
	public function getBackTarget()
	{
		return $this->back_target ? $this->back_target : array();
	}
	
	/**
	* admin and normal tabs are equal for roles
	*/
	function getAdminTabs(&$tabs_gui)
	{
		$this->getTabs($tabs_gui);
	}
	
	/**
	 * Get type of role container
	 * @return 
	 */
	protected function getContainerType()
	{
		return $this->container_type;
	}
	
	/**
	 * check if default permissions are shown or not
	 * @return 
	 */
	protected function showDefaultPermissionSettings()
	{
		global $objDefinition;
		
		return $objDefinition->isContainer($this->getContainerType());
	}


	function listDesktopItemsObject()
	{
		global $rbacsystem,$rbacreview;

		if(!$rbacreview->isAssignable($this->object->getId(),$this->obj_ref_id) &&
			$this->obj_ref_id != ROLE_FOLDER_ID)
		{
			ilUtil::sendInfo($this->lng->txt('role_no_users_no_desk_items'));
			return true;
		}

		if($rbacsystem->checkAccess('push_desktop_items',USER_FOLDER_ID))
		{
			$this->__showButton('selectDesktopItem',$this->lng->txt('role_desk_add'));
		}
		
		include_once 'Services/AccessControl/classes/class.ilRoleDesktopItemsTableGUI.php';
		$tbl = new ilRoleDesktopItemsTableGUI($this, 'listDesktopItems', $this->object);
		$this->tpl->setContent($tbl->getHTML());
		
		return true;
	}

	function askDeleteDesktopItemObject()
	{
		global $rbacsystem;
		
		
		if(!$this->checkAccess('edit_permission'))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		if(!$rbacsystem->checkAccess('push_desktop_items',USER_FOLDER_ID))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		if(!count($_POST['del_desk_item']))
		{
			ilUtil::sendFailure($this->lng->txt('role_select_one_item'));

			$this->listDesktopItemsObject();

			return true;
		}		
		
		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$confirmation_gui = new ilConfirmationGUI();
		$confirmation_gui->setFormAction($this->ctrl->getFormAction($this));
		$confirmation_gui->setHeaderText($this->lng->txt('role_assigned_desk_items').
			' "'.$this->object->getTitle().'": '.
			$this->lng->txt('role_sure_delete_desk_items'));
		$confirmation_gui->setCancel($this->lng->txt("cancel"), "listDesktopItems");
		$confirmation_gui->setConfirm($this->lng->txt("delete"), "deleteDesktopItems");
		
		include_once 'Services/AccessControl/classes/class.ilRoleDesktopItem.php';
		$role_desk_item_obj = new ilRoleDesktopItem($this->object->getId());
		$counter = 0;
		foreach($_POST['del_desk_item'] as $role_item_id)
		{
			$item_data = $role_desk_item_obj->getItem($role_item_id);
			$tmp_obj =& ilObjectFactory::getInstanceByRefId($item_data['item_id']);
									
			if(strlen($desc = $tmp_obj->getDescription()))
			{				
				$desc = '<div class="il_Description_no_margin">'.$desc.'</div>';				
			}
			
			$confirmation_gui->addItem("del_desk_item[]", $role_item_id, $tmp_obj->getTitle().$desc);
		}
		
		$this->tpl->setContent($confirmation_gui->getHTML());

		return true;
	}

	function deleteDesktopItemsObject()
	{
		global $rbacsystem;
		
		if(!$this->checkAccess('edit_permission'))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		if (!$rbacsystem->checkAccess('push_desktop_items',USER_FOLDER_ID))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		if (!count($_POST['del_desk_item']))
		{
			ilUtil::sendFailure($this->lng->txt('role_select_one_item'));

			$this->listDesktopItemsObject();

			return true;
		}

		include_once 'Services/AccessControl/classes/class.ilRoleDesktopItem.php';

		$role_desk_item_obj =& new ilRoleDesktopItem($this->object->getId());

		foreach ($_POST['del_desk_item'] as $role_item_id)
		{
			$role_desk_item_obj->delete($role_item_id);
		}

		ilUtil::sendSuccess($this->lng->txt('role_deleted_desktop_items'));
		$this->listDesktopItemsObject();

		return true;
	}


	function selectDesktopItemObject()
	{
		global $rbacsystem,$tree;

		include_once 'Services/AccessControl/classes/class.ilRoleDesktopItemSelector.php';
		include_once 'Services/AccessControl/classes/class.ilRoleDesktopItem.php';

		if(!$rbacsystem->checkAccess('push_desktop_items',USER_FOLDER_ID))
		{
			#$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
			ilUtil::sendFailure($this->lng->txt('permission_denied'));
			$this->listDesktopItemsObject();
			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.role_desktop_item_selector.html", "Services/AccessControl");
		$this->__showButton('listDesktopItems',$this->lng->txt('back'));

		ilUtil::sendInfo($this->lng->txt("role_select_desktop_item"));
		
		$exp = new ilRoleDesktopItemSelector($this->ctrl->getLinkTarget($this,'selectDesktopItem'),
											 new ilRoleDesktopItem($this->object->getId()));
		$exp->setExpand($_GET["role_desk_item_link_expand"] ? $_GET["role_desk_item_link_expand"] : $tree->readRootId());
		$exp->setExpandTarget($this->ctrl->getLinkTarget($this,'selectDesktopItem'));
		
		$exp->setOutput(0);
		
		$output = $exp->getOutput();
		$this->tpl->setVariable("EXPLORER",$output);
		//$this->tpl->setVariable("EXPLORER", $exp->getOutput());

		return true;
	}

	function assignDesktopItemObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess('push_desktop_items',USER_FOLDER_ID))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
			return false;
		}
	

		if (!isset($_GET['item_id']))
		{
			ilUtil::sendFailure($this->lng->txt('role_no_item_selected'));
			$this->selectDesktopItemObject();

			return false;
		}

		include_once 'Services/AccessControl/classes/class.ilRoleDesktopItem.php';

		$role_desk_item_obj =& new ilRoleDesktopItem($this->object->getId());
		$role_desk_item_obj->add((int) $_GET['item_id'],ilObject::_lookupType((int) $_GET['item_id'],true));

		ilUtil::sendSuccess($this->lng->txt('role_assigned_desktop_item'));

		$this->ctrl->redirect($this,'listDesktopItems');
		return true;
	}
	
	/**
	 * Create role prperty form
	 * @return 
	 * @param int $a_mode
	 */
	protected function initFormRoleProperties($a_mode)
	{
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		$this->form = new ilPropertyFormGUI();

		if($this->creation_mode)
		{
			$this->ctrl->setParameter($this, "new_type", 'role');
		}
		$this->form->setFormAction($this->ctrl->getFormAction($this));
	
		switch($a_mode)
		{
			case self::MODE_GLOBAL_CREATE:
				$this->form->setTitle($this->lng->txt('role_new'));
				$this->form->addCommandButton('save',$this->lng->txt('role_new'));
				break;
				
			case self::MODE_GLOBAL_UPDATE:
				$this->form->setTitle($this->lng->txt('role_edit'));
				$this->form->addCommandButton('update', $this->lng->txt('save'));
				break;
				
			case self::MODE_LOCAL_CREATE:
			case self::MODE_LOCAL_UPDATE:
		}
		// Fix cancel
		$this->form->addCommandButton('cancel', $this->lng->txt('cancel'));
		
		$title = new ilTextInputGUI($this->lng->txt('title'),'title');
		if(ilObjRole::isAutoGenerated($this->object->getId()))
		{
			$title->setDisabled(true);
		}
		else
		{
			//#17111 No validation for disabled fields
			$title->setValidationRegexp('/^(?!il_).*$/');
			$title->setValidationFailureMessage($this->lng->txt('msg_role_reserved_prefix'));
		}

		$title->setSize(40);
		$title->setMaxLength(70);
		$title->setRequired(true);
		$this->form->addItem($title);
		
		$desc = new ilTextAreaInputGUI($this->lng->txt('description'),'desc');
		if(ilObjRole::isAutoGenerated($this->object->getId()))
		{
			$desc->setDisabled(true);
		}
		$desc->setCols(40);
		$desc->setRows(3);
		$this->form->addItem($desc);

		if($a_mode != self::MODE_LOCAL_CREATE && $a_mode != self::MODE_GLOBAL_CREATE)
		{
			$ilias_id = new ilNonEditableValueGUI($this->lng->txt("ilias_id"), "ilias_id");
			$this->form->addItem($ilias_id);
		}
		
		if($this->obj_ref_id == ROLE_FOLDER_ID)
		{
			$reg = new ilCheckboxInputGUI($this->lng->txt('allow_register'),'reg');
			$reg->setValue(1);
			#$reg->setInfo($this->lng->txt('rbac_new_acc_reg_info'));
			$this->form->addItem($reg);
			
			$la = new ilCheckboxInputGUI($this->lng->txt('allow_assign_users'),'la');
			$la->setValue(1);
			#$la->setInfo($this->lng->txt('rbac_local_admin_info'));
			$this->form->addItem($la);
		}
		
		$pro = new ilCheckboxInputGUI($this->lng->txt('role_protect_permissions'),'pro');
		$pro->setValue(1);
		#$pro->setInfo($this->lng->txt('role_protext_permission_info'));
		$this->form->addItem($pro);
		
		include_once 'Services/WebDAV/classes/class.ilDiskQuotaActivationChecker.php';
		if(ilDiskQuotaActivationChecker::_isActive())
		{
			$quo = new ilNumberInputGUI($this->lng->txt('disk_quota'),'disk_quota');
			$quo->setMinValue(0);
			$quo->setSize(4);
			$quo->setInfo($this->lng->txt('enter_in_mb_desc').'<br />'.$this->lng->txt('disk_quota_on_role_desc'));
			$this->form->addItem($quo);
		}
		if(ilDiskQuotaActivationChecker::_isPersonalWorkspaceActive())
		{
			$this->lng->loadLanguageModule("file");
			$wquo = new ilNumberInputGUI($this->lng->txt('personal_workspace_disk_quota'),'wsp_disk_quota');
			$wquo->setMinValue(0);
			$wquo->setSize(4);
			$wquo->setInfo($this->lng->txt('enter_in_mb_desc').'<br />'.$this->lng->txt('disk_quota_on_role_desc'));
			$this->form->addItem($wquo);
		}
			
		return true;
	}
	
	/**
	 * Store form input in role object
	 * @return 
	 * @param object $role
	 */
	protected function loadRoleProperties(ilObjRole $role)
	{
		//Don't set if fields are disabled to prevent html manipulation.
		if(!$this->form->getItemByPostVar('title')->getDisabled())
		{
			$role->setTitle($this->form->getInput('title'));

		}
		if(!$this->form->getItemByPostVar('desc')->getDisabled())
		{
			$role->setDescription($this->form->getInput('desc'));
		}
		$role->setAllowRegister($this->form->getInput('reg'));
		$role->toggleAssignUsersStatus($this->form->getInput('la'));
		$role->setDiskQuota($this->form->getInput('disk_quota') * pow(ilFormat::_getSizeMagnitude(),2));
		$role->setPersonalWorkspaceDiskQuota($this->form->getInput('wsp_disk_quota') * pow(ilFormat::_getSizeMagnitude(),2));
		return true;
	}
	
	/**
	 * Read role properties and write them to form
	 * @return 
	 * @param object $role
	 */
	protected function readRoleProperties(ilObjRole $role)
	{
		global $rbacreview;
		
		include_once 'Services/WebDAV/classes/class.ilDiskQuotaActivationChecker.php';

		$data['title'] = $role->getTitle();
		$data['desc'] = $role->getDescription();
		$data['ilias_id'] = 'il_'.IL_INST_ID.'_'.ilObject::_lookupType($role->getId()).'_'.$role->getId();
		$data['reg'] = $role->getAllowRegister();
		$data['la'] = $role->getAssignUsersStatus();
		if(ilDiskQuotaActivationChecker::_isActive())
		{
			$data['disk_quota'] = $role->getDiskQuota() / (pow(ilFormat::_getSizeMagnitude(),2));
		}
		if(ilDiskQuotaActivationChecker::_isPersonalWorkspaceActive())
		{
			$data['wsp_disk_quota'] = $role->getPersonalWorkspaceDiskQuota() / (pow(ilFormat::_getSizeMagnitude(),2));		
		}
		$data['pro'] = $rbacreview->isProtected($this->obj_ref_id, $role->getId());
		
		$this->form->setValuesByArray($data);
	}
	



	/**
	 * Only called from administration -> role folder ?
	 * Otherwise this check access is wrong
	 * @return 
	 */
	public function createObject()
	{
		global $rbacsystem;
		
		if(!$rbacsystem->checkAccess('create_role',$this->obj_ref_id))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'),$ilErr->MESSAGE);
		}
		
		$this->initFormRoleProperties(self::MODE_GLOBAL_CREATE);
		$this->tpl->setContent($this->form->getHTML());
	}
	
	/**
	 * Edit role properties
	 * @return 
	 */
	public function editObject()
	{
		global $rbacsystem, $rbacreview, $ilSetting,$ilErr;

		if(!$this->checkAccess('write','edit_permission'))
		{
			$ilErr->raiseError($this->lng->txt("msg_no_perm_write"),$ilErr->MESSAGE);
		}
		$this->initFormRoleProperties(self::MODE_GLOBAL_UPDATE);
		$this->readRoleProperties($this->object);
		$this->tpl->setContent($this->form->getHTML());
	}
	
	
	/**
	 * Save new role
	 * @return 
	 */
	public function saveObject()
	{
		global $rbacadmin,$rbacreview;
		
		$this->initFormRoleProperties(self::MODE_GLOBAL_CREATE);
		if($this->form->checkInput() and !$this->checkDuplicate())
		{
			include_once './Services/AccessControl/classes/class.ilObjRole.php';
			$this->loadRoleProperties($this->role = new ilObjRole());
			$this->role->create();
			$rbacadmin->assignRoleToFolder($this->role->getId(), $this->obj_ref_id,'y');
			$rbacadmin->setProtected(
				$this->obj_ref_id,
				$this->role->getId(),
				$this->form->getInput('pro') ? 'y' : 'n'
			);
			ilUtil::sendSuccess($this->lng->txt("role_added"),true);
			$this->ctrl->setParameter($this,'obj_id',$this->role->getId());
			$this->ctrl->redirect($this,'perm');
		}
		
		ilUtil::sendFailure($this->lng->txt('err_check_input'));
		$this->form->setValuesByPost();
		$this->tpl->setContent($this->form->getHTML());
		return false;
	}
	
	/**
	 * Check if role with same name already exists in this folder
	 * @return bool 
	 */
	protected function checkDuplicate($a_role_id = 0)
	{
		// disabled due to mantis #0013742: Renaming global roles: ILIAS denies if title fits other role title partially
		return FALSE;
	}
	
	/**
	 * Save role settings
	 * @return 
	 */
	public function updateObject()
	{
		global $rbacadmin;
		
		$this->initFormRoleProperties(self::MODE_GLOBAL_UPDATE);
		if($this->form->checkInput() and !$this->checkDuplicate($this->object->getId()))
		{
			include_once './Services/AccessControl/classes/class.ilObjRole.php';
			$this->loadRoleProperties($this->object);
			$this->object->update();
			$rbacadmin->setProtected(
				$this->obj_ref_id,
				$this->object->getId(),
				$this->form->getInput('pro') ? 'y' : 'n'
			);
			ilUtil::sendSuccess($this->lng->txt("saved_successfully"),true);
			$this->ctrl->redirect($this,'edit');
		}
		
		ilUtil::sendFailure($this->lng->txt('err_check_input'));
		$this->form->setValuesByPost();
		$this->tpl->setContent($this->form->getHTML());
		return false;
	}
	
	/**
	 * Show template permissions
	 * @return void
	 */
	protected function permObject($a_show_admin_permissions = false)
	{
		global $ilTabs, $ilErr, $ilToolbar, $objDefinition,$rbacreview;
		
		$ilTabs->setTabActive('default_perm_settings');
		
		$this->setSubTabs('default_perm_settings');
		
		if($a_show_admin_permissions)
		{
			$ilTabs->setSubTabActive('rbac_admin_permissions');
		}
		else
		{
			$ilTabs->setSubTabActive('rbac_repository_permissions');	
		}
		
		if(!$this->checkAccess('write','edit_permission'))
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_perm'),$ilErr->MESSAGE);
			return true;
		}
		
		// Show copy role button
		if($this->object->getId() != SYSTEM_ROLE_ID)
		{
			$ilToolbar->setFormAction($this->ctrl->getFormAction($this));
			$ilToolbar->addButton(
				$this->lng->txt("adopt_perm_from_template"),
				$this->ctrl->getLinkTarget($this,'adoptPerm')
			);
			if($rbacreview->isDeleteable($this->object->getId(), $this->obj_ref_id))
			{
				$ilToolbar->addButton(
					$this->lng->txt('rbac_delete_role'),
					$this->ctrl->getLinkTarget($this,'confirmDeleteRole')
				);
			}
		}
		
		$this->tpl->addBlockFile(
			'ADM_CONTENT',
			'adm_content',
			'tpl.rbac_template_permissions.html',
			'Services/AccessControl'
		);
		
		$this->tpl->setVariable('PERM_ACTION',$this->ctrl->getFormAction($this));
		
		include_once './Services/Accordion/classes/class.ilAccordionGUI.php';
		$acc = new ilAccordionGUI();
		$acc->setBehaviour(ilAccordionGUI::FORCE_ALL_OPEN);
		$acc->setId('template_perm_'.$this->getParentRefId());
		
		if($this->obj_ref_id == ROLE_FOLDER_ID)
		{
			if($a_show_admin_permissions)
			{
				$subs = $objDefinition->getSubObjectsRecursively('adm',true,true);
			}
			else
			{
				$subs = $objDefinition->getSubObjectsRecursively('root',true,$a_show_admin_permissions);
			}
		}
		else
		{
			$subs = $objDefinition->getSubObjectsRecursively($this->getParentType(),true,$a_show_admin_permissions);
		}
		
		$sorted = array();
		foreach($subs as $subtype => $def)
		{
			if($objDefinition->isPlugin($subtype))
			{
				$translation = ilPlugin::lookupTxt("rep_robj", $subtype,"obj_".$subtype);
			}
			elseif($objDefinition->isSystemObject($subtype))
			{
				$translation = $this->lng->txt("obj_".$subtype);
			}
			else
			{
				$translation = $this->lng->txt('objs_'.$subtype);
			}
			
			$sorted[$subtype] = $def;
			$sorted[$subtype]['translation'] = $translation;
		}
		
		
		$sorted = ilUtil::sortArray($sorted, 'translation','asc',true,true);
		foreach($sorted as $subtype => $def)
		{
			if($objDefinition->isPlugin($subtype))
			{
				$translation = ilPlugin::lookupTxt("rep_robj", $subtype,"obj_".$subtype);
			}
			elseif($objDefinition->isSystemObject($subtype))
			{
				$translation = $this->lng->txt("obj_".$subtype);
			}
			else
			{
				$translation = $this->lng->txt('objs_'.$subtype);
			}

			include_once 'Services/AccessControl/classes/class.ilObjectRoleTemplatePermissionTableGUI.php';
			$tbl = new ilObjectRoleTemplatePermissionTableGUI(
				$this,
				'perm',
				$this->getParentRefId(),
				$this->object->getId(),
				$subtype,
				$a_show_admin_permissions
			);
			$tbl->parse();
			
			$acc->addItem($translation, $tbl->getHTML());
		}

		$this->tpl->setVariable('ACCORDION',$acc->getHTML());
		
		// Add options table
		include_once './Services/AccessControl/classes/class.ilObjectRoleTemplateOptionsTableGUI.php';
		$options = new ilObjectRoleTemplateOptionsTableGUI(
			$this,
			'perm',
			$this->obj_ref_id,
			$this->object->getId(),
			$a_show_admin_permissions
		);
		if($this->object->getId() != SYSTEM_ROLE_ID)
		{
			$options->addMultiCommand(
				$a_show_admin_permissions ? 'adminPermSave' : 'permSave',
				$this->lng->txt('save')
			);
		}

		$options->parse();
		$this->tpl->setVariable('OPTIONS_TABLE',$options->getHTML());
	}
	
	/**
	 * Show administration permissions
	 * @return 
	 */
	protected function adminPermObject()
	{
		return $this->permObject(true);
	}
	
	/**
	 * Save admin permissions
	 * @return 
	 */
	protected function adminPermSaveObject()
	{
		return $this->permSaveObject(true);
	}

	protected function adoptPermObject()
	{
		global $rbacreview;

		$output = array();
		
		$parent_role_ids = $rbacreview->getParentRoleIds($this->obj_ref_id,true);
		$ids = array();
		foreach($parent_role_ids as $id => $tmp)
		{
			$ids[] = $id;
		}
		// Sort ids
		$sorted_ids = ilUtil::_sortIds($ids,'object_data','type,title','obj_id');
		$key = 0;
		foreach($sorted_ids as $id)
		{
			$par = $parent_role_ids[$id];
			if ($par["obj_id"] != SYSTEM_ROLE_ID && $this->object->getId() != $par["obj_id"])
			{
				$output[$key]["role_id"] = $par["obj_id"];
				$output[$key]["type"] = ($par["type"] == 'role' ? $this->lng->txt('obj_role') : $this->lng->txt('obj_rolt'));
				$output[$key]["role_name"] = ilObjRole::_getTranslation($par["title"]);
				$output[$key]["role_desc"] = $par["desc"];
				$key++;
			}
		}


		include_once('./Services/AccessControl/classes/class.ilRoleAdoptPermissionTableGUI.php');

		$tbl = new ilRoleAdoptPermissionTableGUI($this, "adoptPerm");
		$tbl->setTitle($this->lng->txt("adopt_perm_from_template"));
		$tbl->setData($output);

		$this->tpl->setContent($tbl->getHTML());
	}
	
	/**
	 * Show delete confirmation screen
	 * @return 
	 */
	protected function confirmDeleteRoleObject()
	{
		global $ilErr,$rbacreview,$ilUser;
		
		$access = $this->checkAccess('visible,write','edit_permission');
		if (!$access)
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_perm'),$ilErr->WARNING);
		}

		$question = $this->lng->txt('rbac_role_delete_qst');
		if($rbacreview->isAssigned($ilUser->getId(), $this->object->getId()))
		{
			$question .= ('<br />'.$this->lng->txt('rbac_role_delete_self'));
		}
		ilUtil::sendQuestion($question);
		
		include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
		
		$confirm = new ilConfirmationGUI();
		$confirm->setFormAction($this->ctrl->getFormAction($this));
		$confirm->setHeaderText($question);
		$confirm->setCancel($this->lng->txt('cancel'), 'perm');
		$confirm->setConfirm($this->lng->txt('rbac_delete_role'), 'performDeleteRole');
		
		$confirm->addItem(
			'role',
			$this->object->getId(),
			$this->object->getTitle(),
			ilUtil::getImagePath('icon_role.svg')
		);
		
		$this->tpl->setContent($confirm->getHTML());
		return true;				
	}

	
	/**
	 * Delete role
	 * @return 
	 */
	protected function performDeleteRoleObject()
	{
		global $ilErr;

		$access = $this->checkAccess('visible,write','edit_permission');
		if (!$access)
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_perm'),$ilErr->WARNING);
		}
		
		$this->object->setParent((int) $this->obj_ref_id);
		$this->object->delete();
		ilUtil::sendSuccess($this->lng->txt('msg_deleted_role'),true);
		
		$this->ctrl->returnToParent($this);
	}

	/**
	* save permissions
	* 
	* @access	public
	*/
	function permSaveObject($a_show_admin_permissions = false)
	{
		global $rbacsystem, $rbacadmin, $rbacreview, $objDefinition, $tree;

		// for role administration check write of global role folder
		$access = $this->checkAccess('visible,write','edit_permission');
			
		if (!$access)
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_perm"),$this->ilias->error_obj->MESSAGE);
		}

		// rbac log
		include_once "Services/AccessControl/classes/class.ilRbacLog.php";
		$rbac_log_active = ilRbacLog::isActive();
		if($rbac_log_active)
		{
			$rbac_log_old = ilRbacLog::gatherTemplate($this->obj_ref_id, $this->object->getId());
		}

		// delete all template entries of enabled types
		if($this->obj_ref_id == ROLE_FOLDER_ID)
		{
			if($a_show_admin_permissions)
			{
				$subs = $objDefinition->getSubObjectsRecursively('adm',true,true);
			}
			else
			{
				$subs = $objDefinition->getSubObjectsRecursively('root',true,false);
			}
		}
		else
		{
			$subs = $objDefinition->getSubObjectsRecursively($this->getParentType(),true,false);
		}
		
		foreach($subs as $subtype => $def)
		{
			// Delete per object type
			$rbacadmin->deleteRolePermission($this->object->getId(),$this->obj_ref_id,$subtype);
		}

		if (empty($_POST["template_perm"]))
		{
			$_POST["template_perm"] = array();
		}

		foreach ($_POST["template_perm"] as $key => $ops_array)
		{
			// sets new template permissions
			$rbacadmin->setRolePermission($this->object->getId(), $key, $ops_array, $this->obj_ref_id);
		}

		if($rbac_log_active)
		{
			$rbac_log_new = ilRbacLog::gatherTemplate($this->obj_ref_id, $this->object->getId());
			$rbac_log_diff = ilRbacLog::diffTemplate($rbac_log_old, $rbac_log_new);
			ilRbacLog::add(ilRbacLog::EDIT_TEMPLATE, $this->obj_ref_id, $rbac_log_diff);
		}

		// update object data entry (to update last modification date)
		$this->object->update();
		
		// set protected flag
		if ($this->obj_ref_id == ROLE_FOLDER_ID or $rbacreview->isAssignable($this->object->getId(),$this->obj_ref_id))
		{
			$rbacadmin->setProtected($this->obj_ref_id,$this->object->getId(),ilUtil::tf2yn($_POST['protected']));
		}
		
		if($a_show_admin_permissions)
		{
			$_POST['recursive'] = true;
		}
		
		// Redirect if Change existing objects is not chosen
		if(!$_POST['recursive'] and !is_array($_POST['recursive_list']))
		{
			ilUtil::sendSuccess($this->lng->txt("saved_successfully"),true);
			if($a_show_admin_permissions)
			{
				$this->ctrl->redirect($this,'adminPerm');
			}
			else
			{
				$this->ctrl->redirect($this,'perm');
			}
		}
		// New implementation
		if($this->isChangeExistingObjectsConfirmationRequired() and !$a_show_admin_permissions)
		{
			$this->showChangeExistingObjectsConfirmation();
			return true;
		}
		
		$start = ($this->obj_ref_id == ROLE_FOLDER_ID ? ROOT_FOLDER_ID : $this->obj_ref_id);
		if($a_show_admin_permissions)
		{
			$start = $tree->getParentId($this->obj_ref_id);
		}

		if($_POST['protected'])
		{
			$this->object->changeExistingObjects(
				$start,
				ilObjRole::MODE_PROTECTED_KEEP_LOCAL_POLICIES,
				array('all'),
				array()
				#$a_show_admin_permissions ? array('adm') : array()
			);
		}
		else
		{
			$this->object->changeExistingObjects(
				$start,
				ilObjRole::MODE_UNPROTECTED_KEEP_LOCAL_POLICIES,
				array('all'),
				array()
				#$a_show_admin_permissions ? array('adm') : array()
			);
		}
		ilUtil::sendSuccess($this->lng->txt("saved_successfully"),true);
		
		if($a_show_admin_permissions)
		{
			$this->ctrl->redirect($this,'adminPerm');
		}
		else
		{
			$this->ctrl->redirect($this,'perm');
		}
		return true;
	}


	/**
	* copy permissions from role
	* 
	* @access	public
	*/
	function adoptPermSaveObject()
	{
		global $rbacadmin, $rbacsystem, $rbacreview, $tree;

		if(!$_POST['adopt'])
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			$this->adoptPermObject();
			return false;
		}
	
		$access = $this->checkAccess('visible,write','edit_permission');
		if (!$access)
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_perm"),$this->ilias->error_obj->MESSAGE);
		}

		if ($this->object->getId() == $_POST["adopt"])
		{
			ilUtil::sendFailure($this->lng->txt("msg_perm_adopted_from_itself"),true);
		}
		else
		{
			$rbacadmin->deleteRolePermission($this->object->getId(), $this->obj_ref_id);
			$parentRoles = $rbacreview->getParentRoleIds($this->obj_ref_id,true);
			$rbacadmin->copyRoleTemplatePermissions(
				$_POST["adopt"],
				$parentRoles[$_POST["adopt"]]["parent"],
				$this->obj_ref_id,
				$this->object->getId(),
				false);		

			// update object data entry (to update last modification date)
			$this->object->update();

			// send info
			$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($_POST["adopt"]);
			ilUtil::sendSuccess($this->lng->txt("msg_perm_adopted_from1")." '".$obj_data->getTitle()."'.<br/>".
					 $this->lng->txt("msg_perm_adopted_from2"),true);
		}

		$this->ctrl->redirect($this, "perm");
	}

	/**
	* wrapper for renamed function
	*
	* @access	public
	*/
	function assignSaveObject()
	{
        $this->assignUserObject();
    }


	
	/**
	 * Assign user (callback from ilRepositorySearchGUI) 
	 * @param	array	$a_user_ids		Array of user ids
	 * @return
	 */
	public function addUserObject($a_user_ids)
	{
		global $rbacreview,$rbacadmin;
		
		if(!$this->checkAccess('edit_userassignment','edit_permission'))
		{
			ilUtil::sendFailure($this->lng->txt('msg_no_perm_assign_user_to_role'),true);
			return false;
		}
		if(!$rbacreview->isAssignable($this->object->getId(),$this->obj_ref_id) &&
			$this->obj_ref_id != ROLE_FOLDER_ID)
		{
			ilUtil::sendFailure($this->lng->txt('err_role_not_assignable'),true);
			return false;
		}
		if(!$a_user_ids)
		{
			$GLOBALS['lng']->loadLanguageModule('search');
			ilUtil::sendFailure($this->lng->txt('search_err_user_not_exist'),true);
			return false;
		}

		$assigned_users_all = $rbacreview->assignedUsers($this->object->getId());
				
		// users to assign
		$assigned_users_new = array_diff($a_user_ids,array_intersect($a_user_ids,$assigned_users_all));
		
		// selected users all already assigned. stop
        if (count($assigned_users_new) == 0)
		{
			ilUtil::sendInfo($this->lng->txt("rbac_msg_user_already_assigned"),true);
			$this->ctrl->redirect($this,'userassignment');
		}
		
		// assign new users
        foreach ($assigned_users_new as $user)
		{
			$rbacadmin->assignUser($this->object->getId(),$user,false);
        }
        
    	// update object data entry (to update last modification date)
		$this->object->update();

		ilUtil::sendSuccess($this->lng->txt("msg_userassignment_changed"),true);
		$this->ctrl->redirect($this,'userassignment');
	}
	
	/**
	* de-assign users from role
	*
	* @access	public
	*/
	function deassignUserObject()
	{
    	global $rbacsystem, $rbacadmin, $rbacreview;

		if(!$this->checkAccess('edit_userassignment','edit_permission'))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_assign_user_to_role"),$this->ilias->error_obj->MESSAGE);
		}

    	$selected_users = ($_POST["user_id"]) ? $_POST["user_id"] : array($_GET["user_id"]);

		if ($selected_users[0]=== NULL)
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		// prevent unassignment of system user from system role
		if ($this->object->getId() == SYSTEM_ROLE_ID)
		{
            if ($admin = array_search(SYSTEM_USER_ID,$selected_users) !== false)
			    unset($selected_users[$admin]);
		}

		// check for each user if the current role is his last global role before deassigning him
		$last_role = array();
		$global_roles = $rbacreview->getGlobalRoles();
		
		foreach ($selected_users as $user)
		{
			$assigned_roles = $rbacreview->assignedRoles($user);
			$assigned_global_roles = array_intersect($assigned_roles,$global_roles);

			if (count($assigned_roles) == 1 or (count($assigned_global_roles) == 1 and in_array($this->object->getId(),$assigned_global_roles)))
			{
				$userObj = $this->ilias->obj_factory->getInstanceByObjId($user);
				$last_role[$user] = $userObj->getFullName();
				unset($userObj);
			}
		}

		
		// ... else perform deassignment
		foreach ($selected_users as $user)
        {
			if(!isset($last_role[$user]))
			{
				$rbacadmin->deassignUser($this->object->getId(), $user);
			}
		}

    	// update object data entry (to update last modification date)
		$this->object->update();

		// raise error if last role was taken from a user...
		if(count($last_role))
		{
			$user_list = implode(", ",$last_role);
			ilUtil::sendFailure($this->lng->txt('msg_is_last_role').': '.$user_list.'<br />'.$this->lng->txt('msg_min_one_role'),true);
		}
		else
		{
			ilUtil::sendSuccess($this->lng->txt("msg_userassignment_changed"), true);
		}
		$this->ctrl->redirect($this,'userassignment');
	}
	
	
	/**
	* display user assignment panel
	*/
	function userassignmentObject()
	{
		global $rbacreview, $rbacsystem, $lng, $ilUser;
		
		if(!$this->checkAccess('edit_userassignment','edit_permission'))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_assign_user_to_role"),$this->ilias->error_obj->MESSAGE);
		}
		
		$this->tabs_gui->setTabActive('user_assignment');
		
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.rbac_ua.html','Services/AccessControl');
		
		include_once './Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
		$tb = new ilToolbarGUI();

		// protected admin role
		include_once './Services/PrivacySecurity/classes/class.ilSecuritySettings.php';
		if(
			$this->object->getId() != SYSTEM_ROLE_ID ||
				(
					!$rbacreview->isAssigned($ilUser->getId(),SYSTEM_ROLE_ID) or
					!ilSecuritySettings::_getInstance()->isAdminRoleProtected()
				)
		)
		{


			// add member
			include_once './Services/Search/classes/class.ilRepositorySearchGUI.php';
			ilRepositorySearchGUI::fillAutoCompleteToolbar(
				$this,
				$tb,
				array(
					'auto_complete_name'	=> $lng->txt('user'),
					'submit_name'			=> $lng->txt('add')
				)
			);

	/*		
			// add button
			$tb->addFormButton($lng->txt("add"), "assignUser");
	*/
			$tb->addSpacer();

			$tb->addButton(
				$this->lng->txt('search_user'),
				$this->ctrl->getLinkTargetByClass('ilRepositorySearchGUI','start')
			);
			$tb->addSpacer();
		}
		
		$tb->addButton(
			$this->lng->txt('role_mailto'),
			$this->ctrl->getLinkTarget($this,'mailToRole')
		);
		$this->tpl->setVariable('BUTTONS_UA',$tb->getHTML());
		
		
		include_once './Services/PrivacySecurity/classes/class.ilSecuritySettings.php';
		$role_assignment_editable = true;
		if(
				$this->object->getId() == SYSTEM_ROLE_ID &&
				!ilSecuritySettings::_getInstance()->checkAdminRoleAccessible($ilUser->getId()))
		{
			$role_assignment_editable = false;
		}

		include_once './Services/AccessControl/classes/class.ilAssignedUsersTableGUI.php';
		$ut = new ilAssignedUsersTableGUI($this,'userassignment',$this->object->getId(),$role_assignment_editable);
		
		$this->tpl->setVariable('TABLE_UA',$ut->getHTML());
		
		return true;
		
    }


	/**
	* cancelObject is called when an operation is canceled, method links back
	* @access	public
	*/
	function cancelObject()
	{
		if ($_GET["new_type"] != "role")
		{
			$this->ctrl->redirect($this, "userassignment");
		}
		else
		{
			$this->ctrl->redirectByClass("ilobjrolefoldergui","view");
		}
	}


	function listUsersRoleObject()
	{
		global $rbacsystem,$rbacreview;

		$_SESSION["role_role"] = $_POST["role"] = $_POST["role"] ? $_POST["role"] : $_SESSION["role_role"];

		if (!is_array($_POST["role"]))
		{
			ilUtil::sendFailure($this->lng->txt("role_no_roles_selected"));
			$this->searchObject();

			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.role_usr_selection.html", "Services/AccessControl");
		$this->__showButton("searchUserForm",$this->lng->txt("role_new_search"));

		// GET ALL MEMBERS
		$members = array();

		foreach ($_POST["role"] as $role_id)
		{
			$members = array_merge($rbacreview->assignedUsers($role_id),$members);
		}

		$members = array_unique($members);

		// FORMAT USER DATA
		$counter = 0;
		$f_result = array();

		foreach($members as $user)
		{
			if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user,false))
			{
				continue;
			}
			
			$user_ids[$counter] = $user;
			
			// TODO: exclude anonymous user
			$f_result[$counter][] = ilUtil::formCheckbox(0,"user[]",$user);
			$f_result[$counter][] = $tmp_obj->getLogin();
			$f_result[$counter][] = $tmp_obj->getFirstname();
			$f_result[$counter][] = $tmp_obj->getLastname();

			unset($tmp_obj);
			++$counter;
		}

		$this->__showSearchUserTable($f_result,$user_ids,"listUsersRole");

		return true;
	}

	function __prepareOutput()
	{
		// output objects
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

		// output locator
		//$this->__setLocator();

		// output message
		if ($this->message)
		{
			ilUtil::sendInfo($this->message);
		}

		// display infopanel if something happened
		ilUtil::infoPanel();

		// set header
		$this->__setHeader();
	}

	function __setHeader()
	{
		$this->tpl->setTitle($this->lng->txt('role'));
		$this->tpl->setDescription($this->object->getTitle());
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_role.svg"));

		$this->getTabs($this->tabs_gui);
	}

	function __setLocator()
	{
		global $tree, $ilCtrl;
		
		return;
		
		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html", "Services/Locator");

		$counter = 0;

		foreach ($tree->getPathFull($this->obj_ref_id) as $key => $row)
		{
			if ($counter++)
			{
				$this->tpl->touchBlock('locator_separator_prefix');
			}

			$this->tpl->setCurrentBlock("locator_item");

			if ($row["type"] == 'rolf')
			{
				$this->tpl->setVariable("ITEM",$this->object->getTitle());
				$this->tpl->setVariable("LINK_ITEM",$this->ctrl->getLinkTarget($this));
			}
			elseif ($row["child"] != $tree->getRootId())
			{
				$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $row["child"]);
				$this->tpl->setVariable("ITEM", $row["title"]);
				$this->tpl->setVariable("LINK_ITEM",
					$ilCtrl->getLinkTargetByClass("ilrepositorygui", ""));
			}
			else
			{
				$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $row["child"]);
				$this->tpl->setVariable("ITEM", $this->lng->txt("repository"));
				$this->tpl->setVariable("LINK_ITEM",
					$ilCtrl->getLinkTargetByClass("ilrepositorygui", ""));
			}
			$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);

			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("TXT_LOCATOR",$this->lng->txt("locator"));
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* should be overwritten to add object specific items
	* (repository items are preloaded)
	*/
	function addAdminLocatorItems()
	{
		global $ilLocator;

		if ($_GET["admin_mode"] == "settings"
			&& $_GET["ref_id"] == ROLE_FOLDER_ID)	// system settings
		{		
			parent::addAdminLocatorItems(true);

			$ilLocator->addItem($this->lng->txt("obj_".ilObject::_lookupType(
				ilObject::_lookupObjId($_GET["ref_id"]))),
				$this->ctrl->getLinkTargetByClass("ilobjrolefoldergui", "view"));
			
			if ($_GET["obj_id"] > 0)
			{
				$ilLocator->addItem($this->object->getTitle(),
					$this->ctrl->getLinkTarget($this, "view"));
			}
		}
		else							// repository administration
		{
			// ?
		}
	}
	



	function getTabs(&$tabs_gui)
	{
		global $rbacsystem,$rbacreview, $ilHelp;

		$base_role_container = $rbacreview->getFoldersAssignedToRole($this->object->getId(),true);
		

		$activate_role_edit = false;
		
		// todo: activate the following (allow editing of local roles in
		// roles administration)
		if (in_array($this->obj_ref_id,$base_role_container) ||
			(strtolower($_GET["baseClass"]) == "iladministrationgui" &&
			$_GET["admin_mode"] == "settings"))
		{
			$activate_role_edit = true;
		}

		// not so nice (workaround for using tabs in repository)
		$tabs_gui->clearTargets();

		$ilHelp->setScreenIdComponent("role");

		if ($this->back_target != "")
		{
			$tabs_gui->setBackTarget(
				$this->back_target["text"],$this->back_target["link"]);
		}

		if($this->checkAccess('write','edit_permission') && $activate_role_edit)
		{
			$tabs_gui->addTarget("edit_properties",
				$this->ctrl->getLinkTarget($this, "edit"), array("edit","update"), get_class($this));
		}
/*
		if($this->checkAccess('write','edit_permission') and $this->showDefaultPermissionSettings())
		{
			$force_active = ($_GET["cmd"] == "perm" || $_GET["cmd"] == "")
				? true
				: false;
			$tabs_gui->addTarget("default_perm_settings",
				$this->ctrl->getLinkTarget($this, "perm"), array("perm", "adoptPermSave", "permSave"),
				get_class($this),
				"", $force_active);
		}
*/
		if($this->checkAccess('write','edit_permission') and $this->showDefaultPermissionSettings())
		{
			$tabs_gui->addTarget(
				"default_perm_settings",
				$this->ctrl->getLinkTarget($this, "perm"), array(),get_class($this)
			);
		}

		if($this->checkAccess('write','edit_permission') && $activate_role_edit && $this->object->getId() != ANONYMOUS_ROLE_ID)
		{
			$tabs_gui->addTarget("user_assignment",
				$this->ctrl->getLinkTarget($this, "userassignment"),
				array("deassignUser", "userassignment", "assignUser", "searchUserForm", "search"),
				get_class($this));
		}

		if($this->checkAccess('write','edit_permission') && $activate_role_edit  && $this->object->getId() != ANONYMOUS_ROLE_ID)
		{
			$tabs_gui->addTarget("desktop_items",
				$this->ctrl->getLinkTarget($this, "listDesktopItems"),
				array("listDesktopItems", "deleteDesktopItems", "selectDesktopItem", "askDeleteDesktopItem"),
				get_class($this));
		}
		if($this->checkAccess('write','edit_permission'))
		{
			$tabs_gui->addTarget(
					'export',
					$this->ctrl->getLinkTargetByClass('ilExportGUI'),
					array()
				);
					
		}
	}

	function mailToRoleObject()
	{
		global $rbacreview;
		
		$obj_ids = ilObject::_getIdsForTitle($this->object->getTitle(), $this->object->getType());		
		if(count($obj_ids) > 1)
		{
			$_SESSION['mail_roles'][] = '#il_role_'.$this->object->getId();
		}
		else
		{		
			$_SESSION['mail_roles'][] = $rbacreview->getRoleMailboxAddress($this->object->getId());
		}

        require_once 'Services/Mail/classes/class.ilMailFormCall.php';
        $script = ilMailFormCall::getRedirectTarget($this, 'userassignment', array(), array('type' => 'role'));
		ilUtil::redirect($script);
	}
	
	function checkAccess($a_perm_global,$a_perm_obj = '')
	{
		global $rbacsystem,$ilAccess;
		
		$a_perm_obj = $a_perm_obj ? $a_perm_obj : $a_perm_global;
		
		if($this->obj_ref_id == ROLE_FOLDER_ID)
		{
			return $rbacsystem->checkAccess($a_perm_global,$this->obj_ref_id);
		}
		else
		{
			return $ilAccess->checkAccess($a_perm_obj,'',$this->obj_ref_id);
		}
	}
	
	/**
	 * Check if a confirmation about further settings is required or not
	 * @return bool
	 */
	protected function isChangeExistingObjectsConfirmationRequired()
	{
		global $rbacreview;
		
		if(!(int) $_POST['recursive'] and !is_array($_POST['recursive_list']))
		{
			return false;
		}
		
		// Role is protected
		if($rbacreview->isProtected($this->obj_ref_id, $this->object->getId()))
		{
			// TODO: check if recursive_list is enabled
			// and if yes: check if inheritance is broken for the relevant object types
			return count($rbacreview->getFoldersAssignedToRole($this->object->getId())) > 1;
		}
		else
		{
			// TODO: check if recursive_list is enabled
			// and if yes: check if inheritance is broken for the relevant object types
			return count($rbacreview->getFoldersAssignedToRole($this->object->getId())) > 1;
		}
	}
	
	/**
	 * Show confirmation screen
	 * @return 
	 */
	protected function showChangeExistingObjectsConfirmation()
	{
		$protected = $_POST['protected'];
		
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this,'changeExistingObjects'));
		$form->setTitle($this->lng->txt('rbac_change_existing_confirm_tbl'));
		
		$form->addCommandButton('changeExistingObjects', $this->lng->txt('change_existing_objects'));
		$form->addCommandButton('perm',$this->lng->txt('cancel'));
		
		$hidden = new ilHiddenInputGUI('type_filter');
		$hidden->setValue(
			$_POST['recursive'] ?
				serialize(array('all')) :
				serialize($_POST['recursive_list'])
		);
		$form->addItem($hidden);

		$rad = new ilRadioGroupInputGUI($this->lng->txt('rbac_local_policies'),'mode');
		
		if($protected)
		{
			$rad->setValue(ilObjRole::MODE_PROTECTED_DELETE_LOCAL_POLICIES);
			$keep = new ilRadioOption(
				$this->lng->txt('rbac_keep_local_policies'),
				ilObjRole::MODE_PROTECTED_KEEP_LOCAL_POLICIES,
				$this->lng->txt('rbac_keep_local_policies_info')
			);
		}
		else
		{
			$rad->setValue(ilObjRole::MODE_UNPROTECTED_KEEP_LOCAL_POLICIES);
			$keep = new ilRadioOption(
				$this->lng->txt('rbac_keep_local_policies'),
				ilObjRole::MODE_UNPROTECTED_KEEP_LOCAL_POLICIES,
				$this->lng->txt('rbac_unprotected_keep_local_policies_info')
			);
			
		}
		$rad->addOption($keep);
		
		if($protected)
		{
			$del =  new ilRadioOption(
				$this->lng->txt('rbac_delete_local_policies'),
				ilObjRole::MODE_PROTECTED_DELETE_LOCAL_POLICIES,
				$this->lng->txt('rbac_delete_local_policies_info')
			);
		}
		else
		{
			$del =  new ilRadioOption(
				$this->lng->txt('rbac_delete_local_policies'),
				ilObjRole::MODE_UNPROTECTED_DELETE_LOCAL_POLICIES,
				$this->lng->txt('rbac_unprotected_delete_local_policies_info')
			);
		}
		$rad->addOption($del);
		
		$form->addItem($rad);
		$this->tpl->setContent($form->getHTML());
	}
	
	/**
	 * Change existing objects
	 * @return 
	 */
	protected function changeExistingObjectsObject()
	{
		global $tree,$rbacreview,$rbacadmin;
		
		$mode = (int) $_POST['mode'];
		$start = ($this->obj_ref_id == ROLE_FOLDER_ID ? ROOT_FOLDER_ID : $this->obj_ref_id);
		
		$this->object->changeExistingObjects($start,$mode,unserialize(ilUtil::stripSlashes($_POST['type_filter'])));
		
		ilUtil::sendSuccess($this->lng->txt('settings_saved'),true);
		$this->ctrl->redirect($this,'perm');
	}
	
	/**
	 * Set sub tabs
	 * @param object $a_tab
	 * @return 
	 */
	protected function setSubTabs($a_tab)
	{
		global $ilTabs;
		
		switch($a_tab)
		{
			case 'default_perm_settings':
				if($this->obj_ref_id != ROLE_FOLDER_ID)
				{
					return true;
				}
				$ilTabs->addSubTabTarget(
					'rbac_repository_permissions',
					$this->ctrl->getLinkTarget($this,'perm')
				);
				$ilTabs->addSubTabTarget(
					'rbac_admin_permissions',
					$this->ctrl->getLinkTarget($this,'adminPerm')
				);
		}
		return true;
	}
	
	
} // END class.ilObjRoleGUI
?>
