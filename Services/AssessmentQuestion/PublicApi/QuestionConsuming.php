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
 * Interface QuestionConsuming
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi
 */
class QuestionConsuming {

	/**
	 * QuestionConsuming constructor.
	 *
	 * @param int                $containerId
	 * @param int                $actorId
	 * @param QuestionIdContract $questionUuid
	 * @param RevisionIdContract $revisionUuid
	 */
	public function __construct(
		int $containerId,
		int $actorId,
		QuestionIdContract $questionUuid,
		RevisionIdContract $revisionUuid
	)
	{
		// TODO
	}


	/**
	 * @return QuestionComponentContract
	 */
	public function GetQuestionPresentation(UserAnswerIdContract $userAnswerUuid): QuestionComponentContract {
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
	public function CreateUserAnswer(UserAnswerSubmitContract $user_answer): void {
		// TODO: Implement SaveUserAnswer() method.
	}

	/**
	 * @param UserAnswerSubmitContract $user_answer
	 * @return int
	 */
	public function UpdateUserAnswer(UserAnswerSubmitContract $user_answer): void {
		// TODO: Implement SaveUserAnswer() method.
	}
	
	
	/**
	 * @param UserAnswerIdContract $userAnswerUuid
	 * @return UserAnswerScoringContract
	 */
	public function GetUserScore(UserAnswerIdContract $userAnswerUuid): UserAnswerScoringContract {
		// TODO: Implement GetUserScore() method.
	}
}