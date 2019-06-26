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

	/**
	 * @var DomainEvents
	 */
	private $recordedEvents;

	protected function __construct()
	{
		$this->recordedEvents = new DomainEvents();
	}


	protected function ExecuteEvent(DomainEvent $event) {
		// apply results of event to class, most events should result in some changes
		$this->applyEvent($event);

		// always record that the event has happened
		$this->recordEvent($event);
	}


	protected function recordEvent(DomainEvent $event) {
		$this->recordedEvents->addEvent($event);
	}


	protected function applyEvent(DomainEvent $event) {
		$action_handler = $this->getHandlerName($event);

		if (method_exists($this, $action_handler)) {
		   $this->$action_handler($event);
		}
	}

	private function getHandlerName(DomainEvent $event) {
		return self::APPLY_PREFIX . join('',
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