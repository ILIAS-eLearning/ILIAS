<?php

namespace ILIS\AssessmentQuestion\Application;

use ILIAS\AssessmentQuestion\Application\AuthoringApplicationServiceSpec;
use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\CQRS\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\CQRS\Command\CommandBusBuilder;
use ILIAS\AssessmentQuestion\DomainModel\Command\CreateQuestionCommand;
use ILIAS\AssessmentQuestion\DomainModel\Command\CreateQuestionRevisionCommand;
use ILIAS\AssessmentQuestion\DomainModel\Command\SaveQuestionCommand;
use ILIAS\AssessmentQuestion\DomainModel\Question;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\DomainModel\QuestionRepository;
use ILIAS\AssessmentQuestion\Infrastructure\Persistence\ilDB\ilDBQuestionEventStore;

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
	protected $actor_user_id;

	/**
	 * AsqAuthoringService constructor.
	 *
	 * @param int $actor_user_id
	 */
	public function __construct(int $actor_user_id) {
		$this->actor_user_id = $actor_user_id;
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
		?int $container_id = null,
		?int $answer_type_id = null
	): void {
		//CreateQuestion.png
		CommandBusBuilder::getCommandBus()->handle
		(new CreateQuestionCommand
		 ($question_uuid,
		  $this->actor_user_id,
		  $container_id,
		  $answer_type_id));
	}

	public function SaveQuestion(QuestionDto $question_dto) {
		// check changes and trigger them on question if there are any
		/** @var Question $question */
		$question = QuestionRepository::getInstance()->getAggregateRootById(new DomainObjectId($question_dto->getId()));

		if (!AbstractValueObject::isNullableEqual($question_dto->getData(), $question->getData())) {
			$question->setData($question_dto->getData(), $this->actor_user_id);
		}

		if (!AbstractValueObject::isNullableEqual($question_dto->getPlayConfiguration(), $question->getPlayConfiguration())) {
			$question->setPlayConfiguration($question_dto->getPlayConfiguration(), $this->actor_user_id);
		}

		// TODO implement equals for answer options
		if ($question_dto->getAnswerOptions() !== $question->getAnswerOptions()){
			$question->setAnswerOptions($question_dto->getAnswerOptions(), $this->actor_user_id);
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

	public function GetQuestions():array {
		// returns all questions of parent
		// GetQuestionList.png
		//TODO - use the Query Bus
		$event_store = new ilDBQuestionEventStore();
		return $event_store->allStoredQuestionsForParentSince(0);


		// TODO ev getquestionsofpool, getquestionsoftest methode pro object -> Denke nicht, die ParentIds in ILIAS sind eindeutig. Somit ruft man einfach jene Fragen ab, welche einem in seinem Parent zur Verfügung stehen, resp. welche man bereitgestellt hat.
	}
}