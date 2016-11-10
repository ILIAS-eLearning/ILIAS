<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Object/classes/class.ilObjectGUI.php';

/**
 * Class ilObjLTIAdministrationGUI
 * @author Jesús López <lopez@leifos.com>
 *
 * @ilCtrl_Calls      ilObjLTIAdministrationGUI: ilPermissionGUI
 * @ilCtrl_isCalledBy ilObjLTIAdministrationGUI: ilAdministrationGUI
 *
 * @ingroup ServicesLTI
 */
class ilObjLTIAdministrationGUI extends ilObjectGUI
{

	public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
		$this->type = "ltis";
		parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

	}

	public function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd        = $this->ctrl->getCmd();
		$this->prepareOutput();

		switch($next_class)
		{
			case 'ilpermissiongui':
				require_once 'Services/AccessControl/classes/class.ilPermissionGUI.php';
				$perm_gui = new ilPermissionGUI($this);
				$this->ctrl->forwardCommand($perm_gui);
				break;

			default:
				if (!$cmd || $cmd == 'view')
				{
					$cmd = "initSettingsForm";
				}
				$this->$cmd();
				break;
		}
	}

	public function getType()
	{
		return "ltis";
	}

	public function getAdminTabs()
	{

		/** I need info about this controls after print tabs
		 *  sometimes we use:
		 *    global $rbacsystem;
		 *    if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		 *    .....
		 * and sometimes:
		 * 	if($this->checkPermissionBool('read'))
		 *
		 */

		global $rbacsystem;

		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->tabs_gui->addTab("settings",
				$this->lng->txt("settings"),
				$this->ctrl->getLinkTarget($this, "initSettingsForm"));

			$this->tabs_gui->addTab("consumers",
				$this->lng->txt("consumers"),
				$this->ctrl->getLinkTarget($this, "listConsumers"));
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$this->tabs_gui->addTab("perm_settings",
				$this->lng->txt("perm_settings"),
				$this->ctrl->getLinkTargetByClass('ilpermissiongui',"perm"));
		}

	}

	public function initSettingsForm(ilPropertyFormGUI $form = null)
	{
		if(!($form instanceof ilPropertyFormGUI))
		{
			$form = $this->getSettingsForm();
		}
		$this->tabs_gui->activateTab("settings");
		$this->tpl->setContent($form->getHTML());
	}

	protected function getSettingsForm()
	{
		require_once ("Services/Form/classes/class.ilPropertyFormGui.php");

		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this,'saveSettingsForm'));
		$form->setTitle($this->lng->txt("lti_settings"));

		// object types
		$cb_obj_types = new ilCheckboxGroupInputGUI($this->lng->txt("act_lti_for_obj_type"), 'types');

		$valid_obj_types = $this->object->getLTIObjectTypes();

		foreach($valid_obj_types as $obj_type_id => $obj_name)
		{
			$cb_obj_types->addOption(new ilCheckboxOption($obj_name, $obj_type_id));
		}
		$form->addItem($cb_obj_types);

		// test roles
		$roles = $this->object->getLTIRoles();
		foreach($roles as $role_id => $role_name)
		{
			$options[$role_id] = $role_name;
		}

		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
		$si_roles = new ilSelectInputGUI($this->lng->txt("gbl_roles_to_users"), 'roles');
		$si_roles->setOptions($options);
		$form->addItem($si_roles);

		$form->addCommandButton("saveSettingsForm", $this->lng->txt("save"));

		return $form;

	}

	protected function saveSettingsForm()
	{
		global $ilCtrl;

		$this->checkPermission("write");

		$form = $this->getSettingsForm();
		if($form->checkInput())
		{
			$obj_types = $form->getInput('types');

			$role = $form->getInput('roles');

			$this->object->saveData($obj_types, $role);

			ilUtil::sendSuccess($this->lng->txt("settings_saved"),true);
		}

		$form->setValuesByPost();
		$this->initSettingsForm($form);
	}

	protected function listConsumers()
	{
		global $ilAccess, $ilToolbar;

		$this->ctrl->setParameter($this,'new_consumer','consumer');
		$ilToolbar->addButton(
			$this->lng->txt('consf_create_consumer'),
			$this->ctrl->getLinkTarget($this,'create')
		);

		$this->tabs_gui->setTabActive("consumers");

		include_once "Services/LTI/classes/Consumer/class.ilConsumerTableGUI.php";
		$tbl = new ilObjectConsumerTableGUI($this, "listConsumers",
			$ilAccess->checkAccess("write", "", $this->object->getRefId()));
		$this->tpl->setContent($tbl->getHTML());

		/*
		include_once "Services/Badge/classes/class.ilBadgeTypesTableGUI.php";
		$tbl = new ilBadgeTypesTableGUI($this, "listTypes",
			$ilAccess->checkAccess("write", "", $this->object->getRefId()));
		$this->tpl->setContent($tbl->getHTML());
		*/
	}

}