<?php
namespace ILIAS\Data\Domain\Event;

use ilDateTime;
use ILIAS\Data\Domain\Entity\AggregateId;

/**
 * Interface EventStore
 *
 * @package ILIAS\Data\Domain
 */
interface EventStore {
	/**
	 * @param DomainEvents $domain_events
	 */
	public function commit(DomainEvents $domain_events) : void;

	/**
	 * @param AggregateId $id
	 *
	 * @return DomainEvents
	 */
	public function getAggregateHistoryFor(AggregateId $id) : DomainEvents;
}