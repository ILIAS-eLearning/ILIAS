<?php

namespace ILIAS\Changelog\Membership\Events\Handlers;


use ILIAS\Changelog\Event;
use ILIAS\Changelog\Membership\Events\MembershipRequestDenied;
use ILIAS\Changelog\Membership\MembershipEventHandler;

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
	public function handle(Event $changelogEvent): void {
		/** @var $changelogEvent MembershipRequestDenied */
		$this->repository->saveMembershipRequestDenied($changelogEvent);
	}
}