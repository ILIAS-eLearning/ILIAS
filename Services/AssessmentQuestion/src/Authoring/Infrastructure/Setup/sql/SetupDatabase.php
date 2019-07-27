<?php
namespace ILIAS\AssessmentQuestion\AuthoringInfrastructure\Setup\sql;
use ILIAS\AssessmentQuestion\Authoring\Infrastructure\Persistence\ilDB\ilDBQuestionStoredEvent;
use QuestionListItem;

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