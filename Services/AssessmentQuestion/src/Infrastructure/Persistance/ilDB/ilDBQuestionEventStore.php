<?php
namespace ILIAS\AssessmentQuestion\Infrastructure\Persistence\ilDB;

use ActiveRecord;
use \ILIAS\Data\Domain\AbstractEventStore;
use ILIAS\Data\Domain\StoredEvent;

class ilDBQuestionEventStore extends AbstractEventStore {

	protected function getStoredEvent() {
		return new ilDBQuestionStoredEvent();
	}


	protected function getEventToStore(int $aggregate_id, string $event_name, \DateTime $occured_on, int $initiating_user_id, string $event_body) {
		//TODO
	}
}