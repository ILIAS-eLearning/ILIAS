<?php
namespace  ILIAS\AssessmentQuestion\Domain\Question\Shared;
use ILIAS\Data\Domain\DomainEvents;

interface Projection
{
	public function project(DomainEvents $event_stream);
}