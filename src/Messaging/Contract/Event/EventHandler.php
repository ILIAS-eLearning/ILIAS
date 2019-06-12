<?php
/* Copyright (c) 2019 - Martin Studer <ms@studer-raimann.ch> - Extended GPL, see LICENSE */

namespace ILIAS\Messaging\Contract\Event;

/**
 * Interface CommandHandler
 *
 * Command Handler is the place where a command is being dispatched
 * and handled.
 */
Interface EventHandler {

	//public function __construct(Command $command);
	public function handle(Command $command);
}