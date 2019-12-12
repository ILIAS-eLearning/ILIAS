<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Data\Factory;
use ILIAS\Validation\Constraint;
use ILIAS\Validation\Constraints\Custom;

/**
 * Class ilTermsOfServiceDocumentCriterionAssignmentConstraint
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocumentCriterionAssignmentConstraint extends Custom implements Constraint
{
    /** @var \ilTermsOfServiceCriterionTypeFactoryInterface */
    protected $criterionTypeFactory;

    /** @var \ilTermsOfServiceDocument */
    protected $document;

    /**
     * ilTermsOfServiceDocumentCriterionAssignmentConstraint constructor.
     * @param \ilTermsOfServiceCriterionTypeFactoryInterface $criterionTypeFactory
     * @param \ilTermsOfServiceDocument $document
     * @param Factory $dataFactory
     * @param \ilLanguage $lng
     */
    public function __construct(
        \ilTermsOfServiceCriterionTypeFactoryInterface $criterionTypeFactory,
        \ilTermsOfServiceDocument $document,
        Factory $dataFactory,
        \ilLanguage $lng
    ) {
        $this->criterionTypeFactory = $criterionTypeFactory;
        $this->document = $document;

        parent::__construct(
            function (\ilTermsOfServiceDocumentCriterionAssignment $value) {
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
     * @param \ilTermsOfServiceDocumentCriterionAssignment $value
     * @return \ilTermsOfServiceDocumentCriterionAssignment[]
     */
    protected function filterEqualValues(
        \ilTermsOfServiceDocumentCriterionAssignment $value
    ) : array {
        $otherValues = $this->document->criteria();

        return array_filter(
            $otherValues,
            function (\ilTermsOfServiceDocumentCriterionAssignment $otherValue) use ($value) {
                $idCurrent = $otherValue->getId();
                $idNew = $value->getId();

                $uniqueIdEquals = $idCurrent === $idNew;
                if ($uniqueIdEquals) {
                    return false;
                }

                $valuesEqual = $value->equals($otherValue) ;
                if ($valuesEqual) {
                    return true;
                }

                $valuesHaveSameNature = $this->haveSameNature($value, $otherValue);

                return $valuesHaveSameNature;
            }
        );
    }

    /**
     * @param \ilTermsOfServiceDocumentCriterionAssignment $value
     * @param \ilTermsOfServiceDocumentCriterionAssignment $otherValue
     * @return bool
     */
    protected function haveSameNature(
        \ilTermsOfServiceDocumentCriterionAssignment $value,
        \ilTermsOfServiceDocumentCriterionAssignment $otherValue
    ) : bool {
        if ($value->getCriterionId() !== $otherValue->getCriterionId()) {
            return false;
        }

        $valuesHaveSameNature = $this->criterionTypeFactory->findByTypeIdent($value->getCriterionId())->hasUniqueNature();

        return $valuesHaveSameNature;
    }
}
