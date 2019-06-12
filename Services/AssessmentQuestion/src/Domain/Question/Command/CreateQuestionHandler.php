<?php

namespace ILIAS\AssessmentQuestion\Domain\Question\Command;

use ILIAS\AssessmentQuestion\Infrastructure\Persistence\QuestionRepository;
use ILIAS\Messaging\Contract\Command\Command;
use ILIAS\Messaging\Contract\Command\CommandHandler;

class CreateQuestionHandler implements CommandHandler {

	/**
	 * @var QuestionRepository
	 */
	//private $repository;


	public function __construct() {
		//TODO create repository
	}


	public function handle(Command $command) {
		$blah = 4;
		//$question = Question::create(
		//	QuestionId::generate(),
		//	$command->getTitle(),
		//	$command->getDescription()
		//);
		//$this->repository->add($question);
	}
}