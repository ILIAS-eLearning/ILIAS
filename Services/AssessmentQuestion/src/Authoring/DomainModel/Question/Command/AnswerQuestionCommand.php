<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Answer;
use ILIAS\Messaging\Contract\Command\AbstractCommand;
use ILIAS\Messaging\Contract\Command\Command;

/**
 * Class AnswerQuestionCommand
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class AnswerQuestionCommand extends AbstractCommand implements Command {

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