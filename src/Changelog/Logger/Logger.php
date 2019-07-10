<?php

namespace ILIAS\Changelog\Logger;


use ILIAS\Changelog\Exception\EventHandlerNotFoundException;
use ILIAS\Changelog\Interfaces\Event;
use ILIAS\Changelog\Interfaces\EventHandler;
use ILIAS\Changelog\Interfaces\Repository;

/**
 * Class Bus
 * @package ILIAS\Changelog\Bus
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class Logger {

	/**
	 * @param Event $event
	 * @throws EventHandlerNotFoundException
	 */
	public function logEvent(Event $event) {
		$handler_class = get_class($event) . 'Handler';
		if (!is_subclass_of($handler_class, EventHandler::class)) {
			throw new EventHandlerNotFoundException('handler class "' . $handler_class . '" should be a subclass of ILIAS\Changelog\Interfaces\EventHandler');
		}

		/** @var EventHandler $EventHandler */
		$EventHandler = new $handler_class($this->getRepositoryForEvent($event));
		$EventHandler->handle($event);
	}

	/**
	 * @param Event $event
	 * @return Repository
	 */
	abstract protected function getRepositoryForEvent(Event $event): Repository;

}