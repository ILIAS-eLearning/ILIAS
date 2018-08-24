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

require_once "./Services/Object/classes/class.ilObjectGUI.php";

/**
* Class ilObjRoleFolderGUI
*
* @author Stefan Meyer <meyer@leifos.com>
* @author Sascha Hofmann <saschahofmann@gmx.de>
*
* @version $Id$
* 
* @ilCtrl_Calls ilObjRoleFolderGUI: ilPermissionGUI
*
* @ingroup	ServicesAccessControl
*/
class ilObjRoleFolderGUI extends ilObjectGUI
{
	/**
	* ILIAS3 object type abbreviation
	* @var		string
	* @access	public
	*/
	var $type;

	/**
	* Constructor
	* @access	public
	*/
	function __construct($a_data,$a_id,$a_call_by_reference)
	{
		global $lng;

		$this->type = "rolf";
		parent::__construct($a_data,$a_id,$a_call_by_reference, false);
		$lng->loadLanguageModule('rbac');
	}
	
	function executeCommand()
	{
		global $ilTabs;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		$this->prepareOutput();

		switch($next_class)
		{

			case 'ilpermissiongui':
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui = new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			default:
				if(!$cmd)
				{
					$cmd = "view";
				}
				$cmd .= "Object";
				$this->$cmd();

				break;
		}
		return true;
	}

