<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\Services\AssessmentQuestion\PublicApi\Factory;

use ILIAS\Services\AssessmentQuestion\PublicApi\AsqApiAuthoringQuestionService;
use ILIAS\Services\AssessmentQuestion\PublicApi\AsqApiAuthoringQuestionServiceSpec;
use ILIAS\Services\AssessmentQuestion\PublicApi\AsqApiAuthoringQueryService;
use ILIAS\Services\AssessmentQuestion\PublicApi\AsqApiPlayService;
use ILIAS\Services\AssessmentQuestion\PublicApi\AsqApiPlayServiceSpec;

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
	 * @return AsqApiAuthoringQueryService
	 */
	public function authoringQuery(int $containerId) : AsqApiAuthoringQueryService
	{
		return new AsqApiAuthoringQueryService($containerId);
	}
	
	/**
	 * @param AsqApiAuthoringQuestionServiceSpec $authoringQuestionServiceSpec
	 * @return AsqApiAuthoringQuestionService
	 */
	public function authoringQuestion(AsqApiAuthoringQuestionServiceSpec $authoringQuestionServiceSpec) : AsqApiAuthoringQuestionService
	{
		return new AsqApiAuthoringQuestionService($authoringQuestionServiceSpec);
	}
	
	/**
	 * @param AsqApiPlayServiceSpec $playServiceSpec
	 * @return AsqApiPlayService
	 */
	public function play(AsqApiPlayServiceSpec $playServiceSpec) : AsqApiPlayService
	{
		return new AsqApiPlayService($playServiceSpec);
	}
}
