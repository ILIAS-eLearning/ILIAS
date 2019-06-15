<?php
namespace ILIAS\Data\Domain\Event;

/**
 * Interface EventStore
 *
 * @package ILIAS\Data\Domain
 */
interface EventStore {

	public function append(DomainEvent $domain_event);


	public function allStoredEventsSince($event_id);
}