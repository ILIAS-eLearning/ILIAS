<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Processing;

use ILIAS\UI\Component\Component;

/**
 * Class QuestionProcessing
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>$
 */
class Question {

	/**
	 * QuestionConsuming constructor.
	 *
	 * @param string $userAnswerUuid
	 * @param int    $actor_user_id
	 * @param string $revision_id
	 */
	public function __construct(string $question_revision_uuid, int $actor_user_id, string $userAnswerUuid) {
		// TODO
	}


	/**
	 * @return QuestionFormDto
	 */
	public function getQuestionPresentation(): QuestionFormDto {
		// TODO: Implement GetQuestionPresentation() method.
	}


	/**
	 * @param QuestionResourcesDto       $collector
	 * @param                            $image_path
	 * @param                            $a_mode
	 * @param                            $a_no_interaction
	 *
	 * @return QuestionFormDto
	 */
	//TODO kann man diese weg machen???
	public function getStandaloneQuestionExportPresentation(QuestionResourcesDto $collector, $image_path, $a_mode, $a_no_interaction): QuestionFormDto {
		// TODO: Implement GetStandaloneQuestionExportPresentation() method.
	}


	/**
	 * @param UserAnswerId $userAnswerUuid
	 *
	 * @return Component
	 */
	public function getGenericFeedbackOutput(): Component {
		// TODO: Implement getGenericFeedbackOutput() method.
	}


	/**
	 * @param UserAnswerId $userAnswerUuid
	 *
	 * @return Component
	 */
	public function getSpecificFeedbackOutput(): Component {
		// TODO: Implement getSpecificFeedbackOutput() method.
	}


	/**
	 * @param UserAnswerSubmit $user_answer
	 *
	 * @return int
	 */
	public function createUserAnswer(UserAnswerSubmit $user_answer): void {
		// TODO: Implement SaveUserAnswer() method.
	}


	/**
	 * @param UserAnswerSubmit $user_answer
	 *
	 * @return int
	 */
	public function updateUserAnswer(UserAnswerSubmit $user_answer): void {
		// TODO: Implement SaveUserAnswer() method.
	}


	/**
	 * @param UserAnswerId $userAnswerUuid
	 *
	 * @return ScoredUserAnswerDto
	 */
	public function getUserScore(): ScoredUserAnswerDto {
		// TODO: Implement GetUserScore() method.
	}
}