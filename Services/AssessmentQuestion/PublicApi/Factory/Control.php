<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\Services\AssessmentQuestion\PublicApi\Factory;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AuthoringServiceSpecContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\AuthoringServiceSpec;
use ilAsqQuestionAuthoringGUI;

/**
 * Class Control
 *
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Services/AssessmentQuestion
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
