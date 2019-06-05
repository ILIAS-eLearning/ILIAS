<?php
namespace ILIAS\Messaging;

use addCourseMemberToCourseCommandHandler;
use ILIAS\Messaging\Adapter\Command\CommandHandlerMiddlewareAdapter;

class Middleware implements CommandHandlerMiddlewareAdapter {

	/**
	 * The provided $next callable should be called whenever the next middleware should start handling the message.
	 * Its only argument should be a Message object (usually the same as the originally provided message).
	 *
	 * @param object   $command
	 * @param callable $next
	 *
	 * @return void
	 */
	public function handle($command, callable $next) {



		/*$handler = get_class($message).'Handler';
		$handler = new $handler;*/
		//TODO
		$handler = new addCourseMemberToCourseCommandHandler();

		$handler->handle($command);


		$next($command);
	}
}