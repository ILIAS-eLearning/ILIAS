<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Command;

use ILIAS\AssessmentQuestion\CQRS\Command\AbstractCommand;
use ILIAS\AssessmentQuestion\CQRS\Command\CommandContract;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;

/**
 * Class ScoreTestAttemptCommand
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ScoreTestAttemptCommand extends AbstractCommand implements CommandContract {

	/**
	 * @var Answer[]
	 */
	private $answers;


    /**
     * ScoreTestAttemptCommand constructor.
     *
     * @param Answer[] $answers
     */
	public function __construct(array $answers,  int $issuer_id) {
		parent::__construct($issuer_id);
		$this->answers = $answers;
	}


    /**
     * @return Answer[]
     */
    public function getAnswers() : array
    {
        return $this->answers;
    }
}
