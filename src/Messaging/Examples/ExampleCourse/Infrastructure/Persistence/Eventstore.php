<?php
namespace ILIAS\Messaging\Example\ExampleCourse\Infrastructure\Persistence\InMemory;
use ILIAS\Data\Domain\DomainEvents;
use ILIAS\Data\Domain\IdentifiesAggregate;

interface EventStore
{
	/**
	 * @param DomainEvents $events
	 *
	 * @return void
	 */
	public function commit(DomainEvents $events);
	/**
	 * @param IdentifiesAggregate $id
	 *
	 * @return AggregateHistory
	 */
	public function getAggregateHistoryFor(IdentifiesAggregate $id);
}
