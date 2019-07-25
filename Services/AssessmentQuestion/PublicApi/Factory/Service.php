<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\Services\AssessmentQuestion\PublicApi\Factory;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\QueryServiceContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\PlayServiceContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\PlayServiceSpecContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AuthoringServiceContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AuthoringServiceSpecContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\AuthoringService;
use ILIAS\Services\AssessmentQuestion\PublicApi\QueryService;
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
	 * @return QueryServiceContract
	 */
	public function query() : QueryServiceContract
	{
		return new QueryService();
	}
	
	/**
	 * @param AuthoringServiceSpecContract $authoringQuestionServiceSpec
	 * @return AuthoringServiceContract
	 */
	public function authoring(AuthoringServiceSpecContract $authoringQuestionServiceSpec) : AuthoringServiceContract
	{
		return new AuthoringService($authoringQuestionServiceSpec);
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
