<?php
/* Copyright (c) 2019 Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\Common\Event;

use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Event\DomainEvent;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Event\EventStore;

/**
 * Class AbstractPersistDomainEventSubscriber
 *
 * @package ILIAS\AssessmentQuestion\Common\Event
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AbstractPersistDomainEventSubscriber implements DomainEventSubscriber {

	/**
	 * @var EventStore
	 */
	private $event_store;


	/**
	 * AbstractPersistDomainEventSubscriber constructor.
	 *
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
	 *
	 * @return bool
	 */
	public function isSubscribedTo($domain_event) {
		return true;
	}
}