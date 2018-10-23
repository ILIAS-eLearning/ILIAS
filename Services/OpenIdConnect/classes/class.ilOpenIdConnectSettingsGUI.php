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
	 * @var \ilErrorHandling
	 */
	protected $error = null;

	/**
	 * @var \ilTemplate|null
	 */
	protected $mainTemplate = null;

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
		$this->ctrl = $DIC->ctrl();
		$this->logger = $DIC->logger()->auth();

		$this->access = $DIC->access();
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
		$secret = new ilTextInputGUI(
			$this->lng->txt('auth_oidc_settings_secret'),
			'secret'
		);
		$secret->setRequired(true);
		$secret->setValue($this->settings->getSecret());
		$form->addItem($secret);

		if($this->checkAccessBool('write'))
		{
			// save button
			$form->addCommandButton('saveSettings', $this->lng->txt('save'));
		}

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
		$this->settings->setSecret((string) $form->getInput('secret'));
		$this->settings->save();

		ilUtil::sendSuccess($this->lng->txt('settings_saved'),true);
		$this->ctrl->redirect($this, 'settings');
	}
}