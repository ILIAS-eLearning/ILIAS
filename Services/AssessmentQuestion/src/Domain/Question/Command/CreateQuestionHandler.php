<?php

namespace ILIAS\AssessmentQuestion\Domainmodel\Question;

use ILIAS\Messaging\Contract\Command\Command;
use ILIAS\Messaging\Contract\Command\CommandHandler;
use ILIAS\AssessmentQuestion\Domainmodel\Common\QuestionId;

class CreateQuestionHandler implements CommandHandler {

	/**
	 * @var QuestionRepository
	 */
	private $repository;


	public function __construct($repository) {
		$this->repository = $repository;
	}


	public function handle(Command $command) {

		$question = Question::create(
			QuestionId::generate(),
			$command->getTitle(),
			$command->getDescription()
		);
		$this->repository->add($question);


	}
}