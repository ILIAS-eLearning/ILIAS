<?php
namespace ILIAS\AssessmentQuestion\AuthoringInfrastructure\Setup\sql;
use  ILIAS\AssessmentQuestion\AuthoringInfrastructure\Persistence\ilDB\ilDBQuestionStoredEvent;

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
	}
}
