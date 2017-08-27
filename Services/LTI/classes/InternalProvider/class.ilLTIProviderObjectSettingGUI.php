<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * GUI class for LTI provider object settings.
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilLTIProviderObjSettingGUI
{
	const ROLE_ADMIN = 'admin';
	const ROLE_TUTOR = 'tutor';
	const ROLE_MEMBER = 'member';
	
	
	/**
	 * @var ilCtrl
	 */
	protected $ctrl = null;

	/**
	 * @var ilLogger
	 */
	 protected $logger = null;
	
	/**
	 * @var ilLanguage
	 */
	protected $lng = null;
	
	/**
	 * @var ilTemplate
	 */
	protected $tpl = null;
	
	/**
	 * @var int
	 */
	protected $ref_id = null;
	
	/**
	 * @param int ref_id
	 */
	public function __construct($a_ref_id)
	{
		$this->ref_id = $a_ref_id;
		$this->logger = $GLOBALS['DIC']->logger()->lti();
		$this->ctrl = $GLOBALS['DIC']->ctrl();
		$this->tpl = $GLOBALS['DIC']->ui()->mainTemplate();

		$this->lng = $GLOBALS['DIC']->language();
		$this->lng->loadLanguageModule('lti');
	}
	
	/**
	 * Check if user has access to lti settings
	 * @param int ref_id
	 * @param int user_id
	 */
	public function hasSettingsAccess()
	{
		if(!ilObjLTIAdministration::isEnabledForType(ilObject::_lookupType($this->ref_id,true)))
		{
			$this->logger->debug('No LTI consumers activated for object type: ' . ilObject::_lookupType($this->ref_id, true));
			return false;
		}
		$access = $GLOBALS['DIC']->rbac()->system();
		return $access->checkAccess(
			'release_objects',
			ilObjLTIAdministration::lookupLTISettingsRefId()
		);
			
	}
	
	/**
	 * Ctrl execute command
	 */
	public function executeCommand()
	{
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
	protected function settings(ilPropertyFormGUI $form = null)
	{
		if(!$form instanceof ilPropertyFormGUI)
		{
			$form = $this->initObjectSettingsForm();
		}
		$this->tpl->setContent($form->getHTML());
	}
	
	
	/**
	 * Init object settings form
	 */
	protected function initObjectSettingsForm()
	{
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt('lti_object_release_settings_form'));
		
		foreach(ilObjLTIAdministration::getEnabledConsumersForType(ilObject::_lookupType($this->ref_id,true)) as $global_consumer)
		{
			// meta data for external consumers
			$section = new ilFormSectionHeaderGUI();
			$section->setTitle($global_consumer->getTitle());
			$section->setInfo($global_consumer->getDescription());
			$form->addItem($section);

			$connector = new ilLTIDataConnector();
			
			$active_consumer = ilLTIToolConsumer::fromGlobalSettingsAndRefId(
				$global_consumer->getExtConsumerId(),
				$this->ref_id,
				$connector
			);
			
			$active = new ilCheckboxInputGUI($GLOBALS['lng']->txt('lti_obj_active'), 'lti_active_'.$global_consumer->getExtConsumerId());
			$active->setInfo($GLOBALS['lng']->txt('lti_obj_active_info'));
			$active->setValue(1);
			$form->addItem($active);

			if($active_consumer->getRefId()) // and enabled
			{
				$active->setChecked(true);
				
				$key = new ilNonEditableValueGUI($this->lng->txt('lti_consumer_key'), 'key');
				$key->setValue($active_consumer->getKey());
				$active->addSubItem($key);
				
				$secret = new ilNonEditableValueGUI($this->lng->txt('lti_consumer_secret'),'secret');
				$secret->setValue($active_consumer->getSecret());
				$active->addSubItem($secret);
			}
			
			
					
			/*
			$admin = new ilCheckboxInputGUI($GLOBALS['lng']->txt('lti_admin'),'lti_admin_'.$ext_consumer->getExtConsumerId());
			$admin->setValue(1);
			$active->addSubItem($admin);
				
			$tutor = new ilCheckboxInputGUI($GLOBALS['lng']->txt('lti_tutor'),'lti_tutor_'.$ext_consumer->getExtConsumerId());
			$tutor->setValue(1);
			$active->addSubItem($tutor);
					
			$member = new ilCheckboxInputGUI($GLOBALS['lng']->txt('lti_member'),'lti_member_'.$ext_consumer->getExtConsumerId());
			$member->setValue(1);
			$active->addSubItem($member);
			 * 
			 */
		}
		
		$form->addCommandButton('updateSettings', $this->lng->txt('save'));
		return $form;
	}
	
	/**
	 * Update settings (activate deactivate lti access)
	 */
	protected function updateSettings()
	{
		$form = $this->initObjectSettingsForm();
		if(!$form->checkInput())
		{
			$form->setValuesByPost();
			$this->settings($form);
			return;
		}
		
		foreach(ilObjLTIAdministration::getEnabledConsumersForType(ilObject::_lookupType($this->ref_id,true)) as $ext_consumer)
		{
			$connector = new ilLTIDataConnector();
			$consumer = new ilLTIToolConsumer(null,$connector);
			$consumer->setExtConsumerId($ext_consumer->getExtConsumerId());
			$consumer->setRefId($this->ref_id);
			$consumer->saveLTI($connector);
		}

		
	}
	
}
