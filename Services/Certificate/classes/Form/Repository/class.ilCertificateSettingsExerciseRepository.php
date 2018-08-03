<?php


class ilCertificateSettingsExerciseRepository implements ilCertificateFormRepository
{
	/**
	 * @var ilLanguage
	 */
	private $language;

	/**
	 * @var ilCertificateSettingsFormRepository
	 */
	private $settingsFromFactory;

	/**
	 * @param ilLanguage $language
	 * @param ilTemplate $template
	 * @param ilCtrl $controller
	 * @param ilAccess $access
	 * @param ilToolbarGUI $toolbar
	 * @param ilCertificatePlaceholderDescription $placeholderDescriptionObject
	 * @param ilCertificateDeleteAction $deleteAction
	 */
	public function __construct(
		ilLanguage $language,
		ilTemplate $template,
		ilCtrl $controller,
		ilAccess $access,
		ilToolbarGUI $toolbar,
		ilCertificatePlaceholderDescription $placeholderDescriptionObject
	)
	{
		$this->language = $language;

		$this->settingsFromFactory = new ilCertificateSettingsFormRepository(
			$language,
			$template,
			$controller,
			$access,
			$toolbar,
			$placeholderDescriptionObject
		);
	}

	/**
	 * @param ilCertificateGUI $certificateGUI
	 * @param ilCertificate $certificateObject
	 *
	 * @return ilPropertyFormGUI
	 *
	 * @throws ilException
	 * @throws ilWACException
	 */
	public function createForm(ilCertificateGUI $certificateGUI, ilCertificate $certificateObject)
	{
		$form = $this->settingsFromFactory->createForm($certificateGUI, $certificateObject);

		$visibility = new ilRadioGroupInputGUI($this->language->txt('certificate_visibility'), 'certificate_visibility');
		$visibility->addOption(new ilRadioOption($this->language->txt('certificate_visibility_always'), 0));
		$visibility->addOption(new ilRadioOption($this->language->txt('certificate_visibility_passed_exercise'), 1));
		$visibility->addOption(new ilRadioOption($this->language->txt('certificate_visibility_never'), 2));

		$form->addItem($visibility);

		return $form;
	}

	/**
	 * @param array $formFields
	 */
	public function save(array $formFields)
	{
		$this->object->saveCertificateVisibility($formFields['certificate_visibility']);
	}

	/**
	 * @param $content
	 * @return array|mixed
	 */
	public function fetchFormFieldData($content)
	{
		$formFields = $this->settingsFromFactory->fetchFormFieldData($content);
		$formFields['certificate_visibility'] = $this->object->getCertificateVisibility();

		return $formFields;
	}
}
