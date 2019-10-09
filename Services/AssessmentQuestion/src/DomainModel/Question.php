<?php

namespace ILIAS\AssessmentQuestion\DomainModel;

use ilDateTimeException;
use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractEventSourcedAggregateRoot;
use ILIAS\AssessmentQuestion\CQRS\Aggregate\AggregateRoot;
use ILIAS\AssessmentQuestion\CQRS\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\CQRS\Aggregate\IsRevisable;
use ILIAS\AssessmentQuestion\CQRS\Aggregate\RevisionId;
use ILIAS\AssessmentQuestion\CQRS\Event\DomainEvents;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOptions;
use ILIAS\AssessmentQuestion\DomainModel\Event\QuestionAnswerAddedEvent;
use ILIAS\AssessmentQuestion\DomainModel\Event\QuestionAnswerOptionsSetEvent;
use ILIAS\AssessmentQuestion\DomainModel\Event\QuestionCreatedEvent;
use ILIAS\AssessmentQuestion\DomainModel\Event\QuestionDataSetEvent;
use ILIAS\AssessmentQuestion\DomainModel\Event\QuestionFeedbackSetEvent;
use ILIAS\AssessmentQuestion\DomainModel\Event\QuestionHintsSetEvent;
use ILIAS\AssessmentQuestion\DomainModel\Event\QuestionLegacyDataSetEvent;
use ILIAS\AssessmentQuestion\DomainModel\Event\QuestionPlayConfigurationSetEvent;
use ILIAS\AssessmentQuestion\DomainModel\Event\QuestionRevisionCreatedEvent;
use ILIAS\AssessmentQuestion\DomainModel\Hint\Hints;
use ILIAS\Services\AssessmentQuestion\DomainModel\Feedback\Feedback;

