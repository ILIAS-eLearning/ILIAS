<?php

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiScoringDto;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiUserAnswerDto;
use ILIAS\UI\Component\Component;

/**
 * Interface AsqApiPlayService
 *
 * @author studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author Adrian Lüthi <al@studer-raimann.ch>
 * @author Björn Heyser <bh@bjoernheyser.de>
 * @author Martin Studer <ms@studer-raimann.ch>
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
interface AsqApiPlayService {

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
	 * @param string   $question_uuid
	 * @param int      $user_answer_id
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
	 * @param AsqApiUserAnswerDto $user_answer
	 *
	 * @return int
	 */
	public function SaveUserAnswer(AsqApiUserAnswerDto $user_answer): int;


	/**
	 * @param int $user_answer_id
	 *
	 * @return AsqApiScoringDto
	 */
	public function GetUserScore(int $user_answer_id): AsqApiScoringDto;


	/**
	 * @param int $user_id
	 *
	 * @return AsqApiScoringDto
	 */
	public function GetUserScoreOfBestScoredAnswer(int $user_id): AsqApiScoringDto;


	/**
	 * @param int $user_id
	 *
	 * @return AsqApiScoringDto
	 */
	public function GetUserScoreLastOfSubmittedAnswer(int $user_id): AsqApiScoringDto;
}