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

	/**
	 * @var self
	 */
	protected static $instance;
	/**
	 * @var AsqAuthoringSpec
	 */
	protected $asq_authoring_service_spec;


	/**
	 * AsqAuthoringService constructor.
	 *
	 * @param AsqAuthoringSpec $asq_authoring_service_spec
	 */
	public function __construct($asq_authoring_service_spec) {
		/**
		 * \ilGlobalPageTemplate
		 */
		$this->tpl = $asq_authoring_service_spec->tpl;
		/**
		 * \ilLanguage
		 */
		$this->lng = $asq_authoring_service_spec->lng;
	}


	public function GetCreationForm(): CreateQuestionFormGUI {
		//CreateQuestion.png
		return new CreateQuestionFormGUI();
	}

	public function GetQuestionList() {
		//returns question list object
		//GetQuestionlist.png
	}

	public function GetPrintView(string $parent_id) {
		//returns print view
		//GetPrintView.png
	}

	public function GetPresentationForm(string $question_id) {
		//returns presentation form
		//EditQuestionPresentation.png
	}

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

	public function AddAnswerToQuestion(string $question_id, int $creator_id, $answer) {
		// add answer to question
	}

	public function RemoveAnswerFromQuestion(string $question_id, $answer) {
		// remove answer from question
	}

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