<?php

namespace ILIAS\AssessmentQuestion\AuthoringInfrastructure\Persistence\ilDB;

use ActiveRecord;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Event\QuestionCreatedEvent;
use ILIAS\Data\Domain\AbstractEventStore;
use ILIAS\Data\Domain\StoredEvent;

class ilDBQuestionEventStore extends AbstractEventStore {

private $eventStore;
private $projector;
public function __construct($eventStore, $projector) {
	$this->eventStore = $eventStore;
	$this->projector = $projector;
}
public function save(QuestionCreatedEvent $question) {
	$events = $question->recordedEvents();
	$this->eventStore->append(new EventStream($question->id(), $events));
	$question->clearEvents();
	$this->projector->project($events);
}




protected function getEventToStore(int $aggregate_id, string $event_name, \DateTime $occured_on, int $initiating_user_id, string $event_body) {
	//TODO
}
}