<?php

namespace ILIAS\AssessmentQuestion\Authoring\Application;

use ILIAS\AssessmentQuestion\Authoring\_PublicApi\AsqAuthoringSpec;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Answer;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Type\AnswerType;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command\CreateQuestionRevisionCommand;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command\SaveQuestionCommand;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Question;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionContainer;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionDto;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionRepository;
use ILIAS\AssessmentQuestion\Authoring\Infrastructure\Persistence\ilDB\ilDBQuestionEventStore;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Command\CommandBusBuilder;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command\CreateQuestionCommand;

const MSG_SUCCESS = "success";

/**
 * Class AsqAuthoringService
 *
 * @package ILIAS\AssessmentQuestion\Authoring\_PublicApi
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class AuthoringApplicationService {

	/**
	 * @var AuthoringApplicationServiceSpec
	 */
	protected $asq_question_spec;

	/**
	 * AsqAuthoringService constructor.
	 *
	 * @param AuthoringApplicationServiceSpec $asq_question_spec
	 */
	public function __construct($asq_question_spec) {
		$this->asq_question_spec = $asq_question_spec;
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
		  $this->asq_question_spec->getInitiatingUserId(),
		  $container_id,
		  $answer_type_id));
	}

	public function SaveQuestion(QuestionDto $question_dto) {
		// check changes and trigger them on question if there are any
		/** @var Question $question */
		$question = QuestionRepository::getInstance()->getAggregateRootById(new DomainObjectId($question_dto->getId()));

		if (!AbstractValueObject::isNullableEqual($question_dto->getData(), $question->getData())) {
			$question->setData($question_dto->getData(), $this->asq_question_spec->getInitiatingUserId());
		}

		if (!AbstractValueObject::isNullableEqual($question_dto->getPlayConfiguration(), $question->getPlayConfiguration())) {
			$question->setPlayConfiguration($question_dto->getPlayConfiguration(), $this->asq_question_spec->getInitiatingUserId());
		}

		// TODO implement equals for answer options
		if ($question_dto->getAnswerOptions() !== $question->getAnswerOptions()){
			$question->setAnswerOptions($question_dto->getAnswerOptions(), $this->asq_question_spec->getInitiatingUserId());
		}

		if(count($question->getRecordedEvents()->getEvents()) > 0) {
			// save changes if there are any
			CommandBusBuilder::getCommandBus()->handle(new SaveQuestionCommand($question, $this->asq_question_spec->getInitiatingUserId()));
		}
	}

	public function projectQuestion(string $question_id) {
		CommandBusBuilder::getCommandBus()->handle(new CreateQuestionRevisionCommand($question_id, $this->asq_question_spec->getInitiatingUserId()));
	}

	public function DeleteQuestion(string $question_id) {
		// deletes question
		// no image
	}

	/**
	 * @param Answer $answer
	 */
	public function SaveAnswer(Answer $answer) {
		// Save Answers
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