<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/WebServices/FileManager/classes/class.ilFMSettings.php';

/**
 * File manager settings
 * 
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 *
 * @ilCtrl_Calls ilFMSettingsGUI:
 */
class ilFMSettingsGUI
{

	private $parent_obj = null;
	
	/**
	 * Constructor
	 */
	public function __construct($a_parent_gui)
	{
		$this->parent_obj = $a_parent_gui;
	}

	/**
	 * Execute command
	 * @global  ilCtrl $ilCtrl 
	 */
	public function executeCommand()
	{
		global $ilCtrl;

		$next_class = $ilCtrl->getNextClass();
		$cmd = $ilCtrl->getCmd();
		switch($next_class)
		{
			default:
				if(!$cmd)
				{
					$cmd = 'settings';
				}
				$this->$cmd();
				break;

		}
	}

	/**
	 * Get parent gui
	 * @return ilObjectGUI
	 */
	public function getParentObject()
	{
		return $this->parent_obj;
	}

	/**
	 * Show settings
	 */
	protected function settings()
	{
		$form = $this->initSettingsForm();

		$GLOBALS['tpl']->setContent($form->getHTML());
	}

	/**
	 * Update
	 */
	protected function update()
	{
		include_once './Services/WebServices/FileManager/classes/class.ilFMSettings.php';
		$settings = ilFMSettings::getInstance();

		$form = $this->initSettingsForm();
		if($form->checkInput())
		{
			$settings->enable($form->getInput('active'));
			$settings->enableLocalFS($form->getInput('local'));
			$settings->setMaxFileSize($form->getInput('filesize'));
			$settings->update();

			ilUtil::sendSuccess($GLOBALS['lng']->txt('settings_saved'), true);
			$GLOBALS['ilCtrl']->redirect($this,'settings');
		}
	}

	/**
	 * Init settings form
	 */
	protected function initSettingsForm()
	{
		global $ilCtrl;

		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		$form->setTitle($GLOBALS['lng']->txt('settings'));
		$form->addCommandButton('update', $GLOBALS['lng']->txt('save'));
		$form->addCommandButton('settings', $GLOBALS['lng']->txt('cancel'));

		// activation
		$active = new ilCheckboxInputGUI($GLOBALS['lng']->txt('fm_settings_active'), 'active');
		$active->setInfo($GLOBALS['lng']->txt('fm_settings_active_info'));
		$active->setValue(1);
		$active->setChecked(ilFMSettings::getInstance()->isEnabled());
		$form->addItem($active);

		// one frame
		$local = new ilCheckboxInputGUI($GLOBALS['lng']->txt('fm_settings_local'), 'local');
		$local->setInfo($GLOBALS['lng']->txt('fm_settings_local_info'));
		$local->setValue(1);
		$local->setChecked(ilFMSettings::getInstance()->IsLocalFSEnabled());
		$form->addItem($local);
		
		$fs = new ilNumberInputGUI($GLOBALS['lng']->txt('fm_settings_filesize'),'filesize');
		$fs->setSuffix('MiB');
		$fs->setSize(3);
		$fs->setMaxLength(3);
		$fs->setMinValue(1);
		$fs->setMaxValue(999);
		$fs->setInfo($GLOBALS['lng']->txt('fm_settings_filesize_info'));
		$fs->setValue(ilFMSettings::getInstance()->getMaxFileSize());
		$form->addItem($fs);
		
		return $form;
	}
}
?>
