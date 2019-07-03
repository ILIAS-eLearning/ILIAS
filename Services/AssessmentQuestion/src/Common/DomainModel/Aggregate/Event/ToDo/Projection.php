<?php

namespace ILIAS\AssessmentQuestion\Common\Event;

/**
 * Interface Projection
 * @package ILIAS\AssessmentQuestion\Common\Event
 */
interface Projection {

	/**
	 * @param DomainEvents $event_stream
	 * @return mixed
	 */
	public function project(DomainEvents $event_stream);
}