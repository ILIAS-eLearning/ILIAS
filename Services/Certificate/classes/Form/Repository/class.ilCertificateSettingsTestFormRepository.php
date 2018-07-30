<?php


class ilCertificateSettingsTestFormRepository implements ilCertificateFormRepository
{
	/**
	 * @var ilCertificateSettingsFormRepository
	 */
	private $settingsFromFactory;

	/**
	 * @var ilLanguage
	 */
	private $language;

	/**
	 * @var ilObjTest
	 */
	private $testObject;

	/**
	 * @param ilObjTest $testObject
	 * @param ilLanguage $language
	 * @param ilTemplate $template
	 * @param ilCtrl $controller
	 * @param ilAccess $access
	 * @param ilToolbarGUI $toolbar
	 * @param ilCertificatePlaceholderDescription $placeholderDescriptionObject
	 * @param ilCertificateGUI|null $certificateGUI
	 */
	public function __construct(
		ilObjTest $testObject,
		ilLanguage $language,
		ilTemplate $template,
		ilCtrl $controller,
		ilAccess $access,
		ilToolbarGUI $toolbar,
		ilCertificatePlaceholderDescription $placeholderDescriptionObject,
		ilCertificateGUI $certificateGUI = null
	) {
		$this->testObject = $testObject;
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
	 * @return ilPropertyFormGUI
	 * @throws ilException
	 * @throws ilWACException
	 */
	public function createForm(ilCertificateGUI $certificateGUI, ilCertificate $certificateObject)
	{
		$form = $this->settingsFromFactory->createForm($certificateGUI, $certificateObject);

		$visibility = new ilRadioGroupInputGUI($this->language->txt('certificate_visibility'), 'certificate_visibility');
		$visibility->addOption(new ilRadioOption($this->language->txt('certificate_visibility_always'), 0));
		$visibility->addOption(new ilRadioOption($this->language->txt('certificate_visibility_passed'), 1));
		$visibility->addOption(new ilRadioOption($this->language->txt('certificate_visibility_never'), 2));
		$visibility->setInfo($this->language->txt('certificate_visibility_introduction'));

		$form->addItem($visibility);

		return $form;
	}

	/**
	 * @param array $formFields
	 */
	public function save(array $formFields)
	{
		$this->testObject->saveCertificateVisibility($formFields['certificate_visibility']);
	}

	public function fetchFormFieldData($content)
	{
		$formFields = $this->settingsFromFactory->fetchFormFieldData($content);
		$formFields['certificate_visibility'] = $this->testObject->getCertificateVisibility();

		return $formFields;
	}
}
