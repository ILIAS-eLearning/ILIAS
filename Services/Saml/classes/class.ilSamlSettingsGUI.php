<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Saml/classes/class.ilSamlSettings.php';

/**
 * Class ilSamlSettingsGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilSamlSettingsGUI
{
	const VIEW_MODE_GLOBAL = 1;
	const VIEW_MODE_SINGLE = 2;

	const DEFAULT_CMD = 'listIdps';

	/**
	 * @var array
	 */
	protected static $globalCommands = array(
		'showAddIdpForm', self::DEFAULT_CMD, 'showSettings', 'saveSettings'
	);

	/**
	 * @var array
	 */
	protected static $ignoredUserFields = array(
		'mail_incoming_mail', 'preferences', 'hide_own_online_status',
		'show_users_online', 'hits_per_page',
		'roles', 'upload', 'password',
		'username', 'language', 'skin_style',
		'interests_general', 'interests_help_offered', 'interests_help_looking',
		'bs_allow_to_contact_me', 'chat_osc_accept_msg'
	);

	/**
	 * @var int
	 */
	protected $ref_id;

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
	 * @var ilAccessHandler
	 */
	protected $access;

	/**
	 * @var ilErrorHandling
	 */
	protected $error_handler;

	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;

	/**
	 * @var ilRbacReview
	 */
	protected $rbacreview;

	/**
	 * @var \ilToolbarGUI
	 */
	protected $toolbar;

	/**
	 * @var ilSamlAttributeMapping
	 */
	protected $mapping;

	/**
	 * @var ilSamlIdp
	 */
	protected $idp;

	/**
	 * ilSamlSettingsGUI constructor.
	 * @param int $ref_id
	 */
	public function __construct($ref_id)
	{
		global $DIC;

		$this->ctrl          = $DIC->ctrl();
		$this->tpl           = $DIC->ui()->mainTemplate();
		$this->lng           = $DIC->language();
		$this->access        = $DIC->access();
		$this->error_handler = $DIC['ilErr'];
		$this->tabs          = $DIC->tabs();
		$this->rbacreview    = $DIC->rbac()->review();
		$this->toolbar       = $DIC['ilToolbar'];

		$this->lng->loadLanguageModule('auth');
		$this->ref_id = $ref_id;
	}

	/**
	 * @param string $operation
	 */
	protected function ensureAccess($operation)
	{
		if(!$this->access->checkAccess($operation, '', $this->getRefId()))
		{
			$this->error_handler->raiseError($this->lng->txt('msg_no_perm_read'), $this->error_handler->WARNING);
		}
	}

	/**
	 *
	 */
	protected function ensureWriteAccess()
	{
		$this->ensureAccess('write');
	}

	/**
	 *
	 */
	protected function ensureReadAccess()
	{
		$this->ensureAccess('read');
	}

	/**
	 * @return int
	 */
	public function getRefId()
	{
		return $this->ref_id;
	}

	/**
	 * @param int $ref_id
	 */
	public function setRefId($ref_id)
	{
		$this->ref_id = $ref_id;
	}

	/**
	 * 
	 */
	protected function initIdp()
	{
		require_once 'Services/Saml/classes/class.ilSamlIdp.php';
		$this->idp = ilSamlIdp::getInstanceByIdpId((int)$_REQUEST['saml_idp_id']);
	}

	/**
	 *
	 */
	public function executeCommand()
	{
		$this->ensureReadAccess();

		switch($this->ctrl->getNextClass())
		{
			default:
				$cmd = $this->ctrl->getCmd();
				if(!strlen($cmd) || !method_exists($this, $cmd))
				{
					$cmd = self::DEFAULT_CMD;
				}

				if(isset($_REQUEST['saml_idp_id']))
				{
					$this->ctrl->saveParameter($this, 'saml_idp_id');
				}

				if(!in_array(strtolower($cmd), array_map('strtolower', self::$globalCommands)))
				{
					if(!isset($_REQUEST['saml_idp_id']))
					{
						$this->ctrl->redirect($this, self::DEFAULT_CMD);
					}

					$this->initIdp();
					$this->initUserAttributeMapping();
				}

				if(in_array(strtolower($cmd), array_map('strtolower', (self::$globalCommands + array('deactivateIdp', 'activateIdp')))))
				{
					$this->setSubTabs(self::VIEW_MODE_GLOBAL);
				}
				else
				{
					$this->setSubTabs(self::VIEW_MODE_SINGLE);
				}

				$this->$cmd();
				break;
		}
	}

	/**
	 * 
	 */
	protected function listIdps()
	{
		require_once 'Services/Saml/classes/class.ilSamlIdpTableGUI.php';
		$table = new ilSamlIdpTableGUI($this, self::DEFAULT_CMD);
		$this->tpl->setContent($table->getHTML());
		return;
	}

	/**
	 *
	 */
	protected function deactivateIdp()
	{
		$this->ensureWriteAccess();

		$this->idp->setActive(0);
		$this->idp->persist();

		ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
		$this->listIdps();
	}

	/**
	 * 
	 */
	protected function activateIdp()
	{
		$this->ensureWriteAccess();

		$this->idp->setActive(1);
		$this->idp->persist();

		ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
		$this->listIdps();
	}

	/**
	 * @param int $a_view_mode
	 */
	protected function setSubTabs($a_view_mode)
	{
		switch($a_view_mode)
		{
			case self::VIEW_MODE_GLOBAL:
				$this->tabs->addSubTabTarget(
					'auth_saml_idps',
					$this->ctrl->getLinkTarget($this, self::DEFAULT_CMD),
					array(self::DEFAULT_CMD, 'activateIdp', 'deactivateIdp'), get_class($this)
				);

				$this->tabs->addSubTabTarget(
					'settings',
					$this->ctrl->getLinkTarget($this, 'showSettings'),
					array('showSettings', 'saveSettings'), get_class($this)
				);

				break;

			case self::VIEW_MODE_SINGLE:
				$this->tabs->clearTargets();
				$this->tabs->setBackTarget($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, self::DEFAULT_CMD));

				$this->tabs->addSubTabTarget(
					'auth_saml_idp_settings',
					$this->ctrl->getLinkTarget($this, 'showIdpSettings'),
					array('showIdpSettings', 'saveIdpSettings'), get_class($this)
				);

				$this->tabs->addSubTabTarget(
					'auth_saml_user_mapping',
					$this->ctrl->getLinkTarget($this, 'showUserAttributeMappingForm'),
					array('showUserAttributeMappingForm', 'saveUserAttributeMapping'), get_class($this)
				);
				break;
		}
	}

	/**
	 *
	 */
	private function initUserAttributeMapping()
	{
		// An idp_id should be passed from request (and saved for links and forms) if we support multiple idps
		require_once 'Services/Saml/classes/class.ilSamlAttributeMapping.php';
		$this->mapping = ilSamlAttributeMapping::getInstanceByIdpId($this->idp->getIdpId());
	}

	/**
	 * @return ilPropertyFormGUI
	 */
	protected function getUserAttributeMappingForm()
	{
		require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this, 'saveUserAttributeMapping'));
		$form->setTitle($this->lng->txt('auth_saml_user_mapping'));

		require_once 'Services/User/classes/class.ilUserProfile.php';
		$usr_profile = new ilUserProfile();
		foreach($usr_profile->getStandardFields() as $id => $definition)
		{
			if(in_array($id, self::$ignoredUserFields))
			{
				continue;
			}

			if('instant_messengers' == $id)
			{
				foreach($definition['types'] as $type)
				{
					$this->addAttributeRuleFieldToForm($form, $this->lng->txt('im_' . $type), 'im_' . $type);
				}
				continue;
			}

			$this->addAttributeRuleFieldToForm($form, $this->lng->txt($id), $id);
		}

		require_once 'Services/User/classes/class.ilUserDefinedFields.php';
		foreach(ilUserDefinedFields::_getInstance()->getDefinitions() as $definition)
		{
			$this->addAttributeRuleFieldToForm($form, $definition['field_name'], 'udf_' . $definition['field_id']);
		}

		if(!$this->access->checkAccess('write', '', $this->getRefId()))
		{
			foreach($form->getItems() as $item)
			{
				$item->setDisabled(true);
			}
		}
		else
		{
			$form->addCommandButton('saveUserAttributeMapping', $this->lng->txt('save'));
		}

		return $form;
	}

	/**
	 * @param ilPropertyFormGUI $form
	 * @param string $field_label
	 * @param string $field_name
	 */
	protected function addAttributeRuleFieldToForm($form, $field_label, $field_name)
	{
		$field = new ilTextInputGUI($field_label, $field_name);
		$form->addItem($field);

		$update_automatically = new ilCheckboxInputGUI('', $field_name . '_update');
		$update_automatically->setOptionTitle($this->lng->txt('auth_saml_update_field_info'));
		$update_automatically->setValue(1);
		$form->addItem($update_automatically);
	}

	/**
	 * 
	 */
	protected function saveUserAttributeMapping()
	{
		$this->ensureWriteAccess();

		$form = $this->getUserAttributeMappingForm();
		if($form->checkInput())
		{
			$this->mapping->delete();

			require_once 'Services/User/classes/class.ilUserProfile.php';
			$usr_profile = new ilUserProfile();
			foreach($usr_profile->getStandardFields() as $id => $definition)
			{
				if(in_array($id, self::$ignoredUserFields))
				{
					continue;
				}

				if('instant_messengers' == $id)
				{
					foreach($definition['types'] as $type)
					{
						$rule = $this->mapping->getEmptyRule();
						$rule->setAttribute('im_' . $type);
						$rule->setIdpAttribute($form->getInput($rule->getAttribute()));
						$rule->updateAutomatically((bool)$form->getInput($rule->getAttribute() . '_update'));
						$this->mapping[$rule->getAttribute()] = $rule;
					}
					continue;
				}

				$rule = $this->mapping->getEmptyRule();
				$rule->setAttribute($id);
				$rule->setIdpAttribute($form->getInput($rule->getAttribute()));
				$rule->updateAutomatically((bool)$form->getInput($rule->getAttribute() . '_update'));
				$this->mapping[$rule->getAttribute()] = $rule;
			}

			require_once 'Services/User/classes/class.ilUserDefinedFields.php';
			foreach(ilUserDefinedFields::_getInstance()->getDefinitions() as $definition)
			{
				$rule = $this->mapping->getEmptyRule();
				$rule->setAttribute('udf_' . $definition['field_id']);
				$rule->setIdpAttribute($form->getInput($rule->getAttribute()));
				$rule->updateAutomatically((bool)$form->getInput($rule->getAttribute() . '_update'));
				$this->mapping[$rule->getAttribute()] = $rule;
			}

			$this->mapping->save();

			ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
		}

		$form->setValuesByPost();

		$this->showUserAttributeMappingForm($form);
	}

	/**
	 * @param ilPropertyFormGUI|null $form
	 */
	protected function showUserAttributeMappingForm(ilPropertyFormGUI $form = null)
	{
		$this->tabs->setSubTabActive('auth_saml_user_mapping');

		if(!($form instanceof ilPropertyFormGUI))
		{
			$form = $this->getUserAttributeMappingForm();
			$data = array();
			foreach($this->mapping as $rule)
			{
				$data[$rule->getAttribute()]             = $rule->getIdpAttribute();
				$data[$rule->getAttribute() . '_update'] = (bool)$rule->isAutomaticallyUpdated();
			}
			$form->setValuesByArray($data);
		}

		$this->tpl->setContent($form->getHTML());
	}

	/**
	 * @return ilPropertyFormGUI
	 */
	protected function getSettingsForm()
	{
		require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this, 'saveSettings'));
		$form->setTitle($this->lng->txt('auth_saml_configure'));

		$show_login_form = new ilCheckboxInputGUI($this->lng->txt('auth_saml_login_form'), 'login_form');
		$show_login_form->setInfo($this->lng->txt('auth_saml_login_form_info'));
		$show_login_form->setValue(1);
		$form->addItem($show_login_form);

		if(!$this->access->checkAccess('write', '', $this->getRefId()))
		{
			foreach($form->getItems() as $item)
			{
				$item->setDisabled(true);
			}
		}
		else
		{
			$form->addCommandButton('saveSettings', $this->lng->txt('save'));
		}

		return $form;
	}

	/**
	 * 
	 */
	protected function prepareRoleSelection()
	{
		$global_roles = ilUtil::_sortIds(
			$this->rbacreview->getGlobalRoles(),
			'object_data',
			'title',
			'obj_id'
		);

		$select[0] = $this->lng->txt('links_select_one');
		foreach($global_roles as $role_id)
		{
			$select[$role_id] = ilObject::_lookupTitle($role_id);
		}

		return $select;
	}

	/**
	 * 
	 */
	protected function saveSettings()
	{
		$this->ensureWriteAccess();

		$form = $this->getSettingsForm();
		if($form->checkInput())
		{
			ilSamlSettings::getInstance()->setLoginFormStatus((bool)$form->getInput('login_form'));
			ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
		}

		$form->setValuesByPost();

		$this->showSettings($form);
	}

	/**
	 * @param ilPropertyFormGUI|null $form
	 */
	protected function showSettings(ilPropertyFormGUI $form = null)
	{
		if(!($form instanceof ilPropertyFormGUI))
		{
			$form = $this->getSettingsForm();
			$form->setValuesByArray(array(
				'login_form'       => ilSamlSettings::getInstance()->isDisplayedOnLoginPage()
			));
		}

		$this->tpl->setContent($form->getHTML());
	}

	/**
	 * @return ilPropertyFormGUI
	 */
	protected function getIdpSettingsForm()
	{
		require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this, 'saveIdpSettings'));
		$form->setTitle(sprintf($this->lng->txt('auth_saml_configure_idp'), $this->idp->getAuthId()));

		$idp = new ilTextInputGUI($this->lng->txt('auth_saml_idp'), 'idp');
		$idp->setDisabled(true);
		$form->addItem($idp);

		$local = new ilCheckboxInputGUI($this->lng->txt('auth_allow_local'), 'allow_local_auth');
		$local->setValue(1);
		$local->setInfo($this->lng->txt('auth_allow_local_info'));
		$form->addItem($local);

		$uid_claim = new ilTextInputGUI($this->lng->txt('auth_saml_uid_claim'), 'uid_claim');
		$uid_claim->setInfo($this->lng->txt('auth_saml_uid_claim_info'));
		$uid_claim->setRequired(true);
		$form->addItem($uid_claim);

		$sync = new ilCheckboxInputGUI($this->lng->txt('auth_saml_sync'), 'sync_status');
		$sync->setInfo($this->lng->txt('auth_saml_sync_info'));
		$sync->setValue(1);

		$username_claim = new ilTextInputGUI($this->lng->txt('auth_saml_username_claim'), 'login_claim');
		$username_claim->setInfo($this->lng->txt('auth_saml_username_claim_info'));
		$username_claim->setRequired(true);
		$sync->addSubItem($username_claim);

		$role = new ilSelectInputGUI($this->lng->txt('auth_saml_role_select'), 'default_role_id');
		$role->setOptions($this->prepareRoleSelection());
		$role->setRequired(true);
		$sync->addSubItem($role);

		$migr = new ilCheckboxInputGUI($this->lng->txt('auth_saml_migration'), 'account_migr_status');
		$migr->setInfo($this->lng->txt('auth_saml_migration_info'));
		$migr->setValue(1);
		$sync->addSubItem($migr);
		$form->addItem($sync);

		if(!$this->access->checkAccess('write', '', $this->getRefId()))
		{
			foreach($form->getItems() as $item)
			{
				$item->setDisabled(true);
			}
		}
		else
		{
			$form->addCommandButton('saveIdpSettings', $this->lng->txt('save'));
		}
		$form->addCommandButton(self::DEFAULT_CMD, $this->lng->txt('cancel'));

		return $form;
	}

	/**
	 * @param ilPropertyFormGUI|null $form
	 */
	protected function showIdpSettings(ilPropertyFormGUI $form = null)
	{
		$this->tabs->setSubTabActive('auth_saml_idp_settings');

		if(!($form instanceof ilPropertyFormGUI))
		{
			$form = $this->getIdpSettingsForm();
			$form->setValuesByArray($this->idp->toArray());
		}

		$this->tpl->setContent($form->getHTML());
	}
	
	protected function saveIdpSettings()
	{
		$this->ensureWriteAccess();

		$form = $this->getIdpSettingsForm();
		if($form->checkInput())
		{
			$this->idp->bindForm($form);
			$this->idp->persist();
			ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
		}

		$form->setValuesByPost();

		$this->showIdpSettings($form);
	}
}