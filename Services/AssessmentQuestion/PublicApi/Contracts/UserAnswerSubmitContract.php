<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Contracts;

use ILIAS\Services\AssessmentQuestion\PublicApi\QuestionId;
use ILIAS\Services\AssessmentQuestion\PublicApi\RevisionId;
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
interface UserAnswerSubmitContract {
	
	/**
	 * @return UserAnswerIdContract
	 */
	public function getUserAnswerUuid(): UserAnswerIdContract;

	public function getQuestionUuid(): QuestionIdContract;

	public function getRevisionUuid(): RevisionIdContract;
	
	/**
	 * @return int
	 */
	public function getUserId(): int;
	
	/**
	 * @return JsonSerializable
	 */
	public function getUserAnswer(): JsonSerializable;
}