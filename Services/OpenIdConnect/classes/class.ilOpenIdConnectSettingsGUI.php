<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilOpenIdConnectSettingsGUI
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 *
 */
class ilOpenIdConnectSettingsGUI
{
	const STAB_SETTINGS = 'settings';
	const STAB_PROFILE = 'profile';
	const STAB_ROLES = 'roles';

	const DEFAULT_CMD = 'settings';

	/**
	 * @var int
	 */
	private $ref_id = 0;


	/**
	 * @var \ilOpenIdConnectSettings
	 */
	private $settings = null;

	/**
	 * @var ilLanguage|null
	 */
	protected $lng = null;

	/**
	 * @var ilCtrl|null
	 */
	protected $ctrl = null;

	/**
	 * @var \ilLogger
	 */
	protected $logger = null;

	/**
	 * @var ilAccessHandler|null
	 */
	protected $access = null;

	/**
	 * @var ilRbacReview
	 */
	protected $review;

	/**
	 * @var \ilErrorHandling
	 */
	protected $error = null;

	/**
	 * @var \ilTemplate|null
	 */
	protected $mainTemplate = null;

	/**
	 * @var ilTabsGUI|null
	 */
	protected $tabs = null;

	/**
	 * ilOpenIdConnectSettingsGUI constructor.
	 */
	public function __construct($a_ref_id)
	{
		global $DIC;

		$this->ref_id = $a_ref_id;

		$this->lng = $DIC->language();
		$this->lng->loadLanguageModule('auth');

		$this->mainTemplate = $DIC->ui()->mainTemplate();
		$this->tabs = $DIC->tabs();
		$this->ctrl = $DIC->ctrl();
		$this->logger = $DIC->logger()->auth();

		$this->access = $DIC->access();
		$this->review = $DIC->rbac()->review();
		$this->error = $DIC['ilErr'];


		$this->settings = ilOpenIdConnectSettings::getInstance();
	}

