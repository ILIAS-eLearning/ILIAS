<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command;

use ILIAS\Messaging\Contract\Command\AbstractCommand;
use ILIAS\Messaging\Contract\Command\Command;

class CreateQuestionRevisionCommand extends AbstractCommand implements Command {

	/**
	 * @var string
	 */
	private $question_id;

	public function __construct(string $question_id, int $issuer_id) {
		parent::__construct($issuer_id);
		$this->question_id = $question_id;
	}


	/**
	 * @return string
	 */
	public function getQuestionId(): string {
		return $this->question_id;
	}
}
