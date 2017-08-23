<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Global orgunit settings GUI 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de> 
 * 
 * @ilCtrl_IsCalledBy ilOrgUnitGlobalSettingsGUI: ilObjOrgUnitGUI
 *
 */
class ilOrgUnitGlobalSettingsGUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	
	/**
	 * @var ilLanguage
	 */
	protected $lng;
	
	/**
	 * @var ilTemplate
	 */
	protected $tpl;
	
	/**
	 * Default constructor
	 * @global type $DIC
	 */
	public function __construct()
	{
		global $DIC;
		
		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		$this->lng->loadLanguageModule('orgu');
		$this->tpl = $DIC->ui()->mainTemplate();
	}
	
	/**
	 * Ctrl execute command
	 */
	public function executeCommand() {
		$cmd = $this->ctrl->getCmd('settings');
		$next_class = $this->ctrl->getNextClass($this);

		switch($next_class) {
			default:
				$this->$cmd();
				break;
		}
	}
	
	/**
	 * Show settings
	 * @param ilPropertyFormGUI $form
	 */
	protected function settings(ilPropertyFormGUI $form = null) {
		if(!$form instanceof ilPropertyFormGUI) {
			$form = $this->initSettingsForm();
		}
		$this->tpl->setContent($form->getHTML());
	}
	
	/**
	 * Init settings form
	 */
	protected function initSettingsForm() {
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this, 'saveSettings'));
		$form->setTitle($this->lng->txt('orgu_global_set_form'));

		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('orgu_global_set_positions'));
		$form->addItem($section);
		
		$available_types = $GLOBALS['DIC']['objDefinition']->getOrgUnitPermissionTypes();
		foreach($available_types as $object_type) {
			
			$setting = new ilOrgUnitObjectPositionSetting($object_type);
			
			$type = new ilCheckboxInputGUI(
				$this->lng->txt('orgu_global_set_positions_type_active').' '.$this->lng->txt('objs_'. $object_type),
				$object_type.'_active'
			);
			$type->setValue(1);
			$type->setChecked($setting->isActive());
			
			$scope = new ilRadioGroupInputGUI($this->lng->txt('orgu_global_set_type_changeable'),$object_type.'_changeable');
			$scope->setValue((int) $setting->isChangeableForObject());

			$scope_object = new ilRadioOption(
				$this->lng->txt('orgu_global_set_type_changeable_object'), 
				1
			);
			$default = new ilCheckboxInputGUI($this->lng->txt('orgu_global_set_type_default'), $object_type.'_default');
			$default->setInfo($this->lng->txt('orgu_global_set_type_default_info'));
			$default->setValue(ilOrgUnitObjectPositionSetting::DEFAULT_ON);
			$default->setChecked($setting->getActivationDefault());
			
			$scope_object->addSubItem($default);
			$scope->addOption($scope_object);
			
			$scope_global = new ilRadioOption(
				$this->lng->txt('orgu_global_set_type_changeable_no'),  
				0
			);
			$scope->addOption($scope_global);
			
			$type->addSubItem($scope);
			$form->addItem($type);
		}
		$form->addCommandButton('saveSettings', $this->lng->txt('save'));
		
		return $form;
	}
	
	/**
	 * Save settings
	 * @return 
	 */
	protected function saveSettings() {
		$form = $this->initSettingsForm();
		if($form->checkInput())
		{
			$available_types = $GLOBALS['DIC']['objDefinition']->getOrgUnitPermissionTypes();
			foreach($available_types as $object_type) {
				
				$obj_setting = new ilOrgUnitObjectPositionSetting($object_type);
				$obj_setting->setActive((bool) $form->getInput($object_type.'_active'));
				$obj_setting->setActivationDefault((int) $form->getInput($object_type.'_default'));
				$obj_setting->setChangeableForObject((bool) $form->getInput($object_type.'_changeable'));
				$obj_setting->update();
			}
			ilUtil::sendSuccess($this->lng->txt('settings_saved'),true);
			$this->ctrl->redirect($this,'settings');
		}
		else
		{
			$form->setValuesByPost();
			ilUtil::sendFailure($this->lng->txt('err_check_input'), false);
			$this->settings($form);
		}
	}
}
?>