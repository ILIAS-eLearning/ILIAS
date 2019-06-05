<?php

namespace ILIAS\Messaging\Adapter\Command;

use ILIAS\Messaging\Contract\Command\Command;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware as SimpleBusMessageBusSupportingMiddleware;

use ILIAS\Messaging\Contract\Command\CommandBus as CommandBusContract;
use ILIAS\Messaging\Contract\Command\CommandHandlerMiddleware as CommandHandlerMiddlewareContract;

class CommandBusAdapter implements CommandBusContract {

	/**
	 * @var CommandBusAdapter
	 */
	private $command_bus_adapter;

	/**
	 * @var CommandHandlerMiddlewareContract[]
	 */
	private $middlewares = [];

	public function __construct(array $middlewares) {
		$this->command_bus_adapter = new SimpleBusMessageBusSupportingMiddleware();

		foreach ($middlewares as $middleware) {
			$this->appendMiddleware($middleware);
		}
	}

	/**
	 * Appends new middleware for this message bus. Should only be used at configuration time.
	 *
	 * @private
	 * @param CommandHandlerMiddlewareContract $middleware
	 * @return void
	 */
	public function appendMiddleware(CommandHandlerMiddlewareContract $middleware)
	{
		$this->command_bus_adapter->appendMiddleware($middleware);
	}


	public function handle(Command $command): void
	{
		$this->command_bus_adapter->handle($command);
	}
}