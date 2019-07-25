<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Contracts;

use ilDateTime;

/**
 * Interface ScoringDTOContract
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
interface UserAnswerScoringContract {

	/**
	 * @return QuestionIdContract
	 */
	public function getQuestionUuid(): QuestionIdContract;

	/**
	 * @return UserAnswerIdContract
	 */
	public function getUserAnswerUuid(): UserAnswerIdContract;


	/**
	 * @return int
	 */
	public function getUserId(): int;


	/**
	 * @return ilDateTime
	 */
	public function getSubmittedOn(): ilDateTime;


	/**
	 * @return bool
	 */
	public function isCorrect(): bool;


	/**
	 * @return int
	 */
	public function getPoints(): int;
}