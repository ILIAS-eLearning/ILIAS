<?php

namespace ILIAS\Changelog\Events\Membership\Handlers;


use ILIAS\Changelog\Events\Membership\RemovedFromCourse;
use ILIAS\Changelog\Interfaces\Event;

/**
 * Class RemovedFromCourseHandler
 * @package ILIAS\Changelog\Events\Membership\Handlers
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class RemovedFromCourseHandler extends MembershipEventHandler {
	/**
	 * @param RemovedFromCourse $changelogEvent
	 */
	public function handle(Event $changelogEvent) {
		$this->repository->saveRemovedFromCourse($changelogEvent);
	}
}