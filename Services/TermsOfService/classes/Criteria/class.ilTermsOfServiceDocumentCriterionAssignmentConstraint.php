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
use ILIAS\Refinery\Custom\Constraint;

/**
 * Class ilTermsOfServiceDocumentCriterionAssignmentConstraint
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocumentCriterionAssignmentConstraint extends Constraint
{
    public function __construct(
        protected ilTermsOfServiceCriterionTypeFactoryInterface $criterionTypeFactory,
        protected ilTermsOfServiceDocument $document,
        Factory $dataFactory,
        ilLanguage $lng
    ) {
        parent::__construct(
            function (ilTermsOfServiceDocumentCriterionAssignment $value): bool {
                return [] === $this->filterEqualValues($value);
            },
            static function ($txt, $value): string {
                return 'The passed assignment must be unique for the document!';
            },
            $dataFactory,
            $lng
        );
    }

    /**
     * @return ilTermsOfServiceDocumentCriterionAssignment[]|ilTermsOfServiceEvaluableCriterion[]
     */
    protected function filterEqualValues(
        ilTermsOfServiceDocumentCriterionAssignment $value
    ): array {
        $otherValues = $this->document->criteria();

        return array_filter(
            $otherValues,
            function (ilTermsOfServiceDocumentCriterionAssignment $otherValue) use ($value): bool {
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

                return $this->haveSameNature($value, $otherValue);
            }
        );
    }

    /**
     * @throws ilTermsOfServiceCriterionTypeNotFoundException
     */
    protected function haveSameNature(
        ilTermsOfServiceDocumentCriterionAssignment $value,
        ilTermsOfServiceDocumentCriterionAssignment $otherValue
    ): bool {
        if ($value->getCriterionId() !== $otherValue->getCriterionId()) {
            return false;
        }

        return $this->criterionTypeFactory->findByTypeIdent($value->getCriterionId())->hasUniqueNature();
    }
}
