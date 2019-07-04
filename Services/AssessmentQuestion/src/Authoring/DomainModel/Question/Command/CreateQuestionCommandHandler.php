<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Question;
use ILIAS\AssessmentQuestion\Authoring\Infrastructure\Persistence\QuestionRepository;
use ILIAS\Messaging\Contract\Command\Command;
use ILIAS\Messaging\Contract\Command\CommandHandler;
use QuestionData;

;

/**
 * Class CreateQuestionHandler
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class CreateQuestionHandler implements CommandHandler {

	/**
	 * @param CreateQuestionCommand $command
	 */
	public function handle(Command $command) {

		$question = Question::createNewQuestion($command->getCreator());
		$question->setData(new QuestionData($command->getTitle(), $command->getDescription(), $command->getText()), $command->getCreator());
		QuestionRepository::getInstance()->save($question);
	}
}