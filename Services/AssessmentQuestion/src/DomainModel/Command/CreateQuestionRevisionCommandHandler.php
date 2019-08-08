<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Command;



use ILIAS\AssessmentQuestion\CQRS\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\CQRS\Aggregate\RevisionFactory;
use ILIAS\AssessmentQuestion\CQRS\Command\CommandContract;
use ILIAS\AssessmentQuestion\CQRS\Command\CommandHandlerContract;
use ILIAS\AssessmentQuestion\DomainModel\Projection\ProjectQuestionsToListDb;
use ILIAS\AssessmentQuestion\DomainModel\QuestionRepository;

/**
 * Class CreateQuestionRevisionCommandHandler
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class CreateQuestionRevisionCommandHandler implements CommandHandlerContract {

    /**
     * @param CommandContract $command
     */
	public function handle(CommandContract $command) {
	    /** @var CreateQuestionRevisionCommand $command */
		$question = QuestionRepository::getInstance()->getAggregateRootById(new DomainObjectId($command->getQuestionId()));
		RevisionFactory::setRevisionId($question);
		$projector = new ProjectQuestionsToListDb();
		$projector->project($question);
		QuestionRepository::getInstance()->save($question);
	}
}