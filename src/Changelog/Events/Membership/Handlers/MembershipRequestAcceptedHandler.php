<?php

namespace ILIAS\Changelog\Events\Membership\Handlers;



use ILIAS\Changelog\Events\Membership\MembershipRequestAccepted;
use ILIAS\Changelog\Interfaces\Event;

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
	public function handle(Event $changelogEvent) {
		/** @var $changelogEvent MembershipRequestAccepted */
		$this->repository->saveMembershipRequestAccepted($changelogEvent);
	}
}