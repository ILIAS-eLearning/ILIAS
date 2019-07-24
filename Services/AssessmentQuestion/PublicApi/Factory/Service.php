<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\Services\AssessmentQuestion\PublicApi\Factory;

use ILIAS\Services\AssessmentQuestion\PublicApi\AsqApiServiceAuthoringQuestion;
use ILIAS\Services\AssessmentQuestion\PublicApi\AsqApiServiceAuthoringQuestionSpec;
use ILIAS\Services\AssessmentQuestion\PublicApi\AsqApiServiceAuthoringQuery;
use ILIAS\Services\AssessmentQuestion\PublicApi\AsqApiServicePlay;
use ILIAS\Services\AssessmentQuestion\PublicApi\AsqApiServicePlaySpec;

/**
 * Class Services
 *
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Services/AssessmentQuestion
 */
class Service
{
	/**
	 * @param int $containerId
	 * @return AsqApiServiceAuthoringQuery
	 */
	public function authoringQuery(int $containerId) : AsqApiServiceAuthoringQuery
	{
		return new AsqApiServiceAuthoringQuery($containerId);
	}
	
	/**
	 * @param AsqApiServiceAuthoringQuestionSpec $authoringQuestionServiceSpec
	 * @return AsqApiServiceAuthoringQuestion
	 */
	public function authoringQuestion(AsqApiServiceAuthoringQuestionSpec $authoringQuestionServiceSpec) : AsqApiServiceAuthoringQuestion
	{
		return new AsqApiServiceAuthoringQuestion($authoringQuestionServiceSpec);
	}
	
	/**
	 * @param AsqApiServicePlaySpec $playServiceSpec
	 * @return AsqApiServicePlay
	 */
	public function play(AsqApiServicePlaySpec $playServiceSpec) : AsqApiServicePlay
	{
		return new AsqApiServicePlay($playServiceSpec);
	}
}
