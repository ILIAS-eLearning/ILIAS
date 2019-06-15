<?php

namespace ILIAS\Data\Domain\Event;

interface Projection {

	public function project(DomainEvents $event_stream);
}