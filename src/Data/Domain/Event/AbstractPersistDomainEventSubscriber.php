<?php

namespace ILIAS\Data\Domain\Event;

class AbstractPersistDomainEventSubscriber implements DomainEventSubscriber {

	/**
	 * @var EventStore
	 */
	private $event_store;

	/**
	 * AbstractPersistDomainEventSubscriber constructor.
	 * @param EventStore $event_store
	 */
	public function __construct(EventStore $event_store) {
		$this->event_store = $event_store;
	}


	/**
	 * @param DomainEvent $domain_event
	 */
	public function handle($domain_event) {
		$this->event_store->append($domain_event);
	}


	/**
	 * @param DomainEvent $domain_event
	 * @return bool
	 */
	public function isSubscribedTo($domain_event) {
		return true;
	}
}