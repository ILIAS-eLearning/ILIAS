<?php
/* Copyright (c) 2019 - Martin Studer <ms@studer-raimann.ch> - Extended GPL, see LICENSE */

namespace ILIAS\AssessmentQuestion\CQRS\Command;

/**
 * Interface CommandBusContract
 *
 * The Command Bus is used to dispatch a given Command into the Bus
 * and maps a Command to a Command Handler.
 *
 */
interface CommandBusContract {

	/**
	 * @param CommandContract $command
	 */
	public function handle(CommandContract $command): void;


	/**
	 * Appends new middleware for this message bus.
	 * Should only be used at configuration time.
	 *
	 * @param CommandHandlerMiddleware $middleware
	 *
	 * @return void
	 */
	public function appendMiddleware(CommandHandlerMiddleware $middleware): void;
}
