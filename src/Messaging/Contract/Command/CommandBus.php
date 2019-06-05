<?php
/* Copyright (c) 2019 - Martin Studer <ms@studer-raimann.ch> - Extended GPL, see LICENSE */

namespace ILIAS\Messaging\Contract\Command;

use ILIAS\Messaging\Contract\Handler\CommandHandlerMiddleware as CommandHandlerMiddlewareContract;

/**
 * Interface CommandBus
 *
 * The Command Bus is used to dispatch a given Command into the Bus
 * and maps a Command to a Command Handler.
 *
 */
interface CommandBus {

	/**
	 * @param Command $command
	 */
	public function handle(Command $command): void;


	/**
	 * Appends new middleware for this message bus.
	 * Should only be used at configuration time.
	 *
	 * @param CommandHandlerMiddlewareContract $middleware
	 *
	 * @return void
	 */
	public function appendMiddleware(CommandHandlerMiddlewareContract $middleware): void;
}
