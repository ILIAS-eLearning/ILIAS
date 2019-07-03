<?php

namespace ILIAS\AssessmentQuestion\Common\Event;

use ActiveRecord;

/**
 * Class AbstractEventStore
 *
 * @package ILIAS\Data\Domain
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
abstract class AbstractEventStore implements EventStore {

	/**
	 * @return ActiveRecord
	 */
	protected abstract function getStoredEvent();


	/**
	 * @param int       $aggregate_id
	 * @param string    $event_name
	 * @param \DateTime $occured_on
	 * @param int       $initiating_user_id
	 * @param string    $event_body
	 *
	 * @return StoredEvent
	 */
	protected abstract function getEventToStore(int $aggregate_id, string $event_name, \DateTime $occured_on, int $initiating_user_id, string $event_body);


	public function append(DomainEvent $domain_event) {
		global $DIC;

		$event_to_store = $this->getEventToStore($domain_event->getAggregateId()
			->__toString(), $domain_event->getEventName(), $domain_event->getOccuredOn(), $domain_event->getInitiatingUserId(), $this->serialize($domain_event));

		$event_to_store->create();
	}


	/**
	 * @param $event_id
	 *
	 * @return StoredEvent[]
	 * @throws \arException
	 */
	public function allStoredEventsSince($event_id) {

		return $this->getStoredEvent()::where([ 'event_id', $event_id ], '>')->orderBy('event_id')->get();
	}


	/**
	 * @return string
	 */
	private function serialize($domain_event): string {
		global $DIC;

		return $DIC->refinery()->object()->JsonSerializedObject()->transform($domain_event);
	}
}
