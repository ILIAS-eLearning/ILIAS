<?php

namespace ILIAS\Messaging;

use ILIAS\Messaging\Adapter\CommandHandlerMiddleware as MiddlewareAdapter;
use ILIAS\Messaging\Contract\Command\Command;
use ILIAS\Messaging\Contract\Command\CommandHandlerMiddleware as CommandHandlerMiddleware;
use ILIAS\Messaging\Contract\Command\CommandHandler as CommandHandlerContract;

/**
 * Class MiddlewareHandelsCommands
 *
 * @package ILIAS\Messaging
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class MiddlewareHandelsCommands extends MiddlewareAdapter implements CommandHandlerMiddleware {

	/**
	 * The provided $next callable should be called whenever the next middleware should start handling the message.
	 * Its only argument should be a Message object (usually the same as the originally provided message).
	 *
	 * @param Command  $command
	 * @param callable $next
	 *
	 * @return void
	 */
	public function handle($command, callable $next) {

		$handler_name = get_class($command).'Handler';
		/**
		 * @var CommandHandlerContract $handler
		 */
		$handler = new $handler_name;


		$handler->handle($command);



		$next($command);
	}
}