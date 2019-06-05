<?php
/* Copyright (c) 2019 - Martin Studer <ms@studer-raimann.ch> - Extended GPL, see LICENSE */

namespace ILIAS\Messaging\Adapter\Command;
use ILIAS\Messaging\Contract\Command\CommandBus;
use ILIAS\Messaging\Contract\Command\Command;
use ILIAS\Messaging\Contract\Command\CommandBus as CommandBusContract;

interface MessageBus extends CommandBusContract
{
	/**
	 * @param Command $message
	 * @return void
	 */
	function handle(Command $message): void;
}