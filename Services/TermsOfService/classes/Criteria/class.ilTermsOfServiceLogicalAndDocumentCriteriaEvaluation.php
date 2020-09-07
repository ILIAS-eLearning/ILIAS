<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilTermsOfServiceLogicalAndDocumentCriteriaEvaluation
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceLogicalAndDocumentCriteriaEvaluation implements \ilTermsOfServiceDocumentCriteriaEvaluation
{
    /** @var \ilTermsOfServiceCriterionTypeFactoryInterface */
    protected $criterionTypeFactory;

    /** @var \ilObjUser */
    protected $user;

    /** @var \ilLogger */
    protected $log;

    /**
     * ilTermsOfServiceDocumentLogicalAndCriteriaEvaluation constructor.
     * @param \ilTermsOfServiceCriterionTypeFactoryInterface $criterionTypeFactory
     * @param \ilObjUser $user
     * @param \ilLogger $log
     */
    public function __construct(
        \ilTermsOfServiceCriterionTypeFactoryInterface $criterionTypeFactory,
        \ilObjUser $user,
        \ilLogger $log
    ) {
        $this->criterionTypeFactory = $criterionTypeFactory;
        $this->user = $user;
        $this->log = $log;
    }

    /**
     * @inheritdoc
     */
    public function evaluate(\ilTermsOfServiceSignableDocument $document) : bool
    {
        $this->log->debug(sprintf(
            'Evaluating criteria for document "%s" (id: %s) and user "%s" (id: %s)',
            $document->title(),
            $document->id(),
            $this->user->getLogin(),
            $this->user->getId()
        ));

        foreach ($document->criteria() as $criterionAssignment) {
            /** @var $criterionAssignment \ilTermsOfServiceEvaluableCriterion */

            $criterionType = $this->criterionTypeFactory->findByTypeIdent($criterionAssignment->getCriterionId(), true);

            $result = $criterionType->evaluate($this->user, $criterionAssignment->getCriterionValue());

            $this->log->debug(sprintf(
                'Criterion of type "%s", configured with %s evaluated: %s',
                $criterionType->getTypeIdent(),
                var_export($criterionAssignment->getCriterionValue()->toJson(), 1),
                var_export($result, 1)
            ));

            if (!$result) {
                return false;
            }
        }

        return true;
    }
}
