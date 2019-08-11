<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Command;

use ILIAS\AssessmentQuestion\CQRS\Command\AbstractCommand;
use ILIAS\AssessmentQuestion\CQRS\Command\CommandContract;

/**
 * Class CreateQuestionRevisionCommand
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class CreateQuestionRevisionCommand extends AbstractCommand implements CommandContract {

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
