<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilTermsOfServiceLogicalAndDocumentCriteriaEvaluation
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceLogicalAndDocumentCriteriaEvaluation implements ilTermsOfServiceDocumentCriteriaEvaluation
{
    /** @var ilTermsOfServiceCriterionTypeFactoryInterface */
    protected $criterionTypeFactory;
    /** @var ilObjUser */
    protected $user;
    /** @var ilLogger */
    protected $log;

    /**
     * ilTermsOfServiceDocumentLogicalAndCriteriaEvaluation constructor.
     * @param ilTermsOfServiceCriterionTypeFactoryInterface $criterionTypeFactory
     * @param ilObjUser                                     $user
     * @param ilLogger                                      $log
     */
    public function __construct(
        ilTermsOfServiceCriterionTypeFactoryInterface $criterionTypeFactory,
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
    public function evaluate(ilTermsOfServiceSignableDocument $document, ilObjUser $user = null) : bool
    {
        if (null === $user) {
            $user = $this->user;
        }

        $this->log->debug(sprintf(
            'Evaluating criteria for document "%s" (id: %s) and user "%s" (id: %s)',
            $document->title(),
            $document->id(),
            $user->getLogin(),
            $user->getId()
        ));

        foreach ($document->criteria() as $criterionAssignment) {
            $criterionType = $this->criterionTypeFactory->findByTypeIdent($criterionAssignment->getCriterionId(), true);

            $result = $criterionType->evaluate($user, $criterionAssignment->getCriterionValue());

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
