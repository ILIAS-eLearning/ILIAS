<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilTermsOfServiceCriterionType
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilTermsOfServiceCriterionType
{
	/**
	 * @return string
	 */
	public function getTypeIdent(): string;

	/**
	 * @param \ilObjUser $user
	 * @param array $config
	 * @return bool
	 */
	public function evaluate(\ilObjUser $user, array $config): bool;

	/**
	 * @param \ilLanguage $lng
	 * @return \ilTermsOfServiceCriterionTypeGUI
	 */
	public function getGUI(\ilLanguage $lng): \ilTermsOfServiceCriterionTypeGUI;
}