	/**
	 *
	 * @global ilErrorHandler $ilErr
	 * @global ilRbacSystem $rbacsystem
	 * @global ilToolbarGUI $ilToolbar
	 */
	public function viewObject()
	{
		global $ilErr, $rbacsystem, $ilToolbar,$rbacreview,$ilTabs;

		$ilTabs->activateTab('view');

		if(!$rbacsystem->checkAccess('visible,read',$this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'),$ilErr->MESSAGE);
		}

		
		if($rbacsystem->checkAccess('create_role', $this->object->getRefId()))
		{
			$this->ctrl->setParameter($this,'new_type','role');
			$ilToolbar->addButton(
				$this->lng->txt('rolf_create_role'),
				$this->ctrl->getLinkTarget($this,'create')
			);
		}
		if($rbacsystem->checkAccess('create_rolt', $this->object->getRefId()))
		{

			$this->ctrl->setParameter($this,'new_type','rolt');
			$ilToolbar->addButton(
				$this->lng->txt('rolf_create_rolt'),
				$this->ctrl->getLinkTarget($this,'create')
			);
			$this->ctrl->clearParameters($this);
		}

		if(
			$rbacsystem->checkAccess('create_rolt', $this->object->getRefId()) ||
			$rbacsystem->checkAccess('create_rolt', $this->object->getRefId())
		)
		{
			$ilToolbar->addButton(
					$this->lng->txt('rbac_import_role'),
					$this->ctrl->getLinkTargetByClass('ilPermissionGUI','displayImportRoleForm')
			);
		}

		include_once './Services/AccessControl/classes/class.ilRoleTableGUI.php';
		$table = new ilRoleTableGUI($this,'view');
		$table->init();
		$table->parse($this->object->getId());

		$this->tpl->setContent($table->getHTML());
	}

	/**
	 * Search target roles
	 */
	protected function roleSearchObject()
	{
		global $rbacsystem, $ilCtrl, $ilTabs;

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget(
			$this->lng->txt('rbac_back_to_overview'),
			$this->ctrl->getLinkTarget($this,'view')
		);

		if(!$rbacsystem->checkAccess('visible,read',$this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'),$ilErr->MESSAGE);
		}

		$ilCtrl->setParameter($this,'copy_source',(int) $_REQUEST['copy_source']);
		ilUtil::sendInfo($this->lng->txt('rbac_choose_copy_targets'));

		$form = $this->initRoleSearchForm();
		$this->tpl->setContent($form->getHTML());
	}

	/**
	 * Init role search form
	 */
	protected function initRoleSearchForm()
	{
		global $ilCtrl;

		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->lng->txt('rbac_role_title'));
		$form->setFormAction($ilCtrl->getFormAction($this,'view'));

		$search = new ilTextInputGUI($this->lng->txt('title'), 'title');
		$search->setRequired(true);
		$search->setSize(30);
		$search->setMaxLength(255);
		$form->addItem($search);

		$form->addCommandButton('roleSearchForm', $this->lng->txt('search'));
		return $form;
	}
	
	
	/**
	 * Parse search query
	 * @global type $ilCtrl
	 * @return type
	 */
	protected function roleSearchFormObject()
	{
		global $ilCtrl;
		
		$_SESSION['rolf_search_query'] = '';
		$ilCtrl->setParameter($this,'copy_source',(int) $_REQUEST['copy_source']);

		$form = $this->initRoleSearchForm();
		if($form->checkInput())
		{
			$_SESSION['rolf_search_query'] = $form->getInput('title');
			return $this->roleSearchListObject();
		}

		ilUtil::sendFailure($this->lng->txt('msg_no_search_string'), true);
		$form->setValuesByPost();
		$ilCtrl->redirect($this,'roleSearch');
	}

	/**
	 * List roles
	 */
	protected function roleSearchListObject()
	{
		global $ilTabs, $ilCtrl;

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget(
			$this->lng->txt('rbac_back_to_overview'),
			$this->ctrl->getLinkTarget($this,'roleSearchList')
		);

		$ilCtrl->setParameter($this,'copy_source',(int) $_REQUEST['copy_source']);

		if(strlen($_SESSION['rolf_search_query']))
		{
			ilUtil::sendInfo($this->lng->txt('rbac_select_copy_targets'));

			include_once './Services/AccessControl/classes/class.ilRoleTableGUI.php';
			$table = new ilRoleTableGUI($this,'roleSearchList');
			$table->setType(ilRoleTableGUI::TYPE_SEARCH);
			$table->setRoleTitleFilter($_SESSION['rolf_search_query']);
			$table->init();
			$table->parse($this->object->getId());
			return $this->tpl->setContent($table->getHTML());
		}

		ilUtil::sendFailure($this->lng->txt('msg_no_search_string'), true);
		$ilCtrl->redirect($this,'roleSearch');
	}

	/**
	 * Chosse change existing objects,...
	 *
	 */
	protected function chooseCopyBehaviourObject()
	{
		global $ilCtrl, $ilTabs;

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget(
			$this->lng->txt('rbac_back_to_overview'),
			$this->ctrl->getLinkTarget($this,'roleSearchList')
		);
		
		$GLOBALS['ilLog']->write(__METHOD__.': '.$_REQUEST['copy_source']);

		$ilCtrl->setParameter($this,'copy_source',(int) $_REQUEST['copy_source']);

		$form = $this->initCopyBehaviourForm();
		$this->tpl->setContent($form->getHTML());
	}

	/**
	 * Show copy behaviour form
	 */
	protected function initCopyBehaviourForm()
	{
		global $ilCtrl;

		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->lng->txt('rbac_copy_behaviour'));
		$form->setFormAction($ilCtrl->getFormAction($this,'chooseCopyBehaviour'));

		$ce = new ilRadioGroupInputGUI($this->lng->txt('change_existing_objects'), 'change_existing');
		$ce->setRequired(true);
		$ce->setValue(1);
		$form->addItem($ce);

		$ceo = new ilRadioOption($this->lng->txt('change_existing_objects'),1);
		$ce->addOption($ceo);

		$cne = new ilRadioOption($this->lng->txt('rbac_not_change_existing_objects'), 0);
		$ce->addOption($cne);

		$roles = new ilHiddenInputGUI('roles');
		$roles->setValue(implode(',',(array) $_POST['roles']));
		$form->addItem($roles);
		
		
		// if source is role template show option add permission, remove permissions and copy permissions
		if(ilObject::_lookupType((int) $_REQUEST['copy_source']) == 'rolt')
		{
			$form->addCommandButton('addRolePermissions', $this->lng->txt('rbac_copy_role_add_perm'));
			$form->addCommandButton('removeRolePermissions', $this->lng->txt('rbac_copy_role_remove_perm'));
			$form->addCommandButton('copyRole', $this->lng->txt('rbac_copy_role_copy'));
		}
		else
		{
			$form->addCommandButton('copyRole', $this->lng->txt('rbac_copy_role'));
		}
		return $form;
	}
	

