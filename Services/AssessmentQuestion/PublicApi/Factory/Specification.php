<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\Services\AssessmentQuestion\PublicApi\Factory;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\QuestionIdContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\PlayServiceSpec;
use ILIAS\Services\AssessmentQuestion\PublicApi\AuthoringServiceSpec;
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
	 * @param int $container_obj_id
	 * @param int $actor_user_id
	 * @param Link $container_backlink
	 * @return AuthoringServiceSpec
	 */
	public function questionAuthoring(
		int $container_obj_id,
		int $actor_user_id,
		Link $container_backlink
	): AuthoringServiceSpec {
		return new AuthoringServiceSpec(
			$container_obj_id,
			$actor_user_id,
			$container_backlink
		);
	}
	
	/**
	 * @return PlayServiceSpec
	 * // TODO pass parameters
	 */
	public function play() : PlayServiceSpec
	{
		return new PlayServiceSpec(
		
		);
	}
}
