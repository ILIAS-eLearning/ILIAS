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
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi\Factory
 */
class Specification
{
	/**
	 * @param int $container_obj_id
	 * @param int $actor_user_id
	 * @param Link $container_backlink
	 * @return AuthoringServiceSpec
	 */
	public function authoring(
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
	 * @param int $containerId
	 * @param int $actorId
	 * @return PlayServiceSpec
	 */
	public function play(
		int $containerId,
		int $actorId
	) : PlayServiceSpec
	{
		return new PlayServiceSpec($containerId, $actorId);
	}
}
