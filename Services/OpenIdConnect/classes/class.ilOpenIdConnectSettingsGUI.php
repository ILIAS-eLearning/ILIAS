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
			$this->lng->txt('auth_oidc_settings_txt_val'),
			'le_text'
		);
		$text->setValue($this->settings->getLoginElemenText());
		$text->setMaxLength(120);
		$text->setInfo('auth_oidc_settings_txt_val_info');
		$text_option->addSubItem($text);

		// le -> type img
		$img_option = new ilRadioOption(
			$this->lng->txt('auth_oidc_settings_img'),
			ilOpenIdConnectSettings::LOGIN_ELEMENT_TYPE_IMG
		);
		$login_element->addOption($img_option);



		$image = new ilImageFileInputGUI(
			$this->lng->txt('auth_oidc_settings_img_file'),
			'le_img'
		);

		if($this->settings->hasImageFile())
		{
			$image->setImage($this->settings->getImageFilePath());
		}
		$image->setInfo('auth_oidc_settings_img_file');
		$img_option->addSubItem($image);

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
		$this->settings->setLoginElementType((int) $form->getInput('le'));
		$this->settings->setLoginElementText((string) $form->getInput('le_text'));

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
}