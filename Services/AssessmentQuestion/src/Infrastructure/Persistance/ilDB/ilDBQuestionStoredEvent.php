<?php

namespace ILIAS\AssessmentQuestion\Infrastructure\Persistence\ilDB;

use ILIAS\AssessmentQuestion\CQRS\Event\AbstractStoredEvent;

/**
 * Class ilDBQuestionStoredEvent
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
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
