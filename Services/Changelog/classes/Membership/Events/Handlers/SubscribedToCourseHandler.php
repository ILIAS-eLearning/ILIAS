<?php

namespace ILIAS\Changelog\Membership\Events\Handlers;


use ILIAS\Changelog\Event;
use ILIAS\Changelog\Membership\Events\SubscribedToCourse;
use ILIAS\Changelog\Membership\MembershipEventHandler;

/**
 * Class SubscribedToCourseHandler
 * @package ILIAS\Changelog\Membership\Events\Handlers
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class SubscribedToCourseHandler extends MembershipEventHandler {
	/**
	 * @param Event $changelogEvent
	 */
	public function handle(Event $changelogEvent): void {
		/** @var $changelogEvent SubscribedToCourse */
		$this->repository->saveSubscribedToCourse($changelogEvent);
	}
}