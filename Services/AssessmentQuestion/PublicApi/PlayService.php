<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\QuestionComponentContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\QuestionResourcesCollectorContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\PlayServiceContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\PlayServiceSpecContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\QuestionIdContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\RevisionIdContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\UserAnswerIdContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\UserAnswerScoringContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\UserAnswerSubmitContract;
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
		// TODO
	}


	/**
	 * @return QuestionComponentContract
	 */
	public function GetQuestionPresentation(): QuestionComponentContract {
		// TODO: Implement GetQuestionPresentation() method.
	}
	
	
	/**
	 * @param QuestionResourcesCollectorContract $collector
	 * @param $image_path
	 * @param $a_mode
	 * @param $a_no_interaction
	 * @return QuestionComponentContract
	 */
	public function GetStandaloneQuestionExportPresentation(
		QuestionResourcesCollectorContract $collector,
		$image_path, $a_mode, $a_no_interaction
	): QuestionComponentContract {
		// TODO: Implement GetStandaloneQuestionExportPresentation() method.
	}
	
	
	/**
	 * @param UserAnswerIdContract $userAnswerUuid
	 * @return QuestionComponentContract
	 */
	public function GetUserAnswerPresentation(UserAnswerIdContract $userAnswerUuid): QuestionComponentContract {
		// TODO: Implement GetUserAnswerPresentation() method.
	}
	
	
	/**
	 * @param UserAnswerIdContract $userAnswerUuid
	 * @return Component
	 */
	public function getGenericFeedbackOutput(UserAnswerIdContract $userAnswerUuid): Component {
		// TODO: Implement getGenericFeedbackOutput() method.
	}
	
	
	/**
	 * @param UserAnswerIdContract $userAnswerUuid
	 * @return Component
	 */
	public function getSpecificFeedbackOutput(UserAnswerIdContract $userAnswerUuid): Component {
		// TODO: Implement getSpecificFeedbackOutput() method.
	}
	
	
	/**
	 * @param UserAnswerSubmitContract $user_answer
	 * @return int
	 */
	public function SaveUserAnswer(UserAnswerSubmitContract $user_answer): int {
		// TODO: Implement SaveUserAnswer() method.
	}
	
	
	/**
	 * @param UserAnswerIdContract $userAnswerUuid
	 * @return UserAnswerScoringContract
	 */
	public function GetUserScore(UserAnswerIdContract $userAnswerUuid): UserAnswerScoringContract {
		// TODO: Implement GetUserScore() method.
	}


	/**
	 * @param int $user_id
	 * @return UserAnswerScoringContract
	 */
	public function GetUserScoreOfBestScoredAnswer(int $user_id): UserAnswerScoringContract {
		// TODO: Implement GetUserScoreOfBestScoredAnswer() method.
	}


	/**
	 * @param int $user_id
	 * @return UserAnswerScoringContract
	 */
	public function GetUserScoreLastOfSubmittedAnswer(int $user_id): UserAnswerScoringContract {
		// TODO: Implement GetUserScoreLastOfSubmittedAnswer() method.
	}
}