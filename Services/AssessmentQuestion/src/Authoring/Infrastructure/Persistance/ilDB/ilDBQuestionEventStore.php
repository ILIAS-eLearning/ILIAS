<?php

namespace ILIAS\AssessmentQuestion\Authoring\Infrastructure\Persistence\ilDB;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Event\EventStream;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Event\GenericEvent;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Shared\QuestionId;
use ILIAS\AssessmentQuestion\Authoring\Infrastructure\Persistence\EventStore;
use ILIAS\Data\Domain\AggregateHistory;
use ILIAS\Data\Domain\Entity\AbstractAggregateId;
use ILIAS\Data\Domain\Entity\AggregateId;
use ILIAS\Data\Domain\Event\DomainEvent;
use ILIAS\Data\Domain\Event\DomainEvents;
use ILIAS\Data\Domain\IdentifiesAggregate;
use ILIAS\Data\Domain\StoredEvent;


class ilDBQuestionEventStore implements EventStore {

	//TODO Constructor with DIC->DB-Connection - we will be a microservice

	/**
	 * @param DomainEvents $events
	 *
	 * @return void
	 */
	public function commit(DomainEvents $events) {
		/** @var DomainEvent $event */
		foreach ($events->getEvents() as $event) {
			$stored_event = new ilDBQuestionStoredEvent();
			$stored_event->setEventData($event->getAggregateId()
				->id(), $event->getEventName(), $event->getOccurredOn(), $event->getInitiatingUserId(), $event->getEventBody());

			$stored_event->create();
		}
	}


	/**
	 * @param $parent_id
	 * @param $anEventId
	 *
	 * @return array
	 */

	//TODO since hier nicht verwenden. Die Methode hier müsste bereits wissen wann sie das letzte Mal projeziert hat und dann von da an die Fragen neu rechnen. ODER?
	public function allStoredQuestionsForParentSince($parent_id, $anEventId): array {
		//TODO Remove That DIC - we will be a microservice
		global $DIC;

		//TODO Parent! $parent_id

		$sql = "SELECT * FROM " . ilDBQuestionStoredEvent::STORAGE_NAME . " where event_id > " . $DIC->database()->quote($anEventId);
		$res = $DIC->database()->query($sql);

		$arr_data = [];
		while ($row = $DIC->database()->fetchAssoc($res)) {
			//TODO remove this ugly if!
			if ($row['event_body']) {
				$row['event_body'] = json_decode($row['event_body']);
			}
			$arr_data[] = $row;
		}

		return $arr_data;
	}


	/**
	 * @param AggregateId $id
	 *
	 * @return EventStream
	 */
	public function getEventsFor(AggregateId $id): EventStream {
		global $DIC;

		$sql = "SELECT * FROM " . ilDBQuestionStoredEvent::STORAGE_NAME . " where aggregate_id = " . $DIC->database()->quote($id->id());
		$res = $DIC->database()->query($sql);

		$event_stream = new EventStream($id);
		while ($row = $DIC->database()->fetchAssoc($res)) {
			$generic_event = new GenericEvent(new QuestionId($row['event_id']),$row['initiating_user_id']);
			$generic_event->setAggregateId(new QuestionId($row['event_id']));
			$generic_event->setEventName($row['event_name']);
			$generic_event->setInitatingUserId($row['initiating_user_id']);
			$generic_event->setOccurredOn($row['occured_on']);
			$generic_event->setEventBody($row['event_body']);

			$event_stream->appendEvent($generic_event);
		}

		return $event_stream;
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