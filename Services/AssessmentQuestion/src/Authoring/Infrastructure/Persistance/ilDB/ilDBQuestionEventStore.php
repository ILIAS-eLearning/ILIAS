<?php

namespace ILIAS\AssessmentQuestion\Authoring\Infrastructure\Persistence\ilDB;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Event\QuestionCreatedEvent;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Shared\QuestionId;
use ILIAS\Data\Domain\Entity\AggregateId;
use ILIAS\Data\Domain\Event\{AbstractDomainEvent, DomainEvent, DomainEvents, EventStore};

class ilDBQuestionEventStore implements EventStore {

	//TODO Constructor with DIC->DB-Connection - we will be a microservice

	/**
	 * @param DomainEvents $events
	 *
	 * @return void
	 */
	public function commit(DomainEvents $events) : void {
		/** @var DomainEvent $event */
		foreach ($events->getEvents() as $event) {
			$stored_event = new ilDBQuestionStoredEvent();
			$stored_event->setEventData(
				$event->getAggregateId()->getId(),
				$event->getEventName(),
				$event->getOccurredOn(),
				$event->getInitiatingUserId(),
				$event->getEventBody());

			$stored_event->create();
		}
	}

	/**
	 * @param AggregateId $id
	 *
	 * @return DomainEvents
	 */
	public function getAggregateHistoryFor(AggregateId $id): DomainEvents {
		global $DIC;

		$sql = "SELECT * FROM " . ilDBQuestionStoredEvent::STORAGE_NAME . " where aggregate_id = " . $DIC->database()->quote($id->getId());
		$res = $DIC->database()->query($sql);

		$event_stream = new DomainEvents();
		while ($row = $DIC->database()->fetchAssoc($res)) {
			/**@var AbstractDomainEvent $event */
			//TODO namespaces in db oder fix?
			$event_name = "ILIAS\\AssessmentQuestion\\Authoring\\DomainModel\\Question\\Event\\".utf8_encode(trim($row['event_name']));
			$event = new $event_name(new QuestionId($row['event_id']), $row['initiating_user_id']);
			$event->restoreEventBody($row['event_body']);
			$event_stream->addEvent($event);
		}

		return $event_stream;
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
}