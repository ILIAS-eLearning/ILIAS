<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObjectGUI.php";
require_once('./Services/Repository/classes/class.ilObjectPlugin.php');

/**
* Class ilObjRoleTemplateGUI
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @ilCtrl_Calls ilObjRoleTemplateGUI:
*
* @ingroup	ServicesAccessControl
*/
class ilObjRoleTemplateGUI extends ilObjectGUI
{
	const FORM_MODE_EDIT = 1;
	const FORM_MODE_CREATE = 2;
	
	/**
	* ILIAS3 object type abbreviation
	* @var		string
	* @access	public
	*/
	var $type;

	/**
	* rolefolder ref_id where role is assigned to
	* @var		string
	* @access	public
	*/
	var $rolf_ref_id;
	/**
	* Constructor
	*
	* @access	public
	*/
	function __construct($a_data,$a_id,$a_call_by_reference)
	{
		global $DIC;

		$lng = $DIC['lng'];
		
		$lng->loadLanguageModule('rbac');
		
		$this->type = "rolt";
		parent::__construct($a_data,$a_id,$a_call_by_reference,false);
		$this->rolf_ref_id =& $this->ref_id;
		$this->ctrl->saveParameter($this, "obj_id");
	}
	
	function executeCommand()
	{
		global $DIC;

		$rbacsystem = $DIC['rbacsystem'];

		$this->prepareOutput();

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			default:
				if(!$cmd)
				{
					$cmd = "perm";
				}
				$cmd .= "Object";
				$this->$cmd();
					
				break;
		}

