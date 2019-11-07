<?php

namespace ILIAS\AssessmentQuestion\Application;

use ILIAS\AssessmentQuestion\CQRS\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\CQRS\Command\CommandBusBuilder;
use ILIAS\AssessmentQuestion\DomainModel\Command\CreateQuestionRevisionCommand;
use ILIAS\AssessmentQuestion\DomainModel\Command\ScoreTestAttemptCommand;
use ILIAS\AssessmentQuestion\DomainModel\Question;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\QuestionRepository;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;
use ILIAS\AssessmentQuestion\DomainModel\Command\AnswerQuestionCommand;
use ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection\PublishedQuestionRepository;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\QuestionComponent;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Feedback\AnswerFeedbackComponent;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Feedback\FeedbackComponent;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Feedback\ScoringComponent;
use ILIAS\AssessmentQuestion\UserInterface\Web\Page\Page;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AssessmentEntityId;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\ProcessingContextContainer;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\QuestionCommands;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\QuestionConfig;
use ilAsqQuestionPageGUI;
use ilAsqQuestionProcessingGUI;
const MSG_SUCCESS = "success";

/**
 * Class PlayApplicationService
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ProcessingApplicationService
{

    /**
     * @var int
     */
    protected $processing_obj_id;
    /**
     * @var int
     */
    protected $actor_user_id;
    /**
     * @var int
     */
    protected $attempt_number;
    /**
     * @var string
     */
    protected $lng_key;


    /**
     * AsqAuthoringService constructor.
     *
     * @param int $processing_obj_id
     * @param int $actor_user_id
     */
    public function __construct(int $processing_obj_id, int $actor_user_id, int $attempt_number, string $lng_key)
    {
        $this->processing_obj_id = $processing_obj_id;
        $this->actor_user_id = $actor_user_id;
        $this->attempt_number = $attempt_number;
        $this->lng_key = $lng_key;
    }


    public function getProcessingQuestionGUI(string $revision_key, QuestionConfig $question_config) : ilAsqQuestionProcessingGUI
    {
        $processing_context_container = new ProcessingContextContainer($this->processing_obj_id, $this->actor_user_id);

        return new ilAsqQuestionProcessingGUI(
            $revision_key,
            $this->attempt_number,
            $processing_context_container,
            $question_config
        );
    }


    /**
     * @param Answer $answer
     */
    public function answerQuestion(Answer $answer)
    {
        CommandBusBuilder::getCommandBus()->handle(new AnswerQuestionCommand($answer));
    }


    /**
     * @param Answer[] $answers
     */
    public function scoreAndProjectTestAttempt(array $answers) : void
    {
        CommandBusBuilder::getCommandBus()->handle(new ScoreTestAttemptCommand($answers, $this->actor_user_id));
    }


    /**
     * @return ilAsqQuestionPageGUI
     */
    public function getQuestionPresentation(QuestionDto $question_dto, QuestionConfig $question_config, QuestionCommands $question_commands) : ilAsqQuestionPageGUI
    {
        global $DIC;

        $question_component = $this->getQuestionComponent($question_dto, $question_config, $question_commands);

        $page = Page::getPage(ilAsqQuestionPageGUI::PAGE_TYPE, $question_dto->getContainerObjId(), $question_dto->getQuestionIntId(), $this->lng_key);
        $page_gui = ilAsqQuestionPageGUI::getGUI($page);

        $page_gui->setRenderPageContainer(false);
        $page_gui->setEditPreview(true);
        $page_gui->setEnabledTabs(false);

        $page_gui->setQuestionHTML([$question_dto->getQuestionIntId() => $question_component->renderHtml()]);
        $page_gui->setPresentationTitle($question_dto->getData()->getTitle());

        $subbline = "";
        if ($question_config->getSubline()) {
            $subbline .= $question_config->getSubline() . " ";
        }
        if ($question_config->isShowTotalPointsOfQuestion()) {
            $subbline .= "(TODO " . $DIC->language()->txt('points') . ")";
        }
        $page_gui->setQuestionInfoHTML($subbline);

        return $page_gui;
    }


    public function getQuestionComponent(QuestionDto $question_dto, QuestionConfig $question_config, QuestionCommands $question_commands) : QuestionComponent
    {
        $question_component = new QuestionComponent($question_dto, $question_config, $question_commands);

        return $question_component;
    }


    /**
     * @param AssessmentEntityId $question_uuid
     *
     * @return QuestionComponent
     */
    public function getFeedbackComponent(QuestionDto $question_dto) : FeedbackComponent
    {
        return new FeedbackComponent($this->getScoringComponent($question_dto), $this->getAnswerFeedbackComponent($question_dto));
    }


    /**
     * @param AssessmentEntityId $question_uuid
     *
     * @return QuestionComponent
     */
    public function getScoringComponent(QuestionDto $question_dto) : ScoringComponent
    {
        return new ScoringComponent($question_dto, $this->getCurrentAnswer($question_dto));
    }


    /**
     * @param AssessmentEntityId $question_uuid
     *
     * @return QuestionComponent
     */
    public function getAnswerFeedbackComponent(QuestionDto $question_dto) : AnswerFeedbackComponent
    {
        return new AnswerFeedbackComponent($question_dto, $this->getCurrentAnswer($question_dto));
    }


    /**
     * @param AssessmentEntityId $question_uuid
     *
     * @return QuestionComponent
     */
    public function getCurrentAnswer(QuestionDto $question_dto) : Answer
    {
        $editor_class = QuestionPlayConfiguration::getEditorClass($question_dto->getPlayConfiguration());
        /** @var AbstractEditor $editor * */
        $editor = new $editor_class($question_dto);

        return new Answer($this->actor_user_id, $question_dto->getId(), $question_dto->getRevisionId(), $question_dto->getContainerObjId(), $this->attempt_number, $editor->readAnswer());
    }


    /**
     * @param string $question_id
     * @param int    $user_id
     * @param string $test_id
     */
    public function ClearAnswer(string $question_id, int $user_id, string $test_id)
    {
        //TODO CommandBusBuilder::getCommandBus()->handle(new QuestionAnswerClearedCommand($question_id, $user_id, $test_id));
    }


    /**
     * @param string $question_id
     * @param string $revision_key
     * @param int    $user_id
     * @param string $test_id
     * @param int    $attempt_number
     *
     * @return Answer|null
     */
    public function getUserAnswer(string $question_id, string $revision_key) : ?Answer
    {
        $question = QuestionRepository::getInstance()->getAggregateRootById(new DomainObjectId($question_id));

        return $question->getAnswer($this->actor_user_id,$this->processing_obj_id, $revision_key, $this->attempt_number);
    }


    public function GetPointsByUser(string $question_id, string $revision_key, int $user_id, int $test_id, int $attempt_number) : float
    {
        // gets the result of the user
        $question = QuestionRepository::getInstance()->getAggregateRootById(new DomainObjectId($question_id));
        $scoring_class = QuestionPlayConfiguration::getScoringClass($question->getPlayConfiguration());
        $scoring = new $scoring_class($question);

        return $scoring->score($question->getAnswer($user_id, $test_id, $revision_key, $attempt_number));
    }


    /**
     * @param string $revision_key
     *
     * @return QuestionDto
     */
    public function GetQuestion(string $revision_key) : QuestionDto
    {
        $repository = new PublishedQuestionRepository();

        return $repository->getQuestionByRevisionId($revision_key);
    }


    public function GetQuestions() : array
    {
        $repository = new PublishedQuestionRepository();

        return $repository->getQuestionsByContainer($this->processing_obj_id);
    }


    /**
     * @return Answer[]
     */
    /*public function getAnswersFromAnsweredQuestions() : array
    {
        $repository = new PublishedQuestionRepository();

        $answered_quetsion_answera = [];
        foreach ($repository->getQuestionsByContainer($this->container_obj_id) as $question) {

            $answer = $this->getUserAnswer($question->getId(), $question->getRevisionId(), $this->container_obj_id);

            if (is_object($answer)) {
                $answered_quetsion_answera[] = $answer;
            }
        }

        return $answered_quetsion_answera;
    }*/


    /*public function getUnansweredQuestions() : array
    {
        $repository = new PublishedQuestionRepository();

        $unanswered_quetsions = [];
        foreach ($repository->getQuestionsByContainer($this->container_obj_id) as $question) {
            if (is_null($this->getUserAnswer($question->getId(), $question->getRevisionId(), $this->container_obj_id))) {
                $unanswered_quetsions[] = $question;
            }
        }

        return $unanswered_quetsions;
    }*/
}