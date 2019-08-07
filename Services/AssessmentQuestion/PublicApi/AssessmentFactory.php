<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Factory;

use ILIAS\Services\AssessmentQuestion\PublicApi\Authoring\AuthoringService;
use ILIAS\Services\AssessmentQuestion\PublicApi\Processing\ProcessingService;

/**
 * Class AssessmentServices
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi\Factory
 */
class AssessmentFactory {

	public function questionAuthoring(int $container_obj_id, int $actor_user_id): AuthoringService {
		return new AuthoringService($container_obj_id, $actor_user_id);
	}


	public function questionProcessing(int $actor_user_id) {
		return new ProcessingService($actor_user_id);
	}
}
