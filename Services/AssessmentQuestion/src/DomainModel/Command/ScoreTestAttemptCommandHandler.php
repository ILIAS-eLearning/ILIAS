<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Command;



use ILIAS\AssessmentQuestion\CQRS\Command\CommandContract;
use ILIAS\AssessmentQuestion\CQRS\Command\CommandHandlerContract;

/**
 * Class ScoreTestAttemptCommandHandler
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ScoreTestAttemptCommandHandler implements CommandHandlerContract {

    /**
     * @param CommandContract $command
     */
	public function handle(CommandContract $command) {

	    //TODO

	}
}