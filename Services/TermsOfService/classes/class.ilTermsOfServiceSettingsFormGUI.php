<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceSettingsFormGUI
 */
class ilTermsOfServiceSettingsFormGUI extends \ilPropertyFormGUI
{
	/**
	 * @var \ilObjTermsOfService
	 */
	protected $tos;

	/**
	 * @var \ilLanguage
	 */
	protected $lng;

	/**
	 * @var string
	 */
	protected $saveCommand;

	/**
	 * @var $bool
	 */
	protected $isEditable;

	/**
	 * @var string
	 */
	protected $translatedError = '';

	/**
	 * ilTermsOfServiceSettingsForm constructor.
	 * @param ilObjTermsOfService $tos
	 * @param ilLanguage $lng
	 * @param string $saveCommand
	 * @param bool $isEditable
	 */
	public function __construct(
		\ilObjTermsOfService $tos,
		\ilLanguage $lng,
		$saveCommand = 'saveSettings',
		$isEditable = false
	) {
		$this->tos = $tos;
		$this->lng = $lng;
		$this->saveCommand = $saveCommand;
		$this->isEditable = $isEditable;

		parent::__construct();

		$this->initForm();
	}

	/**
	 * 
	 */
	protected function initForm()
	{
		$this->setTitle($this->lng->txt('tos_tos_settings'));

		$status = new \ilCheckboxInputGUI($this->lng->txt('tos_status_enable'), 'tos_status');
		$status->setValue(1);
		$status->setChecked((bool)$this->tos->getStatus());
		$status->setInfo($this->lng->txt('tos_status_desc'));
		$status->setDisabled(!$this->isEditable);
		$this->addItem($status);

		if ($this->isEditable) {
			$this->addCommandButton($this->saveCommand, $this->lng->txt('save'));
		}
	}

	/**
	 * @return bool
	 */
	public function hasTranslatedError()
	{
		return strlen($this->translatedError);
	}

	/**
	 * @return string
	 */
	public function getTranslatedError()
	{
		return $this->translatedError;
	}

	/**
	 * @return bool
	 */
	public function saveObject()
	{
		if (!$this->fillObject()) {
			$this->setValuesByPost();
			return false;
		}

		// TODO: Count total documents
		$hasDocuments = true;
		if ($hasDocuments || !(int)$this->getInput('tos_status')) {
			$this->tos->saveStatus((int)$this->getInput('tos_status'));

			return true;
		}

		if (!$hasDocuments && (int)$this->getInput('tos_status') && !$this->tos->getStatus()) {
			$this->translatedError = $this->lng->txt('tos_no_documents_exist_cant_save');
			$this->setValuesByPost();
			return false;
		}

		return true;
	}

	/**
	 *
	 */
	protected function fillObject()
	{
		if (!$this->checkInput()) {
			return false;
		}

		return true;
	}
}