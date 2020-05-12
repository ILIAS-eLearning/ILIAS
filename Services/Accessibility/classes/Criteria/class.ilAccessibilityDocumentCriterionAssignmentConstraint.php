<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Data\Factory;
use ILIAS\Refinery\Custom\Constraint;

/**
 * Class ilAccessibilityDocumentCriterionAssignmentConstraint
 */
class ilAccessibilityDocumentCriterionAssignmentConstraint extends Constraint
{
    /** @var ilAccessibilityCriterionTypeFactoryInterface */
    protected $criterionTypeFactory;

    /** @var ilAccessibilityDocument */
    protected $document;

    /**
     * ilAccessibilityDocumentCriterionAssignmentConstraint constructor.
     * @param ilAccessibilityCriterionTypeFactoryInterface $criterionTypeFactory
     * @param ilAccessibilityDocument                      $document
     * @param Factory                                       $dataFactory
     * @param ilLanguage                                    $lng
     */
    public function __construct(
        ilAccessibilityCriterionTypeFactoryInterface $criterionTypeFactory,
        ilAccessibilityDocument $document,
        Factory $dataFactory,
        ilLanguage $lng
    ) {
        $this->criterionTypeFactory = $criterionTypeFactory;
        $this->document = $document;

        parent::__construct(
            function (ilAccessibilityDocumentCriterionAssignment $value) {
                return 0 === count($this->filterEqualValues($value));
            },
            function ($txt, $value) {
                return "The passed assignment must be unique for the document!";
            },
            $dataFactory,
            $lng
        );
    }

    /**
     * @param ilAccessibilityDocumentCriterionAssignment $value
     * @return ilAccessibilityDocumentCriterionAssignment[]|ilAccessibilityEvaluableCriterion[]
     */
    protected function filterEqualValues(
        ilAccessibilityDocumentCriterionAssignment $value
    ) : array {
        $otherValues = $this->document->criteria();

        return array_filter(
            $otherValues,
            function (ilAccessibilityDocumentCriterionAssignment $otherValue) use ($value) {
                $idCurrent = $otherValue->getId();
                $idNew = $value->getId();

                $uniqueIdEquals = $idCurrent === $idNew;
                if ($uniqueIdEquals) {
                    return false;
                }

                $valuesEqual = $value->equals($otherValue);
                if ($valuesEqual) {
                    return true;
                }

                $valuesHaveSameNature = $this->haveSameNature($value, $otherValue);

                return $valuesHaveSameNature;
            }
        );
    }

    /**
     * @param ilAccessibilityDocumentCriterionAssignment $value
     * @param ilAccessibilityDocumentCriterionAssignment $otherValue
     * @return bool
     * @throws ilAccessibilityCriterionTypeNotFoundException
     */
    protected function haveSameNature(
        ilAccessibilityDocumentCriterionAssignment $value,
        ilAccessibilityDocumentCriterionAssignment $otherValue
    ) : bool {
        if ($value->getCriterionId() !== $otherValue->getCriterionId()) {
            return false;
        }

        $valuesHaveSameNature = $this->criterionTypeFactory->findByTypeIdent($value->getCriterionId())->hasUniqueNature();

        return $valuesHaveSameNature;
    }
}
