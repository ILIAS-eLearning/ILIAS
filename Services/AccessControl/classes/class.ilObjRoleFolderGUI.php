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
	function ilObjRoleFolderGUI($a_data,$a_id,$a_call_by_reference)
	{
		global $lng;

		$this->type = "rolf";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference, false);
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
				$perm_gui =& new ilPermissionGUI($this);
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

		$this->ctrl->setParameter($this,'new_type','role');
		$ilToolbar->addButton(
			$this->lng->txt('rolf_create_role'),
			$this->ctrl->getLinkTarget($this,'create')
		);
		$this->ctrl->setParameter($this,'new_type','rolt');
		$ilToolbar->addButton(
			$this->lng->txt('rolf_create_rolt'),
			$this->ctrl->getLinkTarget($this,'create')
		);
		$this->ctrl->clearParameters($this);

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

		$form->addCommandButton('roleSearchList', $this->lng->txt('search'));
		return $form;
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

		$form = $this->initRoleSearchForm();
		if($form->checkInput())
		{
			ilUtil::sendInfo($this->lng->txt('rbac_select_copy_targets'));

			include_once './Services/AccessControl/classes/class.ilRoleTableGUI.php';
			$table = new ilRoleTableGUI($this,'view');
			$table->setType(ilRoleTableGUI::TYPE_SEARCH);
			$table->setRoleTitleFilter($form->getInput('title'));
			$table->init();
			$table->parse($this->object->getId());
			return $this->tpl->setContent($table->getHTML());
		}

		ilUtil::sendFailure($this->lng->txt('msg_no_search_string'), true);
		$form->setValuesByPost();
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

		$form->addCommandButton('copyRole', $this->lng->txt('rbac_copy_role'));
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



		// Copy role template permissions
		$rbacadmin->copyRoleTemplatePermissions(
			$source,
			$this->object->getRefId(),
			$rbacreview->getRoleFolderOfRole($target),
			$target
		);

		if(!$change_existing)
		{
			return true;
		}

		$start = ($this->object->getRefId() == ROLE_FOLDER_ID) ?
			ROOT_FOLDER_ID :
			$tree->getParentId($this->object->getRefId());



		include_once './Services/AccessControl/classes/class.ilObjRole.php';
		if($rbacreview->isProtected($this->object->getRefId(),$source))
		{
			$mode = ilObjRole::MODE_PROTECTED_DELETE_LOCAL_POLICIES;
		}
		else
		{
			$mode = ilObjRole::MODE_UNPROTECTED_DELETE_LOCAL_POLICIES;
		}

		$role = new ilObjRole($target);
		$role->changeExistingObjects(
			$start,
			$mode,
			array('all')
		);
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

		include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
		$confirm = new ilConfirmationGUI();
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
	function deleteObject()	
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
	function showPossibleSubObjects($a_tpl)
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
			$a_tpl->setCurrentBlock("add_object");
			$a_tpl->setVariable("SELECT_OBJTYPE", $opts);
			$a_tpl->setVariable("BTN_NAME", "create");
			$a_tpl->setVariable("TXT_ADD", $this->lng->txt("add"));
			$a_tpl->parseCurrentBlock();
		}
		
		return $a_tpl;
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
	function getAdminTabs(&$tabs_gui)
	{
		global $tree,$lng;

		if ($this->checkPermissionBool("visible,read"))
		{
			$tabs_gui->addTarget(
				"view",
				$this->ctrl->getLinkTarget($this, "view"),
				array("", "view"),
				get_class($this)
			);

		}

		if($this->checkPermissionBool("edit_permission"))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'),
				"perm"),
				"",
				"ilpermissiongui");
		}

	}



} // END class.ilObjRoleFolderGUI
?>
