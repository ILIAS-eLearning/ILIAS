<?php

namespace ILIAS\AssessmentQuestion\Infrastructure\Persistence\ilDB;



use ILIAS\AssessmentQuestion\CQRS\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\CQRS\Event\AbstractDomainEvent;
use ILIAS\AssessmentQuestion\CQRS\Event\DomainEvent;
use ILIAS\AssessmentQuestion\CQRS\Event\DomainEvents;
use ILIAS\AssessmentQuestion\CQRS\Event\EventStore;

/**
 * Class ilDBQuestionEventStore
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
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
	 * @param DomainObjectId $id
	 *
	 * @return DomainEvents
	 */
	public function getAggregateHistoryFor(DomainObjectId $id): DomainEvents {
		global $DIC;

		$sql = "SELECT * FROM " . ilDBQuestionStoredEvent::STORAGE_NAME . " where aggregate_id = " . $DIC->database()->quote($id->getId(),'string');
		$res = $DIC->database()->query($sql);

		$event_stream = new DomainEvents();
		while ($row = $DIC->database()->fetchAssoc($res)) {
			/**@var AbstractDomainEvent $event */
			$event_name = "ILIAS\\AssessmentQuestion\\DomainModel\\Event\\".utf8_encode(trim($row['event_name']));
			$event = new $event_name(new DomainObjectId($row['aggregate_id']), $row['initiating_user_id']);
			$event->restoreEventBody($row['event_body']);
			$event_stream->addEvent($event);
		}

		return $event_stream;
	}


    /**
     * @param $anEventId
     *
     * @return array
     */
	public function allStoredQuestionsForParentSince($anEventId): array {
	   //TODO Remove That DIC - we will be a microservice
	   global $DIC;

	   //TODO Parent! $parent_id

	   $sql = "SELECT * FROM " . ilDBQuestionStoredEvent::STORAGE_NAME . " where event_name = 'QuestionCreatedEvent' and event_id > " . $DIC->database()->quote($anEventId);
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