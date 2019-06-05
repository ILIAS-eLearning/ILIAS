<?php

namespace ILIAS\Messaging\Adapter\Bus;

use ILIAS\Messaging\Contract\Command\Command;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware as SimpleBusMessageBusSupportingMiddleware;

use ILIAS\Messaging\Contract\Command\CommandBus as CommandBusContract;
use ILIAS\Messaging\Contract\Command\CommandHandlerMiddlewareAdapter as CommandHandlerMiddlewareContract;

class CommandBusAdapter implements CommandBusContract {

	/**
	 * @var SimpleBusMessageBusSupportingMiddleware
	 */
	private $command_bus_adapter;

	/**
	 * @var CommandHandlerMiddlewareContract[]
	 */
	private $middlewares = [];

	public function __construct(array $middlewares) {
		$this->command_bus_adapter = new SimpleBusMessageBusSupportingMiddleware($middlewares);
	}

	public function handle(Command $command): void
	{
		$this->command_bus_adapter->handle($command);
	}
}