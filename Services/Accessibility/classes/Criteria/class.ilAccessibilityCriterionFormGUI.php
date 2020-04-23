<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Data\Factory;

/**
 * Class ilAccessibilityCriterionFormGUI
 */
class ilAccessibilityCriterionFormGUI extends ilPropertyFormGUI
{
    /** @var ilAccessibilityDocument */
    protected $document;

    /** @var ilAccessibilityDocumentCriterionAssignment */
    protected $assignment;

    /** @var string */
    protected $formAction;

    /** @var ilObjUser */
    protected $actor;

    /** @var string */
    protected $saveCommand;

    /** @var string */
    protected $cancelCommand;

    /** @var string */
    protected $translatedError = '';

    /** @var ilAccessibilityCriterionTypeFactoryInterface */
    protected $criterionTypeFactory;

    /**
     * ilAccessibilityCriterionFormGUI constructor.
     * @param ilAccessibilityDocument                      $document
     * @param ilAccessibilityDocumentCriterionAssignment   $assignment
     * @param ilAccessibilityCriterionTypeFactoryInterface $criterionTypeFactory
     * @param ilObjUser                                     $actor
     * @param string                                        $formAction
     * @param string                                        $saveCommand
     * @param string                                        $cancelCommand
     */
    public function __construct(
        ilAccessibilityDocument $document,
        ilAccessibilityDocumentCriterionAssignment $assignment,
        ilAccessibilityCriterionTypeFactoryInterface $criterionTypeFactory,
        ilObjUser $actor,
        string $formAction = '',
        string $saveCommand = 'saveDocument',
        string $cancelCommand = 'showDocuments'
    ) {
        $this->document = $document;
        $this->assignment = $assignment;
        $this->criterionTypeFactory = $criterionTypeFactory;
        $this->actor = $actor;
        $this->formAction = $formAction;
        $this->saveCommand = $saveCommand;
        $this->cancelCommand = $cancelCommand;

        parent::__construct();

        $this->initForm();
    }

    /**
     * @param bool $status
     */
    public function setCheckInputCalled(bool $status) : void
    {
        $this->check_input_called = $status;
    }

    /**
     *
     */
    protected function initForm() : void
    {
        if ($this->assignment->getId() > 0) {
            $this->setTitle($this->lng->txt('acc_form_edit_criterion_head'));
        } else {
            $this->setTitle($this->lng->txt('acc_form_attach_criterion_head'));
        }

        $this->setFormAction($this->formAction);

        $document = new ilNonEditableValueGUI($this->lng->txt('acc_document'));
        $document->setValue($this->document->getTitle());
        $this->addItem($document);


        if ($this->criterionTypeFactory->hasOnlyOneCriterion()) {
            $criteriaSelection = new ilHiddenInputGUI('criterion');
            $criteriaSelection->setRequired(true);
            $criteriaSelection->setValue($this->assignment->getCriterionId());

            foreach ($this->criterionTypeFactory->getTypesByIdentMap() as $criterion) {
                /** @var $criterion ilAccessibilityCriterionType */
                if (!$this->assignment->getId()) {
                    $criteriaSelection->setValue($criterion->getTypeIdent());
                }

                $criterionGui = $criterion->ui($this->lng);
                if ($this->assignment->getCriterionId() == $criterion->getTypeIdent()) {
                    $languageSelection = $criterionGui->getSelection($this->assignment->getCriterionValue());
                } else {
                    $languageSelection = $criterionGui->getSelection(new ilAccessibilityCriterionConfig());
                }
                $this->addItem($languageSelection);
            }
            $this->addItem($criteriaSelection);
        } else {
            $criteriaSelection = new ilRadioGroupInputGUI($this->lng->txt('acc_form_criterion'), 'criterion');
            $criteriaSelection->setRequired(true);
            $criteriaSelection->setValue($this->assignment->getCriterionId());

            $first = true;
            foreach ($this->criterionTypeFactory->getTypesByIdentMap() as $criterion) {
                /** @var $criterion ilAccessibilityCriterionType */
                if (!$this->assignment->getId() && $first) {
                    $criteriaSelection->setValue($criterion->getTypeIdent());
                }
                $first = false;

                $criterionGui = $criterion->ui($this->lng);
                if ($this->assignment->getCriterionId() == $criterion->getTypeIdent()) {
                    $criterionGui->appendOption(
                        $criteriaSelection,
                        $this->assignment->getCriterionValue()
                    );
                } else {
                    $criterionGui->appendOption($criteriaSelection, new ilAccessibilityCriterionConfig());
                }
            }
            $this->addItem($criteriaSelection);
        }

        $this->addCommandButton($this->saveCommand, $this->lng->txt('save'));
        $this->addCommandButton($this->cancelCommand, $this->lng->txt('cancel'));
    }

    /**
     * @return bool
     */
    public function hasTranslatedError() : bool
    {
        return strlen($this->translatedError) > 0;
    }

    /**
     * @return string
     */
    public function getTranslatedError() : string
    {
        return $this->translatedError;
    }

    /**
     * @return bool
     * @throws ilAccessibilityDuplicateCriterionAssignmentException
     */
    public function saveObject() : bool
    {
        if (!$this->fillObject()) {
            $this->setValuesByPost();
            return false;
        }

        $uniqueAssignmentConstraint = new ilAccessibilityDocumentCriterionAssignmentConstraint(
            $this->criterionTypeFactory,
            $this->document,
            new Factory(),
            $this->lng
        );

        if (!$uniqueAssignmentConstraint->accepts($this->assignment)) {
            $this->getItemByPostVar('criterion')->setAlert($this->lng->txt('acc_criterion_assignment_must_be_unique_insert'));
            if ($this->assignment->getId() > 0) {
                $this->getItemByPostVar('criterion')->setAlert($this->lng->txt('acc_criterion_assignment_must_be_unique_update'));
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
    protected function fillObject() : bool
    {
        if (!$this->checkInput()) {
            return false;
        }

        try {
            $criterionType = $this->criterionTypeFactory->findByTypeIdent($this->getInput('criterion'));
            $criterionGui = $criterionType->ui($this->lng);

            $this->assignment->setCriterionId($criterionType->getTypeIdent());
            $this->assignment->setCriterionValue($criterionGui->getConfigByForm($this));

            if ($this->assignment->getId() > 0) {
                $this->assignment->setLastModifiedUsrId($this->actor->getId());
            } else {
                $this->assignment->setOwnerUsrId($this->actor->getId());
            }
        } catch (Exception $e) {
            $this->getItemByPostVar('criterion')->setAlert($e->getMessage());
            $this->translatedError = $this->lng->txt('form_input_not_valid');
            return false;
        }

        return true;
    }
}
