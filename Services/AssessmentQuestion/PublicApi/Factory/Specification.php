<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\Services\AssessmentQuestion\PublicApi\Factory;

use ILIAS\Services\AssessmentQuestion\PublicApi\AsqApiAuthoringQuestionServiceSpec;
use ILIAS\Services\AssessmentQuestion\PublicApi\AsqApiPlayServiceSpec;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiContainerId;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiQuestionId;

use ILIAS\UI\Component\Link\Link;

/**
 * Class Specification
 *
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Services/AssessmentQuestion
 */
class Specification
{
	/**
	 * @param AsqApiContainerId $container_id
	 * @param AsqApiQuestionId $question_uuid
	 * @param int $actor_user_id
	 * @param Link $container_backlink
	 * @return AsqApiAuthoringQuestionServiceSpec
	 */
	public function authoringQuestion(
		AsqApiContainerId $container_id,
		AsqApiQuestionId $question_uuid,
		int $actor_user_id,
		Link $container_backlink
	) : AsqApiAuthoringQuestionServiceSpec
	{
		return new AsqApiAuthoringQuestionServiceSpec(
			$container_id,
			$question_uuid,
			$actor_user_id,
			$container_backlink
		);
	}
	
	public function play() : AsqApiPlayServiceSpec
	{
		return new AsqApiPlayServiceSpec(
		
		);
	}
}
