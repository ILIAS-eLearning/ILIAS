<?php

namespace ILIAS\AssessmentQuestion\Infrastructure\Setup\sql;

use ilCtrlStructureReader;
use ILIAS\AssessmentQuestion\Infrastructure\Persistence\EventStore\QuestionEventStoreAr;
use ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection\AnswerOptionImageAr;
use ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection\AnswerOptionTextAr;
use ILIAS\AssessmentQuestion\Infrastructure\Persistence\EventStore\questionEventStore;
use ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection\QuestionListItem;

/**
 * Class SetupDatabase
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 */
class SetupDatabase {
	public function __contstruct() {

	}

	public function run():void {
	    global $DIC;

        /*$ilCtrlStructureReader = new ilCtrlStructureReader($DIC->clientIni());
        $ilCtrlStructureReader->readStructure(true);*/

        $DIC->database()->dropTable(QuestionEventStoreAr::STORAGE_NAME);
        $DIC->database()->dropTable(QuestionListItem::STORAGE_NAME);
        $DIC->database()->dropTable(AnswerOptionImageAr::STORAGE_NAME);
        $DIC->database()->dropTable(AnswerOptionTextAr::STORAGE_NAME);

        QuestionEventStoreAr::updateDB();
		QuestionListItem::updateDB();
        AnswerOptionImageAr::updateDB();
        AnswerOptionTextAr::updateDB();

        echo "Setup wurde durchgefüht, CtrlStruktur neu geladen, Datentabellen wurden installiert / aktualisiert.<br><br>";
        echo "Es müsste nun neben dem Setup / Resetup ASQ ein neuer Tab 'asqDebugGUI' angezeigt werden<br><br>";

        echo "<a href='/'>zurück zu ILIAS</a>";

	}
}