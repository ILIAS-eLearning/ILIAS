<?php

namespace ILIAS\Data\Domain\Entity;

use ILIAS\Data\Domain\Event\DomainEvent;
use ILIAS\Data\Domain\Event\DomainEventPublisher;
use ILIAS\Data\Domain\Event\DomainEvents;

/**
 * Class AggregateRoot
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
abstract class AggregateRoot {

	/**
	 * @var DomainEvents
	 */
	private $recordedEvents;

	public function __construct()
	{
		$this->recordedEvents = new DomainEvents();
	}


	protected function recordApplyAndPublishThat(DomainEvent $domainEvent) {
		$this->recordThat($domainEvent);
		$this->applyThat($domainEvent);
		$this->publishThat($domainEvent);
	}


	protected function recordThat(DomainEvent $domainEvent) {
		$this->recordedEvents->addEvent($domainEvent);
	}


	protected function applyThat(DomainEvent $domainEvent) {
		$event_class_without_namespace = join('', array_slice(explode('\\', get_class($domainEvent)), -1));



		$modifier = 'apply' . $event_class_without_namespace;
		$this->$modifier($domainEvent);
	}


	protected function publishThat(DomainEvent $domainEvent) {
		//TODO publish event, so that happy middlewares may munch on it
		//DomainEventPublisher::getInstance()->publish($domainEvent);
	}


	/**
	 * @return DomainEvents
	 */
	public function getRecordedEvents() {
		return $this->recordedEvents;
	}


	public function clearRecordedEvents() {
		$this->recordedEvents = new DomainEvents();
	}
}