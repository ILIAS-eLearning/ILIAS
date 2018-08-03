<?php


class TestPlaceholderDescription implements ilCertificatePlaceholderDescription
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
	private $placeHolders;

	/**
	 * @param ilDefaultPlaceholderDescription|null $defaultPlaceholderDescriptionObject
	 * @param ilLanguage|null $language
	 */
	public function __construct(ilDefaultPlaceholderDescription $defaultPlaceholderDescriptionObject = null, ilLanguage $language = null)
	{
		global $DIC;

		if (null === $language) {
			$language = $DIC->language();
		}
		$this->language = $language;

		if (null === $defaultPlaceholderDescriptionObject) {
			$defaultPlaceholderDescriptionObject = new ilDefaultPlaceholderDescription($language);
		}
		$this->defaultPlaceHolderDescriptionObject = $defaultPlaceholderDescriptionObject;

		$this->placeHolders = $this->defaultPlaceHolderDescriptionObject->getPlaceholderDescriptions();

		$this->placeHolder['RESULT_PASSED']     = ilUtil::prepareFormOutput($this->language->txt('certificate_var_result_passed'));
		$this->placeHolder['RESULT_POINTS']     = ilUtil::prepareFormOutput($this->language->txt('certificate_var_result_points'));
		$this->placeHolder['RESULT_PERCENT']    = ilUtil::prepareFormOutput($this->language->txt('certificate_var_result_percent'));
		$this->placeHolder['MAX_POINTS']        = ilUtil::prepareFormOutput($this->language->txt('certificate_var_max_points'));
		$this->placeHolder['RESULT_MARK_SHORT'] = ilUtil::prepareFormOutput($this->language->txt('certificate_var_result_mark_short'));
		$this->placeHolder['RESULT_MARK_LONG']  = ilUtil::prepareFormOutput($this->language->txt('certificate_var_result_mark_long'));
		$this->placeHolder['TEST_TITLE']        = ilUtil::prepareFormOutput($this->language->txt('certificate_var_title'));
	}


	/**
	 * This methods MUST return an array containing an array with
	 * the the description as array value.
	 *
	 * @return mixed - [PLACEHOLDER] => 'description'
	 */
	public function createPlaceholderHtmlDescription()
	{
		$template = new ilTemplate('tpl.default_description.html', true, true, 'Services/Certificates');

		$template->setVariable('PLACEHOLDER_INTRODUCTION', $this->language->txt('certificate_ph_introduction'));

		$template->setCurrentBlock('items');
		foreach($this->placeholder as $id => $caption)
		{
			$template->setVariable('ID', $id);
			$template->setVariable('TXT', $caption);
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
	public function getPlaceholderDescriptions()
	{
		return $this->placeHolders;
	}
}
