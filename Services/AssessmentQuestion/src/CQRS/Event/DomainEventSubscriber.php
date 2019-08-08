<?php

namespace ILIAS\AssessmentQuestion\CQRS\Event;

/**
 * Class DomainEventSubscriber
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
interface DomainEventSubscriber {

	/**
	 * @param DomainEvent $aDomainEvent
	 */
	public function handle($aDomainEvent);


	/**
	 * @param DomainEvent $aDomainEvent
	 *
	 * @return bool
	 */
	public function isSubscribedTo($aDomainEvent);
}