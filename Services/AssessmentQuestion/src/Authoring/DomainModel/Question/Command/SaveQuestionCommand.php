<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Question;
use ILIAS\Messaging\Contract\Command\AbstractCommand;
use ILIAS\Messaging\Contract\Command\Command;

/**
 * Class CreateQuestionCommand
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command
 * @author  Adrian Lüthi <adrian.luethi@studer-raimann.ch>
 */
class SaveQuestionCommand extends AbstractCommand implements Command {
	private $question;

	public function __construct(Question $question) {
		$this->question = $question;
	}

	public function GetQuestion(): Question {
		return $this->question;
	}
}