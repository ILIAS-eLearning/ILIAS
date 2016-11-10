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
					$cmd = "showSettingsForm";
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
				$this->ctrl->getLinkTarget($this, "showSettingsForm"));

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

	public function showSettingsForm(ilPropertyFormGUI $form = null)
	{
		//$this->checkPermission('read');

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

		// object types
		$cb_obj_types = new ilCheckboxGroupInputGUI($this->lng->txt("act_lti_for_obj_type"), 'types');

		$valid_obj_types = $this->object->getLTIObjectTypes();

		foreach($valid_obj_types as $obj_type_id => $obj_name)
		{
			$cb_obj_types->addOption(new ilCheckboxOption($obj_name, $obj_type_id));
		}
		$form->addItem($cb_obj_types);

		// test roles
		// TODO get roles from db, but which roles?
		$roles = array(
			'1' => 'users',
			'2' => 'groups'
		);
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

	protected function listConsumers()
	{
		global $ilAccess;

		$this->assertActive();
		$this->tabs_gui->setTabActive("types");

		include_once "Services/Badge/classes/class.ilBadgeTypesTableGUI.php";
		$tbl = new ilBadgeTypesTableGUI($this, "listTypes",
			$ilAccess->checkAccess("write", "", $this->object->getRefId()));
		$this->tpl->setContent($tbl->getHTML());
	}

}