<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command;

use CreateQuestionRevisionCommand;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Question;
use ILIAS\AssessmentQuestion\Authoring\Infrastructure\Persistence\QuestionRepository;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\Common\RevisionFactory;
use ILIAS\Messaging\Contract\Command\Command;
use ILIAS\Messaging\Contract\Command\CommandHandler;
use ProjectQuestionsToListDb;
use QuestionData;

;

/**
* Class CreateQuestionHandler
*
* @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command
* @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
* @author  Adrian Lüthi <al@studer-raimann.ch>
*/
class CreateQuestionRevisionCommandHandler implements CommandHandler {

	/**
	* @param CreateQuestionRevisionCommand $command
	*/
	public function handle(Command $command) {
		$question = QuestionRepository::getInstance()->getAggregateRootById(new DomainObjectId($command->getQuestionId()));
		RevisionFactory::setRevisionId($question);
		QuestionRepository::getInstance()->save($question);
		$projector = new ProjectQuestionsToListDb();
		$projector->project($question);
	}
}