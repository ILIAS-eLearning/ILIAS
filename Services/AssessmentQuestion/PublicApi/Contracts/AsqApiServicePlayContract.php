<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Contracts;

use ILIAS\UI\Component\Component;

/**
 * Interface AsqApiServicePlayContract
 *
 * @author studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author Adrian Lüthi <al@studer-raimann.ch>
 * @author Björn Heyser <bh@bjoernheyser.de>
 * @author Martin Studer <ms@studer-raimann.ch>
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
interface AsqApiServicePlayContract {

	/**
	 * @return AsqApiComponentQuestionContract
	 *
	 * Gets Question Presentation
	 */
	public function GetQuestionPresentation(): AsqApiComponentQuestionContract;


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
	 * @param int $user_answer_id
	 *
	 * @return AsqApiComponentQuestionContract
	 *
	 * Gets Question Presentation Component with user answer
	 */
	public function GetUserAnswerPresentation(int $user_answer_id): AsqApiComponentQuestionContract;


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
	 * @param AsqApiDtoUserAnswerContract $user_answer
	 *
	 * @return int
	 */
	public function SaveUserAnswer(AsqApiDtoUserAnswerContract $user_answer): int;


	public function GetUserScore(int $user_answer_id): AsqApiDtoScoringContract;


	public function GetUserScoreOfBestScoredAnswer(int $user_id): AsqApiDtoScoringContract;


	public function GetUserScoreLastOfSubmittedAnswer(int $user_id): AsqApiDtoScoringContract;
}