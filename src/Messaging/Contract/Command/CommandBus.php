<?php
/* Copyright (c) 2019 - Martin Studer <ms@studer-raimann.ch> - Extended GPL, see LICENSE */

namespace ILIAS\Messaging\Contract\Command;

/**
 * Interface CommandBus
 *
 * The Command Bus is used to dispatch a given Command into the Bus
 * and maps a Command to a Command Handler.
 *
 * @package ILIAS\Messaging\Contract\Command
 */
interface CommandBus {

	/**
	 * @param Command $command
	 */
	public function handle(Command $command): void;
}
