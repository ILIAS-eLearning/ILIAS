<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\Services\AssessmentQuestion\PublicApi\Factory;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\QueryServiceContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\PlayServiceContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\PlayServiceSpecContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AuthoringServiceContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AuthoringServiceSpecContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\AuthoringService;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\QuestionIdContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\RevisionIdContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\QueryService;
use ILIAS\Services\AssessmentQuestion\PublicApi\PlayService;
use ILIAS\Services\AssessmentQuestion\PublicApi\QuestionId;

/**
 * Class Services
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi\Factory
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
	public function authoring(
		AuthoringServiceSpecContract $authoringQuestionServiceSpec,
		QuestionIdContract $questionUuid
	) : AuthoringServiceContract
	{
		return new AuthoringService($authoringQuestionServiceSpec, $questionUuid);
	}
	
	/**
	 * @param PlayServiceSpecContract $playServiceSpec
	 * @return PlayServiceContract
	 */
	public function play(
		PlayServiceSpecContract $playServiceSpec,
		QuestionIdContract $questionUuid,
		RevisionIdContract $revisionUuid = null
	) : PlayServiceContract
	{
		return new PlayService($playServiceSpec, $questionUuid, $revisionUuid);
	}
}
