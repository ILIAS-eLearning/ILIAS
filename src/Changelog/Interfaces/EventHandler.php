<?php


namespace ILIAS\Changelog\Interfaces;

/**
 * Interface EventHandler
 * @package ILIAS\Changelog\Interfaces
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
interface EventHandler {

	public function handle(Event $changelogEvent);
}