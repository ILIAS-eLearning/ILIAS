<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Command;


use ILIAS\AssessmentQuestion\CQRS\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\CQRS\Command\CommandContract;
use ILIAS\AssessmentQuestion\CQRS\Command\CommandHandlerContract;
use ILIAS\AssessmentQuestion\DomainModel\Question;
use ILIAS\AssessmentQuestion\DomainModel\QuestionRepository;

/**
 * Class AnswerQuestionCommandHandler
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AnswerQuestionCommandHandler implements CommandHandlerContract {

    /**
     * @param CommandContract $command
     */
	public function handle(CommandContract $command) {
	    /** @var AnswerQuestionCommand $command */
		$repo = QuestionRepository::getInstance();
		/** @var Question $question */
		$question = $repo->getAggregateRootById(new DomainObjectId($command->getAnswer()->getQuestionId()));
		$question->addAnswer($command->getAnswer());
		QuestionRepository::getInstance()->save($question);
	}
}