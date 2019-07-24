<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\Services\AssessmentQuestion\PublicApi\Factory;

use ILIAS\Services\AssessmentQuestion\PublicApi\AsqApiServiceAuthoringQuestionSpec;
use ILIAS\Services\AssessmentQuestion\PublicApi\AsqApiServicePlaySpec;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiIdContainerContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiIdQuestionContract;

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
	 * @param integer $container_id
	 * @param AsqApiIdQuestionContract $question_uuid
	 * @param int $actor_user_id
	 * @param Link $container_backlink
	 * @return AsqApiServiceAuthoringQuestionSpec
	 */
	public function authoringQuestion(
		AsqApiIdContainerContract $container_id,
		AsqApiIdQuestionContract $question_uuid,
		int $actor_user_id,
		Link $container_backlink
	) : AsqApiServiceAuthoringQuestionSpec
	{
		return new AsqApiServiceAuthoringQuestionSpec(
			$container_id,
			$question_uuid,
			$actor_user_id,
			$container_backlink
		);
	}
	
	/**
	 * @return AsqApiServicePlaySpec
	 * // TODO pass parameters
	 */
	public function play() : AsqApiServicePlaySpec
	{
		return new AsqApiServicePlaySpec(
		
		);
	}
}
