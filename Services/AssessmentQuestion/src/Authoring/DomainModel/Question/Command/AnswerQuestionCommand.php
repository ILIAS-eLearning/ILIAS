<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Answer;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Command\AbstractCommand;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Command\CommandContract;

/**
 * Class AnswerQuestionCommand
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class AnswerQuestionCommand extends AbstractCommand implements CommandContract {

	/**
	 * @var Answer
	 */
	private $answer;

	/**
	 * QuestionAnsweredCommand constructor.
	 *
	 * @param Answer $answer
	 */
	public function __construct(Answer $answer) {
		parent::__construct($answer->getAnswererId());
		$this->answer = $answer;
	}


	/**
	 * @return Answer
	 */
	public function getAnswer(): Answer {
		return $this->answer;
	}
}