<?php

namespace ILIAS\Changelog\Logger;


use ILIAS\Changelog\Exception\EventHandlerNotFoundException;
use ILIAS\Changelog\Interfaces\Event;
use ILIAS\Changelog\Interfaces\EventHandler;
use ILIAS\Changelog\Interfaces\Repository;
use ReflectionClass;

/**
 * Class Logger
 * @package ILIAS\Changelog\Logger
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class Logger {

	/**
	 * @param Event $event
	 * @throws EventHandlerNotFoundException
	 * @throws \ReflectionException
	 */
	public function logEvent(Event $event) {
		$reflect = new ReflectionClass($event);

		$handler_class = $reflect->getShortName() . 'Handler';
		if ($handler_class instanceof EventHandler) {
			throw new EventHandlerNotFoundException('handler class "' . $handler_class . '" should implement ILIAS\Changelog\Interfaces\EventHandler');
		}

		$fully_qualified_handler_class = $reflect->getNamespaceName() . "\Handlers\\" . $handler_class;
		/** @var EventHandler $EventHandler */
		$EventHandler = new $fully_qualified_handler_class($this->getRepositoryForEvent($event));
		$EventHandler->handle($event);
	}

	/**
	 * @param Event $event
	 * @return Repository
	 */
	abstract protected function getRepositoryForEvent(Event $event): Repository;

}