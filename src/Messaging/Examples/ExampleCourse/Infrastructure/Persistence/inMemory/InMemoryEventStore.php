<?php
namespace ILIAS\Messaging\Example\ExampleCourse\Infrastructure\Persistence\InMemory;

class InMemoryEventStore implements EventStore {

private $events = [];


public function commit(DomainEvents $events) {
foreach ($events as $event) {
$this->events[] = $event;
}
}


public function getAggregateHistoryFor(IdentifiesAggregate $id) {
return new AggregateHistory($id, array_filter($this->events, function (DomainEvent $event) use ($id) {
return $event->getAggregateId()->equals($id);
}));
}
}