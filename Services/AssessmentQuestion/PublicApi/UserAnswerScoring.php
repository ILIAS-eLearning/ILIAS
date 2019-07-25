<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\Services\AssessmentQuestion\PublicApi;


use ilDateTime;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\QuestionIdContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\UserAnswerScoringContract;

/**
 * Class UserAnswerScoring
 *
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Services/AssessmentQuestion
 */
class UserAnswerScoring implements UserAnswerScoringContract
{
	public function getQuestionUuid(): QuestionIdContract
	{
		// TODO: Implement getQuestionUuid() method.
	}
	
	public function getUserId(): int
	{
		// TODO: Implement getUserId() method.
	}
	
	public function getSubmittedOn(): ilDateTime
	{
		// TODO: Implement getSubmittedOn() method.
	}
	
	public function isUserAnswerCorrect(): bool
	{
		// TODO: Implement isUserAnswerCorrect() method.
	}
	
	public function getPoints(): int
	{
		// TODO: Implement getPoints() method.
	}
	
}
