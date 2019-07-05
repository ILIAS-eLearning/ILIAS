<?php

namespace ILIAS\Changelog\Membership\Events\Handlers;


use ILIAS\Changelog\Event;
use ILIAS\Changelog\Membership\Events\UnsubscribedFromCourse;
use ILIAS\Changelog\Membership\MembershipEventHandler;

/**
 * Class UnsubscribedFromCourseHandler
 * @package ILIAS\Changelog\Membership\Events\Handlers
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class UnsubscribedFromCourseHandler extends MembershipEventHandler {
	/**
	 * @param Event $changelogEvent
	 */
	public function handle(Event $changelogEvent): void {
		/** @var $changelogEvent UnsubscribedFromCourse */
		$this->repository->saveUnsubscribedFromCourse($changelogEvent);
	}
}