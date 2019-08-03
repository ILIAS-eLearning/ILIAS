<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Question;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionData;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionLegacyData;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionRepository;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Command\CommandContract;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Command\CommandHandlerContract;



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
class CreateQuestionCommandHandler implements CommandHandlerContract {

	/**
	 * @param CreateQuestionCommand $command
	 */
	public function handle(CommandContract $command) {

		$question = Question::createNewQuestion(
			$command->getQuestionUuid(),
			$command->getInitiatingUserId()
		);

		if (!is_null($command->getAnswerType())
			|| !is_null($command->getQuestionContainer())
		) {
			$question->setLegacyData(
				new QuestionLegacyData(
					$command->getAnswerType(),
					$command->getQuestionContainer()
				)
			);
		}

		QuestionRepository::getInstance()->save($question);
	}
}