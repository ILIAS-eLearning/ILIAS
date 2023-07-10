<?php

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
use ILIAS\Refinery\Custom\Constraint;

/**
 * Class ilAccessibilityDocumentCriterionAssignmentConstraint
 */
class ilAccessibilityDocumentCriterionAssignmentConstraint extends Constraint
{
    protected ilAccessibilityCriterionTypeFactoryInterface $criterionTypeFactory;
    protected ilAccessibilityDocument $document;

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
     * @return ilAccessibilityDocumentCriterionAssignment[]|ilAccessibilityEvaluableCriterion[]
     * @throws ilAccessibilityCriterionTypeNotFoundException
     */
    protected function filterEqualValues(
        ilAccessibilityDocumentCriterionAssignment $value
    ): array {
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
     * @throws ilAccessibilityCriterionTypeNotFoundException
     */
    protected function haveSameNature(
        ilAccessibilityDocumentCriterionAssignment $value,
        ilAccessibilityDocumentCriterionAssignment $otherValue
    ): bool {
        if ($value->getCriterionId() !== $otherValue->getCriterionId()) {
            return false;
        }

        $valuesHaveSameNature = $this->criterionTypeFactory->findByTypeIdent($value->getCriterionId())->hasUniqueNature();

        return $valuesHaveSameNature;
    }
}
