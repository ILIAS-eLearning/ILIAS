<?php
/* Copyright (c) 2019 - Martin Studer <ms@studer-raimann.ch> - Extended GPL, see LICENSE */

namespace ILIAS\AssessmentQuestion\CQRS\Command;

/**
 * Class CommandHandlerMiddleware
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
interface CommandHandlerMiddleware {

	/**
	 * A middleware may run things before handling a command
	 *
	 * Its only argument is the current Command object
	 *
	 * The return object is a command of the same type
	 * (usually the same as the originally provided command).
	 *
	 * @param CommandContract $command
	 *
	 * @return CommandContract
	 */
	public function handle(CommandContract $command) : CommandContract;
}