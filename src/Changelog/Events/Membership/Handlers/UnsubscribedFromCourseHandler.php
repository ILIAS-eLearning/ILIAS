<?php

namespace ILIAS\Changelog\Events\Membership\Handlers;


use ILIAS\Changelog\Events\Membership\UnsubscribedFromCourse;
use ILIAS\Changelog\Interfaces\Event;

/**
 * Class UnsubscribedFromCourseHandler
 * @package ILIAS\Changelog\Membership\Events\Handlers
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class UnsubscribedFromCourseHandler extends MembershipEventHandler {
	/**
	 * @param UnsubscribedFromCourse $changelogEvent
	 */
	public function handle(Event $changelogEvent) {
		$this->repository->saveUnsubscribedFromCourse($changelogEvent);
	}
}