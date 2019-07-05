<?php

namespace ILIAS\Changelog\Logger;


use ILIAS\Changelog\Event;
use ILIAS\Changelog\EventHandler;
use ILIAS\Changelog\Membership\Exception\EventHandlerNotFoundException;
use ILIAS\Changelog\Membership\Exception\UnknownEventTypeException;
use ILIAS\Changelog\Membership\MembershipEvent;
use ILIAS\Changelog\Membership\Repository\ilDBMembershipEventRepository;
use ILIAS\Changelog\Repository;

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
	public function logEvent(Event $event): void {
		$handler_class = get_class($event) . 'Handler';
		if (!is_subclass_of($handler_class, EventHandler::class)) {
			throw new EventHandlerNotFoundException('handler class "' . $handler_class . '" should be a subclass of ILIAS\Changelog\EventHandler');
		}

		/** @var EventHandler $EventHandler */
		$EventHandler = new $handler_class($this->getRepositoryForEvent($event));
		$EventHandler->handle($event);
	}

	abstract protected function getRepositoryForEvent(Event $event): Repository;

}