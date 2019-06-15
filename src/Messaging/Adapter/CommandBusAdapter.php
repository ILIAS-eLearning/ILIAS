<?php

namespace ILIAS\Messaging\Adapter;

use ILIAS\Data\Domain\Exception\DomainException;
use ILIAS\Messaging\Contract\Command\CommandHandler as CommandHandlerContract;
use ILIAS\Messaging\Contract\Command\CommandHandlerMiddleware;
use ILIAS\Messaging\Contract\Command\Command;
use ILIAS\Messaging\Contract\Command\CommandHandler;
use ILIAS\Messaging\Adapter\SimpleBus\Command\CommandBus;
use ILIAS\Messaging\Contract\Command\CommandBus as CommandBusContract;

class CommandBusAdapter extends CommandBus implements CommandBusContract {

	/** @var array */
	private $middlewares;


	public function __construct() {
		parent::__construct();
		$this->middlewares = [];
	}


	/**
	 * @param Command $command
	 *
	 * @throws DomainException
	 */
	public function handle(Command $command): void {

		foreach ($this->middlewares as $middleware) {
			$command = $middleware->handle($command);
		}

		$handler_name = get_class($command).'Handler';
		/** @var CommandHandlerContract $handler */
		$handler = new $handler_name;

		if (!is_object($handler)) {
			throw new DomainException(sprintf("No handler found for command %s", $command));
		}

		$handler->handle($command);
	}


	/**
	 * Appends new middleware for this message bus.
	 * Should only be used at configuration time.
	 *
	 * @param CommandHandlerMiddleware $middleware
	 *
	 * @return void
	 */
	public function appendMiddleware(CommandHandlerMiddleware $middleware): void {
		$this->middlewares[] = $middleware;
	}
}