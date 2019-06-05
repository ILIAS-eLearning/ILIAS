<?php
class CourseWasCreated implements DomainEvent
{
private $course_id;
private $usr_id;

public function __construct($aggregateId, $title)
{
$this->course_id = $aggregateId;
$this->title = $title;
}
/**
* The Aggregate this event belongs to.
* @return IdentifiesAggregate
*/
public function getAggregateId():IdentifiesAggregate
{
return CourseId::fromString($this->course_id);
}
public function getTitle() {
return $this->title;
}
}
