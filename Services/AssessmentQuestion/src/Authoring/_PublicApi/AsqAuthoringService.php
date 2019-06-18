<?php

namespace ILIAS\AssessmentQuestion\Authoring\_PublicApi;

use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\CreateQuestionFormGUI;
use ILIAS\Messaging\CommandBusBuilder;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command\CreateQuestionCommand;

const MSG_SUCCESS = "success";

/**
 * Class AsqAuthoringService
 *
 * @package ILIAS\AssessmentQuestion\Authoring\_PublicApi
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class AsqAuthoringService {

	public function CreateQuestion(string $title, string $description, int $creator_id): void {
		//CreateQuestion.png
		try {
			$command_busbuilder = new CommandBusBuilder();
			$command_bus = $command_busbuilder->getCommandBus();
			$command_bus->handle(new CreateQuestionCommand($title, $description, $creator_id));
		} catch (\Exception $e) {
			//TODO Failure Message
			//$this->tpl->
		}
	}

	public function CreateNewVersionOfQuestion(string $title, string $description, int $creator_id, string $old_id) {
		// creates new version of a question ('edit question' but with immutable domain object)
		// CreateQuestion.png
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

	public function GetQuestions(string $parent_id) {
		// returns all questions of parent
		// GetQuestionList.png
		// TODO ev getquestionsofpool, getquestionsoftest methode pro object
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