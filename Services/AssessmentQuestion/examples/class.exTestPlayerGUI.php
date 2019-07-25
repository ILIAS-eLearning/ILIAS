<?php

use ILIAS\Services\AssessmentQuestion\PublicApi\PlayServiceSpec;

/**
 * When a component wants to integrate the assessment question service to present questions
 * to users in an assessment scenario, the following use cases needs to be handled by the component.
 * 
 * This kind consume of the assessment question service does not support the offline export presentation yet.
 */
class exTestPlayerGUI
{
	/**
	 * When presenting an assessment question to a user, it is relevant wether the user
	 * has allready submited an answer or not.
	 *
	 * Simply determine the relevant question uuid and a possible user answer uuid. Then use
	 * the asq play service to retrieve either a question presentation without user answer,
	 * or to retrieve a question presentation including a user's answer.
	 *
	 * The return value of the corresponding service methods are renderable ui components.
	 *
	 * For the presentation of generic or answer specific feedbacks the asq play service
	 * comes with additional methods, that also returns ui components again.
	 *
	 * Variants in usage defined by the consumer:
	 * - question can be shown with user answer
	 * - question can be shown without user answer
	 * - feedbacks can be shown if required
	 */
	public function showQuestion()
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		/**
		 * initialise with id of question to be shown
		 */
		$questionUuid = 'any-valid-question-uuid';
		
		/**
		 * fetch possibly existing participant answer uuid,
		 * an empty string is returned when no user answer exists
		 */
		
		$userAnswerUuid = $this->getParticipantAnswerUuid($questionUuid);
		
		/**
		 * initialise the asq play service
		 */

		$asqPlayService = $DIC->assessment()->service()->play(
			$this->buildAsqPlayServiceSpec(), $DIC->assessment()->consumer()->questionUuid($questionUuid)
		);
		
		/**
		 * get a question presentation with or without a user answer
		 */
		
		if( $userAnswerUuid )
		{
			$questionComponent = $asqPlayService->GetUserAnswerPresentation(
				$DIC->assessment()->consumer()->userAnswerUuid($userAnswerUuid)
			);
		}
		else
		{
			$questionComponent = $asqPlayService->GetQuestionPresentation();
		}
		
		$testplayerPageHTML = $DIC->ui()->renderer()->render($questionComponent);
		
		/**
		 * get feedback presentation
		 */
		
		if( $userAnswerUuid && $showFeedbacks = true )
		{
			$genericFeedbackComponent = $asqPlayService->getGenericFeedbackOutput(
				$DIC->assessment()->consumer()->userAnswerUuid($userAnswerUuid)
			);
			
			$specificFeedbackComponent = $asqPlayService->getSpecificFeedbackOutput(
				$DIC->assessment()->consumer()->userAnswerUuid($userAnswerUuid)
			);
			
			$testplayerPageHTML .= $DIC->ui()->renderer()->render([
				$genericFeedbackComponent, $specificFeedbackComponent
			]);
		}
		
		$testplayerPageHTML; // complete test player question page html
	}
	
	/**
	 * With the presentation of an assessment question, this question also gets submitted having any solution
	 * filled out by any user. With the first presentation there should be no previous user response available.
	 * The consuming component needs to request an empty ilAsqSolution instance for the given questionId.
	 * 
	 * The ilAsqSolution interface method initFromServerRequest is to be used to initialize the object instance
	 * with the user response. With the current concept the newly introduced \Psr\Http\Message\ServerRequestInterface
	 * needs to be injected to this method, but may simply passing $_POST could be an alternative.
	 * This depends on the future strategy of abstracting the http server request in ILIAS.
	 * 
	 * After having this solution saved, the consuming component needs to register the now available solutionId
	 * together with the questionId and the userId. Additionally this ilAsqSolution instance can be used
	 * with a question corresponding ilAsqResultCalculator to retrieve the information about right/wrong
	 * (used for e.g. answer status in CTM's test sequence) and reached points (used as a future ilTestResult)
	 * to be stored as any result within the consuming component.
	 * 
	 * After the first submission of any user response the consuming component needs to provide the corresponsing
	 * solutionId to request the existing ilAsqSolution instance from the ilAsqFactory for every additional submit.
	 * 
	 * The way harvesting and handling solution data in short:
	 * - post submit gets parsed by ilAsqSolution
	 * - ilAsqResultCalculator calculates points and right/wrong
	 * - the test object can use these information for different purposes
	 * - points can be saved as an ilTestResult referenced by the questionId and the participantId
	 * - right/wrong can be used for determining the correct feedbacks for the feedback loop
	 * - right/wrong can be used as the answer status within the CTM test sequence
	 */
	public function submitSolution()
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		/**
		 * initialise with id of question to be shown
		 */
		
		$questionUuid = $DIC->assessment()->consumer()->questionUuid('any-valid-question-uuid');
		
		/**
		 * fetch possibly existing participant answer uuid,
		 * when no participant answer exist yet,
		 * generate a new user answer uuid
		 */
		
		$userAnswerUuid = $this->getParticipantAnswerUuid($questionUuid->getId());
		
		if( $userAnswerUuid )
		{
			$userAnswerUuid = $DIC->assessment()->consumer()->userAnswerUuid($userAnswerUuid);
		}
		else
		{
			$userAnswerUuid = $DIC->assessment()->consumer()->userAnswerUuid();
		}
		
		/**
		 * generate a user answer submit containing the post data
		 */
		
		$userAnswerSubmit = $DIC->assessment()->consumer()->userAnswerSubmit(
			$userAnswerUuid, $questionUuid, $DIC->user()->getId(), $_POST
		);
		
		/**
		 * - initialise the asq play service
		 * - save the participant's answer submission
		 * - retrieve the scoring for the answer
		 */
		
		$asqPlayService = $DIC->assessment()->service()->play(
			$this->buildAsqPlayServiceSpec(), $questionUuid
		);
		
		$asqPlayService->SaveUserAnswer($userAnswerSubmit);
		
		$userAnswerScoring = $asqPlayService->GetUserScore($userAnswerUuid);
		
		/**
		 * handle the calculated result in any kind
		 */
		
		// can be stored in any ilTestResult object managed by the test
		$reachedPoints = $userAnswerScoring->getPoints();
		
		// can be used to differ answer status in CTM's test sequence
		$isCorrect = $userAnswerScoring->isCorrect();
	}
	
	/**
	 * This method checks, wether the user allready submitted an answer, by looking up
	 * self managed test results. When an user answer is found, the uuid string is returned.
	 *
	 * When no user answer has been submitted yet, an empty string is returned.
	 *
	 * @param string $questionUuid
	 * @return string
	 */
	public function getParticipantAnswerUuid(string $questionUuid): string
	{
		/**
		 * when the test has any test result for the given question uuid,
		 * based on an existing participant answer, the participant answer uuid
		 * needs to be looked up and is to be returned.
		 */
		
		$userAnswerUuid = ''; // use $questionUuid and lookup $userAnswerUuid
		
		if( $userAnswerUuid )
		{
			return $userAnswerUuid;
		}
		
		return '';
	}
	
	/**
	 * this method does build an asq play service specification object,
	 * that is needed to get an instance of the asq play service.
	 *
	 * it describes us as a consumer of the asq play service.
	 *
	 * @return PlayServiceSpec
	 */
	public function buildAsqPlayServiceSpec()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		return $DIC->assessment()->specification()->play(
			$this->object->getId(), $DIC->user()->getId()
		);
	}
}