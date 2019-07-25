<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Contracts;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\QuestionResourcesCollectorContract;
use ILIAS\UI\Component\Component;

/**
 * Interface PlayServiceContract
 * @package ILIAS\Services\AssessmentQuestion\PublicApi\Contracts
 *
 * @author studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author Adrian Lüthi <al@studer-raimann.ch>
 * @author Björn Heyser <bh@bjoernheyser.de>
 * @author Martin Studer <ms@studer-raimann.ch>
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
interface PlayServiceContract {

	/**
	 * @return QuestionComponentContract
	 *
	 * Gets Question Presentation
	 */
	public function GetQuestionPresentation(): QuestionComponentContract;


	/**
	 * @param string $question_uuid
	 *
	 * @return Component
	 *
	 * Gets Question Presentation Component, if solution is given that solution
	 * will be displayed
	 */
	public function GetStandaloneQuestionExportPresentation(
		QuestionResourcesCollectorContract $collector,
		$image_path, $a_mode, $a_no_interaction
	): Component;


	/**
	 * @param int $user_answer_id
	 *
	 * @return QuestionComponentContract
	 *
	 * Gets Question Presentation Component with user answer
	 */
	public function GetUserAnswerPresentation(int $user_answer_id): QuestionComponentContract;


	/**
	 *
	 * @return Component
	 */
	public function getGenericFeedbackOutput(int $user_answer_id): Component;


	/**
	 *
	 * @return Component
	 */
	public function getSpecificFeedbackOutput(int $user_answer_id): Component;


	/**
	 * @param UserAnswerDTOContract $user_answer
	 *
	 * @return int
	 */
	public function SaveUserAnswer(UserAnswerDTOContract $user_answer): int;


	public function GetUserScore(int $user_answer_id): ScoringDTOContract;


	public function GetUserScoreOfBestScoredAnswer(int $user_id): ScoringDTOContract;


	public function GetUserScoreLastOfSubmittedAnswer(int $user_id): ScoringDTOContract;
}