<?php

namespace ILIAS\AssessmentQuestion\AuthoringInfrastructure\Persistence\ilDB;
use ILIAS\Data\Domain\AbstractStoredEvent;

/**
 * Class ilDBEventStore
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 */
class ilDBQuestionStoredEvent extends AbstractStoredEvent {

	const STORAGE_NAME = "asq_qst_event_store";

	/**
	 * @return string
	 */
	static function returnDbTableName() {
		return self::STORAGE_NAME;
	}

}
