<?php
namespace ILIAS\AssessmentQuestion\Authoring\Infrastructure\Persistence;
use ILIAS\Data\Domain\Entity\AggregateId;
use ILIAS\Data\Domain\Event\DomainEvents;

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
	public function getAggregateHistoryFor(AggregateId $id);
}
