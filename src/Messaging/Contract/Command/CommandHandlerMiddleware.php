<?php
/* Copyright (c) 2019 - Martin Studer <ms@studer-raimann.ch> - Extended GPL, see LICENSE */

namespace ILIAS\Messaging\Contract\Command;


interface CommandHandlerMiddleware {

	/**
	 * A middleware may run things before handling a command
	 *
	 * Its only argument is the current Command object
	 *
	 * The return object is a command of the same type
	 * (usually the same as the originally provided command).
	 *
	 * @param Command $command
	 *
	 * @return Command
	 */
	public function handle(Command $command) : Command;
}