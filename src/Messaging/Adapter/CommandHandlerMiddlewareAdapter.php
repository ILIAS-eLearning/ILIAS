<?php

namespace ILIAS\Messaging\Adapter;

use ILIAS\Messaging\Adapter\SimpleBus\Command\CommandHandlerMiddlewareAdapter;
use ILIAS\Messaging\Contract\Command\CommandHandlerMiddleware as CommandHandlerMiddlewareContract;

class CommandHandlerMiddleware extends CommandHandlerMiddlewareAdapter implements CommandHandlerMiddlewareContract {

	/**
	 * The provided $next callable should be called whenever the next middleware should start handling the message.
	 * Its only argument should be a Message object (usually the same as the originally provided message).
	 *
	 * @param object   $message
	 * @param callable $next
	 *
	 * @return void
	 */
	public function handle($message, callable $next) {
		// TODO: Implement handle() method.
	}
}