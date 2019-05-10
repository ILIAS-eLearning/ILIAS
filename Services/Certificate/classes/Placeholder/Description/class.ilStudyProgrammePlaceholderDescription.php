<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilStudyProgrammePlaceholderDescription implements ilCertificatePlaceholderDescription
{
	/**
	 * @var ilDefaultPlaceholderDescription
	 */
	private $defaultPlaceHolderDescriptionObject;

	/**
	 * @var ilLanguage|null
	 */
	private $language;

	/**
	 * @var array
	 */
	private $placeholder;

	/**
	 * @param ilDefaultPlaceholderDescription|null $defaultPlaceholderDescriptionObject
	 * @param ilLanguage|null $language
	 * @param ilUserDefinedFieldsPlaceholderDescription|null $userDefinedFieldPlaceHolderDescriptionObject
	 */
	public function __construct(
		ilDefaultPlaceholderDescription $defaultPlaceholderDescriptionObject = null,
		ilLanguage $language = null,
		ilUserDefinedFieldsPlaceholderDescription $userDefinedFieldPlaceHolderDescriptionObject = null
	) {
		global $DIC;

		if (null === $language) {
			$language = $DIC->language();
		}
		$this->language = $language;

		if (null === $defaultPlaceholderDescriptionObject) {
			$defaultPlaceholderDescriptionObject = new ilDefaultPlaceholderDescription($language, $userDefinedFieldPlaceHolderDescriptionObject);
		}
		$this->defaultPlaceHolderDescriptionObject = $defaultPlaceholderDescriptionObject;

		$this->placeholder = $this->defaultPlaceHolderDescriptionObject->getPlaceholderDescriptions();
		$this->placeholder['SP_TITLE'] = $this->language->txt('sp_certificate_title');
		$this->placeholder['SP_DESCRIPTION'] = $this->language->txt('sp_certificate_description');
		$this->placeholder['SP_TYPE'] = $this->language->txt('sp_certificate_type');
		$this->placeholder['POINTS'] = $this->language->txt('sp_certificate_points');
		$this->placeholder['COMPLETION_DATE'] = $this->language->txt('sp_certificate_completion_date');
		$this->placeholder['EXPIRES_AT'] = $this->language->txt('sp_certificate_progress_expires_at');
	}


	/**
	 * This methods MUST return an array containing an array with
	 * the the description as array value.
	 *
	 * @param null $template
	 * @return mixed - [PLACEHOLDER] => 'description'
	 */
	public function createPlaceholderHtmlDescription(ilTemplate $template = null) : string
	{
		if (null === $template) {
			$template = new ilTemplate('tpl.default_description.html', true, true, 'Services/Certificate');
		}

		$template->setVariable("PLACEHOLDER_INTRODUCTION", $this->language->txt('certificate_ph_introduction'));

		$template->setCurrentBlock("items");
		foreach($this->placeholder as $id => $caption)
		{
			$template->setVariable("ID", $id);
			$template->setVariable("TXT", $caption);
			$template->parseCurrentBlock();
		}

		return $template->get();
	}

	/**
	 * This method MUST return an array containing an array with
	 * the the description as array value.
	 *
	 * @return mixed - [PLACEHOLDER] => 'description'
	 */
	public function getPlaceholderDescriptions() : array
	{
		return $this->placeholder;
	}
}
