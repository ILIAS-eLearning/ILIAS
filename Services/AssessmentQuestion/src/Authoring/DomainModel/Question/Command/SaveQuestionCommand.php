<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Question;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Command\AbstractCommand;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Command\CommandContract;


/**
 * Class CreateQuestionCommand
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command
 * @author  Adrian Lüthi <adrian.luethi@studer-raimann.ch>
 */

class SaveQuestionCommand extends AbstractCommand implements CommandContract {

	/**
	 * @var Question
	 */
	private $question;

	public function __construct(Question $question, int $issuing_user_id) {
		parent::__construct($issuing_user_id);
		$this->question = $question;
	}

	public function GetQuestion(): Question {
		return $this->question;
	}
}