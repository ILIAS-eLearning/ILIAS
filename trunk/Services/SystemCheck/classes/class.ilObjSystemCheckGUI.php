<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Object/classes/class.ilObjectGUI.php';
include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
include_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';

/**
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @version           $Id$
 * @ilCtrl_Calls      ilObjSystemCheckGUI: ilPermissionGUI
 * @ilCtrl_isCalledBy ilObjSystemCheckGUI: ilAdministrationGUI
 */
class ilObjSystemCheckGUI extends ilObjectGUI
{
	/**
	 * @var ilLanguage
	 */
	public $lng;

	/**
	 * @var ilCtrl
	 */
	public $ctrl;

	/**
	 * @param      $a_data
	 * @param      $a_id
	 * @param      $a_call_by_reference
	 * @param bool $a_prepare_output
	 */
	public function __construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output = true)
	{
		$this->type = 'sysc';
		parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
		$this->lng->loadLanguageModule('sysc');
	}

	/**
	 * ilCtrl execute command
	 */
	public function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd        = $this->ctrl->getCmd();
		$this->prepareOutput();

		switch($next_class)
		{
			case 'ilpermissiongui':
				$this->tabs_gui->setTabActive('perm_settings');
				require_once 'Services/AccessControl/classes/class.ilPermissionGUI.php';
				$perm_gui = new ilPermissionGUI($this);
				$this->ctrl->forwardCommand($perm_gui);
				break;

			default:
				if($cmd == '' || $cmd == 'view')
				{
					$cmd = 'overview';
				}
				$this->$cmd();
				break;
		}
	}

	/**
	 * Get administration tabs
	 * @param ilTabsGUI $tabs_gui
	 */
	public function getAdminTabs(ilTabsGUI $tabs_gui)
	{
		/**
		 * @var $rbacsystem ilRbacSystem
		 */
		global $rbacsystem;

		if($rbacsystem->checkAccess('read', $this->object->getRefId()))
		{
			$tabs_gui->addTarget('overview', $this->ctrl->getLinkTarget($this, 'overview'));
		}
		if($rbacsystem->checkAccess('edit_permission', $this->object->getRefId()))
		{
			$tabs_gui->addTarget('perm_settings', $this->ctrl->getLinkTargetByClass(array(get_class($this), 'ilpermissiongui'), 'perm'), array('perm', 'info', 'owner'), 'ilpermissiongui');
		}
	}

	/**
	 * @param bool $init_from_database
	 */
	protected function overview()
	{
	}

}
?>