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
	    
        /** @var Question $question */
		$question = Question::createNewQuestion(
			$command->getQuestionUuid(),
            $command->getQuestionContainerId(),
			$command->getInitiatingUserId(),
		    $command->getQuestionIntId() ?? QuestionRepository::getInstance()->getNextId()
		);

		if (!is_null($command->getAnswerType()) ||
		    !is_null($command->getContentEditingMode()))
        {
			$question->setLegacyData(
				QuestionLegacyData::create(
					$command->getAnswerType(),
				    $command->getContentEditingMode(),
                    $command->getContainerObjType()
				),
			    $command->getQuestionContainerId(),
			    $command->getInitiatingUserId()
			);
		}

		QuestionRepository::getInstance()->save($question);
	}
}