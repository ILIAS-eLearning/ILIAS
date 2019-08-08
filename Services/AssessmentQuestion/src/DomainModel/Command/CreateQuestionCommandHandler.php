<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Command;

use ILIAS\AssessmentQuestion\CQRS\Command\CommandContract;
use ILIAS\AssessmentQuestion\CQRS\Command\CommandHandlerContract;
use ILIAS\AssessmentQuestion\DomainModel\Question;
use ILIAS\AssessmentQuestion\DomainModel\QuestionLegacyData;
use ILIAS\AssessmentQuestion\DomainModel\QuestionRepository;

/**
 * Class CreateQuestionCommandHandler
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class CreateQuestionCommandHandler implements CommandHandlerContract {

    /**
     * @param CommandContract $command
     */
	public function handle(CommandContract $command) {

	    /** @var CreateQuestionCommand $command */
		$question = Question::createNewQuestion(
			$command->getQuestionUuid(),
			$command->getInitiatingUserId()
		);

		if (!is_null($command->getAnswerType())
			|| !is_null($command->getQuestionContainer())
		) {
			$question->setLegacyData(
				QuestionLegacyData::create(
					$command->getAnswerType(),
					$command->getQuestionContainer()
				)
			);
		}

		QuestionRepository::getInstance()->save($question);
	}
}