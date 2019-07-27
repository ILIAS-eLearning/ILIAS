<?php

namespace ILIAS\AssessmentQuestion\Authoring\Infrastructure\Persistence\ilDB;

use ILIAS\AssessmentQuestion\Common\Event\AbstractStoredEvent;

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
