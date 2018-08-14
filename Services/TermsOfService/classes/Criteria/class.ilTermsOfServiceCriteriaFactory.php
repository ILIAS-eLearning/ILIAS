<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceCriteriaFactory
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceCriteriaFactory
{
	/**
	 * @var \ilRbacReview $rbacReview
	 */
	protected $rbacReview;

	/**
	 * ilTermsOfServiceCriteriaFactory constructor.
	 * @param ilRbacReview $rbacReview
	 */
	public function __construct(\ilRbacReview $rbacReview)
	{
		$this->rbacReview = $rbacReview;
	}

	/**
	 * @return \ilTermsOfServiceCriterionType[]
	 */
	public function getCriteria()
	{
		return [
			new ilTermsOfServiceUserHasLanguageCriterion(),
			new ilTermsOfServiceUserHasGlobalRoleCriterion($this->rbacReview),
		];
	}
}