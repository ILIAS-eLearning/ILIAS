<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Question;
use ILIAS\AssessmentQuestion\Authoring\Infrastructure\Persistence\QuestionRepository;
use ILIAS\Messaging\Contract\Command\Command;
use ILIAS\Messaging\Contract\Command\CommandHandler;;

/**
 * Class CreateQuestionCommandHandler
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class CreateQuestionCommandHandler implements CommandHandler {

	/**
	 * @param CreateQuestionCommand $command
	 */
	public function handle(Command $command) {

		$question = Question::createNewQuestion(
			$command->getTitle(),
			$command->getDescription(),
			$command->getCreator()
		);

		QuestionRepository::getInstance()->save($question);
	}
}