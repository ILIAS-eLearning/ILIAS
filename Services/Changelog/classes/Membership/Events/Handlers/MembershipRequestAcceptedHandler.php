<?php

namespace ILIAS\Changelog\Membership\Events\Handlers;


use ILIAS\Changelog\Event;
use ILIAS\Changelog\Membership\Events\MembershipRequestAccepted;
use ILIAS\Changelog\Membership\MembershipEventHandler;

/**
 * Class MembershipRequestAcceptedHandler
 * @package ILIAS\Changelog\Membership\Events\Handlers
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class MembershipRequestAcceptedHandler extends MembershipEventHandler {

	/**
	 * @param Event $changelogEvent
	 */
	public function handle(Event $changelogEvent): void {
		/** @var $changelogEvent MembershipRequestAccepted */
		$this->repository->saveMembershipRequestAccepted($changelogEvent);
	}
}