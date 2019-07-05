<?php

namespace ILIAS\Changelog\Membership\Events\Handlers;


use ILIAS\Changelog\Event;
use ILIAS\Changelog\Membership\Events\MembershipRequested;
use ILIAS\Changelog\Membership\MembershipEventHandler;

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
	public function handle(Event $changelogEvent): void {
		/** @var $changelogEvent MembershipRequested */
		$this->repository->saveMembershipRequested($changelogEvent);
	}

}