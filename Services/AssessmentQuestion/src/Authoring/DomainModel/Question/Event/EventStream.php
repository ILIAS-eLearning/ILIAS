<?php
//TODO wird verwendet um von der History wieder den aktallen Stand herzustellen. prüfen wie dies andere tun!
namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Event;

use ILIAS\Data\Domain\Event\AbstractDomainEvent;
use ILIAS\Data\Domain\Entity\AggregateId;

class EventStream {

	/**
	 * @var AggregateId
	 */
	public $aggregate;

	/**
	 * @var AbstractDomainEvent[]
	 */
	public $events;

	public function __construct(AggregateId $aggregate) {
		$this->aggregate = $aggregate;
	}


	/**
	 * @return AggregateId
	 */
	public function getAggregate(): AggregateId {
		return $this->aggregate;
	}


	/**
	 * @return AbstractDomainEvent[]
	 */
	public function getEvents(): array {
		return $this->events;
	}


	/**
	 * @param AbstractDomainEvent
	 */
	public function appendEvent($event) {
		$this->events[] = $event;
	}

}