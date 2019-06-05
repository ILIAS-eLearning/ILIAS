<?php
/* Copyright (c) 2019 - Martin Studer <ms@studer-raimann.ch> - Extended GPL, see LICENSE */

namespace ILIAS\Messaging\Adapter\Bus;
use ILIAS\Messaging\Contract\Command\CommandBus;

use ILIAS\Messaging\Contract\CommandBus as CommandBusContract;

interface MessageBus extends CommandBusContract
{
	/**
	 * @param object $message
	 * @return void
	 */
	function handle($message);
}