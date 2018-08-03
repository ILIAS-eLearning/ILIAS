<?php


class ilCertificateSettingsScormFormRepository implements ilCertificateFormRepository
{
	/**
	 * @var ilLanguage
	 */
	private $language;

	/**
	 * @var ilCertificateSettingsFormRepository
	 */
	private $settingsFromFactory;

	private $object;

	/**
	 * @param ilLanguage $language
	 * @param ilTemplate $template
	 * @param ilCtrl $controller
	 * @param ilAccess $access
	 * @param ilToolbarGUI $toolbar
	 * @param ilCertificatePlaceholderDescription $placeholderDescriptionObject
	 * @param ilCertificateGUI|null $certificateGUI
	 */
	public function __construct(
		ilLanguage $language,
		ilTemplate $template,
		ilCtrl $controller,
		ilAccess $access,
		ilToolbarGUI $toolbar,
		ilCertificatePlaceholderDescription $placeholderDescriptionObject,
		ilCertificateGUI $certificateGUI = null
	)
	{
		$this->language = $language;

		$this->settingsFromFactory = new ilCertificateSettingsFormRepository(
			$language,
			$template,
			$controller,
			$access,
			$toolbar,
			$placeholderDescriptionObject,
			$certificateGUI
		);
	}

	/**
	 * @param ilCertificateGUI $certificateGUI
	 * @param ilCertificate $certificateObject
	 * @throws ilException
	 * @throws ilWACException
	 */
	public function createForm(ilCertificateGUI $certificateGUI, ilCertificate $certificateObject)
	{
		$form = $this->settingsFromFactory->createForm($certificateGUI, $certificateObject);

		$short_name = new ilTextInputGUI($this->lng->txt('certificate_short_name'), 'short_name');
		$short_name->setRequired(true);
		$short_name->setValue(ilStr::subStr($this->object->getTitle(), 0, 30));
		$short_name->setSize(30);

		$infoText = $this->lng->txt('certificate_short_name_description');
		$short_name->setInfo($infoText);

		$form->addItem($short_name);

		return $form;
	}

	/**
	 * @param array $formFields
	 */
	public function save(array $formFields)
	{
		$scormSetting = new ilSetting('scorm');

		$scormSetting->set('certificate_' . $this->object->getId(), $formFields['certificate_enabled_scorm']);
		$scormSetting->set('certificate_short_name_' . $this->object->getId(), $formFields['short_name']);
	}
}
