<?php
namespace srag\IliasComponent\Context\Command\Event;

use srag\IliasComponent\Context\Command\Event\Event;

interface EventBus
{
	/**
	 * Publishes the given domain event.
	 *
	 * @param Event $event The domain event
	 */
	public function handle(Event $event);
}