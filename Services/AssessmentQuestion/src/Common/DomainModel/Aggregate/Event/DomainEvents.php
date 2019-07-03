<?php
/* Copyright (c) 2019 Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Event;

/**
 * Class DomainEvents
 *
 * List of domain events
 *
 * @package ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Event
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class DomainEvents {

	/**
	 * @var array
	 */
	private $events;


	/**
	 * DomainEvents constructor.
	 */
	public function __construct() {
		$events = [];
	}


	/**
	 * @param DomainEvent $event
	 */
	public function addEvent(DomainEvent $event) {
		$this->events[] = $event;
	}


	/**
	 * @return array
	 */
	public function getEvents(): array {
		return $this->events;
	}
}