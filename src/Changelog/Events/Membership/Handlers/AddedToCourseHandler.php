<?php

namespace ILIAS\Changelog\Events\Membership\Handlers;


use ILIAS\Changelog\Events\Membership\AddedToCourse;
use ILIAS\Changelog\Interfaces\Event;

/**
 * Class AddedToCourseHandler
 * @package ILIAS\Changelog\Events\Membership\Handlers
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class AddedToCourseHandler extends MembershipEventHandler {

	/**
	 * @param Event $changelogEvent
	 */
	public function handle(Event $changelogEvent) {
		/** @var $changelogEvent AddedToCourse */
		$this->repository->saveAddedToCourse($changelogEvent);
	}
}