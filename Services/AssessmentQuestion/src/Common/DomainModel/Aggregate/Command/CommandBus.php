<?php

namespace ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Command;

class CommandBus implements CommandBusContract {

	/** @var array */
	private $middlewares;


	public function __construct() {
		$this->middlewares = [];
	}


	/**
	 * @param Command $command
	 *
	 */
	public function handle(CommandContract $command): void {

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