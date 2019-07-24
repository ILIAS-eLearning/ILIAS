<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\Services\AssessmentQuestion\PublicApi\Factory;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\QuestionAuthoringServiceSpecContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\QuestionAuthoringServiceSpec;
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
	 * @param QuestionAuthoringServiceSpecContract $authoringQuestionServiceSpec
	 * @return ilAsqQuestionAuthoringGUI
	 */
	public function authoringGUI(QuestionAuthoringServiceSpecContract $authoringQuestionServiceSpec) : ilAsqQuestionAuthoringGUI
	{
		return new ilAsqQuestionAuthoringGUI($authoringQuestionServiceSpec);
	}
}
