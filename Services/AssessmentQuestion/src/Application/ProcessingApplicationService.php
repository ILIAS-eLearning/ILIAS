<?php

namespace ILIAS\AssessmentQuestion\Application;

use ILIAS\AssessmentQuestion\CQRS\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\CQRS\Command\CommandBusBuilder;
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
    protected $container_obj_id;
    /**
     * @var int
     */
    protected $actor_user_id;
    /**
     * @var QuestionConfig
     */
    protected $question_config;
    /**
     * @var string
     */
    protected $lng_key;


    /**
     * AsqAuthoringService constructor.
     *
     * @param int $container_obj_id
     * @param int $actor_user_id
     */
    public function __construct(int $container_obj_id, int $actor_user_id, QuestionConfig $question_config, string $lng_key)
    {
        $this->container_obj_id = $container_obj_id;
        $this->actor_user_id = $actor_user_id;
        $this->question_config = $question_config;
        $this->lng_key = $lng_key;
    }

    public function getProcessingQuestionGUI(string $choose_new_question_cmd, string $revision_key) : ilAsqQuestionProcessingGUI
    {
        $processing_context_container = new ProcessingContextContainer($this->container_obj_id, $this->actor_user_id);

        return new ilAsqQuestionProcessingGUI(
            $choose_new_question_cmd,
            $revision_key,
            $processing_context_container,
            $this->question_config
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
     * @return ilAsqQuestionPageGUI
     */
    public function getQuestionPresentation(QuestionDto $question_dto, QuestionCommands $question_commands) : ilAsqQuestionPageGUI
    {
        $question_component = $this->getQuestionComponent($question_dto, $question_commands);
        $question_component->readAnswer();

        $page = Page::getPage(ilAsqQuestionPageGUI::PAGE_TYPE, $question_dto->getContainerObjId(), $question_dto->getQuestionIntId(), $this->lng_key);
        $page_gui = ilAsqQuestionPageGUI::getGUI($page);

        $page_gui->setRenderPageContainer(false);
        $page_gui->setEditPreview(true);
        $page_gui->setEnabledTabs(false);

        $page_gui->setQuestionHTML([$question_dto->getQuestionIntId() => $question_component->renderHtml()]);
        $page_gui->setPresentationTitle($question_dto->getData()->getTitle());

        return $page_gui;
    }


    public function getQuestionComponent(QuestionDto $question_dto, QuestionCommands $question_commands) : QuestionComponent
    {
        $question_component = new QuestionComponent($question_dto, $this->question_config, $question_commands);

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

        return new Answer($this->actor_user_id, $question_dto->getId(), $question_dto->getContainerObjId(),$question_dto->getRevisionId(),$editor->readAnswer());
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
     *
     * @return Answer|null
     */
    public function GetUserAnswer(string $question_id, string $revision_key, int $user_id, int $test_id) : ?Answer
    {
        //TODO get from read side after test ist finished (projected)
        /** @var Question $question */
        $question = QuestionRepository::getInstance()->getAggregateRootById(new DomainObjectId($question_id));

        return $question->getAnswer($user_id, $test_id, $revision_key);
    }


    public function GetPointsByUser(string $question_id, int $user_id, int $test_id) : float
    {
        // gets the result of the user
        $question = QuestionRepository::getInstance()->getAggregateRootById(new DomainObjectId($question_id));
        $scoring_class = QuestionPlayConfiguration::getScoringClass($question->getPlayConfiguration());
        $scoring = new $scoring_class($question);

        return $scoring->score($question->getAnswer($user_id, $test_id));
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

        return $repository->getQuestionsByContainer($this->container_obj_id);
    }
}