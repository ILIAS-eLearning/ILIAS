<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionRepository;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Command\CommandContract;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Command\CommandHandlerContract;

/**
 * Class AnswerQuestionCommandHandler
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 */
class AnswerQuestionCommandHandler implements CommandHandlerContract {

	/**
	 * @param AnswerQuestionCommand $command
	 */
	public function handle(CommandContract $command) {
		$repo = QuestionRepository::getInstance();
		/** @var Question $question */
		$question = $repo->getAggregateRootById(new DomainObjectId($command->getAnswer()->getQuestionId()));
		$question->addAnswer($command->getAnswer());
		QuestionRepository::getInstance()->save($question);
	}
}