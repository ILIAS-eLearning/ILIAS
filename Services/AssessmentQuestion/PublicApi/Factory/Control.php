<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\Services\AssessmentQuestion\PublicApi\Factory;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AuthoringServiceSpecContract;
use \ilAsqQuestionAuthoringGUI;

/**
 * Class Control
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi\Factory
 */
class Control
{
	/**
	 * @param AuthoringServiceSpecContract $authoringQuestionServiceSpec
	 * @return ilAsqQuestionAuthoringGUI
	 */
	public function authoringGUI(AuthoringServiceSpecContract $authoringQuestionServiceSpec) : ilAsqQuestionAuthoringGUI
	{
		return new ilAsqQuestionAuthoringGUI($authoringQuestionServiceSpec);
	}
}
