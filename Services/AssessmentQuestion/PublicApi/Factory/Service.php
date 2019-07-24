<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\Services\AssessmentQuestion\PublicApi\Factory;

use ILIAS\Services\AssessmentQuestion\PublicApi\QuestionAuthoringService;
use ILIAS\Services\AssessmentQuestion\PublicApi\QuestionAuthoringServiceSpec;
use ILIAS\Services\AssessmentQuestion\PublicApi\AuthoringQueryService;
use ILIAS\Services\AssessmentQuestion\PublicApi\PlayService;
use ILIAS\Services\AssessmentQuestion\PublicApi\PlayServiceSpec;

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
	 * @return AuthoringQueryService
	 */
	public function authoringQuery(int $containerId) : AuthoringQueryService
	{
		return new AuthoringQueryService($containerId);
	}
	
	/**
	 * @param QuestionAuthoringServiceSpec $authoringQuestionServiceSpec
	 * @return QuestionAuthoringService
	 */
	public function authoringQuestion(QuestionAuthoringServiceSpec $authoringQuestionServiceSpec) : QuestionAuthoringService
	{
		return new QuestionAuthoringService($authoringQuestionServiceSpec);
	}
	
	/**
	 * @param PlayServiceSpec $playServiceSpec
	 * @return PlayService
	 */
	public function play(PlayServiceSpec $playServiceSpec) : PlayService
	{
		return new PlayService($playServiceSpec);
	}
}
