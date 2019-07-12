<?php


namespace ILIAS\Changelog\Interfaces;

/**
 * Interface EventHandler
 * @package ILIAS\Changelog\Interfaces
 */
interface EventHandler {

	public function handle(Event $changelogEvent);
}