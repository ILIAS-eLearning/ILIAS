<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\QuestionComponentContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\PlayServiceContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\PlayServiceSpecContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\QuestionIdContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\RevisionIdContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\ScoringDTOContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\UserAnswerDTOContract;
use ILIAS\UI\Component\Component;

/**
 * Interface PlayService
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi
 */
class PlayService implements PlayServiceContract {
	
	/**
	 * PlayService constructor.
	 * @param PlayServiceSpecContract $asq_api_service_play_spec
	 * @param QuestionIdContract $questionUuid
	 * @param RevisionIdContract|null $revisionUuid
	 */
	public function __construct(
		PlayServiceSpecContract $asq_api_service_play_spec,
		QuestionIdContract $questionUuid,
		RevisionIdContract $revisionUuid = null
	)
	{
		//TODO
	}


	/**
	 * @return QuestionComponentContract
	 */
	public function GetQuestionPresentation(): QuestionComponentContract {
		// TODO: Implement GetQuestionPresentation() method.
	}


	/**
	 * @return Component
	 */
	public function GetStandaloneQuestionExportPresentation(): Component {
		// TODO: Implement GetStandaloneQuestionExportPresentation() method.
	}


	/**
	 * @param int $user_answer_id
	 * @return QuestionComponentContract
	 */
	public function GetUserAnswerPresentation(int $user_answer_id): QuestionComponentContract {
		// TODO: Implement GetUserAnswerPresentation() method.
	}


	/**
	 * @param int $user_answer_id
	 * @return Component
	 */
	public function getGenericFeedbackOutput(int $user_answer_id): Component {
		// TODO: Implement getGenericFeedbackOutput() method.
	}


	/**
	 * @param int $user_answer_id
	 * @return Component
	 */
	public function getSpecificFeedbackOutput(int $user_answer_id): Component {
		// TODO: Implement getSpecificFeedbackOutput() method.
	}


	/**
	 * @param UserAnswerDTOContract $user_answer
	 * @return int
	 */
	public function SaveUserAnswer(UserAnswerDTOContract $user_answer): int {
		// TODO: Implement SaveUserAnswer() method.
	}


	/**
	 * @param int $user_answer_id
	 * @return ScoringDTOContract
	 */
	public function GetUserScore(int $user_answer_id): ScoringDTOContract {
		// TODO: Implement GetUserScore() method.
	}


	/**
	 * @param int $user_id
	 * @return ScoringDTOContract
	 */
	public function GetUserScoreOfBestScoredAnswer(int $user_id): ScoringDTOContract {
		// TODO: Implement GetUserScoreOfBestScoredAnswer() method.
	}


	/**
	 * @param int $user_id
	 * @return ScoringDTOContract
	 */
	public function GetUserScoreLastOfSubmittedAnswer(int $user_id): ScoringDTOContract {
		// TODO: Implement GetUserScoreLastOfSubmittedAnswer() method.
	}
}