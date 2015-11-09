<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Object/classes/class.ilObject2GUI.php';
require_once 'Services/Contact/BuddySystem/classes/class.ilBuddySystem.php';

/**
 * Class ilObjContactAdministrationGUI
 * @author Michael Jansen <mjansen@databay.de>
 * @ilCtrl_Calls      ilObjContactAdministrationGUI: ilPermissionGUI
 * @ilCtrl_isCalledBy ilObjContactAdministrationGUI: ilAdministrationGUI
 */
class ilObjContactAdministrationGUI extends ilObject2GUI
{
	/**
	 * @param int $a_id
	 * @param int $a_id_type
	 * @param int $a_parent_node_id
	 */
	public function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
	{
		parent::__construct($a_id, $a_id_type, $a_parent_node_id);
		$this->lng->loadLanguageModule('buddysystem');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getType()
	{
		return 'cadm';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAdminTabs(ilTabsGUI $tabs_gui)
	{
		if($this->checkPermissionBool('read'))
		{
			$tabs_gui->addTarget('settings', $this->ctrl->getLinkTarget($this, 'showConfigurationForm'), array('', 'view', 'showConfigurationForm', 'saveConfigurationForm'), __CLASS__);
		}

		if($this->checkPermissionBool('edit_permission'))
		{
			$tabs_gui->addTarget('perm_settings', $this->ctrl->getLinkTargetByClass(array(get_class($this), 'ilpermissiongui'), 'perm'), array('perm', 'info', 'owner'), 'ilpermissiongui');
		}
	}

	/**
	 * {@inheritdoc}
	 */
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
					$cmd = 'showConfigurationForm';
				}
				$this->$cmd();
				break;
		}
	}

	/**
	 * @return ilPropertyFormGUI 
	 */
	protected function getConfigurationForm()
	{
		require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->lng->txt('settings'));
		$form->setFormAction($this->ctrl->getFormAction($this, 'saveConfigurationForm'));
		
		$enabled = new ilCheckboxInputGUI($this->lng->txt('buddy_enable'), 'enable');
		$enabled->setInfo($this->lng->txt('buddy_enable_info'));
		$enabled->setDisabled(!$this->checkPermissionBool('write'));
		$form->addItem($enabled);

		$form->addCommandButton('saveConfigurationForm', $this->lng->txt('save'));

		return $form;
	}

	/**
	 * @param ilPropertyFormGUI|null $form
	 */
	protected function showConfigurationForm(ilPropertyFormGUI $form = null)
	{
		$this->checkPermission('read');
		
		if(!($form instanceof ilPropertyFormGUI))
		{
			$form = $this->getConfigurationForm();
			$form->setValuesByArray(array(
				'enable' => (bool)ilBuddySystem::getInstance()->getSetting('enabled', 0)
			));
		}

		$this->tpl->setContent($form->getHTML());
	}

	/**
	 * 
	 */
	protected function saveConfigurationForm()
	{
		$this->checkPermission('write');

		$form = $this->getConfigurationForm();
		if(!$form->checkInput())
		{
			$form->setValuesByPost();
			$this->showConfigurationForm($form);
			return;
		}

		ilBuddySystem::getInstance()->setSetting('enabled', (bool)$form->getInput('enable') ? 1 : 0);

		ilUtil::sendSuccess($this->lng->txt('saved_successfully'), true);
		$this->ctrl->redirect($this);
	}
}