<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilTermsOfServiceCriterionFormGUI extends \ilPropertyFormGUI
{
	/** @var \ilTermsOfServiceDocument */
	protected $document;

	/** @var string */
	protected $formAction;

	/** @var string */
	protected $saveCommand;

	/** @var string */
	protected $cancelCommand;

	/** @var string */
	protected $translatedError = '';

	/**
	 * ilTermsOfServiceCriterionFormGUI constructor.
	 * @param \ilTermsOfServiceDocument $document
	 * @param string $formAction
	 * @param string $saveCommand
	 * @param string $cancelCommand
	 */
	public function __construct(
		\ilTermsOfServiceDocument $document,
		string $formAction = '',
		string $saveCommand = 'saveDocument',
		string $cancelCommand = 'showDocuments'
	) {
		$this->document = $document;
		$this->formAction = $formAction;
		$this->saveCommand = $saveCommand;
		$this->cancelCommand = $cancelCommand;

		parent::__construct();

		$this->initForm();
	}


	/**
	 *
	 */
	protected function initForm()
	{
		// TODO
		if (false) {
			$this->setTitle($this->lng->txt('tos_form_edit_criterion_head'));
		} else {
			$this->setTitle($this->lng->txt('tos_form_new_criterion_head'));
		}

		$this->setFormAction($this->formAction);

		$document = new \ilNonEditableValueGUI($this->lng->txt('tos_document'));
		$document->setValue($this->document->getTitle());
		$this->addItem($document);

		$criteria = new \ilRadioGroupInputGUI($this->lng->txt('tos_form_criterion'), 'criterion');
		$criteria->setInfo($this->lng->txt('tos_form_criterion_info'));
		$criteria->setRequired(true);
		$criteria->setValue(''); // TODO: Set current criterion id
		// TODO: Render possible criteria
		foreach ([] as $criterion) {
			$criterion_option = new \ilRadioOption(
				'Label',
				'POST_VAR'
			);
			$criterion::addCriterionSubItems($criterion_option);
			$criteria->addOption($criterion_option);
		}
		$this->addItem($criteria);

		$this->addCommandButton($this->saveCommand, $this->lng->txt('save'));
		$this->addCommandButton($this->cancelCommand, $this->lng->txt('cancel'));
	}

	/**
	 * @return bool
	 */
	public function hasTranslatedError(): bool
	{
		return strlen($this->translatedError);
	}

	/**
	 * @return string
	 */
	public function getTranslatedError(): string
	{
		return $this->translatedError;
	}

	/**
	 * @return bool
	 */
	public function saveObject() :bool
	{
		if (!$this->fillObject()) {
			$this->setValuesByPost();
			return false;
		}

		return true;
	}

	/**
	 *
	 */
	protected function fillObject(): bool
	{
		if (!$this->checkInput()) {
			return false;
		}

		return true;
	}
}