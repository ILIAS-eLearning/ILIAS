<?php

namespace ILIAS\Data\Domain\Event;

/**
 * Interface Projection
 * @package ILIAS\Data\Domain\Event
 */
interface Projection {

	/**
	 * @param DomainEvents $event_stream
	 * @return mixed
	 */
	public function project(DomainEvents $event_stream);
}