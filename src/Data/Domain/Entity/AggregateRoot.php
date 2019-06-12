<?php
/* Copyright (c) 2019 Martin Studer <ms@studer-raimann.ch> Extended GPL, see docs/LICENSE - inspired by https://github.com/buttercup-php/protects */

namespace ILIAS\Data\Domain;

/**
 * Class AggregateRoot
 * @package ILIAS\Data\Domain
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class AggregateRoot implements RecordsEvents, TracksChanges {

	/** @var DomainEvent[] */
	private $recorded_events = [];

	/**
	 * @return DomainEvents
	 */
	public function getRecordedEvents(): DomainEvents {
		return new DomainEvents($this->recorded_events);
	}

	/**
	 *
	 */
	public function clearRecordedEvents(): void {
		$this->recorded_events = [];
	}

	/**
	 * @param DomainEvent $domainEvent
	 */
	protected function recordApplyAndPublishThat(DomainEvent $domainEvent)
	{
		$this->recordThat($domainEvent);
		$this->applyThat($domainEvent);
		$this->publishThat($domainEvent);
	}

	/**
	 * @param DomainEvent $domainEvent
	 */
	protected function recordThat(DomainEvent $domainEvent)
	{
		$this->recorded_events[] = $domainEvent;
	}

	/**
	 * @param DomainEvent $domainEvent
	 */
	protected function applyThat(DomainEvent $domainEvent)
	{
		$modifier = 'apply' . get_class($domainEvent);
		$this->$modifier($domainEvent);
	}

	/**
	 * @param DomainEvent $domainEvent
	 */
	protected function publishThat(DomainEvent $domainEvent)
	{
		DomainEventPublisher::getInstance()->publish($domainEvent);
	}

	/**
	 * @return IdentifiesAggregate
	 */
	abstract public function getAggregateId(): IdentifiesAggregate;

	/**
	 * @return bool
	 */
	abstract public function hasChanges(): bool;

} 