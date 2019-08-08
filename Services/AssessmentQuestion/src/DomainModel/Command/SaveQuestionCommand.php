<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Command;


use ILIAS\AssessmentQuestion\CQRS\Command\AbstractCommand;
use ILIAS\AssessmentQuestion\CQRS\Command\CommandContract;
use ILIAS\AssessmentQuestion\DomainModel\Question;

/**
 * Class SaveQuestionCommand
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class SaveQuestionCommand extends AbstractCommand implements CommandContract {

	/**
	 * @var Question
	 */
	private $question;

    /**
     * SaveQuestionCommand constructor.
     *
     * @param Question $question
     * @param int      $issuing_user_id
     */
	public function __construct(Question $question, int $issuing_user_id) {
		parent::__construct($issuing_user_id);
		$this->question = $question;
	}


    /**
     * @return Question
     */
	public function GetQuestion(): Question {
		return $this->question;
	}
}