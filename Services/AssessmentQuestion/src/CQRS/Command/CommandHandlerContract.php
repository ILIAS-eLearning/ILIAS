<?php
/* Copyright (c) 2019 - Martin Studer <ms@studer-raimann.ch> - Extended GPL, see LICENSE */
declare(strict_types=1);

namespace ILIAS\AssessmentQuestion\CQRS\Command;

/**
 * Interface CommandHandlerContract
 *
 * Command Handler is the place where a command is being dispatched
 * and handled.
 */
Interface CommandHandlerContract {

	public function handle(CommandContract $command);
}