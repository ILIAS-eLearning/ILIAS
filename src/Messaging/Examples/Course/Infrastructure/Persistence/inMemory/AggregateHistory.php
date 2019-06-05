<?php

use ILIAS\Data\Domain\DomainEvent;
use ILIAS\Data\Domain\DomainEvents;
use ILIAS\Data\Domain\IdentifiesAggregate;

final class AggregateHistory extends DomainEvents
{
	/**
	 * @var IdentifiesAggregate
	 */
	private $aggregateId;

	public function __construct(IdentifiesAggregate $aggregateId, array $events)
	{
		/** @var $event DomainEvent */
		foreach($events as $event) {
			if(!$event->getAggregateId()->equals($aggregateId)) {
				throw new CorruptAggregateHistory;
			}
		}
		parent::__construct($events);
		$this->aggregateId = $aggregateId;
	}

	/**
	 * @return IdentifiesAggregate
	 */
	public function getAggregateId()
	{
		return $this->aggregateId;
	}

	/**
	 * @param DomainEvent $domainEvent
	 * @return AggregateHistory
	 */
	public function append(DomainEvent $domainEvent)
	{
		throw new \Exception("@todo  Implement append() method.");
	}
}
