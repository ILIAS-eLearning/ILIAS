<?php

namespace srag\IliasComponent\Context\Command\Event;

interface Event
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