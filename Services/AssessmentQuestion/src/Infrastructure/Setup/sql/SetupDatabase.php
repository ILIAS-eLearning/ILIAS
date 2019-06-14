<?php
namespace ILIAS\AssessmentQuestion\Infrastructure\Setup\sql;
use  ILIAS\AssessmentQuestion\Infrastructure\Persistence\ilDB\ilDBQuestionStoredEvent;

/**
 * Class SetupDatabase
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 */
class SetupDatabase {
	public function __contstruct() {

	}

	public function run() {
		ilDBQuestionStoredEvent::updateDB();
	}
}
