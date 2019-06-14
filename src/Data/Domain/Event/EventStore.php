<?php
namespace ILIAS\Data\Domain;

/**
 * Interface EventStore
 *
 * @package ILIAS\Data\Domain
 */
interface EventStore {

	public function append(DomainEvent $domain_event);


	public function allStoredEventsSince($event_id);
}