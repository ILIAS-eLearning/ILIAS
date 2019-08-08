<?php

namespace ILIAS\AssessmentQuestion\Infrastructure\Setup\sql;

use ILIAS\AssessmentQuestion\Infrastructure\Persistence\ilDB\ilDBQuestionStoredEvent;
use ILIAS\AssessmentQuestion\Infrastructure\Persistence\ilDB\QuestionListItem;

/**
 * Class SetupDatabase
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 */
class SetupDatabase {
	public function __contstruct() {

	}

	public function run():void {
		ilDBQuestionStoredEvent::updateDB();
		QuestionListItem::updateDB();
	}
}