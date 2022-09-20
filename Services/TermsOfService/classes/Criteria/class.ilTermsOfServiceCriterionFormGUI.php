<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\Data\Factory;

/**
 * Class ilTermsOfServiceCriterionFormGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceCriterionFormGUI extends ilPropertyFormGUI
{
    protected string $translatedError = '';

    public function __construct(
        protected ilTermsOfServiceDocument $document,
        protected ilTermsOfServiceDocumentCriterionAssignment $assignment,
        protected ilTermsOfServiceCriterionTypeFactoryInterface $criterionTypeFactory,
        protected ilObjUser $actor,
        protected string $formAction = '',
        protected string $saveCommand = 'saveDocument',
        protected string $cancelCommand = 'showDocuments'
    ) {
        parent::__construct();

        $this->initForm();
    }

    public function setCheckInputCalled(bool $status): void
    {
        $this->check_input_called = $status;
    }

    protected function initForm(): void
    {
        if ($this->assignment->getId() > 0) {
            $this->setTitle($this->lng->txt('tos_form_edit_criterion_head'));
        } else {
            $this->setTitle($this->lng->txt('tos_form_attach_criterion_head'));
        }

        $this->setFormAction($this->formAction);

        $document = new ilNonEditableValueGUI($this->lng->txt('tos_document'));
        $document->setValue($this->document->getTitle());
        $this->addItem($document);

        $criteriaSelection = new ilRadioGroupInputGUI($this->lng->txt('tos_form_criterion'), 'criterion');
        $criteriaSelection->setRequired(true);
        $criteriaSelection->setValue($this->assignment->getCriterionId());

        $first = true;
        foreach ($this->criterionTypeFactory->getTypesByIdentMap() as $criterion) {
            /** @var ilTermsOfServiceCriterionType $criterion */
            if ($first && !$this->assignment->getId()) {
                $criteriaSelection->setValue($criterion->getTypeIdent());
            }
            $first = false;

            $criterionGui = $criterion->ui($this->lng);
            if ($this->assignment->getCriterionId() === $criterion->getTypeIdent()) {
                $criterionGui->appendOption(
                    $criteriaSelection,
                    $this->assignment->getCriterionValue()
                );
            } else {
                $criterionGui->appendOption($criteriaSelection, new ilTermsOfServiceCriterionConfig());
            }
        }
        $this->addItem($criteriaSelection);

        $this->addCommandButton($this->saveCommand, $this->lng->txt('save'));
        $this->addCommandButton($this->cancelCommand, $this->lng->txt('cancel'));
    }

    public function hasTranslatedError(): bool
    {
        return $this->translatedError !== '';
    }

    public function getTranslatedError(): string
    {
        return $this->translatedError;
    }

    public function saveObject(): bool
    {
        if (!$this->fillObject()) {
            $this->setValuesByPost();
            return false;
        }

        $uniqueAssignmentConstraint = new ilTermsOfServiceDocumentCriterionAssignmentConstraint(
            $this->criterionTypeFactory,
            $this->document,
            new Factory(),
            $this->lng
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

    protected function fillObject(): bool
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