	/**
	 * Copy role
	 */
	protected function copyRoleObject()
	{
		global $ilCtrl;

		// Finally copy role/rolt
		$roles = explode(',',$_POST['roles']);
		$source = (int) $_REQUEST['copy_source'];

		$form = $this->initCopyBehaviourForm();
		if($form->checkInput())
		{
			foreach((array) $roles as $role_id)
			{
				if($role_id != $source)
				{
					$this->doCopyRole($source,$role_id,$form->getInput('change_existing'));
				}
			}

			ilUtil::sendSuccess($this->lng->txt('rbac_copy_finished'),true);
			$ilCtrl->redirect($this,'view');
		}
	}

	/**
	 * Add role permissions
	 */
	protected function addRolePermissionsObject()
	{
		global $ilCtrl;

		// Finally copy role/rolt
		$roles = explode(',',$_POST['roles']);
		$source = (int) $_REQUEST['copy_source'];

		$form = $this->initCopyBehaviourForm();
		if($form->checkInput())
		{
			foreach((array) $roles as $role_id)
			{
				if($role_id != $source)
				{
					$this->doAddRolePermissions($source,$role_id,$form->getInput('change_existing'));
				}
			}

			ilUtil::sendSuccess($this->lng->txt('rbac_copy_finished'),true);
			$ilCtrl->redirect($this,'view');
		}
	}

	/**
	 * do add role permission
	 */
	protected function doAddRolePermissions($source, $target, $change_existing)
	{
		global $rbacadmin, $rbacreview;
		
		$rbacadmin->copyRolePermissionUnion(
				$source,
				$this->object->getRefId(),
				$target,
				$rbacreview->getRoleFolderOfRole($target),
				$target,
				$rbacreview->getRoleFolderOfRole($target)
		);
		
		if($change_existing)
		{
			$target_obj = $rbacreview->getRoleFolderOfRole($target);
			$this->doChangeExistingObjects($target_obj, $target);
		}
	}
	
	/**
	 * Remove role permissions
	 */
	protected function removeRolePermissionsObject()
	{
		global $ilCtrl;

		// Finally copy role/rolt
		$roles = explode(',',$_POST['roles']);
		$source = (int) $_REQUEST['copy_source'];

		$form = $this->initCopyBehaviourForm();
		if($form->checkInput())
		{
			foreach((array) $roles as $role_id)
			{
				if($role_id != $source)
				{
					$this->doRemoveRolePermissions($source,$role_id,$form->getInput('change_existing'));
				}
			}

			ilUtil::sendSuccess($this->lng->txt('rbac_copy_finished'),true);
			$ilCtrl->redirect($this,'view');
		}
	}
	
	/**
	 * do add role permission
	 */
	protected function doRemoveRolePermissions($source, $target, $change_existing)
	{
		global $rbacadmin, $rbacreview;
		
		ilLoggerFactory::getLogger('ac')->debug('Remove permission source: ' . $source);
		ilLoggerFactory::getLogger('ac')->debug('Remove permission target: ' . $target);
		ilLoggerFactory::getLogger('ac')->debug('Remove permission change existing: ' . $change_existing);
		
		$rbacadmin->copyRolePermissionSubtract(
				$source,
				$this->object->getRefId(),
				$target,
				$rbacreview->getRoleFolderOfRole($target)
		);
		
		if($change_existing)
		{
			$target_obj = $rbacreview->getRoleFolderOfRole($target);
			$this->doChangeExistingObjects($target_obj, $target);
		}
	}
	
	
	
