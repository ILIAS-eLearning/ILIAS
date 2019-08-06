<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Projection\ProjectQuestionsToListDb;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionRepository;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Command\CommandContract;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Command\CommandHandlerContract;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\Common\RevisionFactory;

/**
* Class CreateQuestionHandler
*
* @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command
* @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
* @author  Adrian Lüthi <al@studer-raimann.ch>
*/
class CreateQuestionRevisionCommandHandler implements CommandHandlerContract {

	/**
	* @param CreateQuestionRevisionCommand $command
	*/
	public function handle(CommandContract $command) {
		$question = QuestionRepository::getInstance()->getAggregateRootById(new DomainObjectId($command->getQuestionId()));
		RevisionFactory::setRevisionId($question);
		$projector = new ProjectQuestionsToListDb();
		$projector->project($question);
		QuestionRepository::getInstance()->save($question);
	}
}