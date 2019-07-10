<?php

namespace ILIAS\Changelog\Events\Membership\Handlers;



use ILIAS\Changelog\Events\Membership\MembershipRequested;
use ILIAS\Changelog\Interfaces\Event;

/**
 * Class MembershipRequestedHandler
 * @package ILIAS\Changelog\Membership\Events\Handlers
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class MembershipRequestedHandler extends MembershipEventHandler {

	/**
	 * @param Event $changelogEvent
	 */
	public function handle(Event $changelogEvent) {
		/** @var $changelogEvent MembershipRequested */
		$this->repository->saveMembershipRequested($changelogEvent);
	}

}