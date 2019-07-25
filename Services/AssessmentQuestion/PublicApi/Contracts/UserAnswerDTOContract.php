<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Contracts;

use JsonSerializable;

/**
 * Interface UserAnswerDTOContract
 * @package ILIAS\Services\AssessmentQuestion\PublicApi\Contracts
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi\Contracts
 */
interface UserAnswerDTOContract {

	/**
	 * UserAnswerDTOContract constructor.
	 * @param int $container_obj_id
	 * @param string $question_uuid
	 * @param int $user_id
	 * @param JsonSerializable $user_answer
	 */
	public function __construct(int $container_obj_id, string $question_uuid, int $user_id, JsonSerializable $user_answer);
}