<?php


namespace ILIAS\Changelog;


interface EventHandler {

	public function handle(Event $changelogEvent): void;
}