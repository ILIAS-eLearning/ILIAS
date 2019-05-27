<?php

namespace srag\IliasComponentCourse\Course\Command\Event;
use SimpleBus\Message\Bus\MessageBus;
use srag\IliasComponent\Context\Command\Event\Event;

class SimpleBusFileEventBus implements CourseEventBus
{
	/**
	 * The message bus.
	 *
	 * @var MessageBus
	 */
	private $message_bus;
	/**
	 * Constructor.
	 *
	 */
	public function __construct(MessageBus $message_bus)
	{
		$this->message_bus = $message_bus;
	}
	/**
	 * @param Event $course_event The domain event
	 */
	public function handle(Event $course_event)
	{
		$this->message_bus->handle($course_event);
	}
}