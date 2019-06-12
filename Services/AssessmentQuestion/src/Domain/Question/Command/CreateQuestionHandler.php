<?php

namespace ILIAS\AssessmentQuestion\Domainmodel\Question;

use ILIAS\AssessmentQuestion\Domainmodel\Common\QuestionId;
use ILIAS\AssessmentQuestion\Domainmodel\Question\QuestionRepository;

class CreateQuestionHandler {

	/**
	 * @var QuestionRepository
	 */
	private $repository;


	public function __construct($repository) {
		$this->repository = $repository;
	}


	public function handle(CreateQuestionCommand $command) {

		$question = Question::create(
			QuestionId::generate(),
			$command->getTitle(),
			$command->getDescription()
		);
		$this->repository->add($question);


	}
}