<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\Services\AssessmentQuestion\PublicApi\Factory;

use ILIAS\Services\AssessmentQuestion\PublicApi\AsqApiServiceAuthoringQuestionSpec;
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
	 * @param AsqApiServiceAuthoringQuestionSpec $authoringQuestionServiceSpec
	 * @return ilAsqQuestionAuthoringGUI
	 */
	public function authoringGUI(AsqApiServiceAuthoringQuestionSpec $authoringQuestionServiceSpec) : ilAsqQuestionAuthoringGUI
	{
		return ilAsqQuestionAuthoringGUI($authoringQuestionServiceSpec);
	}
}
