<?php

namespace  ILIAS\AssessmentQuestion\Infrastructure;

use ILIAS\AssessmentQuestion\Domain\Question\Command\CreateQuestionCommand;
use ILIAS\AssessmentQuestion\Domain\Question\Command\CreateQuestionHandler;

class QuestionDIC {
	private static $instance;

	public static function getInstance() {
		if (QuestionDIC::$instance == null) {
			QuestionDIC::$instance = new QuestionDIC();
		}

		return QuestionDIC::$instance;
	}

	/** @var QuestionEventBus */
	private $event_bus;

	/** @var QuestionCommandBus */
	private $command_bus;

	public function __construct() {
		$command_bus = new QuestionCommandBus();

		$command_bus->appendHandler(
			CreateQuestionCommand::class,
			new CreateQuestionHandler());

		$this->command_bus = $command_bus;
	}


	/**
	 * @return QuestionEventBus
	 */
	public function getEventBus(): QuestionEventBus {
		return $this->event_bus;
	}


	/**
	 * @return QuestionCommandBus
	 */
	public function getCommandBus(): QuestionCommandBus {
		return $this->command_bus;
	}
}