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


        
        QuestionEventStoreAr::updateDB();
	    QuestionListItemAr::updateDB();
	    QuestionAr::updateDB();

	    //Migration
        //Migrate Contentpage Definition (here for the implementation the migration)
        $DIC->database()->query("UPDATE copg_pobj_def SET parent_type = 'asqq' where component = 'Modules/TestQuestionPool' AND class_name = 'ilAssQuestionPage'");

        $DIC->database()->query("UPDATE copg_pobj_def SET component = 'Services/AssessmentQuestion',  class_name = '\\ILIAS\\AssessmentQuestion\\UserInterface\\Web\\Page\\Page', directory = 'src/UserInterface/Web/Page' where parent_type = 'asqq'");



        $DIC->database()->query("UPDATE copg_pobj_def SET parent_type ='asqg' where component = 'Modules/TestQuestionPool' AND class_name = 'ilAssGenFeedbackPage'");

        $DIC->database()->query("UPDATE copg_pobj_def SET component = 'Services/AssessmentQuestion',  class_name = '\\ILIAS\\AssessmentQuestion\\UserInterface\\Web\\Page\\Page', directory = 'src/UserInterface/Web/Page' where parent_type = 'asqg'");


        $DIC->database()->query("UPDATE page_object SET parent_type = 'asqq' where parent_type = 'qpl' and page_id >= 0");



        $this->cleanupContentPages();


        echo "Setup wurde durchgefüht, CtrlStruktur neu geladen, Datentabellen wurden installiert / aktualisiert.<br><br>";
        echo "Es müsste nun neben dem Setup / Resetup ASQ ein neuer Tab 'asqDebugGUI' angezeigt werden<br><br>";

        echo "<a href='../../../../../'>zurück zu ILIAS</a>";

	}

	protected function cleanupContentPages()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        // question pages
        // old
        $DIC->database()->manipulateF(
            "DELETE FROM page_object WHERE parent_type = %s",
            ['text'], ['qpl']
        );
        // new
        $DIC->database()->manipulateF(
            "DELETE FROM page_object WHERE parent_type = %s",
            ['text'], ['asq']
        );


        // generic (correct/wrong) feedback pages
        // old
        $DIC->database()->manipulateF(
            "DELETE FROM page_object WHERE parent_type = %s",
            ['text'], ['afbg']
        );
        //new
        $DIC->database()->manipulateF(
            "DELETE FROM page_object WHERE parent_type = %s",
            ['text'], ['asqq']
        );
    }
}