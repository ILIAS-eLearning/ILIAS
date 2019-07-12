<?php

namespace ILIAS\Changelog\Events\Membership\Handlers;


use ILIAS\Changelog\Events\Membership\ManuallyAddedFromWaitingList;
use ILIAS\Changelog\Interfaces\Event;

/**
 * Class ManuallyAddedFromWaitingListHandler
 * @package ILIAS\Changelog\Events\Membership\Handlers
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ManuallyAddedFromWaitingListHandler extends MembershipEventHandler {

	/**
	 * @param ManuallyAddedFromWaitingList $changelogEvent
	 */
	public function handle(Event $changelogEvent) {
		$this->repository->saveManuallyAddedFromWaitingList($changelogEvent);
	}

}