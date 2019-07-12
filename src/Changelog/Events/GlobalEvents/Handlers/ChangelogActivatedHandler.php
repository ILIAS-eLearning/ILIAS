<?php

namespace ILIAS\Changelog\Events\GlobalEvents\Handlers;


use ILIAS\Changelog\Events\GlobalEvents\ChangelogActivated;
use ILIAS\Changelog\Interfaces\Event;

/**
 * Class ChangelogActivatedHandler
 * @package ILIAS\Changelog\Events\GlobalEvents\Handlers
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ChangelogActivatedHandler extends GlobalEventHandler {

	/**
	 * @param ChangelogActivated $changelogEvent
	 */
	public function handle(Event $changelogEvent) {
		$this->repository->saveChangelogActivated($changelogEvent);
	}


}