	/**
	 * Perform copy of role
	 * @global ilTree $tree
	 * @global <type> $rbacadmin
	 * @global <type> $rbacreview
	 * @param <type> $source
	 * @param <type> $target
	 * @param <type> $change_existing
	 * @return <type> 
	 */
	protected function doCopyRole($source, $target, $change_existing)
	{
		global $tree, $rbacadmin, $rbacreview;

		$target_obj = $rbacreview->getRoleFolderOfRole($target);
		
		// Copy role template permissions
		$rbacadmin->copyRoleTemplatePermissions(
			$source,
			$this->object->getRefId(),
			$target_obj,
			$target
		);

		if(!$change_existing || !$target_obj)
		{
			return true;
		}
		
		$start = $target_obj;

		include_once './Services/AccessControl/classes/class.ilObjRole.php';
		if($rbacreview->isProtected($this->object->getRefId(),$source))
		{
			$mode = ilObjRole::MODE_PROTECTED_DELETE_LOCAL_POLICIES;
		}
		else
		{
			$mode = ilObjRole::MODE_UNPROTECTED_DELETE_LOCAL_POLICIES;
		}

		if($start)
		{
			$role = new ilObjRole($target);
			$role->changeExistingObjects(
				$start,
				$mode,
				array('all')
			);
		}
	}
	
	/**
	 * Do change existing objects
	 * @global type $rbacreview
	 * @param type $a_start_obj
	 * @param type $a_source_role
	 */
	protected function doChangeExistingObjects($a_start_obj, $a_target_role)
	{
		global $rbacreview;
		
		if(!$a_start_obj)
		{
			// todo error handling
		}
		
		include_once './Services/AccessControl/classes/class.ilObjRole.php';
		if($rbacreview->isProtected($this->object->getRefId(),$a_source_role))
		{
			$mode = ilObjRole::MODE_PROTECTED_DELETE_LOCAL_POLICIES;
		}
		else
		{
			$mode = ilObjRole::MODE_UNPROTECTED_DELETE_LOCAL_POLICIES;
		}

		if($a_start_obj)
		{
			$role = new ilObjRole($a_target_role);
			$role->changeExistingObjects(
				$a_start_obj,
				$mode,
				array('all')
			);
		}
		
	}
	

	/**
	 * Apply role filter
	 */
	protected function applyFilterObject()
    {
		include_once './Services/AccessControl/classes/class.ilRoleTableGUI.php';
		$table = new ilRoleTableGUI($this,'view');
		$table->init();
		$table->resetOffset();
		$table->writeFilterToSession();

		$this->viewObject();
	}

	/**
	 * Reset role filter
	 */
	function resetFilterObject()
    {
		include_once './Services/AccessControl/classes/class.ilRoleTableGUI.php';
		$table = new ilRoleTableGUI($this,'view');
		$table->init();
		$table->resetOffset();
		$table->resetFilter();

		$this->viewObject();
	}

	/**
	 * Confirm deletion of roles
	 */
	protected function confirmDeleteObject()
	{
		global $ilCtrl;

		if(!count($_POST['roles']))
		{
			ilUtil::sendFailure($this->lng->txt('select_one'),true);
			$ilCtrl->redirect($this,'view');
		}

		$question = $this->lng->txt('rbac_role_delete_qst');

		include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
		$confirm = new ilConfirmationGUI();
		$confirm->setHeaderText($question);
		$confirm->setFormAction($ilCtrl->getFormAction($this));
		$confirm->setHeaderText($this->lng->txt("info_delete_sure"));
		$confirm->setConfirm($this->lng->txt('delete'), 'deleteRole');
		$confirm->setCancel($this->lng->txt('cancel'), 'cancel');


		include_once './Services/AccessControl/classes/class.ilObjRole.php';
		foreach($_POST['roles'] as $role_id)
		{
			$confirm->addItem(
				'roles[]',
				$role_id,
				ilObjRole::_getTranslation(ilObject::_lookupTitle($role_id))
			);
		}
		$this->tpl->setContent($confirm->getHTML());
	}

	/**
	 * Delete roles
	 */
	protected function deleteRoleObject()
	{
		global $rbacsystem,$ilErr,$rbacreview,$ilCtrl;

		if(!$rbacsystem->checkAccess('delete',$this->object->getRefId()))
		{
			$ilErr->raiseError(
				$this->lng->txt('msg_no_perm_delete'),
				$ilErr->MESSAGE
			);
		}

		foreach((array) $_POST['roles'] as $id)
		{
			// instatiate correct object class (role or rolt)
			$obj = ilObjectFactory::getInstanceByObjId($id,false);

			if ($obj->getType() == "role")
			{
				$rolf_arr = $rbacreview->getFoldersAssignedToRole($obj->getId(),true);
				$obj->setParent($rolf_arr[0]);
			}

			$obj->delete();
		}

		// set correct return location if rolefolder is removed
		ilUtil::sendSuccess($this->lng->txt("msg_deleted_roles_rolts"),true);
		$ilCtrl->redirect($this,'view');
	}


	

	
	/**
	* role folders are created automatically
	* DEPRECATED !!!
	* @access	public
	*/
	function createObject()
	{
		$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		
		/*
		$this->object->setTitle($this->lng->txt("obj_".$this->object->getType()."_local"));
		$this->object->setDescription("obj_".$this->object->getType()."_local_desc");
		
		$this->saveObject();		 
		*/
	}
	
