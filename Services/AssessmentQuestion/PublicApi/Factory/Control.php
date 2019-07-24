<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\Services\AssessmentQuestion\PublicApi\Factory;

use ILIAS\Services\AssessmentQuestion\PublicApi\AsqApiAuthoringQuestionServiceSpec;
use ilAssessmentQuestionServiceGUI;

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
	 * @param AsqApiAuthoringQuestionServiceSpec $authoringQuestionServiceSpec
	 * @return ilAssessmentQuestionServiceGUI
	 */
	public function authoringGUI(AsqApiAuthoringQuestionServiceSpec $authoringQuestionServiceSpec) : ilAssessmentQuestionServiceGUI
	{
		return ilAssessmentQuestionServiceGUI($authoringQuestionServiceSpec);
	}
}
