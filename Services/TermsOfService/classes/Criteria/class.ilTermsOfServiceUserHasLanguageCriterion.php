<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceUserHasLanguageCriterion
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceUserHasLanguageCriterion implements \ilTermsOfServiceCriterionType
{
	/**
	 * @inheritdoc
	 */
	public function getTypeIdent(): string
	{
		return 'usr_language';
	}

	/**
	 * @inheritdoc
	 */
	public function evaluate(\ilObjUser $user, array $config): bool
	{
		$lng = $config['lng'] ?? '';

		if (!is_string($lng)) {
			return false;
		}

		return strtolower($lng) === strtolower($user->getLanguage());
	}

	/**
	 * @inheritdoc
	 */
	public function getGUI(\ilLanguage $lng): \ilTermsOfServiceCriterionTypeGUI
	{
		return new ilTermsOfServiceUserHasLanguageCriterionGUI($this, $lng);
	}
}