	/**
	* display deletion confirmation screen
	* DEPRECATED !!!
	* @access	public
	*/
	function deleteObject($a_error = false)	
	{
		$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
	}

	/**
	* ???
	* TODO: what is the purpose of this function?
	* @access	public
	*/
	function adoptPermSaveObject()
	{
		ilUtil::sendSuccess($this->lng->txt("saved_successfully"),true);
		
		$this->ctrl->redirect($this, "view");
	}
	
	/**
	* show possible subobjects (pulldown menu)
	* overwritten to prevent displaying of role templates in local role folders
	*
	* @access	public
 	*/
	function showPossibleSubObjects()
	{
		global $rbacsystem;

		$d = $this->objDefinition->getCreatableSubObjects($this->object->getType());
		
		if ($this->object->getRefId() != ROLE_FOLDER_ID or !$rbacsystem->checkAccess('create_rolt',ROLE_FOLDER_ID))
		{
			unset($d["rolt"]);
		}
		
		if (!$rbacsystem->checkAccess('create_role',$this->object->getRefId()))
		{
			unset($d["role"]);			
		}

		if (count($d) > 0)
		{
			foreach ($d as $row)
			{
			    $count = 0;
				if ($row["max"] > 0)
				{
					//how many elements are present?
					for ($i=0; $i<count($this->data["ctrl"]); $i++)
					{
						if ($this->data["ctrl"][$i]["type"] == $row["name"])
						{
						    $count++;
						}
					}
				}
				if ($row["max"] == "" || $count < $row["max"])
				{
					$subobj[] = $row["name"];
				}
			}
		}

		if (is_array($subobj))
		{
			//build form
			$opts = ilUtil::formSelect(12,"new_type",$subobj);
			$this->tpl->setCurrentBlock("add_object");
			$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
			$this->tpl->setVariable("BTN_NAME", "create");
			$this->tpl->setVariable("TXT_ADD", $this->lng->txt("add"));
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* save object
	* @access	public
	*/
	function saveObject()
	{
		global $rbacadmin;

		// role folders are created automatically
		$_GET["new_type"] = $this->object->getType();
		$_POST["Fobject"]["title"] = $this->object->getTitle();
		$_POST["Fobject"]["desc"] = $this->object->getDescription();

		// always call parent method first to create an object_data entry & a reference
		$newObj = parent::saveObject();

		// put here your object specific stuff	

		// always send a message
		ilUtil::sendSuccess($this->lng->txt("rolf_added"),true);
		
		$this->ctrl->redirect($this, "view");
	}

	/**
	 * Add role folder tabs
	 * @global ilTree $tree
	 * @global ilLanguage $lng
	 * @param ilTabsGUI $tabs_gui 
	 */
	function getAdminTabs()
	{		
		if ($this->checkPermissionBool("visible,read"))
		{
			$this->tabs_gui->addTarget(
				"view",
				$this->ctrl->getLinkTarget($this, "view"),
				array("", "view"),
				get_class($this)
			);
			
			$this->tabs_gui->addTarget(
				"settings",
				$this->ctrl->getLinkTarget($this, "editSettings"),
				array("editSettings"),
				get_class($this)
			);
		}

		if($this->checkPermissionBool("edit_permission"))
		{
			$this->tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'),
				"perm"),
				"",
				"ilpermissiongui");
		}
	}
	
	function editSettingsObject(ilPropertyFormGUI $a_form = null)
	{
		if(!$a_form)
		{
			$a_form = $this->initSettingsForm();
		}
		
		$this->tpl->setContent($a_form->getHTML());
	}

