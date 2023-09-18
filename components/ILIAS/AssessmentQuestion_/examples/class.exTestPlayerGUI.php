<?php

/**
 * When a component wants to integrate the assessment question service to present questions
 * to users in an assessment scenario, the following use cases needs to be handled by the component.
 *
 * This kind consume of the assessment question service does not support the offline export presentation yet.
 */
class exTestPlayerGUI
{
    /**
     * When presenting an assessment question to a user, the ilAsqQuestionPresentation provides the
     * interface methods to either get a renerable UI coponent for a question presentation or
     * for a solution presentation. An instance implementing the ilAsqPresentation according to
     * the given question type is provided by a factory method within ilAsqFactory.
     *
     * Both methods getQuestionPresentation and getSolutionPresentation gets an instance of ilAsqSolution injected.
     * This solution can either be an user solution or the best solution.
     *
     * Additional interface methods in ilAsqQuestionPresentation return renderable UI components for the generic
     * and specific feedbacks. These methods also need to get an ilAsqSolution instance injected.
     *
     * In case of presenting the best solution the required ilAsqSolution instance can be retrieved
     * from the ilAsqQuestion interface.
     *
     * For any existing user response that is to be presented with the question the instance
     * implementing ilAsqSolution can be requested from the ilAsqFactory using the solutionId
     * that is to be registered within the consuming component in relation to the user's id
     * and additional information (like e.g. test results).
     *
     * Variants in usage defined by the consumer:
     * - question can be shown writable for the examine
     * - question can be shown readable for the examine
     * - feedbacks can be shown if required
     * - best solution can be shown if required
     */
    public function showQuestion()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        $questionId = 0; // initialise with id of question to be shown

        /**
         * fetch possibly existing participant solution, an empty one is required otherwise
         */

        $participantSolution = $this->getParticipantSolution($questionId);

        /**
         * question presentation to be answered by the examine
         */

        $questionInstance = $DIC->question()->getQuestionInstance($questionId);
        $questionPresentationGUI = $DIC->question()->getQuestionPresentationInstance($questionInstance);

        $questionNavigationAware; /* @var ilAsqQuestionNavigationAware $questionNavigationAware */
        $questionPresentationGUI->setQuestionNavigation($questionNavigationAware);

        $questionPresentationGUI->setRenderPurpose(ilAsqQuestionPresentation::RENDER_PURPOSE_PLAYBACK);

        if ($participantSolutionLocked = false) {
            $renderer = $questionPresentationGUI->getSolutionPresentation($participantSolution);
        } else {
            $renderer = $questionPresentationGUI->getQuestionPresentation($participantSolution);
        }

        $playerQstPageHTML = $renderer->getContent();

        /**
         * feedback presentation for the given
         */

        if ($showFeedbacks = true && !$participantSolution->isEmpty()) {
            $genericFeedbackRenderer = $questionPresentationGUI->getGenericFeedbackOutput($participantSolution);
            $playerQstPageHTML .= $genericFeedbackRenderer->getContent();

            $specificFeedbackRenderer = $questionPresentationGUI->getSpecificFeedbackOutput($participantSolution);
            $playerQstPageHTML .= $specificFeedbackRenderer->getContent();
        }

        /**
         * best solution presentation to be answered by the examine
         */

        if ($showBestSolution = true) {
            $renderer = $questionPresentationGUI->getSolutionPresentation(
                $questionInstance->getBestSolution()
            );

            $playerQstPageHTML .= $renderer->getContent();
        }

        $playerQstPageHTML; // complete question page html
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

        // this can also be $_REQUEST or any other future ilias post-request handler
        $serverRequestObject; /* @var \Psr\Http\Message\ServerRequestInterface $serverRequestObject */

        $questionId = 0; // initialise with id of question that just submits

        /**
         * fetch possibly existing participant solution, an empty one is required otherwise
         */

        $participantSolution = $this->getParticipantSolution($questionId);

        /**
         * let the solution object instance harvest the submission post data
         */

        $participantSolution->initFromServerRequest($serverRequestObject);

        /**
         * get results calculator to be used to retrieve calculated reached points
         * that can be stored in a test result storage managed by the test object
         */

        $questionInstance = $DIC->question()->getQuestionInstance($questionId);
        $solutionInstance = $this->getParticipantSolution($questionId);
        $resultCalculator = $DIC->question()->getResultCalculator($questionInstance, $solutionInstance);

        $resultInstance = $resultCalculator->calculate();

        /**
         * handle the calculated result in any kind
         */

        // can be stored in any ilTestResult object managed by the test
        $reachedPoints = $resultInstance->getPoints();

        // can be used to differ answer status in CTM's test sequence
        $isCorrect = $resultInstance->isCorrect();
    }

    /**
     * this method returns either an initialised solution object instance, or and empty one,
     * depending on self managed test results (handled by a future ilTestResult)
     *
     * @param integer $questionId
     * @return ilAsqQuestionSolution
     */
    public function getParticipantSolution($questionId)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        /**
         * when the test has any test result based on an existing participant solution,
         * the solution id needs to be looked up. an empty solution is returned otherwise.
         */

        $solutionId = 0;

        if ($solutionId) {
            return $DIC->question()->getQuestionSolutionInstance($questionId, $solutionId);
        }

        return $DIC->question()->getEmptyQuestionSolutionInstance($questionId);
    }
}
