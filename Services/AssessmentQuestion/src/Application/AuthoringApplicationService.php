<?php

namespace ILIS\AssessmentQuestion\Application;

use ilAsqQuestionPageGUI;
use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\CQRS\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\CQRS\Command\CommandBusBuilder;
use ILIAS\AssessmentQuestion\DomainModel\Command\CreateQuestionCommand;
use ILIAS\AssessmentQuestion\DomainModel\Command\CreateQuestionRevisionCommand;
use ILIAS\AssessmentQuestion\DomainModel\Command\SaveQuestionCommand;
use ILIAS\AssessmentQuestion\DomainModel\Question;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\DomainModel\QuestionRepository;
use ILIAS\AssessmentQuestion\Infrastructure\Persistence\EventStore\QuestionEventStoreRepository;
use ILIAS\AssessmentQuestion\UserInterface\Web\Page\Page;
use ILIAS\AssessmentQuestion\UserInterface\Web\Page\PageFactory;

const MSG_SUCCESS = "success";

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
class AuthoringApplicationService {

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

	/**
	 * AsqAuthoringService constructor.
     *
     * @param int $container_obj_id
	 * @param int $actor_user_id
     * @param string $lng_key
	 */
	public function __construct(int $container_obj_id, int $actor_user_id) {
	    global $DIC;
	    $this->container_obj_id = $container_obj_id;
		$this->actor_user_id = $actor_user_id;
        //The lng_key could be used in future as parameter in the constructor
        $this->lng_key = $DIC->language()->getDefaultLanguage();
	}


	/**
	 * @param string $aggregate_id
	 *
	 * @return QuestionDto
	 */
	public function GetQuestion(string $aggregate_id) : QuestionDto {
		$question = QuestionRepository::getInstance()->getAggregateRootById(new DomainObjectId($aggregate_id));
		return QuestionDto::CreateFromQuestion($question);
	}

	public function CreateQuestion(
		DomainObjectId $question_uuid,
		int $container_id,
	    ?int $object_id = null,
		?int $answer_type_id = null,
	    ?string $content_editing_mode
	): void {
		//CreateQuestion.png
		CommandBusBuilder::getCommandBus()->handle
		(new CreateQuestionCommand
		 ($question_uuid,
		  $this->actor_user_id,
		  $container_id,
		  $answer_type_id,
		  $object_id,
		  $content_editing_mode));
	}

	public function SaveQuestion(QuestionDto $question_dto) {
		// check changes and trigger them on question if there are any
		/** @var Question $question */
		$question = QuestionRepository::getInstance()->getAggregateRootById(new DomainObjectId($question_dto->getId()));

		if (!AbstractValueObject::isNullableEqual($question_dto->getData(), $question->getData())) {
			$question->setData($question_dto->getData(), $this->container_obj_id, $this->actor_user_id);
		}

		if (!AbstractValueObject::isNullableEqual($question_dto->getPlayConfiguration(), $question->getPlayConfiguration())) {
			$question->setPlayConfiguration($question_dto->getPlayConfiguration(), $this->container_obj_id, $this->actor_user_id);
		}

		if (!$question_dto->getAnswerOptions()->equals($question->getAnswerOptions())){
			$question->setAnswerOptions($question_dto->getAnswerOptions(), $this->container_obj_id, $this->actor_user_id);
		}

        if (!AbstractValueObject::isNullableEqual($question_dto->getFeedback(), $question->getFeedback())){
            $question->setFeedback($question_dto->getFeedback(), $this->container_obj_id, $this->actor_user_id);
        }

		if(count($question->getRecordedEvents()->getEvents()) > 0) {
			// save changes if there are any
			CommandBusBuilder::getCommandBus()->handle(new SaveQuestionCommand($question, $this->actor_user_id));
		}
	}

	public function projectQuestion(string $question_id) {
	    CommandBusBuilder::getCommandBus()->handle(new CreateQuestionRevisionCommand($question_id, $this->actor_user_id));
	}

	public function DeleteQuestion(string $question_id) {
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
	public function GetQuestions():array {
	    $questions = [];
		$event_store = new QuestionEventStoreRepository();
	    foreach($event_store->allStoredQuestionIdsForContainerObjId($this->container_obj_id) as $aggregate_id) {
            $questions[] = $this->GetQuestion($aggregate_id);
        }
	    return $questions;
	}

	public function getQuestionPage(int $question_int_id) : \ilAsqQuestionPageGUI
    {
        global $DIC;
        $page = Page::getPage(ilAsqQuestionPageGUI::PAGE_TYPE,$this->container_obj_id,$question_int_id,$DIC->language()->getDefaultLanguage());
        $page_gui = \ilAsqQuestionPageGUI::getGUI($page);

        $page_gui->setRenderPageContainer(false);
        $page_gui->setEditPreview(true);
        $page_gui->setEnabledTabs(false);

        return $page_gui;
    }

	public function getQuestionPageEditor(int $question_int_id) : \ilAsqQuestionPageGUI
    {
        global $DIC;
        $page = Page::getPage(ilAsqQuestionPageGUI::PAGE_TYPE,$this->container_obj_id,$question_int_id,$DIC->language()->getDefaultLanguage());
        $page_gui = \ilAsqQuestionPageGUI::getGUI($page);

        $page_gui->setOutputMode('edit');
        $page_gui->setEditPreview(true);
        $page_gui->setEnabledTabs(false);

        return $page_gui;
    }
}