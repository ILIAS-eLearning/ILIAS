<?php
/* Copyright (c) 2019 - Martin Studer <ms@studer-raimann.ch> - Extended GPL, see LICENSE */

namespace ILIAS\Messaging\Contract\Event;


interface EventHandlerMiddleware {
	/**
	 * A middleware may run things before or after handling a command
	 *
	 * The provided $next callable should be called whenever a next middleware
	 * should start handling the command.
	 * Its only argument should be a Command object
	 * (usually the same as the originally provided command).
	 *
	 * @param Command $command
	 * @param callable $next
	 *
	 * @return void
	 */
	public function handle(Command $command, callable $next);
}