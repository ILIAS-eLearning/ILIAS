<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\Services\AssessmentQuestion\PublicApi;


use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\QuestionIdContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\RevisionIdContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\UserAnswerIdContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\UserAnswerSubmitContract;
use JsonSerializable;

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
class UserAnswerSubmit implements UserAnswerSubmitContract
{
	/**
	 * @var UserAnswerIdContract
	 */
	protected $userAnswerUuid;
	
	/**
	 * @var QuestionIdContract
	 */
	protected $questionUuid;

	/**
	 * @var RevisionIdContract
	 */
	protected $revisionUuid;
	
	/**
	 * @var int
	 */
	protected $userId;
	
	/**
	 * @var JsonSerializable
	 */
	protected $userAnswer;
	
	/**
	 * UserAnswerDTOContract constructor.
	 *
	 * @param QuestionIdContract $questionUuid
	 * @param int $user_id
	 * @param JsonSerializable $user_answer
	 */
	public function __construct(
		UserAnswerIdContract $userAnswerUuid,
		RevisionIdContract $revisionUuid,
		QuestionIdContract $questionUuid,
		int $user_id,
		JsonSerializable $user_answer
	)
	{
		$this->userAnswerUuid = $userAnswerUuid;
		$this->questionUuid = $questionUuid;
		$this->revisionUuid = $revisionUuid;
		$this->userId = $user_id;
		$this->userAnswer = $user_answer;
	}
	
	/**
	 * @return UserAnswerIdContract
	 */
	public function getUserAnswerUuid(): UserAnswerIdContract
	{
		return $this->userAnswerUuid;
	}
	
	/**
	 * @return QuestionIdContract
	 */
	public function getQuestionUuid(): QuestionIdContract
	{
		return $this->questionUuid;
	}

	/**
	 * @return QuestionIdContract
	 */
	public function getRevisionUuid(): RevisionIdContract
	{
		return $this->questionUuid;
	}
	
	/**
	 * @return int
	 */
	public function getUserId(): int
	{
		return $this->userId;
	}
	
	/**
	 * @return JsonSerializable
	 */
	public function getUserAnswer(): JsonSerializable
	{
		return $this->userAnswer;
	}
	
}
