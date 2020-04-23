<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAccessibilityLogicalAndDocumentCriteriaEvaluation
 */
class ilAccessibilityLogicalAndDocumentCriteriaEvaluation implements ilAccessibilityDocumentCriteriaEvaluation
{
    /** @var ilAccessibilityCriterionTypeFactoryInterface */
    protected $criterionTypeFactory;

    /** @var ilObjUser */
    protected $user;

    /** @var ilLogger */
    protected $log;

    /**
     * ilAccessibilityDocumentLogicalAndCriteriaEvaluation constructor.
     * @param ilAccessibilityCriterionTypeFactoryInterface $criterionTypeFactory
     * @param ilObjUser                                     $user
     * @param ilLogger                                      $log
     */
    public function __construct(
        ilAccessibilityCriterionTypeFactoryInterface $criterionTypeFactory,
        ilObjUser $user,
        ilLogger $log
    ) {
        $this->criterionTypeFactory = $criterionTypeFactory;
        $this->user = $user;
        $this->log = $log;
    }

    /**
     * @inheritdoc
     */
    public function evaluate(ilAccessibilitySignableDocument $document) : bool
    {
        $this->log->debug(sprintf(
            'Evaluating criteria for document "%s" (id: %s) and user "%s" (id: %s)',
            $document->title(),
            $document->id(),
            $this->user->getLogin(),
            $this->user->getId()
        ));

        foreach ($document->criteria() as $criterionAssignment) {
            /** @var $criterionAssignment ilAccessibilityEvaluableCriterion */

            $criterionType = $this->criterionTypeFactory->findByTypeIdent($criterionAssignment->getCriterionId(), true);

            $result = $criterionType->evaluate($this->user, $criterionAssignment->getCriterionValue());

            $this->log->debug(sprintf(
                'Criterion of type "%s", configured with %s evaluated: %s',
                $criterionType->getTypeIdent(),
                var_export($criterionAssignment->getCriterionValue()->toJson(), true),
                var_export($result, true)
            ));

            if (!$result) {
                return false;
            }
        }

        return true;
    }
}
