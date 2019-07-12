<?php

namespace ILIAS\Changelog\Events\Membership\Handlers;


use ILIAS\Changelog\Events\Membership\AutofilledFromWaitingList;
use ILIAS\Changelog\Interfaces\Event;

/**
 * Class AutofilledFromWaitingListHandler
 * @package ILIAS\Changelog\Events\Membership\Handlers
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class AutofilledFromWaitingListHandler extends MembershipEventHandler {

	/**
	 * @param AutofilledFromWaitingList $changelogEvent
	 */
	public function handle(Event $changelogEvent) {
		$this->repository->saveAutofilledFromWaitingList($changelogEvent);
	}

}