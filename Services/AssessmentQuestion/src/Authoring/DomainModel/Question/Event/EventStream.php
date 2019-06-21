<?php
//TODO wird verwendet um von der History wieder den aktallen Stand herzustellen. prüfen wie dies andere tun!
namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Event;

use ILIAS\Data\Domain\Event\AbstractDomainEvent;

class EventStream {
	/**
	 * @var AbstractDomainEvent[]
	 */
	public $events;

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