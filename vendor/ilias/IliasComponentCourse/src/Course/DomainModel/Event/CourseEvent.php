<?php

namespace srag\IliasComponentCourse\Course\Command\Event;

use srag\IliasComponent\Context\Command\Event\Event;

interface CourseEvent extends Event
{
	/**
	 * @return int id
	 */
	public function getId();
	/**
	 * @return \DateTimeImmutable
	 */
	public function getOccurredOn();
}