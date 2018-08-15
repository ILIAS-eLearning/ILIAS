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

	/**
	 * ilTermsOfServiceDocumentLogicalAndCriteriaEvaluation constructor.
	 * @param \ilTermsOfServiceCriterionTypeFactoryInterface $criterionTypeFactory
	 * @param \ilObjUser $user
	 */
	public function __construct(
		\ilTermsOfServiceCriterionTypeFactoryInterface $criterionTypeFactory,
		\ilObjUser $user
	) {
		$this->criterionTypeFactory = $criterionTypeFactory;
		$this->user = $user;
	}

	/**
	 * @inheritdoc
	 */
	public function evaluate(\ilTermsOfServiceSignableDocument $document): bool
	{
		foreach ($document->getCriteria() as $criterionAssignment) {
			/** @var $criterionAssignment \ilTermsOfServiceEvaluableCriterion */

			$criterionType = $this->criterionTypeFactory->findByTypeIdent($criterionAssignment->getCriterionId(), true);

			$result = $criterionType->evaluate($this->user, $criterionAssignment->getCriterionValue());

			if (!$result) {
				return false;
			}
		}

		return true;
	}
}