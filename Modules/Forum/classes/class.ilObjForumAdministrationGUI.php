<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Object/classes/class.ilObjectGUI.php';

/**
 * Forum Administration Settings.
 * @author            Nadia Ahmad <nahmad@databay.de>
 * @version           $Id:$
 * @ilCtrl_Calls      ilObjForumAdministrationGUI: ilPermissionGUI
 * @ilCtrl_IsCalledBy ilObjForumAdministrationGUI: ilAdministrationGUI
 * @ingroup           ModulesForum
 */
class ilObjForumAdministrationGUI extends ilObjectGUI
{
	/**
	 * Contructor
	 * @access public
	 */
	public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
		$this->type = 'frma';
		parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
		$this->lng->loadLanguageModule('forum');
	}

	/**
	 * @return bool
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
				$this->ctrl->forwardCommand(new ilPermissionGUI($this));
				break;

			default:
				if(!$cmd || $cmd == 'view')
				{
					$cmd = 'editSettings';
				}

				$this->$cmd();
				break;
		}
		return true;
	}

	/**
	 *
	 */
	public function getAdminTabs()
	{
		/**
		 * @var $rbacsystem ilRbacSystem
		 */
		global $rbacsystem;

		if($rbacsystem->checkAccess('visible,read', $this->object->getRefId()))
		{
			$this->tabs_gui->addTarget('settings',
				$this->ctrl->getLinkTarget($this, 'editSettings'),
				array('editSettings', 'view'));
		}

		if($rbacsystem->checkAccess('edit_permission', $this->object->getRefId()))
		{
			$this->tabs_gui->addTarget('perm_settings',
				$this->ctrl->getLinkTargetByClass('ilpermissiongui', 'perm'),
				array(), 'ilpermissiongui');
		}
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	public function editSettings(ilPropertyFormGUI $form = null)
	{
		$this->tabs_gui->setTabActive('settings');

		if(!$form)
		{
			$form = $this->getSettingsForm();
			$this->populateForm($form);
		}

		$this->tpl->setContent($form->getHtml());
	}

	/**
	 * Save settings
	 */
	public function saveSettings()
	{
		/**
		 * @var $ilSetting ilSetting
		 */
		global $ilSetting;

		$this->checkPermission("write");

		$form = $this->getSettingsForm();
		if(!$form->checkInput())
		{
			$form->setValuesByPost();
			$this->editSettings($form);
			return;
		}

		$frma_set = new ilSetting('frma');
		$frma_set->set('forum_overview', $form->getInput('forum_overview'));

		$ilSetting->set('enable_fora_statistics', (int)$form->getInput('fora_statistics'));
		$ilSetting->set('enable_anonymous_fora', (int)$form->getInput('anonymous_fora'));

		require_once 'Services/Cron/classes/class.ilCronManager.php';
		if(!ilCronManager::isJobActive('frm_notification'))
		{
			$ilSetting->set('forum_notification', (int)$form->getInput('forum_notification'));
		}

		require_once 'Services/Captcha/classes/class.ilCaptchaUtil.php';
		ilCaptchaUtil::setActiveForForum((bool)$form->getInput('activate_captcha_anonym'));

		ilUtil::sendSuccess($this->lng->txt('settings_saved'));
		$form->setValuesByPost();
		$this->editSettings($form);
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	protected function populateForm(ilPropertyFormGUI $form)
	{
		/**
		 * @var $ilSetting ilSetting
		 */
		global $ilSetting;

		require_once 'Services/Captcha/classes/class.ilCaptchaUtil.php';

		$frma_set = new ilSetting('frma');

		$form->setValuesByArray(array(
			'forum_overview'          => (bool)$frma_set->get('forum_overview', false),
			'fora_statistics'         => (bool)$ilSetting->get('enable_fora_statistics', false),
			'anonymous_fora'          => (bool)$ilSetting->get('enable_anonymous_fora', false),
			'forum_notification'      => (int)$ilSetting->get('forum_notification') === 1 ? true : false,
			'activate_captcha_anonym' => ilCaptchaUtil::isActiveForForum()
		));
	}

	/**
	 * @return ilPropertyFormGUI
	 */
	protected function getSettingsForm()
	{
		require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this, 'saveSettings'));
		$form->setTitle($this->lng->txt('settings'));

		$frm_radio = new ilRadioGroupInputGUI($this->lng->txt('frm_displayed_infos'), 'forum_overview');
		$frm_radio->addOption(new ilRadioOption($this->lng->txt('new') . ', ' . $this->lng->txt('is_read') . ', ' . $this->lng->txt('unread'), '0'));
		$frm_radio->addOption(new ilRadioOption($this->lng->txt('is_read') . ', ' . $this->lng->txt('unread'), '1'));
		$frm_radio->setInfo($this->lng->txt('frm_disp_info_desc'));
		$form->addItem($frm_radio);

		$check = new ilCheckboxInputGui($this->lng->txt('enable_fora_statistics'), 'fora_statistics');
		$check->setInfo($this->lng->txt('enable_fora_statistics_desc'));
		$form->addItem($check);

		$check = new ilCheckboxInputGui($this->lng->txt('enable_anonymous_fora'), 'anonymous_fora');
		$check->setInfo($this->lng->txt('enable_anonymous_fora_desc'));
		$form->addItem($check);

		require_once 'Services/Cron/classes/class.ilCronManager.php';
		if(ilCronManager::isJobActive('frm_notification'))
		{
			require_once 'Services/Administration/classes/class.ilAdministrationSettingsFormHandler.php';
			ilAdministrationSettingsFormHandler::addFieldsToForm(
				ilAdministrationSettingsFormHandler::FORM_FORUM,
				$form,
				$this
			);
		}
		else
		{
			$notifications = new ilCheckboxInputGui($this->lng->txt('cron_forum_notification'), 'forum_notification');
			$notifications->setInfo($this->lng->txt('cron_forum_notification_desc'));
			$notifications->setValue(1);
			$form->addItem($notifications);
		}

		require_once 'Services/Captcha/classes/class.ilCaptchaUtil.php';
		$cap = new ilCheckboxInputGUI($this->lng->txt('adm_captcha_anonymous_short'), 'activate_captcha_anonym');
		$cap->setInfo($this->lng->txt('adm_captcha_anonymous_frm'));
		$cap->setValue(1);
		if(!ilCaptchaUtil::checkFreetype())
		{
			$cap->setAlert(ilCaptchaUtil::getPreconditionsMessage());
		}
		$form->addItem($cap);

		$form->addCommandButton('saveSettings', $this->lng->txt('save'));
		$form->addCommandButton('cancel', $this->lng->txt('cancel'));

		return $form;
	}

	/**
	 * @param string $a_form_id
	 * @return array
	 */
	public function addToExternalSettingsForm($a_form_id)
	{
		/**
		 * @var $ilSetting ilSetting
		 */
		global $ilSetting;

		switch($a_form_id)
		{
			case ilAdministrationSettingsFormHandler::FORM_PRIVACY:

				$fields = array(
					'enable_fora_statistics' => array($ilSetting->get('enable_fora_statistics', false), ilAdministrationSettingsFormHandler::VALUE_BOOL),
					'enable_anonymous_fora'  => array($ilSetting->get('enable_anonymous_fora', false), ilAdministrationSettingsFormHandler::VALUE_BOOL)
				);

				return array(array("editSettings", $fields));

			case ilAdministrationSettingsFormHandler::FORM_ACCESSIBILITY:
				require_once 'Services/Captcha/classes/class.ilCaptchaUtil.php';
				$fields = array(
					'adm_captcha_anonymous_short' => array(ilCaptchaUtil::isActiveForForum(), ilAdministrationSettingsFormHandler::VALUE_BOOL)
				);

				return array('obj_frma' => array('editSettings', $fields));
		}
	}
}