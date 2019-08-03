<?php

namespace ILIAS\AssessmentQuestion\Common\Event;


/**
 * Interface DomainEventPublisher
 *
 * @package ILIAS\AssessmentQuestion\Common\Event
 */
interface DomainEventPublisher {

	/**
	 * @return DomainEventPublisher
	 */
	public static function getInstance();


	/**
	 * DomainEventPublisher constructor.
	 */
	public function __construct();


	/**
	 * @param DomainEventSubscriber $aDomainEventSubscriber
	 */
	public function subscribe(DomainEventSubscriber $aDomainEventSubscriber);


	/**
	 * @param DomainEvent $anEvent
	 */
	public function publish(DomainEvent $anEvent);
}