<?php
/* Copyright (c) 2019 - Martin Studer <ms@studer-raimann.ch> - Extended GPL, see LICENSE */
declare(strict_types=1);

namespace ILIAS\Messaging\Contract\Command;

/**
 * Interface CommandHandler
 *
 * Command Handler is the place where a command is being dispatched
 * and handled.
 */
Interface CommandHandler {

	public function handle(Command $command);
}