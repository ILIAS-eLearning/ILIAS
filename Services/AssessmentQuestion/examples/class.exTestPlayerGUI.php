<?php
declare(strict_types=1);

use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AssessmentEntityId;
use ILIAS\Services\AssessmentQuestion\PublicApi\Processing\ProcessingService;
use ILIAS\Services\AssessmentQuestion\PublicApi\Processing\QuestionRevisionId;
use ILIAS\Services\AssessmentQuestion\PublicApi\Processing\UserAnswerId;
use ILIAS\Services\AssessmentQuestion\PublicApi\Processing\UserAnswerSubmit;

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

    /**
     * @var ProcessingService
     */
    protected $processing_service;


    public function __construct()
    {
        /* @var ILIAS\DI\Container $DIC */ global $DIC;

        $this->processing_service = $DIC->assessment()->questionProcessing($DIC->user()->getId());
    }


    public function showQuestion()
    {
        global $DIC;
        /* @var ILIAS\DI\Container $DIC */

        /**
         * initialise with revision_id of question to be shown
         */
        $revision_uuid = 'any-valid-revision-uuid';

        /**
         * fetch possibly existing participant answer uuid,
         * an empty string is returned when no user answer exists
         */
        $user_answer_uuid = $this->getParticipantAnswerUuid(new QuestionRevisionId($revision_uuid));

        /**
         * initialise the processing question service
         */
        $processing_question = $this->processing_service->question(new QuestionRevisionId($revision_uuid), $user_answer_uuid);

        /**
         * get a question presentation with or without a user answer
         */
        $questionComponent = $processing_question->getQuestionPresentation();

        $testplayerPageHTML = $DIC->ui()->renderer()->render($questionComponent);

        /**
         * get feedback presentation
         */

        if ($showFeedbacks = true) {
            $genericFeedbackComponent = $processing_question->getGenericFeedbackOutput();

            $specificFeedbackComponent = $processing_question->getSpecificFeedbackOutput();

            $testplayerPageHTML .= $DIC->ui()->renderer()->render([
                $genericFeedbackComponent,
                $specificFeedbackComponent,
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
        global $DIC;
        /* @var ILIAS\DI\Container $DIC */

        /**
         * initialise with id of question to be shown
         */
        $question_revision_id = $DIC->assessment()->entityIdBuilder()->fromString('any-valid-revision-uuid');

        /**
         * fetch possibly existing participant answer uuid,
         * when no participant answer exist yet,
         * generate a new user answer uuid
         */
        $user_answer_id = $this->getParticipantAnswerUuid($question_revision_id);

        /**
         * initialise the processing question service
         */
        $processing_question = $this->processing_service->question($question_revision_id, $user_answer_id);

        /**
         * generate a user answer submit containing the post data
         */
        $user_answer_submit = new UserAnswerSubmit(json_encode($DIC->http()->request('user_answer')));
        $processing_question->storeUserAnswerstoreUserAnswer($user_answer_submit);

        $user_answer_score = $processing_question->getUserScore();

        /**
         * handle the calculated result in any kind
         */

        // can be stored in any ilTestResult object managed by the test
        $reached_points = $user_answer_score->getPoints();

        // can be used to differ answer status in CTM's test sequence
        $is_correct = $user_answer_score->isCorrect();
    }


    /**
     * This method checks, wether the user allready submitted an answer, by looking up
     * self managed test results. When an user answer is found, the uuid string is returned.
     *
     * When no user answer has been submitted yet, an empty string is returned.
     *
     * @param QuestionRevisionId $revision_uuid
     *
     * @return UserAnswerId
     */
    public function getParticipantAnswerUuid(QuestionRevisionId $revision_uuid) : AssessmentEntityId
    {
        global $DIC;

        /**
         * when the test has any test result for the given question uuid,
         * based on an existing participant answer, the participant answer uuid
         * needs to be looked up and is to be returned.
         */

        $user_answer_id = null; // use $revision_uuid and lookup UserAnswerId

        if (is_object($user_answer_id)) {
            return $user_answer_id;
        }

        return $DIC->assessment()->entityIdBuilder()->new();
    }
}