<?php

use ILIAS\Services\AssessmentQuestion\PublicApi\PlayServiceSpec;
use ILIAS\Services\AssessmentQuestion\PublicApi\UserAnswerSubmit;

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
	 * has already submitted an answer or not.
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
		 * initialise with revision_id of question to be shown
		 */
		$revisionUuid = 'any-valid-revision-uuid';
		
		/**
		 * fetch possibly existing participant answer uuid,
		 * an empty string is returned when no user answer exists
		 */
		$userAnswerUuid = $this->getParticipantAnswerUuid($questionUuid);
		
		/**
		 * initialise the asq play service
		 */
		$asqPlayService = $DIC->assessment()->service()->play(
			$this->buildAsqPlayServiceSpec(), $DIC->assessment()->consumer()->questionUuid($questionUuid),$DIC->assessment()->consumer()->revisionUuid($questionUuid,$revisionUuid)
		);
		
		/**
		 * get a question presentation with or without a user answer
		 */
		
		if( $userAnswerUuid )
		{
			$questionComponent = $asqPlayService->GetQuestionPresentation(
				$DIC->assessment()->consumer()->userAnswerUuid($userAnswerUuid)
			);
		}
		else
		{
			$questionComponent = $asqPlayService->GetQuestionPresentation($DIC->assessment()->consumer()->newUserAnswerUuid());
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
	 * When the user submits an answer the answer is to be saved using the asq play service.
	 *
	 * To get an user answer saved it is neccessary to have an user answer uuid. When the user already submitted
	 * an answer before, the test can lookup the user answer uuid in the test results.
	 * Otherwise a new user answer uuid is to be generated.
	 *
	 * Once the user answer submission has been saved, the asq play service can be used to retrieve
	 * an user answer scoring, that offers all neccessary result information.
	 * 
	 * The way harvesting and handling user answer data in short:
	 * - lookup a possibly existing user answer uuid, generate a new one otherwise
	 * - create an user answer submit conataining the post submit data
	 * - save the user answer submit using the asq play service
	 * - retrieve the user answer scoring using the asq play service
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
		$revisionUuid = $DIC->assessment()->consumer()->questionUuid('any-valid-revision-uuid');
		
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
			$userAnswerUuid = $DIC->assessment()->consumer()->newUserAnswerUuid();
		}
		
		/**
		 * generate a user answer submit containing the post data
		 */
		$asqPlayService = $DIC->assessment()->service()->play(
			$this->buildAsqPlayServiceSpec(), $DIC->assessment()->consumer()->questionUuid($questionUuid),$DIC->assessment()->consumer()->revisionUuid($questionUuid,$revisionUuid)
		);
		
		$DIC->assessment()->service()->play()->CreateUserAnswer(
				new UserAnswerSubmitContract(
					$DIC->assessment()->consumer()->UserAnswerUuid(
						new PostDataFromServerRequest($request)->get('user_answer_uuid')
					),
                    $DIC->assessment()->consumer()->questionUuid(
                        new PostDataFromServerRequest($request)->get('question_uuid')
                    ),
                    $DIC->assessment()->consumer()->revisionUuid(
	                    new PostDataFromServerRequest($request)->get('revision_uuid')
                    ),
                    $user_id,
                    json_encode(
                        new PostDataFromServerRequest($request)->get('user_answer')
					)
                )
			);



		
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