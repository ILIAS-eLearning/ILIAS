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
	 * @param \ilTermsOfServiceCriterionConfig $config
	 * @return bool
	 */
	public function evaluate(\ilObjUser $user, \ilTermsOfServiceCriterionConfig $config): bool;

	/**
	 * @param \ilLanguage $lng
	 * @return \ilTermsOfServiceCriterionTypeGUI
	 */
	public function ui(\ilLanguage $lng): \ilTermsOfServiceCriterionTypeGUI;
}
