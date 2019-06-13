<?php


namespace ILIAS\Data\Domain;

/**
 * Interface DomainEventSubscriber
 * @package ILIAS\Data\Domain
 */
interface DomainEventSubscriber {
	/**
	 * @param DomainEvent $aDomainEvent
	 */
	public function handle($aDomainEvent);
	/**
	 * @param DomainEvent $aDomainEvent
	 * @return bool
	 */
	public function isSubscribedTo($aDomainEvent);
}