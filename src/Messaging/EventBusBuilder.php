<?php

namespace ILIAS\Messaging;

use ILIAS\Messaging\Contract\Command;
use ILIAS\Messaging\Adapter\CommandBus;

class CommandBusBuilder {

	private $command_bus;


	public function __construct() {

		$middleware = new Middleware();
		$this->command_bus = new CommandBus([ $middleware ]);
	}


	public function handle($command) {
		$this->command_bus->handle($command);
	}
}