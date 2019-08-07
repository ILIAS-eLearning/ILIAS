<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\Services\AssessmentQuestion\PublicApi;


use ilDateTime;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\QuestionIdContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\RevisionIdContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\UserAnswerIdContract;
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
    
    
	/**
	 * @return QuestionIdContract
	 */
	public function getQuestionId(): QuestionIdContract {
		// TODO: Implement getQuestionId() method.
	}


	/**
	 * @return RevisionIdContract
	 */
	public function getRevisioId(): RevisionIdContract {
		// TODO: Implement getRevisioId() method.
	}


	/**
	 * @return UserAnswerIdContract
	 */
	public function getUserAnswerId(): UserAnswerIdContract {
		// TODO: Implement getUserAnswerId() method.
	}


	/**
	 * @return int
	 */
	public function getUserId(): int {
		// TODO: Implement getUserId() method.
	}


	/**
	 * @return ilDateTime
	 */
	public function getSubmittedOn(): ilDateTime {
		// TODO: Implement getSubmittedOn() method.
	}


	/**
	 * @return bool
	 */
	public function isCorrect(): bool {
		// TODO: Implement isCorrect() method.
	}


	/**
	 * @return int
	 */
	public function getPoints(): int {
		// TODO: Implement getPoints() method.
	}
}
