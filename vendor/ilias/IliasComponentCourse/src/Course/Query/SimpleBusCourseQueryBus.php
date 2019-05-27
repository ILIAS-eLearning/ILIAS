<?php
namespace Bsrag\IliasComponentCourse\Course\Query;
use Message\Bus\CatchReturnMessageBus;
use SimpleBus\Message\Bus\MessageBus;
use srag\IliasComponentCourse\Course\Query\CourseQueryBus;


class SimpleBusFileQueryBus implements CourseQueryBus
{
	/**
	 * The message bus.
	 *
	 * @var MessageBus
	 */
	private $messageBus;
	/**
	 * Constructor.
	 *
	 * @param $message_bus
	 */
	public function __construct(MessageBus $message_bus)
	{
		$this->message_bus = $message_bus;
	}
	/**
	 * {@inheritdoc}
	 */
	public function handle($query)
	{
		$this->messageBus->handle($query);
	}
}