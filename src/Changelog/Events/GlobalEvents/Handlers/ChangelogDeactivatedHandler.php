<?php

namespace ILIAS\Changelog\Events\GlobalEvents\Handlers;


use ILIAS\Changelog\Events\GlobalEvents\ChangelogDeactivated;
use ILIAS\Changelog\Interfaces\Event;

/**
 * Class ChangelogDeactivatedHandler
 * @package ILIAS\Changelog\Events\GlobalEvents\Handlers
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ChangelogDeactivatedHandler extends GlobalEventHandler {

	/**
	 * @param ChangelogDeactivated $changelogEvent
	 */
	public function handle(Event $changelogEvent) {
		$this->repository->saveChangelogDeactivated($changelogEvent);
	}
}