	/**
	 * @param string $a_permission
	 */
	protected function checkAccess($a_permission)
	{
		if(!$this->checkAccessBool($a_permission))
		{
			$this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->WARNING);
		}
	}

	/**
	 * @param string $a_permission
	 * @return bool
	 */
	protected function checkAccessBool($a_permission)
	{
		return $this->access->checkAccess($a_permission,'',$this->ref_id);
	}


	/**
	 * Execute command
	 */
	public function executeCommand()
	{
		$this->checkAccess('read');

		switch($this->ctrl->getNextClass())
		{
			default:
				$cmd = $this->ctrl->getCmd(self::DEFAULT_CMD);
				$this->$cmd();
				break;
		}
	}

	/**
	 * @param \ilPropertyFormGUI|null $form
	 */
	protected function settings(ilPropertyFormGUI $form = null)
	{
		$this->checkAccess('read');
		$this->setSubTabs(self::STAB_SETTINGS);


		if(!$form instanceof ilPropertyFormGUI)
		{
			$form = $this->initSettingsForm();
		}

		$this->mainTemplate->setContent($form->getHTML());
	}

	/**
	 * Init general settings form
	 */
	protected function initSettingsForm()
	{
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->lng->txt('auth_oidc_settings_title'));
		$form->setFormAction($this->ctrl->getFormAction($this));

		// activation
		$activation = new ilCheckboxInputGUI(
			$this->lng->txt('auth_oidc_settings_activation'),
			'activation'
		);
		$activation->setChecked($this->settings->getActive());
		$form->addItem($activation);

		// provider
		$provider = new ilTextInputGUI(
			$this->lng->txt('auth_oidc_settings_provider'),
			'provider'
		);
		$provider->setRequired(true);
		$provider->setValue($this->settings->getProvider());
		$form->addItem($provider);

		$client_id = new ilTextInputGUI(
			$this->lng->txt('auth_oidc_settings_client_id'),
			'client_id'
		);
		$client_id->setRequired(true);
		$client_id->setValue($this->settings->getClientId());
		$form->addItem($client_id);

		// secret
		$secret = new ilPasswordInputGUI(
			$this->lng->txt('auth_oidc_settings_secret'),
			'secret'
		);
		$secret->setSkipSyntaxCheck(true);
		$secret->setRetype(false);
		$secret->setRequired(false);
		if(strlen($this->settings->getSecret()))
		{
			$secret->setValue('******');
		}
		$form->addItem($secret);

		// login element
		$login_element = new ilRadioGroupInputGUI(
			$this->lng->txt('auth_oidc_settings_le'),
			'le'
		);
		$login_element->setRequired(true);
		$login_element->setValue($this->settings->getLoginElementType());
		$form->addItem($login_element);

		// le -> type text
		$text_option = new ilRadioOption(
			$this->lng->txt('auth_oidc_settings_txt'),
			ilOpenIdConnectSettings::LOGIN_ELEMENT_TYPE_TXT
		);
		$login_element->addOption($text_option);

		// le -> type text -> text
		$text = new ilTextInputGUI(
			'',
			'le_text'
		);
		$text->setValue($this->settings->getLoginElemenText());
		$text->setMaxLength(120);
		$text->setInfo($this->lng->txt('auth_oidc_settings_txt_val_info'));
		$text_option->addSubItem($text);

		// le -> type img
		$img_option = new ilRadioOption(
			$this->lng->txt('auth_oidc_settings_img'),
			ilOpenIdConnectSettings::LOGIN_ELEMENT_TYPE_IMG
		);
		$login_element->addOption($img_option);

		$image = new ilImageFileInputGUI(
			'',
			'le_img'
		);
		$image->setALlowDeletion(false);

		if($this->settings->hasImageFile())
		{
			$image->setImage($this->settings->getImageFilePath());
		}
		$image->setInfo($this->lng->txt('auth_oidc_settings_img_file_info'));
		$img_option->addSubItem($image);

		// login options
		$login_options = new ilRadioGroupInputGUI(
			$this->lng->txt('auth_oidc_settings_login_options'),
			'login_prompt'
		);
		$login_options->setValue($this->settings->getLoginPromptType());

		// enforce login
		$enforce = new ilRadioOption(
			$this->lng->txt('auth_oidc_settings_login_option_enforce'),
			ilOpenIdConnectSettings::LOGIN_ENFORCE
		);
		$enforce->setInfo($this->lng->txt('auth_oidc_settings_login_option_enforce_info'));
		$login_options->addOption($enforce);

		// default login
		$default = new ilRadioOption(
			$this->lng->txt('auth_oidc_settings_login_option_default'),
			ilOpenIdConnectSettings::LOGIN_STANDARD
		);
		$default->setInfo($this->lng->txt('auth_oidc_settings_login_option_default_info'));
		$login_options->addOption($default);

		$form->addItem($login_options);

		// logout scope
		$logout_scope = new ilRadioGroupInputGUI(
			$this->lng->txt('auth_oidc_settings_logout_scope'),
			'logout_scope'
		);
		$logout_scope->setValue($this->settings->getLogoutScope());

		// scope global
		$global_scope = new ilRadioOption(
			$this->lng->txt('auth_oidc_settings_logout_scope_global'),
			ilOpenIdConnectSettings::LOGOUT_SCOPE_GLOBAL
		);
		$global_scope->setInfo($this->lng->txt('auth_oidc_settings_logout_scope_global_info'));
		$logout_scope->addOption($global_scope);

		// ilias scope
		$ilias_scope = new ilRadioOption(
			$this->lng->txt('auth_oidc_settings_logout_scope_local'),
			ilOpenIdConnectSettings::LOGOUT_SCOPE_LOCAL
		);
		$logout_scope->addOption($ilias_scope);

		$form->addItem($logout_scope);

		$use_custom_session = new ilCheckboxInputGUI(
			$this->lng->txt('auth_oidc_settings_custom_session_duration_type'),
			'custom_session'
		);
		$use_custom_session->setOptionTitle(
			$this->lng->txt('auth_oidc_settings_custom_session_duration_option')
		);
		$use_custom_session->setChecked($this->settings->isCustomSession());
		$form->addItem($use_custom_session);

		// session duration
		$session = new ilNumberInputGUI(
			$this->lng->txt('auth_oidc_settings_session_duration'),
			'session_duration'
		);
		$session->setValue($this->settings->getSessionDuration());
		$session->setSuffix($this->lng->txt('minutes'));
		$session->setMinValue(5);
		$session->setMaxValue(1440);
		$session->setRequired(true);
		$use_custom_session->addSubItem($session);

		if($this->checkAccessBool('write'))
		{
			// save button
			$form->addCommandButton('saveSettings', $this->lng->txt('save'));
		}


		// User sync settings --------------------------------------------------------------
		$user_sync = new ilFormSectionHeaderGUI();
		$user_sync->setTitle($this->lng->txt('auth_oidc_settings_section_user_sync'));
		$form->addItem($user_sync);

		$sync = new ilCheckboxInputGUI(
			$this->lng->txt('auth_oidc_settings_user_sync'),
			'sync'
		);
		$sync->setChecked($this->settings->isSyncAllowed());
		$sync->setInfo($this->lng->txt('auth_oidc_settings_user_sync_info'));
		$sync->setValue(1);
		$form->addItem($sync);

		$roles = new ilSelectInputGUI(
			$this->lng->txt('auth_oidc_settings_default_role'),
			'role'
		);
		$roles->setValue($this->settings->getRole());
		$roles->setInfo($this->lng->txt('auth_oidc_settings_default_role_info'));
		$roles->setOptions($this->prepareRoleSelection());
		$roles->setRequired(true);
		$sync->addSubItem($roles);

		$user_attr = new ilTextInputGUI(
			$this->lng->txt('auth_oidc_settings_user_attr'),
			'username'
		);
		$user_attr->setValue($this->settings->getUidField());
		$user_attr->setRequired(true);
		$form->addItem($user_attr);

		return $form;
	}

	/**
	 * Save settings
	 */
	protected function saveSettings()
	{
		$this->checkAccess('write');

		$form = $this->initSettingsForm();
		if(!$form->checkInput())
		{
			ilUtil::sendFailure(
				$this->lng->txt('err_check_input')
			);
			$form->setValuesByPost();
			$this->settings($form);
			return;
		}

		$this->settings->setActive((bool) $form->getInput('activation'));
		$this->settings->setProvider((string) $form->getInput('provider'));
		$this->settings->setClientId((string) $form->getInput('client_id'));
		if(strlen($form->getInput('secret')) && strcmp($form->getInput('secret'),'******') !== 0)
		{
			$this->settings->setSecret((string) $form->getInput('secret'));
		}
		$this->settings->setLoginElementType((int) $form->getInput('le'));
		$this->settings->setLoginElementText((string) $form->getInput('le_text'));
		$this->settings->setLoginPromptType((int) $form->getInput('login_prompt'));
		$this->settings->setLogoutScope((int) $form->getInput('logout_scope'));
		$this->settings->useCustomSession((bool) $form->getInput('custom_session'));
		$this->settings->setSessionDuration((int) $form->getInput('session_duration'));
		$this->settings->allowSync((bool) $form->getInput('sync'));
		$this->settings->setRole((int) $form->getInput('role'));
		$this->settings->setUidField((string) $form->getInput('username'));

		$fileData = (array) $form->getInput('le_img');

		if(strlen($fileData['tmp_name']))
		{
			$this->saveImageFromHttpRequest();
		}

		$this->settings->save();

		ilUtil::sendSuccess($this->lng->txt('settings_saved'),true);
		$this->ctrl->redirect($this, 'settings');
	}

	/**
	 * Save image from http request
	 */
	protected function saveImageFromHttpRequest()
	{
		global $DIC;

		try {
			$upload = $DIC->upload();
			if(!$upload->hasBeenProcessed())
			{
				$upload->process();
			}
			foreach($upload->getResults() as $single_file_upload)
			{
				if($single_file_upload->getStatus() == \ILIAS\FileUpload\DTO\ProcessingStatus::OK)
				{
					$this->settings->deleteImageFile();
					$upload->moveFilesTo(
						ilOpenIdConnectSettings::FILE_STORAGE,
						\ILIAS\FileUpload\Location::WEB
					);
					$this->settings->setLoginElementImage($single_file_upload->getName());
				}
			}
		}
		catch (\ILIAS\Filesystem\Exception\IllegalStateException $e) {
			$this->logger->warning('Upload failed with message: ' . $e->getMessage());
		}
	}

	/**
	 * @param bool $a_with_select_option
	 * @return mixed
	 */
	protected function prepareRoleSelection($a_with_select_option = true) : array
	{
		$global_roles = ilUtil::_sortIds(
			$this->review->getGlobalRoles(),
			'object_data',
			'title',
			'obj_id'
		);

		$select = [];
		if($a_with_select_option)
		{
			$select[0] = $this->lng->txt('links_select_one');
		}
		foreach($global_roles as $role_id)
		{
			if($role_id == ANONYMOUS_ROLE_ID)
			{
				continue;
			}
			$select[$role_id] = ilObject::_lookupTitle($role_id);
		}
		return $select;
	}


	/**
	 * @param ilPropertyFormGUI|null $form
	 */
	protected function profile(ilPropertyFormGUI $form = null)
	{
		$this->checkAccess('read');
		$this->setSubTabs(self::STAB_PROFILE);

		if(!$form instanceof ilPropertyFormGUI)
		{
			$form = $this->initProfileForm();
		}
		$this->mainTemplate->setContent($form->getHTML());
	}

	/**
	 * @return ilPropertyFormGUI
	 */
	protected function initProfileForm() : \ilPropertyFormGUI
	{
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->lng->txt('auth_oidc_mapping_table'));
		$form->setFormAction($this->ctrl->getFormAction($this, 'saveProfile'));

		foreach($this->settings->getProfileMappingFields() as $field => $lng_key)
		{
			$text_form = new ilTextInputGUI($this->lng->txt($lng_key));
			$text_form->setPostVar($field."_value");
			$text_form->setValue($this->settings->getProfileMappingFieldValue($field));
			$form->addItem($text_form);

			$checkbox_form = new ilCheckboxInputGUI('');
			$checkbox_form->setValue(1);
			$checkbox_form->setPostVar($field . "_update");
			$checkbox_form->setChecked($this->settings->getProfileMappingFieldUpdate($field));
			$checkbox_form->setOptionTitle($this->lng->txt('auth_oidc_update_field_info'));
			$form->addItem($checkbox_form);
		}

		if($this->checkAccessBool('write'))
		{
			$form->addCommandButton('saveProfile',$this->lng->txt('save'));
		}
		return $form;
	}

	/**
	 * @return bool
	 */
	protected function saveProfile()
	{
		$this->checkAccessBool('write');

		$form = $this->initProfileForm();
		if(!$form->checkInput()) {
			ilUtil::sendFailure($this->lng->txt('err_check_input'));
			$form->setValuesByPost();
			$this->profile($form);
			return false;
		}

		foreach($this->settings->getProfileMappingFields() as $field => $lng_key)
		{
			$this->settings->setProfileMappingFieldValue(
				$field,
				$form->getInput($field.'_value')
			);
			$this->settings->setProfileMappingFieldUpdate(
				$field,
				$form->getInput($field.'_update')
			);
		}
		$this->settings->save();
		ilUtil::sendSuccess($this->lng->txt('settings_saved'),true);
		$this->ctrl->redirect($this, self::STAB_PROFILE);
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	protected function roles(\ilPropertyFormGUI $form = null)
	{
		$this->checkAccess('read');
		$this->setSubTabs(self::STAB_ROLES);

		if(!$form instanceof ilPropertyFormGUI)
		{
			$form = $this->initRolesForm();
		}
		$this->mainTemplate->setContent($form->getHTML());
	}

	/**
	 * @return \ilPropertyFormGUI
	 */
	protected function initRolesForm()
	{
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->lng->txt('auth_oidc_role_mapping_table'));
		$form->setFormAction($this->ctrl->getFormAction($this, self::STAB_ROLES));

		foreach($this->prepareRoleSelection(false) as $role_id => $role_title)
		{
			$role_map = new ilTextInputGUI(
				$role_title,
				'role_map_'.$role_id
			);
			$role_map->setValue($this->settings->getRoleMappingValueForId($role_id));
			$form->addItem($role_map);

			$update = new ilCheckboxInputGUI(
				'',
				'role_map_update_'.$role_id
			);
			$update->setOptionTitle($this->lng->txt('auth_oidc_update_role_info'));
			$update->setValue(1);
			$update->setChecked(!$this->settings->getRoleMappingUpdateForId($role_id));
			$form->addItem($update);
		}

		if($this->checkAccessBool('write'))
		{
			$form->addCommandButton('saveRoles', $this->lng->txt('save'));
		}
		return $form;
	}

	/**
	 * save role selection
	 */
	protected function saveRoles()
	{
		$this->checkAccess('write');
		$form = $this->initRolesForm();
		if($form->checkInput()) {

			$this->logger->dump($_POST, \ilLogLevel::DEBUG);


			$role_settings = [];
			foreach($this->prepareRoleSelection(false) as $role_id => $role_title) {

				$this->logger->dump($form->getInput('role_map_' . $role_id));
				$role_settings[$role_id]['update'] = (bool) !$form->getInput('role_map_update_' . $role_id);
				$role_settings[$role_id]['value'] = (string) $form->getInput('role_map_' . $role_id);
			}

			$this->settings->setRoleMappings($role_settings);
			$this->settings->save();
			ilUtil::sendSuccess($this->lng->txt('settings_saved'),true);
			$this->ctrl->redirect($this, 'roles');
		}

		$form->setValuesByPost();
		\ilUtil::sendFailure($this->lng->txt('err_check_input'));
		$this->roles($form);
	}

	/**
	 * Set sub tabs
	 */
	protected function setSubTabs(string $active_tab)
	{
		$this->tabs->addSubTab(
			self::STAB_SETTINGS,
			$this->lng->txt('auth_oidc_' . self::STAB_SETTINGS),
			$this->ctrl->getLinkTarget($this,self::STAB_SETTINGS)
		);
		$this->tabs->addSubTab(
			self::STAB_PROFILE,
			$this->lng->txt('auth_oidc_' . self::STAB_PROFILE),
			$this->ctrl->getLinkTarget($this,self::STAB_PROFILE)
		);
		$this->tabs->addSubTab(
			self::STAB_ROLES,
			$this->lng->txt('auth_oidc_' . self::STAB_ROLES),
			$this->ctrl->getLinkTarget($this,self::STAB_ROLES)
		);

		$this->tabs->activateSubTab($active_tab);
	}

}