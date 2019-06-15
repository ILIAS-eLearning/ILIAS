<?php
/* Copyright (c) 2019 Martin Studer <ms@studer-raimann.ch> Extended GPL, see docs/LICENSE - inspired by https://github.com/buttercup-php/protects */

namespace ILIAS\Data\Domain\Event;

final class AggregateHistory extends DomainEvents {

	/**
	 * @var IdentifiesAggregate
	 */
	private $aggregate_Id;


	/**
	 * AggregateHistory constructor.
	 *
	 * @param IdentifiesAggregate $aggregate_Id
	 * @param DomainEvent[]       $events
	 *
	 * @throws DomainExceptionCorruptAggregateHistory
	 */
	public function __construct(IdentifiesAggregate $aggregate_Id, array $events) {
		/** @var $event DomainEvent */
		foreach ($events as $event) {
			if (!$event->getAggregateId()->equals($aggregate_Id)) {
				throw new DomainExceptionCorruptAggregateHistory;
			}
		}
		parent::__construct($events);
		$this->aggregate_Id = $aggregate_Id;
	}


	/**
	 * @return IdentifiesAggregate
	 */
	public function getAggregateId(): IdentifiesAggregate {
		return $this->aggregate_Id;
	}


	/**
	 * @param DomainEvent $domainEvent
	 *
	 * @throws DomainExceptionMissingImplementation
	 */
	public function append(DomainEvent $domainEvent) {
		throw new DomainExceptionMissingImplementation("@todo  Implement append() method.");
	}
}