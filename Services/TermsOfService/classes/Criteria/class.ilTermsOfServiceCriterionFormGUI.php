<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceCriterionFormGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
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

		$criteriaSelection = new \ilRadioGroupInputGUI($this->lng->txt('tos_form_criterion'), 'criterion');
		$criteriaSelection->setInfo($this->lng->txt('tos_form_criterion_info'));
		$criteriaSelection->setRequired(true);
		$criteriaSelection->setValue($this->assignment->getCriterionId());

		// TODO: Read from factory (dependencies should be moved to the factory constructor)
		$criteria = [
			new ilTermsOfServiceUserHasLanguageCriterion(),
			new ilTermsOfServiceUserHasGlobalRoleCriterion(
				$GLOBALS['DIC']['rbacreview'],
				$GLOBALS['DIC']['ilObjDataCache']
			),
		];

		$first = true;
		foreach ($criteria as $criterion) {
			/** @var $criterion \ilTermsOfServiceCriterionType */
			if (!$this->assignment->getId() && $first) {
				$criteriaSelection->setValue($criterion->getTypeIdent());
			}
			$first = false;

			$criterionGui = $criterion->getGUI($this->lng);
			if ($this->assignment->getCriterionId() == $criterion->getTypeIdent()) {
				// TODO: Hide json_Decode, this is an impl. detail which should be somehow centralized
				$criterionGui->appendOption(
					$criteriaSelection, 
					json_decode($this->assignment->getCriterionValue(), true)
				);
			} else {
				$criterionGui->appendOption($criteriaSelection, []);
			}
		}
		$this->addItem($criteriaSelection);

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

		$uniqueAssignmentConstraint = new ilTermsOfServiceDocumentCriterionAssignmentConstraint(
			$this->document, new \ILIAS\Data\Factory()
		);

		if (!$uniqueAssignmentConstraint->accepts($this->assignment)) {
			$this->getItemByPostVar('criterion')->setAlert($this->lng->txt('tos_criterion_assignment_must_be_unique_insert'));
			if ($this->assignment->getId() > 0) {
				$this->getItemByPostVar('criterion')->setAlert($this->lng->txt('tos_criterion_assignment_must_be_unique_update'));
			}

			$this->translatedError = $this->lng->txt('form_input_not_valid');
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

		// TODO: Read criterion to use from factory by "criterion" field (dependencies should be moved to the factory constructor)
		$criteria = [
			new ilTermsOfServiceUserHasLanguageCriterion(),
			new ilTermsOfServiceUserHasGlobalRoleCriterion(
				$GLOBALS['DIC']['rbacreview'],
				$GLOBALS['DIC']['ilObjDataCache']
			),
		];

		// TODO: If the factory returns THE criterion, we do not need the loop
		foreach ($criteria as $criterion) {
			/** @var $criterion \ilTermsOfServiceCriterionType */
			if ($this->getInput('criterion') == $criterion->getTypeIdent()) {
				$criterionGui = $criterion->getGUI($this->lng);

				$this->assignment->setCriterionId($criterion->getTypeIdent());
				// TODO: Hide json_encode, this is an impl. detail which should be somehow centralized
				$this->assignment->setCriterionValue(json_encode($criterionGui->getConfigByForm($this)));
			}
		}

		if ($this->assignment->getId() > 0) {
			$this->assignment->setLastModifiedUsrId($this->user->getId());
		} else {
			$this->assignment->setOwnerUsrId($this->user->getId());
		}

		return true;
	}
}