<?php

namespace ILIAS\Changelog\Events\Membership\Handlers;



use ILIAS\Changelog\Events\Membership\MembershipRequestAccepted;
use ILIAS\Changelog\Interfaces\Event;

/**
 * Class MembershipRequestAcceptedHandler
 * @package ILIAS\Changelog\Events\Membership\Handlers
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class MembershipRequestAcceptedHandler extends MembershipEventHandler {

	/**
	 * @param MembershipRequestAccepted $changelogEvent
	 */
	public function handle(Event $changelogEvent) {
		$this->repository->saveMembershipRequestAccepted($changelogEvent);
	}
}