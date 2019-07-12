<?php

namespace ILIAS\Changelog\Events\Membership\Handlers;



use ILIAS\Changelog\Events\Membership\SubscribedToCourse;
use ILIAS\Changelog\Interfaces\Event;

/**
 * Class SubscribedToCourseHandler
 * @package ILIAS\Changelog\Membership\Events\Handlers
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class SubscribedToCourseHandler extends MembershipEventHandler {
	/**
	 * @param SubscribedToCourse $changelogEvent
	 */
	public function handle(Event $changelogEvent) {
		$this->repository->saveSubscribedToCourse($changelogEvent);
	}
}