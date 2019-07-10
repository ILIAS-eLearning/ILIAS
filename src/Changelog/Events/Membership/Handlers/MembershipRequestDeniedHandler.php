<?php

namespace ILIAS\Changelog\Events\Membership\Handlers;



use ILIAS\Changelog\Events\Membership\MembershipRequestDenied;
use ILIAS\Changelog\Interfaces\Event;

/**
 * Class MembershipRequestDeniedHandler
 * @package ILIAS\Changelog\Membership\Events\Handlers
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class MembershipRequestDeniedHandler extends MembershipEventHandler {
	/**
	 * @param Event $changelogEvent
	 */
	public function handle(Event $changelogEvent) {
		/** @var $changelogEvent MembershipRequestDenied */
		$this->repository->saveMembershipRequestDenied($changelogEvent);
	}
}