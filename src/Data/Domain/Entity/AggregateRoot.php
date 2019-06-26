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
	const APPLY_PREFIX = 'apply';
	const PUSH_PREFIX = 'push';

	/**
	 * @var DomainEvents
	 */
	private $recordedEvents;

	protected function __construct()
	{
		$this->recordedEvents = new DomainEvents();
	}


	protected function ExecuteEvent(DomainEvent $event) {
		//TODO @mst transaction around push and apply and only record if transaction successful?

		//trigger push event listeners/event queues
		//TODO @mst wäre potentiell patterniger wenn alle event konsumenten pullen würden / drop if not needed
		$this->doEventAction($event, self::PUSH_PREFIX);

		// apply results of event to class, most events should result in some changes
		$this->doEventAction($event, self::APPLY_PREFIX);

		// always record that the event has happened
		$this->recordEvent($event);
	}


	protected function recordEvent(DomainEvent $event) {
		$this->recordedEvents->addEvent($event);
	}


	protected function doEventAction(DomainEvent $event, string $prefix) {
		$action_handler = $this->getHandlerName($event, $prefix);

		if (method_exists($this, $action_handler)) {
		   $this->$action_handler($event);
		}
	}

	private function getHandlerName(DomainEvent $event, string $prefix) {
		return $prefix . join('',
							array_slice(
								explode('\\', get_class($event)), -1));
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

	abstract function getAggregateId() : AggregateId;
}