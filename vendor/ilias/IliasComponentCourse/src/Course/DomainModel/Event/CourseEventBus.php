<?php
namespace srag\IliasComponentCourse\Course\Command\Event;

use srag\IliasComponent\Context\Command\Event\Event;
use srag\IliasComponent\Context\Command\Event\EventBus;

interface CourseEventBus extends EventBus
{
	/**
	 * Publishes the given domain event.
	 *
	 * @param Event $course_event The domain event
	 */
	public function handle(Event $course_event);
}