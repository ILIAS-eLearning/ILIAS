<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilTermsOfServiceCriterionFormGUI extends \ilPropertyFormGUI
{
	/** @var \ilTermsOfServiceDocument */
	protected $document;
	
	/** @var \ilTermsOfServiceDocumentCriterionAssignment */
	protected $assignment;

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
	 * @param \ilTermsOfServiceDocumentCriterionAssignment $assignment
	 * @param string $formAction
	 * @param string $saveCommand
	 * @param string $cancelCommand
	 */
	public function __construct(
		\ilTermsOfServiceDocument $document,
		\ilTermsOfServiceDocumentCriterionAssignment $assignment,
		string $formAction = '',
		string $saveCommand = 'saveDocument',
		string $cancelCommand = 'showDocuments'
	) {
		$this->document = $document;
		$this->assignment = $assignment;
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
		if ($this->assignment->getId() > 0) {
			$this->setTitle($this->lng->txt('tos_form_edit_criterion_head'));
		} else {
			$this->setTitle($this->lng->txt('tos_form_attach_criterion_head'));
		}

		$this->setFormAction($this->formAction);

		$document = new \ilNonEditableValueGUI($this->lng->txt('tos_document'));
		$document->setValue($this->document->getTitle());
		$this->addItem($document);

		$criteria = new \ilRadioGroupInputGUI($this->lng->txt('tos_form_criterion'), 'criterion');
		$criteria->setInfo($this->lng->txt('tos_form_criterion_info'));
		// TODO: Make required if criteria definitions concept is implemented
		//$criteria->setRequired(true);
		$criteria->setValue($this->assignment->getCriterionId());
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

		if (!$this->assignment->getId()) {
			$this->document->attachCriterion($this->assignment);
		}
		$this->document->save();

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

		// TODO: Fill with form values
		if (rand(0, 1)) {
			$this->assignment->setCriterionId('language');
			$this->assignment->setCriterionValue('de');
		} else {
			$this->assignment->setCriterionId('global_role');
			$this->assignment->setCriterionValue('4');
		}

		if ($this->assignment->getId() > 0) {
			$this->assignment->setLastModifiedUsrId($this->user->getId());
		} else {
			$this->assignment->setOwnerUsrId($this->user->getId());
		}

		return true;
	}
}