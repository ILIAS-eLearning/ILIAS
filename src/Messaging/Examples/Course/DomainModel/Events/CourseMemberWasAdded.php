<?php

class CourseMemberWasAdded implements DomainEvent
{
private $course_id;
private $usr_id;

public function __construct($aggregateId, $usr_id)
{
$this->course_id = $aggregateId;
$this->usr_id = $usr_id;
}
/**
* The Aggregate this event belongs to.
* @return IdentifiesAggregate
*/
public function getAggregateId():IdentifiesAggregate
{
return CourseId::fromString($this->course_id);
}
public function getUsrId() {
return $this->usr_id;
}
}