	function saveSettingsObject()
	{
		global $ilErr, $rbacreview, $ilUser;
		
		if (!$this->checkPermissionBool("write"))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'),$ilErr->MESSAGE);
		}
		
		$form = $this->initSettingsForm();
		if($form->checkInput())
		{
			include_once('./Services/PrivacySecurity/classes/class.ilPrivacySettings.php');			
			$privacy = ilPrivacySettings::_getInstance();
			$privacy->enableRbacLog((int) $_POST['rbac_log']);
			$privacy->setRbacLogAge((int) $_POST['rbac_log_age']);	
			$privacy->save();
						
			if($rbacreview->isAssigned($ilUser->getId(),SYSTEM_ROLE_ID))
			{
				include_once('./Services/PrivacySecurity/classes/class.ilSecuritySettings.php');
				$security = ilSecuritySettings::_getInstance();
				$security->protectedAdminRole((int) $_POST['admin_role']);
				$security->save();
			}
			
			ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
			$this->ctrl->redirect($this, "editSettings");
		}
		
		$form->setValuesByPost();
		$this->editSettingsObject($form);
	}
	
	protected function initSettingsForm()
	{
		global $rbacreview, $ilUser;
		
		$this->lng->loadLanguageModule('ps');
		
		include_once('./Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
		include_once('./Services/PrivacySecurity/classes/class.ilSecuritySettings.php');
		$privacy = ilPrivacySettings::_getInstance();
		$security = ilSecuritySettings::_getInstance();
	 	
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this, "saveSettings"));
		$form->setTitle($this->lng->txt('settings'));		
		
		// protected admin
		$admin = new ilCheckboxInputGUI($GLOBALS['lng']->txt('adm_adm_role_protect'),'admin_role');
		$admin->setDisabled(!$rbacreview->isAssigned($ilUser->getId(),SYSTEM_ROLE_ID));
		$admin->setInfo($this->lng->txt('adm_adm_role_protect_info'));
		$admin->setChecked((int) $security->isAdminRoleProtected());
		$admin->setValue(1);
		$form->addItem($admin);
		
		$check = new ilCheckboxInputGui($this->lng->txt('rbac_log'), 'rbac_log');
		$check->setInfo($this->lng->txt('rbac_log_info'));
		$check->setChecked($privacy->enabledRbacLog());
		$form->addItem($check);

		$age = new ilNumberInputGUI($this->lng->txt('rbac_log_age'),'rbac_log_age');
		$age->setInfo($this->lng->txt('rbac_log_age_info'));
	    $age->setValue($privacy->getRbacLogAge());
		$age->setMinValue(1);
		$age->setMaxValue(24);
		$age->setSize(2);
		$age->setMaxLength(2);
		$check->addSubItem($age);
		
		$form->addCommandButton('saveSettings',$this->lng->txt('save'));
	
		return $form;
	}
	
	public function addToExternalSettingsForm($a_form_id)
	{		
		switch($a_form_id)
		{
			case ilAdministrationSettingsFormHandler::FORM_SECURITY:
				
				include_once('./Services/PrivacySecurity/classes/class.ilSecuritySettings.php');
				$security = ilSecuritySettings::_getInstance();
				
				$fields = array('adm_adm_role_protect' => array($security->isAdminRoleProtected(), ilAdministrationSettingsFormHandler::VALUE_BOOL));
				
				return array(array("editSettings", $fields));			
				
			case ilAdministrationSettingsFormHandler::FORM_PRIVACY:
				
				include_once('./Services/PrivacySecurity/classes/class.ilPrivacySettings.php');	
				$privacy = ilPrivacySettings::_getInstance();
				
				$subitems = null;
				if((bool)$privacy->enabledRbacLog())
				{
					$subitems = array('rbac_log_age' => $privacy->getRbacLogAge());
				}				
				$fields = array('rbac_log' => array($privacy->enabledRbacLog(), ilAdministrationSettingsFormHandler::VALUE_BOOL, $subitems));
				
				return array(array("editSettings", $fields));			
		}
	}

} // END class.ilObjRoleFolderGUI
?>
