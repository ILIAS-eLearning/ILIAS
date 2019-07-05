<?php

namespace ILIAS\Changelog\Logger;


use ILIAS\Changelog\Event;
use ILIAS\Changelog\EventHandler;
use ILIAS\Changelog\Membership\Exception\EventHandlerNotFoundException;
use ILIAS\Changelog\Membership\Exception\UnknownEventTypeException;
use ILIAS\Changelog\Membership\MembershipEvent;
use ILIAS\Changelog\Membership\Repository\ilDBMembershipRepository;
use ILIAS\Changelog\Repository;

/**
 * Class ilDBLogger
 * @package ILIAS\Changelog\Logger
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ilDBLogger extends Logger {

	/**
	 * @param Event $event
	 * @return Repository
	 * @throws UnknownEventTypeException
	 */
	protected function getRepositoryForEvent(Event $event): Repository {
		if (is_subclass_of($event, MembershipEvent::class)) {
			return new ilDBMembershipRepository();
		} else {
			throw new UnknownEventTypeException("couldn't find event type for event: " . get_class($event));
		}
	}


}