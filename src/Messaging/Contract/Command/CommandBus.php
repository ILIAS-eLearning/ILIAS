<?php
/* Copyright (c) 2019 - Martin Studer <ms@studer-raimann.ch> - Extended GPL, see LICENSE */

namespace ILIAS\Messaging\Contract\Command;

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
	public function appendMiddleware(CommandHandlerMiddleware $middleware): void;


	/**
	 * Appends handler to bus for corresponding command class
	 *
	 * @param string         $command_class
	 * @param CommandHandler $handler
	 */
	public function appendHandler(string $command_class, CommandHandler $handler) : void;
}
