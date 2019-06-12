<?php

namespace  ILIAS\AssessmentQuestion\Infrastructure;

use ILIAS\Messaging\Contract\Command\Command;
use ILIAS\Messaging\Contract\Command\CommandBus;
use ILIAS\Messaging\Contract\Command\CommandHandler;
use ILIAS\Messaging\Contract\Command\CommandHandlerMiddleware;
use mysql_xdevapi\Exception;

class QuestionCommandBus implements CommandBus {
	/** @var array */
	private $handlers;

	/** @var array */
	private $middlewares;

	public function __construct() {
		$this->handlers = [];
		$this->middlewares = [];
	}

	/**
	 * @param Command $command
	 */
	public function handle(Command $command): void {
		$command_type = get_class($command);

		if (!array_key_exists($command_type, $this->handlers)) {
			throw new Exception(sprintf("No handler set for message of type %s", $command_type));
		}

		foreach ($this->middlewares as $middleware) {
			$command = $middleware->handle($command);
		}

		$this->handlers[$command_type]->handle($command);
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


	/**
	 * Appends handler to bus for corresponding command class
	 *
	 * @param string         $command_class
	 * @param CommandHandler $handler
	 */
	public function appendHandler(string $command_class, CommandHandler $handler): void {
		$this->handlers[$command_class] = $handler;
	}
}
