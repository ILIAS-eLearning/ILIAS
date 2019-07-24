<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Contracts;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiScoringDtoContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiUserAnswerDtoContract;
use ILIAS\UI\Component\Component;

/**
 * Interface AsqApiPlayServiceContract
 *
 * @author studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author Adrian Lüthi <al@studer-raimann.ch>
 * @author Björn Heyser <bh@bjoernheyser.de>
 * @author Martin Studer <ms@studer-raimann.ch>
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
interface AsqApiPlayServiceContract {

	/**
	 * @param string $question_uuid
	 *
	 * @return QuestionComponent
	 *
	 * Gets Question Presentation
	 */
	public function GetQuestionPresentation(): QuestionComponent;


	/**
	 * @param string $question_uuid
	 *
	 * @return Component
	 *
	 * Gets Question Presentation Component, if solution is given that solution
	 * will be displayed
	 */
	public function GetStandaloneQuestionExportPresentation(): Component;


	/**
	 * @param string $question_uuid
	 * @param int    $user_answer_id
	 *
	 * @return QuestionComponent
	 *
	 * Gets Question Presentation Component with user answer
	 */
	public function GetUserAnswerPresentation(int $user_answer_id): QuestionComponent;


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
	 * @param AsqApiUserAnswerDtoContract $user_answer
	 *
	 * @return int
	 */
	public function SaveUserAnswer(AsqApiUserAnswerDtoContract $user_answer): int;


	/**
	 * @param int $user_answer_id
	 *
	 * @return AsqApiScoringDtoContract
	 */
	public function GetUserScore(int $user_answer_id): AsqApiScoringDtoContract;


	/**
	 * @param int $user_id
	 *
	 * @return AsqApiScoringDtoContract
	 */
	public function GetUserScoreOfBestScoredAnswer(int $user_id): AsqApiScoringDtoContract;


	/**
	 * @param int $user_id
	 *
	 * @return AsqApiScoringDtoContract
	 */
	public function GetUserScoreLastOfSubmittedAnswer(int $user_id): AsqApiScoringDtoContract;
}