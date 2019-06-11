<?php
namespace  ILIAS\Messaging\Example\ExampleAsq\Domainmodel\Common;
use ILIAS\Data\Domain\DomainEvents;
interface Projection
{
	public function project(DomainEvents $event_stream);
}