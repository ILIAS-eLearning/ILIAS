<?php

namespace ILIAS\Data\Domain\Event;

class AbstractPersistDomainEventSubscriber implements DomainEventSubscriber {

	private $event_store;


	public function __construct(EventStore $event_store) {
		$this->event_store = $event_store;
	}


	public function handle($domain_event) {
		$this->event_store->append($domain_event);
	}


	public function isSubscribedTo($domain_event) {
		return true;
	}
}