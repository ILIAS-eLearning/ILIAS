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
		return new CreateQuestionFormGUI();
	}


	public function CreateQuestion(string $title, string $description, int $creator_id): void {

		try {
			$command_busbuilder = new CommandBusBuilder();
			$command_bus = $command_busbuilder->getCommandBus();
			$command_bus->handle(new CreateQuestionCommand($title, $description, $creator_id));
		} catch (\Exception $e) {
			//TODO Failure Message
			//$this->tpl->
		}
	}
}