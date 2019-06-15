<?php

namespace ILIAS\Data\Domain\Event;

/**
 * Interface DomainEventPublisher
 *
 * @package ILIAS\Data\Domain\Event
 */
interface DomainEventPublisher {

	/**
	 * @return DomainEventPublisher.php
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