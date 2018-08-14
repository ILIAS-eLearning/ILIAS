<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceUserHasGlobalRoleCriterion
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceUserHasGlobalRoleCriterion implements \ilTermsOfServiceCriterionType
{
	/**
	 * @var \ilRbacReview
	 */
	protected $rbacReview;

	/**
	 * ilTermsOfServiceUserHasGlobalRoleCriterion constructor.
	 * @param ilRbacReview $rbacReview
	 */
	public function __construct(\ilRbacReview $rbacReview)
	{
		$this->rbacReview = $rbacReview;
	}

	/**
	 * @inheritdoc
	 */
	public function getTypeIdent(): string
	{
		return 'usr_global_role';
	}

	/**
	 * @inheritdoc
	 */
	public function evaluate(\ilObjUser $user, array $config): bool
	{
		$roleId = $config['role_id'] ?? 0;

		if (!is_numeric($roleId) || $roleId < 1) {
			return false;
		}

		if ($this->rbacReview->isGlobalRole($roleId)) {
			return false;
		}

		return $this->rbacReview->isAssigned($user->getId(), $roleId);
	}

	/**
	 * @inheritdoc
	 */
	public function getGUI(\ilLanguage $lng): \ilTermsOfServiceCriterionTypeGUI
	{
		return new \ilTermsOfServiceUserHasGlobalRoleCriterionGUI($this, $lng, $this->rbacReview);
	}
}