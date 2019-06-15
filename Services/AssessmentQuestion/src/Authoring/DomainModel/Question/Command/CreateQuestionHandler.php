<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Question;
use ILIAS\AssessmentQuestion\Authoring\Infrastructure\Persistence\QuestionRepository;
use ILIAS\Messaging\Contract\Command\Command;
use ILIAS\Messaging\Contract\Command\CommandHandler;;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Shared\QuestionId;

/**
 * Class CreateQuestionCommandHandler
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class CreateQuestionCommandHandler implements CommandHandler {

	/**
	 * @var QuestionRepository
	 */
	//private $repository;


	public function __construct() {
		//TODO create repository
	}


	public function handle(Command $command) {

		$question = Question::createFrom(
			$command->getTitle(),
			$command->getDescription()
		);

	}
}