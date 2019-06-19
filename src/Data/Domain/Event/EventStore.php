<?php
namespace ILIAS\Data\Domain\Event;

/**
 * Interface EventStore
 *
 * @package ILIAS\Data\Domain
 */
interface EventStore {

	/**
	 * @param DomainEvent $domain_event
	 * @return mixed
	 */
	public function append(DomainEvent $domain_event);


	/**
	 * @param $event_id
	 * @return mixed
	 */
	public function allStoredEventsSince($event_id);
}