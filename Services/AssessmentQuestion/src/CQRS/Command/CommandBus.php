<?php

namespace ILIAS\AssessmentQuestion\CQRS\Command;

use DomainException;

/**
 * Class CommandBus
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class CommandBus implements CommandBusContract {

	/** @var array */
	private $middlewares;


	public function __construct() {
		$this->middlewares = [];
	}


	/**
	 * @param CommandContract $command
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