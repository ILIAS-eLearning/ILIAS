<?php

namespace ILIAS\AssessmentQuestion\Authoring\Infrastructure\Persistence\ilDB;

use ILIAS\AssessmentQuestion\Authoring\Infrastructure\Persistence\EventStore;
use ILIAS\Data\Domain\AggregateHistory;
use ILIAS\Data\Domain\Entity\AggregateId;
use ILIAS\Data\Domain\Event\DomainEvent;
use ILIAS\Data\Domain\Event\DomainEvents;
use ILIAS\Data\Domain\IdentifiesAggregate;
use ILIAS\Data\Domain\StoredEvent;

class ilDBQuestionEventStore implements EventStore {

	/**
	 * @param DomainEvents $events
	 *
	 * @return void
	 */
	public function commit(DomainEvents $events) {
		/** @var DomainEvent $event */
		foreach ($events->getEvents() as $event) {
			$stored_event = new ilDBQuestionStoredEvent();
			$stored_event->setEventData(
				$event->getAggregateId()->id(),
				$event->getEventName(),
				$event->getOccurredOn(),
				$event->getInitiatingUserId(),
				$event->getEventBody()
			);

			$stored_event->create();
		}
	}


	/**
	 * @param AggregateId $id
	 *
	 * @return AggregateHistory
	 */
	public function getAggregateHistoryFor(AggregateId $id) {
		// TODO: Implement getAggregateHistoryFor() method.
	}
}