<?php

namespace ILIAS\Messaging;

use ILIAS\Messaging\Adapter\CommandBusAdapter;
use ILIAS\Messaging\Contract\Command\CommandBus;
use ILIAS\Messaging\FinishHandlingMessageBefordeHandlingNext;

class CommandBusBuilder {

	/**
	 * @var CommandBus
	 */
	protected $command_bus;


	/**
	 * CommandBusBuilder constructor.
	 */
	public function __construct() {
		$this->command_bus = new CommandBusAdapter();
		//$this->command_bus->appendMiddleware(new FinishHandlingMessageBefordeHandlingNextMiddleware());
	}


	/**
	 * @return CommandBusAdapter|CommandBus
	 */
	public function getCommandBus() {
		return $this->command_bus;
	}


	public function appendMiddlware($middleware) {
		$this->command_bus->appendMiddleware($middleware);
	}
}