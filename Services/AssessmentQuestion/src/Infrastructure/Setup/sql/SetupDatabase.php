<?php

namespace ILIAS\AssessmentQuestion\Infrastructure\Setup\sql;

use ilCtrlStructureReader;
use ILIAS\AssessmentQuestion\Infrastructure\Persistence\EventStore\QuestionEventStoreAr;
use ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection\QuestionListItemAr;
use ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection\QuestionAr;

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

        $ilCtrlStructureReader = new ilCtrlStructureReader($DIC->clientIni());
        $ilCtrlStructureReader->readStructure(true);

        $DIC->database()->dropTable(QuestionEventStoreAr::STORAGE_NAME, false);
        $DIC->database()->dropTable(QuestionListItemAr::STORAGE_NAME, false);
        $DIC->database()->dropTable(QuestionAr::STORAGE_NAME, false);

        $DIC->database()->manipulateF(
            "DELETE FROM page_object WHERE parent_type = %s",
            ['text'], ['asq']
        );
        
        QuestionEventStoreAr::updateDB();
	    QuestionListItemAr::updateDB();
	    QuestionAr::updateDB();
        
        echo "Setup wurde durchgefüht, CtrlStruktur neu geladen, Datentabellen wurden installiert / aktualisiert.<br><br>";
        echo "Es müsste nun neben dem Setup / Resetup ASQ ein neuer Tab 'asqDebugGUI' angezeigt werden<br><br>";

        echo "<a href='../../../../../'>zurück zu ILIAS</a>";

	}
}