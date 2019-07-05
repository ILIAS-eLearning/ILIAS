<?php

namespace ILIAS\AssessmentQuestion\Authoring\_PublicApi;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command\SaveQuestionCommand;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Question;
use ILIAS\AssessmentQuestion\Authoring\Infrastructure\Persistence\ilDB\ilDBQuestionEventStore;
use ILIAS\AssessmentQuestion\Authoring\Infrastructure\Persistence\QuestionRepository;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\DomainObjectId;
use ILIAS\Messaging\CommandBusBuilder;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command\CreateQuestionCommand;
use QuestionDto;

const MSG_SUCCESS = "success";

/**
 * Class AsqAuthoringService
 *
 * @package ILIAS\AssessmentQuestion\Authoring\_PublicApi
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class AsqAuthoringService {

	/**
	 * @var AsqAuthoringSpec
	 */
	protected $asq_question_spec;

	/**
	 * AsqAuthoringService constructor.
	 *
	 * @param $asq_question_spec
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

	public function CreateQuestion(string $title, string $description, string $text, int $creator_id): void {
		//CreateQuestion.png
		CommandBusBuilder::getCommandBus()->handle(new CreateQuestionCommand($title, $description, $text, $creator_id));
	}

	public function SaveQuestion(QuestionDto $question_dto) {
		// check changes and trigger them on question if there are any
		/** @var Question $question */
		$question = QuestionRepository::getInstance()->getAggregateRootById(new DomainObjectId($question_dto->getId()));

		if ($question_dto->getData() !== $question->getData()) {
			$question->setData($question_dto->getData());
		}

		if(count($question->getRecordedEvents()->getEvents()) > 0) {
			// save changes if there are any
			CommandBusBuilder::getCommandBus()->handle(new SaveQuestionCommand($question));
		}
	}

	public function DeleteQuestion(string $question_id) {
		// deletes question
		// no image
	}


	/**
	 * @param Answer $answer -> vgl Services/AssessmentQuestion/docs/Big_Picture.puml -> AnswerEntity
	 */
	public function SaveAnswer(array $answer) {
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
		return $event_store->allStoredQuestionsForParentSince($this->asq_question_spec->container_id,0);


		// TODO ev getquestionsofpool, getquestionsoftest methode pro object -> Denke nicht, die ParentIds in ILIAS sind eindeutig. Somit ruft man einfach jene Fragen ab, welche einem in seinem Parent zur Verfügung stehen, resp. welche man bereitgestellt hat.
	}

	public function SearchQuestions(array $parameters) {
		// searches questions by query parameters
		// GetQuestionList.png
	}

	public function GetAvilableQuestionTypes() {
		// returns all know question type
		// GetAvilableQuestionTypes
	}

	public function SaveQuestionPresentation(string $question_id, $presentation) {
		// saves display options
		//EditQuestionPresentation.png
	}

	public function ImportQuestion($question) {
		// imports the question
		// TODO support what
	}

	public function ExportQuestion(string $question_id) {
		// exports the question
		// TODO support what
	}
}