		return true;
	}
	
	/**
	 * Init create form
	 * @param bool creation mode
	 * @return ilPropertyFormGUI $form
	 */
	protected function initFormRoleTemplate($a_mode = self::FORM_MODE_CREATE)
	{
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();

		if($this->creation_mode)
		{
			$this->ctrl->setParameter($this, "new_type", 'rolt');
		}
		
		$form->setFormAction($this->ctrl->getFormAction($this));
		
		if($a_mode == self::FORM_MODE_CREATE)
		{
			$form->setTitle($this->lng->txt('rolt_new'));
			$form->addCommandButton('save', $this->lng->txt('rolt_new'));
		}
		else
		{
			$form->setTitle($this->lng->txt('rolt_edit'));
			$form->addCommandButton('update', $this->lng->txt('save'));
			
		}
		$form->addCommandButton('cancel', $this->lng->txt('cancel'));
		
		$title = new ilTextInputGUI($this->lng->txt('title'),'title');
		if($a_mode != self::FORM_MODE_CREATE)
		{
			if($this->object->isInternalTemplate())
			{
				$title->setDisabled(true);
			}
			$title->setValue($this->object->getTitle());
		}
		$title->setSize(40);
		$title->setMaxLength(70);
		$title->setRequired(true);
		$form->addItem($title);
		
		$desc = new ilTextAreaInputGUI($this->lng->txt('description'),'desc');
		
		if($a_mode != self::FORM_MODE_CREATE)
		{
			$desc->setValue($this->object->getDescription());
		}
		$desc->setCols(40);
		$desc->setRows(3);
		$form->addItem($desc);

		if($a_mode != self::FORM_MODE_CREATE)
		{
			$ilias_id = new ilNonEditableValueGUI($this->lng->txt("ilias_id"), "ilias_id");
			$ilias_id->setValue('il_'.IL_INST_ID.'_'.ilObject::_lookupType($this->object->getId()).'_'.$this->object->getId());
			$form->addItem($ilias_id);
		}

		$pro = new ilCheckboxInputGUI($this->lng->txt('role_protect_permissions'),'protected');
		$pro->setChecked($GLOBALS['DIC']['rbacreview']->isProtected(
				$this->rolf_ref_id,
				$this->object->getId()
		));
		$pro->setValue(1);
		$form->addItem($pro);

		return $form;
	}

	
	/**
	* create new role definition template
	*
	* @access	public
	*/
	function createObject(ilPropertyFormGUI $form = null)
	{
		global $DIC;

		$rbacsystem = $DIC['rbacsystem'];

		if (!$rbacsystem->checkAccess("create_rolt", $this->rolf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		if(!$form)
		{
			$form = $this->initFormRoleTemplate(self::FORM_MODE_CREATE);
		}
		$this->tpl->setContent($form->getHTML());
		return true;
	}
	
	/**
	 * Create new object
	 */
	public function editObject(ilPropertyFormGUI $form = null)
	{
		global $DIC;

		$rbacsystem = $DIC['rbacsystem'];

		$this->tabs_gui->activateTab('settings');
		
		if (!$rbacsystem->checkAccess("write", $this->rolf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		
		if(!$form)
		{
			$form = $this->initFormRoleTemplate(self::FORM_MODE_EDIT);	
		}
		$GLOBALS['DIC']['tpl']->setContent($form->getHTML());
	}

	/**
	* update role template object
	*
	* @access	public
	*/
	public function updateObject()
	{
		global $DIC;

		$rbacsystem = $DIC['rbacsystem'];
		$rbacadmin = $DIC['rbacadmin'];
		$rbacreview = $DIC['rbacreview'];

		// check write access
		if (!$rbacsystem->checkAccess("write", $this->rolf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_modify_rolt"),$this->ilias->error_obj->WARNING);
		}
		
		$form = $this->initFormRoleTemplate(self::FORM_MODE_EDIT);
		if($form->checkInput())
		{
			$this->object->setTitle($form->getInput('title'));
			$this->object->setDescription($form->getInput('desc'));
			$rbacadmin->setProtected(
					$this->rolf_ref_id,
					$this->object->getId(),
					$form->getInput('protected') ? 'y' : 'n'
			);
			$this->object->update();
			ilUtil::sendSuccess($this->lng->txt("saved_successfully"),true);
			$this->ctrl->returnToParent($this);
		}
		
		$form->setValuesByPost();
		$this->editObject($form);
	}
	


	/**
	* save a new role template object
	*
	* @access	public
	*/
	public function saveObject()
	{
		global $DIC;

		$rbacsystem = $DIC['rbacsystem'];
		$rbacadmin = $DIC['rbacadmin'];
		$rbacreview = $DIC['rbacreview'];

		if (!$rbacsystem->checkAccess("create_rolt",$this->rolf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_create_rolt"),$this->ilias->error_obj->WARNING);
		}
		$form = $this->initFormRoleTemplate();
		if($form->checkInput())
		{
			include_once("./Services/AccessControl/classes/class.ilObjRoleTemplate.php");
			$roltObj = new ilObjRoleTemplate();
			$roltObj->setTitle($form->getInput('title'));
			$roltObj->setDescription($form->getInput('desc'));
			$roltObj->create();
			$rbacadmin->assignRoleToFolder($roltObj->getId(), $this->rolf_ref_id,'n');
			$rbacadmin->setProtected(
					$this->rolf_ref_id,
					$roltObj->getId(),
					$form->getInput('protected') ? 'y' : 'n'
			);
			
			ilUtil::sendSuccess($this->lng->txt("rolt_added"),true);
			// redirect to permission screen
			$this->ctrl->setParameter($this,'obj_id',$roltObj->getId());
			$this->ctrl->redirect($this,'perm');
		}
		$form->setValuesByPost();
		$this->createObject($form);
	}


	/**
	 * Show role template permissions
	 */
	protected function permObject()
	{
		global $DIC;

		/**
		 * @var ilRbacSystem
		 */
		$rbacsystem = $DIC->rbac()->system();

		/**
		 * @var ilErrorHandling
		 */
		$ilErr = $DIC['ilErr'];

		/**
		 * @var ilObjectDefinition
		 */
		$objDefinition = $DIC['objDefinition'];

		if(!$rbacsystem->checkAccess('edit_permission', $this->ref_id))
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_perm'),$ilErr->MESSAGE);
			return true;
		}
		$this->tabs_gui->activateTab('perm');

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
		$acc->setId('template_perm_'.$this->ref_id);

		$subs = ilObjRole::getSubObjects('root', false);

		foreach($subs as $subtype => $def)
		{
			$tbl = new ilObjectRoleTemplatePermissionTableGUI(
				$this,
				'perm',
				$this->ref_id,
				$this->obj_id,
				$subtype,
				false
			);
			$tbl->setShowChangeExistingObjects(false);
			$tbl->parse();

			$acc->addItem($def['translation'], $tbl->getHTML());
		}

		$this->tpl->setVariable('ACCORDION',$acc->getHTML());

		// Add options table
		include_once './Services/AccessControl/classes/class.ilObjectRoleTemplateOptionsTableGUI.php';
		$options = new ilObjectRoleTemplateOptionsTableGUI(
			$this,
			'perm',
			$this->ref_id,
			$this->obj_id,
			false
		);
		$options->setShowOptions(false);
		$options->addMultiCommand(
			'permSave',
			$this->lng->txt('save')
		);

		$options->parse();
		$this->tpl->setVariable('OPTIONS_TABLE',$options->getHTML());
	}


	/**
	* save permission templates of role
	*
	* @access	public
	*/
	protected function permSaveObject()
	{
		global $DIC;

		/**
		 * @var ilRbacSystem
		 */
		$rbacsystem = $DIC->rbac()->system();

		/**
		 * @var ilRbacAdmin
		 */
		$rbacadmin = $DIC->rbac()->admin();

		/**
		 * @var ilErrorHandling
		 */
		$ilErr = $DIC['ilErr'];

		/**
		 * @var ilObjectDefinition
		 */
		$objDefinition = $DIC['objDefinition'];


		if (!$rbacsystem->checkAccess('write',$this->rolf_ref_id))
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_perm'),$ilErr->MESSAGE);
			return true;
		}
		// delete all existing template entries
		//$rbacadmin->deleteRolePermission($this->object->getId(), $this->ref_id);
		$subs = ilObjRole::getSubObjects('root', false);

		foreach($subs as $subtype => $def)
		{
			// Delete per object type
			$rbacadmin->deleteRolePermission($this->object->getId(),$this->ref_id,$subtype);
		}

		foreach ($_POST["template_perm"] as $key => $ops_array)
		{
			$rbacadmin->setRolePermission($this->object->getId(), $key,$ops_array,$this->rolf_ref_id);
		}

		// update object data entry (to update last modification date)
		$this->object->update();
		
		ilUtil::sendSuccess($this->lng->txt("saved_successfully"),true);
		$this->ctrl->redirect($this, "perm");
	}

	/**
	* adopting permission setting from other roles/role templates
	*
	* @access	public
	*/
	function adoptPermSaveObject()
	{
		global $DIC;

		$rbacadmin = $DIC['rbacadmin'];
		$rbacsystem = $DIC['rbacsystem'];
		$rbacreview = $DIC['rbacreview'];

		if (!$rbacsystem->checkAccess('write',$this->rolf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_perm"),$this->ilias->error_obj->WARNING);
		}
		elseif ($this->obj_id == $_POST["adopt"])
		{
			ilUtil::sendFailure($this->lng->txt("msg_perm_adopted_from_itself"),true);
		}
		else
		{
			$rbacadmin->deleteRolePermission($this->obj_id, $this->rolf_ref_id);
			$parentRoles = $rbacreview->getParentRoleIds($this->rolf_ref_id,true);
			$rbacadmin->copyRoleTemplatePermissions($_POST["adopt"],$parentRoles[$_POST["adopt"]]["parent"],
										   $this->rolf_ref_id,$this->obj_id);		
			// update object data entry (to update last modification date)
			$this->object->update();

			// send info
			$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($_POST["adopt"]);
			ilUtil::sendSuccess($this->lng->txt("msg_perm_adopted_from1")." '".$obj_data->getTitle()."'.<br/>".$this->lng->txt("msg_perm_adopted_from2"),true);
		}

		$this->ctrl->redirect($this, "perm");
	}

	/**
	* admin and normal tabs are equal for roles
	*/
	function getAdminTabs()
	{
		$this->getTabs();
	}

	/**
	 * @inheritdoc
	 */
	protected function getTabs()
	{
		global $DIC;

		$rbacsystem = $DIC->rbac()->system();

		if($rbacsystem->checkAccess('write',$this->ref_id))
		{
			$this->tabs_gui->addTab(
				'settings',
				$this->lng->txt('settings'),
				$this->ctrl->getLinkTarget($this,'edit')
			);
		}
		if($rbacsystem->checkAccess('edit_permission',$this->ref_id))
		{
			$this->tabs_gui->addTab(
				'perm',
				$this->lng->txt('default_perm_settings'),
				$this->ctrl->getLinkTarget($this,'perm')
			);
		}
	}

	/**
	* cancelObject is called when an operation is canceled, method links back
	* @access	public
	*/
	function cancelObject()
	{
		$this->ctrl->redirectByClass("ilobjrolefoldergui","view");
	}



	/**
	 * @inheritdoc
	 */
	protected function addAdminLocatorItems($a_do_not_add_object = false)
	{
		global $DIC;

		$ilLocator = $DIC['ilLocator'];
		
		parent::addAdminLocatorItems(true);
				
		$ilLocator->addItem(ilObject::_lookupTitle(
			ilObject::_lookupObjId($_GET["ref_id"])),
			$this->ctrl->getLinkTargetByClass("ilobjrolefoldergui", "view"));
	}


} // END class.ilObjRoleTemplateGUI
?>
