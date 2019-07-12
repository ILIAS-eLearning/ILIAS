<?php

namespace ILIAS\Changelog\Logger;


use ILIAS\Changelog\Events\GlobalEvents\GlobalEvent;
use ILIAS\Changelog\Events\Membership\MembershipEvent;
use ILIAS\Changelog\Exception\UnknownEventTypeException;
use ILIAS\Changelog\Infrastructure\Repository\ilDBGlobalEventRepository;
use ILIAS\Changelog\Infrastructure\Repository\ilDBMembershipEventRepository;
use ILIAS\Changelog\Interfaces\Event;
use ILIAS\Changelog\Interfaces\Repository;

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
			return new ilDBMembershipEventRepository();
		} elseif (is_subclass_of($event, GlobalEvent::class)) {
			return new ilDBGlobalEventRepository();
		} else {
			throw new UnknownEventTypeException("couldn't find event type for event: " . get_class($event));
		}
	}


}