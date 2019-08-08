<?php

namespace ILIAS\AssessmentQuestion\CQRS\Event;

/**
 * Class DomainEventPublisher
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
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