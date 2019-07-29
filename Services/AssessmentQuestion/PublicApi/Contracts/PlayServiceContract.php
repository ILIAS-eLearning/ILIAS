<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Contracts;

use ILIAS\UI\Component\Component;

/**
 * Interface PlayServiceContract
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi\Contracts
 */
interface PlayServiceContract {

	/**
	 * @param UserAnswerIdContract $userAnswerUuid
	 * @return QuestionComponentContract
	 *
	 * Gets Question Presentation Component with user answer
	 */
	public function GetQuestionPresentation(UserAnswerIdContract $userAnswerUuid): QuestionComponentContract;


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
	): QuestionComponentContract;

	/**
	 * @param UserAnswerIdContract $userAnswerUuid
	 * @return Component
	 */
	public function getGenericFeedbackOutput(UserAnswerIdContract $userAnswerUuid): Component;


	/**
	 * @param UserAnswerIdContract $userAnswerUuid
	 * @return Component
	 */
	public function getSpecificFeedbackOutput(UserAnswerIdContract $userAnswerUuid): Component;


	/**
	 * @param UserAnswerSubmitContract $user_answer
	 * @throws UserAnswerUuidAlreadyExistsException
	 * @throws UserGivesAnAnswerToAnOtherRevisionThanTheContainerHasInitiatedException
	 * @throws UserGivesAnAnswerToAnOtherQuestionThanTheContainerHasInitiatedException
	 */
	public function CreateUserAnswer(UserAnswerSubmitContract $user_answer):void;

	/**
	 * @param UserAnswerSubmitContract $user_answer
	 * @throws NoUserAnswerFoundToUpateException
	 * @throws UserGivesAnAnswerToAnOtherRevisionThanTheContainerHasInitiatedException
	 * @throws UserGivesAnAnswerToAnOtherQuestionThanTheContainerHasInitiatedException
	 */
	public function UpdateUserAnswer(UserAnswerSubmitContract $user_answer):void;
	
	/**
	 * @param UserAnswerIdContract $userAnswerUuid
	 * @return UserAnswerScoringContract
	 */
	public function GetUserScore(UserAnswerIdContract $userAnswerUuid): UserAnswerScoringContract;
}