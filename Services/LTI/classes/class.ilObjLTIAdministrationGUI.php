<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Object/classes/class.ilObject2GUI.php';

/**
 * Class ilObjLTIAdministrationGUI
 * @author Jesús López <lopez@leifos.com>
 *
 * @ilCtrl_Calls      ilObjLTIAdministrationGUI: ilPermissionGUI
 * @ilCtrl_isCalledBy ilObjLTIAdministrationGUI: ilAdministrationGUI
 */
class ilObjLTIAdministrationGUI extends ilObject2GUI
{
	public function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
	{
		parent::__construct($a_id, $a_id_type, $a_parent_node_id);

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
				if($cmd == '' || $cmd == 'view')
				{
					$cmd = "showSettingsForm";
				}
				$this->cmd();
				break;
		}
	}

	public function getType()
	{
		return "ltis";
	}

	public function getAdminTabs()
	{
		if($this->checkPermissionBool('read'))
		{
			$this->tabs_gui->addTarget('settings', $this->ctrl->getLinkTarget($this, 'showConfigurationForm'), array('', 'view', 'showConfigurationForm', 'saveConfigurationForm'), __CLASS__);
		}

		if($this->checkPermissionBool('edit_permission'))
		{
			$this->tabs_gui->addTarget('perm_settings', $this->ctrl->getLinkTargetByClass(array(get_class($this), 'ilpermissiongui'), 'perm'), array('perm', 'info', 'owner'), 'ilpermissiongui');
		}
	}

	public function showSettingsForm(ilPropertyFormGUI $form = null)
	{
		$this->checkPermission('read');

		if(!($form instanceof ilPropertyFormGUI))
		{
			$form = $this->getSettingsForm();
		}

		$this->tabs_gui->activateTab("settings");
		$this->tpl->setContent("<p>Hi</p>");
	}

	public function getSettingsForm()
	{
		require_once ("Services/Form/classes/class.ilPropertyFormGui.php");
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->lng->txt('settings'));
		$form->setFormAction($this->ctrl->getFormAction($this,'saveSettingsForm'));

		$obj_types = new ilCheckboxGroupInputGUI($this->lng->txt("act_lti_for_obj_type"), 'types')

		//Radiobuttons with objects which are allowed to LTI
		//we need function to store this array plus query to compare with real objects in db
	}
}