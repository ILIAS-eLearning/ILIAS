<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Processing;

use JsonSerializable;
use QuestionId;

/**
 * Class UserAnswerSubmit
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi
 */

class UserAnswerSubmit {

	/**
	 * @var QuestionRevisionId
	 */
	protected $question_revision_id;
	/**
	 * @var UserAnswerId
	 */
	protected $user_answer_id;
	/**
	 * @var int
	 */
	protected $user_id;
	/**
	 * @var JsonSerializable
	 */
	protected $user_answer;


	/**
	 * UserAnswerDTO constructor.
	 *
	 * @param QuestionId       $questionUuid
	 * @param int              $user_id
	 * @param JsonSerializable $user_answer
	 */
	public function __construct(QuestionRevisionId $question_revision_id,
		UserAnswerId $user_answer_id,
		int $user_id,
		JsonSerializable $user_answer) {
		$this->question_revision_id = $question_revision_id;
		$this->user_answer_id = $user_answer_id;
		$this->user_id = $user_id;
		$this->user_answer = $user_answer;
	}


	/**
	 * @return UserAnswerId
	 */
	public function getUserAnswerUuid(): UserAnswerId {
		return $this->user_answer_id;
	}


	/**
	 * @return QuestionId
	 */
	public function getRevisionUuid(): QuestionRevisionId {
		return $this->question_revision_id;
	}


	/**
	 * @return int
	 */
	public function getUserId(): int {
		return $this->user_id;
	}


	/**
	 * @return JsonSerializable
	 */
	public function getUserAnswer(): JsonSerializable {
		return $this->user_answer;
	}
}
