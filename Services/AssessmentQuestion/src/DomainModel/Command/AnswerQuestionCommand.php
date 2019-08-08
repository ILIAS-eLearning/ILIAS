<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Command;



use ILIAS\AssessmentQuestion\CQRS\Command\AbstractCommand;
use ILIAS\AssessmentQuestion\CQRS\Command\CommandContract;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;

/**
 * Class AnswerQuestionCommand
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
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