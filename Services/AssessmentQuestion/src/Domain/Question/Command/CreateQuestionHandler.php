<?php

namespace ILIAS\AssessmentQuestion\Domain\Question\Command;

use ILIAS\AssessmentQuestion\Infrastructure\Persistence\QuestionRepository;
use ILIAS\Messaging\Contract\Command\Command;
use ILIAS\Messaging\Contract\Command\CommandHandler;
use ILIAS\AssessmentQuestion\Domain\Question\Aggregate\Question;
use ILIAS\AssessmentQuestion\Domain\Question\Shared\QuestionId;

class CreateQuestionHandler implements CommandHandler {

	/**
	 * @var QuestionRepository
	 */
	//private $repository;


	public function __construct() {
		//TODO create repository
	}


	public function handle(Command $command) {

		$question = Question::create(
			QuestionId::generate(),
			$command->getTitle(),
			$command->getDescription()
		);

	}
}