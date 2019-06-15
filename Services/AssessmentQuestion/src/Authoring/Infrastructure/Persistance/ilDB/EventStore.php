<?php
namespace ILIAS\AssessmentQuestion\AuthoringInfrastructure\Persistence;
use ILIAS\Data\Domain\DomainEvents;
use ILIAS\Data\Domain\IdentifiesAggregate;
use ILIAS\Data\Domain\AggregateHistory;

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
