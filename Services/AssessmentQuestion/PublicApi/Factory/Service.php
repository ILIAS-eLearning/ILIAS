<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\Services\AssessmentQuestion\PublicApi\Factory;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AuthoringQueryServiceContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\PlayServiceContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\PlayServiceSpecContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\QuestionAuthoringServiceContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\QuestionAuthoringServiceSpecContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\QuestionAuthoringService;
use ILIAS\Services\AssessmentQuestion\PublicApi\AuthoringQueryService;
use ILIAS\Services\AssessmentQuestion\PublicApi\PlayService;

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
	 * @return AuthoringQueryServiceContract
	 */
	public function authoringQuery(int $containerId) : AuthoringQueryServiceContract
	{
		return new AuthoringQueryService($containerId);
	}
	
	/**
	 * @param QuestionAuthoringServiceSpecContract $authoringQuestionServiceSpec
	 * @return QuestionAuthoringServiceContract
	 */
	public function questionAuthoring(QuestionAuthoringServiceSpecContract $authoringQuestionServiceSpec) : QuestionAuthoringServiceContract
	{
		return new QuestionAuthoringService($authoringQuestionServiceSpec);
	}
	
	/**
	 * @param PlayServiceSpecContract $playServiceSpec
	 * @return PlayServiceContract
	 */
	public function play(PlayServiceSpecContract $playServiceSpec) : PlayServiceContract
	{
		return new PlayService($playServiceSpec);
	}
}