/**
 * Class Question
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class Question extends AbstractEventSourcedAggregateRoot implements IsRevisable
{

    //TODO get that from DB
    const SYSTEM_USER_ID = 3;
    /**
     * @var DomainObjectId
     */
    private $id;
    /**
     * @var RevisionId
     */
    private $revision_id;
    /**
     * @var string
     */
    private $revision_name;
    /**
     * @var int
     */
    private $creator_id;
    /**
     * @var int
     */
    private $container_obj_id;
    /**
     * @var int
     */
    private $question_int_id;
    /**
     * @var QuestionData
     */
    private $data;
    /**
     * @var QuestionPlayConfiguration
     */
    private $play_configuration;
    /**
     * @var AnswerOptions
     */
    private $answer_options;
    /**
     * @var Hints
     */
    private $hints;
    /**
     * @var array
     */
    private $answers;
    /**
     * @var QuestionLegacyData
     */
    private $legacy_data;
    /**
     * @var ContentEditingMode
     */
    private $contentEditingMode;
    /**
     * @var Feedback
     */
    protected $feedback;


    /**
     * Question constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->answers = [];
        $this->answer_options = new AnswerOptions();
        $this->hints = new Hints();

        /**
         * TODO: I guess this is not the right place.
         * It just helps to develop for the moment.
         */
        $this->contentEditingMode = new ContentEditingMode(
            ContentEditingMode::PAGE_OBJECT
        );
    }


    /**
     * @param DomainObjectId $question_uuid
     * @param int            $initiating_user_id
     *
     * @return Question
     * @throws ilDateTimeException
     */
    public static function createNewQuestion(
        DomainObjectId $question_uuid,
        int $container_obj_id,
        int $initiating_user_id,
        int $question_int_id
    ) : Question {
        $question = new Question();
        $question->ExecuteEvent(
            new QuestionCreatedEvent(
                $question_uuid,
                $container_obj_id,
                $initiating_user_id,
                $question_int_id
            ));

        return $question;
    }


    /**
     * @param QuestionCreatedEvent $event
     */
    protected function applyQuestionCreatedEvent(QuestionCreatedEvent $event)
    {
        $this->id = $event->getAggregateId();
        $this->creator_id = $event->getInitiatingUserId();
        $this->container_obj_id = $event->getContainerObjId();
        $this->question_int_id = $event->getQuestionIntId();
    }


    /**
     * @param QuestionDataSetEvent $event
     */
    protected function applyQuestionDataSetEvent(QuestionDataSetEvent $event)
    {
        $this->data = $event->getData();
    }


    /**
     * @param QuestionPlayConfigurationSetEvent $event
     */
    protected function applyQuestionPlayConfigurationSetEvent(QuestionPlayConfigurationSetEvent $event)
    {
        $this->play_configuration = $event->getPlayConfiguration();
    }


    /**
     * @param QuestionRevisionCreatedEvent $event
     */
    protected function applyQuestionRevisionCreatedEvent(QuestionRevisionCreatedEvent $event)
    {
        $this->revision_id = new RevisionId($event->getRevisionKey());
    }


    /**
     * @param QuestionAnswerOptionsSetEvent $event
     */
    protected function applyQuestionAnswerOptionsSetEvent(QuestionAnswerOptionsSetEvent $event)
    {
        $this->answer_options = $event->getAnswerOptions();
    }


    /**
     * @param QuestionHintsSetEvent $event
     */
    protected function applyQuestionHintsSetEvent(QuestionHintsSetEvent $event)
    {
        $this->hints = $event->getHints();
    }


    /**
     * @param QuestionAnswerAddedEvent $event
     */
    protected function applyQuestionAnswerAddedEvent(QuestionAnswerAddedEvent $event)
    {
        $answer = $event->getAnswer();
        $this->answers[$answer->getTestId()][$answer->getAnswererId()] = $answer;
    }

    /**
     * @param QuestionAnswerAddedEvent $event
     */
    protected function applyQuestionFeedbackSetEvent(QuestionFeedbackSetEvent $event)
    {
        $feedback = $event->getFeedback();
        $this->feedback = $feedback;
    }


    /**
     * @param QuestionLegacyDataSetEvent $event
     */
    protected function applyQuestionLegacyDataSetEvent(QuestionLegacyDataSetEvent $event)
    {
        $this->legacy_data = $event->getLegacyData();
    }


    /**
     * @return QuestionData
     */
    public function getData() : ?QuestionData
    {
        return $this->data;
    }


    /**
     * @param QuestionData $data
     * @param int          $container_obj_id
     * @param int          $creator_id
     *
     * @throws ilDateTimeException
     */
    public function setData(QuestionData $data, int $container_obj_id, int $creator_id = self::SYSTEM_USER_ID) : void
    {
        $this->ExecuteEvent(new QuestionDataSetEvent(
            $this->getAggregateId(),
            $container_obj_id,
            $creator_id,
            $this->getQuestionIntId(),
            $data));
    }


    /**
     * @return QuestionPlayConfiguration
     */
    public function getPlayConfiguration() : ?QuestionPlayConfiguration
    {
        return $this->play_configuration;
    }


    /**
     * @param QuestionPlayConfiguration $play_configuration
     * @param int                       $creator_id
     *
     * @throws ilDateTimeException
     */
    public function setPlayConfiguration(
        QuestionPlayConfiguration $play_configuration,
        int $container_obj_id,
        int $creator_id = self::SYSTEM_USER_ID
    ) : void {
        $this->ExecuteEvent(new QuestionPlayConfigurationSetEvent(
            $this->getAggregateId(),
            $container_obj_id,
            $creator_id,
            $this->getQuestionIntId(),
            $play_configuration));
    }


    /**
     * @return QuestionLegacyData
     */
    public function getLegacyData() : ?QuestionLegacyData
    {
        return $this->legacy_data;
    }


    /**
     * @param QuestionLegacyData $legacy_data
     * @param int                $creator_id
     *
     * @throws ilDateTimeException
     */
    public function setLegacyData(
        QuestionLegacyData $legacy_data,
        int $container_obj_id,
        int $creator_id = self::SYSTEM_USER_ID
    ) : void {
        $this->ExecuteEvent(new QuestionLegacyDataSetEvent($this->getAggregateId(),
            $container_obj_id,
            $creator_id,
            $this->getQuestionIntId(),
            $legacy_data));
    }


    /**
     * @return AnswerOptions
     */
    public function getAnswerOptions() : AnswerOptions
    {
        return $this->answer_options;
    }

    /**
     * @param AnswerOptions $options
     * @param int           $creator_id
     *
     * @throws ilDateTimeException
     */
    public function setAnswerOptions(AnswerOptions $options, int $container_obj_id, int $creator_id = self::SYSTEM_USER_ID)
    {
        $this->ExecuteEvent(new QuestionAnswerOptionsSetEvent(
            $this->getAggregateId(),
            $container_obj_id,
            $creator_id,
            $this->getQuestionIntId(),
            $options));
    }


    /**
     * @return Hints
     */
    public function getHints() : Hints
    {
        return $this->hints;
    }


    /**
     * @param Hints $hints
     * @param int           $creator_id
     *
     * @throws ilDateTimeException
     */
    public function setHints(Hints $hints, int $container_obj_id, int $creator_id = self::SYSTEM_USER_ID)
    {
        $this->ExecuteEvent(new QuestionHintsSetEvent(
            $this->getAggregateId(),
            $container_obj_id,
            $creator_id,
            $this->getQuestionIntId(),
            $hints));
    }


    /**
     * @param Answer $answer
     *
     * @throws ilDateTimeException
     */
    function addAnswer(Answer $answer, $container_obj_id)
    {
        $this->ExecuteEvent(new QuestionAnswerAddedEvent(
            $this->getAggregateId(),
            $container_obj_id,
            $answer->getAnswererId(),
            $this->getQuestionIntId(),
            $answer));
    }


    /**
     * @param int    $user_id
     * @param string $test_id
     *
     * @return Answer|null
     */
    public function getAnswer(int $user_id, string $test_id) : ?Answer
    {
        return $this->answers[$test_id][$user_id];
    }


    /**
     * @param int    $user_id
     * @param string $test_id
     */
    function clearAnswer(int $user_id, string $test_id)
    {

    }

    /**
     * @return Feedback
     */
    public function getFeedback() : ?Feedback
    {
        return $this->feedback;
    }


    /**
     * @param Feedback $feedback
     * @param int $creator_id
     *
     * @throws ilDateTimeException
     */
    public function setFeedback(
        Feedback $feedback,
        int $container_obj_id,
        int $creator_id = self::SYSTEM_USER_ID
    ) : void {
        $this->ExecuteEvent(new QuestionFeedbackSetEvent(
            $this->getAggregateId(),
            $container_obj_id,
            $creator_id,
            $this->getQuestionIntId(),
            $feedback));
    }


    /**
     * @return ContentEditingMode
     */
    public function getContentEditingMode() : ContentEditingMode
    {
        return $this->contentEditingMode;
    }


    /**
     * @param ContentEditingMode $contentEditingMode
     */
    public function setContentEditingMode(ContentEditingMode $contentEditingMode) : void
    {
        $this->contentEditingMode = $contentEditingMode;
    }


    /**
     * @return int
     */
    public function getCreatorId() : int
    {
        return $this->creator_id;
    }


    /**
     * @return int
     */
    public function getContainerObjId() : int
    {
        return $this->container_obj_id;
    }


    /**
     * @return int
     */
    public function getQuestionIntId() : int
    {
        return $this->question_int_id;
    }


    /**
     * @param int $creator_id
     */
    public function setCreatorId(int $creator_id) : void
    {
        $this->creator_id = $creator_id;
    }


    /**
     * @return RevisionId revision id of object
     */
    public function getRevisionId() : ?RevisionId
    {
        return $this->revision_id;
    }


    /**
     * @param RevisionId $id
     *
     * @return mixed|void
     * @throws ilDateTimeException
     */
    public function setRevisionId(RevisionId $id)
    {
        $this->ExecuteEvent(new QuestionRevisionCreatedEvent(
            $this->getAggregateId(),
            $this->getContainerObjId(),
            $this->getCreatorId(),
            $this->getQuestionIntId(),
            $id->GetKey()));
    }


    /**
     * @return string
     *
     * Name of the revision used by the RevisionFactory when generating a revision
     * Using of Creation Date and or an increasing Number are encouraged
     *
     */
    public function getRevisionName() : ?string
    {
        return time();
    }


    /**
     * @return array
     *
     * Data used for signing the revision, so this method needs to to collect all
     * Domain specific data of an object and return it as an array
     */
    public function getRevisionData() : array
    {
        $data = [];
        $data[] = $this->getAggregateId()->getId();
        $data[] = $this->getData();
        $data[] = $this->getAnswerOptions();

        return $data;
    }


    public static function reconstitute(DomainEvents $event_history) : AggregateRoot
    {
        $question = new Question();
        foreach ($event_history->getEvents() as $event) {
            $question->applyEvent($event);
        }

        return $question;
    }


    function getAggregateId() : DomainObjectId
    {
        return $this->id;
    }
}