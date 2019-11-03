<?php

namespace ILIAS\AssessmentQuestion\Application;

use ILIAS\AssessmentQuestion\Application\QtiV2\Import\QtiImportService;
use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\CQRS\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\CQRS\Aggregate\IsValueOfOrderedList;
use ILIAS\AssessmentQuestion\CQRS\Command\CommandBusBuilder;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\DomainModel\QuestionRepository;
use ILIAS\AssessmentQuestion\DomainModel\Command\CreateQuestionCommand;
use ILIAS\AssessmentQuestion\DomainModel\Command\CreateQuestionRevisionCommand;
use ILIAS\AssessmentQuestion\DomainModel\Command\SaveQuestionCommand;
use ILIAS\AssessmentQuestion\Infrastructure\Persistence\EventStore\QuestionEventStoreRepository;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\QuestionComponent;
use ILIAS\AssessmentQuestion\UserInterface\Web\Page\Page;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AssessmentEntityId;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\QuestionCommands;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\QuestionConfig;
use ilAsqQuestionPageGUI;

/**
 * Class AuthoringApplicationService
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AuthoringApplicationService
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
     * @var string
     */
    protected $lng_key;

    const QTI_FIELD_LABEL_QUESTION_TYPE = "QUESTIONTYPE";


    /**
     * AsqAuthoringService constructor.
     *
     * @param int $container_obj_id
     * @param int $actor_user_id
     */
    public function __construct(int $container_obj_id, int $actor_user_id, string $lng_key)
    {
        $this->container_obj_id = $container_obj_id;
        $this->actor_user_id = $actor_user_id;
        $this->lng_key = $lng_key;
    }


    /**
     * @param string $aggregate_id
     *
     * @return QuestionDto
     */
    public function getQuestion(string $aggregate_id) : QuestionDto
    {
        $question = QuestionRepository::getInstance()->getAggregateRootById(new DomainObjectId($aggregate_id));

        if(is_object($question->getAggregateId())) {
            return QuestionDto::CreateFromQuestion($question);
        }
        $question_dto = new QuestionDto();
        $question_dto->setId($aggregate_id);
        return $question_dto;
    }


    public function createQuestion(
        DomainObjectId $question_uuid,
        int $container_id,
        ?string $container_obj_type = null,
        ?int $object_id = null,
        ?int $answer_type_id = null,
        ?string $content_editing_mode
    ) : void {
        //CreateQuestion.png
        CommandBusBuilder::getCommandBus()->handle
        (new CreateQuestionCommand
        ($question_uuid,
            $this->actor_user_id,
            $container_id,
            $container_obj_type,
            $answer_type_id,
            $object_id,
            $content_editing_mode));
    }


    public function saveQuestion(QuestionDto $question_dto)
    {
        // check changes and trigger them on question if there are any
        /** @var Question $question */
        $question = QuestionRepository::getInstance()->getAggregateRootById(new DomainObjectId($question_dto->getId()));

        if (!AbstractValueObject::isNullableEqual($question_dto->getData(), $question->getData())) {
            $question->setData($question_dto->getData(), $this->container_obj_id, $this->actor_user_id);
        }

        if (!AbstractValueObject::isNullableEqual($question_dto->getPlayConfiguration(), $question->getPlayConfiguration())) {
            $question->setPlayConfiguration($question_dto->getPlayConfiguration(), $this->container_obj_id, $this->actor_user_id);
        }

        if (!$question_dto->getAnswerOptions()->equals($question->getAnswerOptions())) {
            $question->setAnswerOptions($question_dto->getAnswerOptions(), $this->container_obj_id, $this->actor_user_id);
        }

        if (is_object($question_dto->getFeedback()) && !AbstractValueObject::isNullableEqual($question_dto->getFeedback(), $question->getFeedback())) {
            $question->setFeedback($question_dto->getFeedback(), $this->container_obj_id, $this->actor_user_id);
        }

        if (!is_null($question_dto->getQuestionHints())
            && !$question_dto->getQuestionHints()->equals($question->getHints())
        ) {
            $question->setHints($question_dto->getQuestionHints(), $this->container_obj_id, $this->actor_user_id);
        }

        if (count($question->getRecordedEvents()->getEvents()) > 0) {
            // save changes if there are any
            CommandBusBuilder::getCommandBus()->handle(new SaveQuestionCommand($question, $this->actor_user_id));
        }
    }

    public function importQtiQuestion(string $qti_item_xml) {
        global $DIC;

        $qti_application_service = new QtiImportService($this->container_obj_id);
        $question_dto = $qti_application_service->getQuestionDtoFromXml($qti_item_xml);

        if(is_object($question_dto)) {
            $uid = new DomainObjectId();

            $this->createQuestion($uid,
                $this->container_obj_id,
                $question_dto->getLegacyData()->getContainerObjType(),
                NULL,
                $question_dto->getLegacyData()->getAnswerTypeId(),
                $question_dto->getLegacyData()->getContentEditingMode()
            );

            $question_dto->setId($uid->getId());
            $this->saveQuestion($question_dto);
        }
    }


    public function projectQuestion(string $question_id)
    {
        CommandBusBuilder::getCommandBus()->handle(new CreateQuestionRevisionCommand($question_id, $this->actor_user_id));
    }


    public function DeleteQuestion(string $question_id)
    {
        // deletes question
        // no image
    }

    /* Ich würde die Answers immer als Ganzes behandeln
    public function RemoveAnswerFromQuestion(string $question_id, $answer) {
        // remove answer from question
    }*/

    /**
     * @return QuestionDto[]
     */
    public function getQuestions(?bool $is_complete = null) : array
    {
        $questions = [];
        $event_store = new QuestionEventStoreRepository();
        foreach ($event_store->allStoredQuestionIdsForContainerObjId($this->container_obj_id) as $aggregate_id) {
            $question = $this->getQuestion($aggregate_id);
            
            if(!is_null($is_complete)) {
                if ($question->isComplete() !== $is_complete) {
                    continue;
                }
            }
            
            $questions[] = $question;
        }

        return $questions;
    }


    /**
     * @param AssessmentEntityId $question_uuid
     *
     * @return QuestionComponent
     */
    public function getQuestionComponent(AssessmentEntityId $question_uuid) : QuestionComponent
    {

        $question_config = new QuestionConfig([]);
        $question_commands = new QuestionCommands();

        return new QuestionComponent($this->getQuestion($question_uuid->getId()), $question_config, $question_commands);
    }


    public function getQuestionPage(int $question_int_id) : \ilAsqQuestionPageGUI
    {
        $page = Page::getPage(ilAsqQuestionPageGUI::PAGE_TYPE, $this->container_obj_id, $question_int_id, $this->lng_key);
        $page_gui = \ilAsqQuestionPageGUI::getGUI($page);

        $page_gui->setRenderPageContainer(false);
        $page_gui->setEditPreview(true);
        $page_gui->setEnabledTabs(false);

        return $page_gui;
    }


    public function getQuestionPageEditor(int $question_int_id) : \ilAsqQuestionPageGUI
    {
        $page = Page::getPage(ilAsqQuestionPageGUI::PAGE_TYPE, $this->container_obj_id, $question_int_id, $this->lng_key);
        $page_gui = \ilAsqQuestionPageGUI::getGUI($page);

        $page_gui->setOutputMode('edit');
        $page_gui->setEditPreview(true);
        $page_gui->setEnabledTabs(false);

        return $page_gui;
    }


    /**
     * @param isValueOfOrderedList[] $items
     * @param int   $order_gap
     */
    public function reOrderListItems(array $items, int $order_gap) {
        $order_number = $order_gap;

        //reorder hints
        $ordered_items = [];
        usort($items, array('ILIAS\AssessmentQuestion\Application\AuthoringApplicationService', 'compareOrderNumber'));
        foreach ($items as $item) {
            /** $item */
            $ordered_items[] = $item::createWithNewOrderNumber($item, $order_number);

            $order_number = $order_number + $order_gap;
        }
        return $ordered_items;
    }

    private static function compareOrderNumber(IsValueOfOrderedList $a, IsValueOfOrderedList $b)
    {
        return $a->getOrderNumber() - $b->getOrderNumber();
    }

}