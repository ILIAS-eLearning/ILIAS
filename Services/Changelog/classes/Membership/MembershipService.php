<?php

namespace ILIAS\Changelog\Membership;


use ILIAS\Changelog\Event;
use ILIAS\Changelog\ChangelogServiceInterface;

/**
 * Class ChangelogServiceMembership
 * @package ILIAS\Changelog\Membership
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class MembershipService implements ChangelogServiceInterface {

	/**
	 * @param Event $changelogEvent
	 */
	public function logEvent(Event $changelogEvent): void {
		// TODO: Implement logEvent() method.
	}


}