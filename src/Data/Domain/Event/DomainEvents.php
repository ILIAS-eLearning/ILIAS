<?php
/* Copyright (c) 2019 Martin Studer <ms@studer-raimann.ch> Extended GPL, see docs/LICENSE - inspired by https://github.com/buttercup-php/protects */

namespace ILIAS\Data\Domain\Event;


/**
 * List of domain events
 */
class DomainEvents
{
	/**
	 * @var array
	 */
	private $events;

	public function __construct()
	{
		$events = [];
	}

	public function addEvent(DomainEvent $event)
	{
		$this->events[] = $event;
	}

	public function getEvents() : array
	{
		return $this->events;
	}
}