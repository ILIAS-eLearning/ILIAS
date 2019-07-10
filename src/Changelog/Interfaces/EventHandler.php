<?php


namespace ILIAS\Changelog\Interfaces;


interface EventHandler {

	public function handle(Event $changelogEvent);
}