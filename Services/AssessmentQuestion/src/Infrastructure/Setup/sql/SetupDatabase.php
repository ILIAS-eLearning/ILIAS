<?php

namespace ILIAS\AssessmentQuestion\Infrastructure\Setup\sql;

use ilCtrlStructureReader;
use ILIAS\AssessmentQuestion\Infrastructure\Persistence\EventStore\QuestionEventStoreAr;
use ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection\QuestionListItemAr;
use ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection\QuestionAr;
use ILIAS\Modules\Test\Result\TestResultAr;

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


        $DIC->database()->dropTable(QuestionEventStoreAr::STORAGE_NAME, false);
        $DIC->database()->dropTable(QuestionListItemAr::STORAGE_NAME, false);
        $DIC->database()->dropTable(QuestionAr::STORAGE_NAME, false);
        $DIC->database()->dropTable("asq_user_answer_scpre", false);
        $DIC->database()->dropTable("asq_user_test_score", false);


        
        QuestionEventStoreAr::updateDB();
	    QuestionListItemAr::updateDB();
	    QuestionAr::updateDB();
        TestResultAr::updateDB();




	    //Migration
        //Migrate Contentpage Definition (here for the implementation the migration)
        $DIC->database()->query("UPDATE copg_pobj_def SET parent_type = 'asqq' where component = 'Modules/TestQuestionPool' AND class_name = 'ilAssQuestionPage'");
        $DIC->database()->query("UPDATE copg_pobj_def SET component = 'Services/AssessmentQuestion',  class_name = '".addslashes('\ILIAS\AssessmentQuestion\UserInterface\Web\Page\AsqPageObject')."', directory = 'src/UserInterface/Web/Page' where parent_type = 'asqq'");


        $DIC->database()->query("UPDATE copg_pobj_def SET parent_type ='asqg' where component = 'Modules/TestQuestionPool' AND class_name = 'ilAssGenFeedbackPage'");
        $DIC->database()->query("UPDATE copg_pobj_def SET component = 'Services/AssessmentQuestion',  class_name = '".addslashes('\ILIAS\AssessmentQuestion\UserInterface\Web\Page\AsqPageObject')."', directory = 'src/UserInterface/Web/Page' where parent_type = 'asqg'");

        $DIC->database()->query("UPDATE page_object SET parent_type = 'asqq' where parent_type = 'qpl' and page_id >= 0");

        $DIC->database()->query("UPDATE copg_pobj_def SET parent_type ='asqa' where component = 'Modules/TestQuestionPool' AND class_name = 'ilAssSpecFeedbackPage'");
        $DIC->database()->query("UPDATE copg_pobj_def SET component = 'Services/AssessmentQuestion',  class_name = '".addslashes('\ILIAS\AssessmentQuestion\UserInterface\Web\Page\AsqPageObject')."', directory = 'src/UserInterface/Web/Page' where parent_type = 'asqa'");

        $DIC->database()->query("UPDATE page_object SET parent_type = 'asqa' where parent_type = 'qfbs' and page_id >= 0");



        $this->cleanupContentPages();

        ////////////////////////////////////////////////////
        /// Test Object Tables
        $this->changeTestObjectTables();
        $this->cleanTestObjectTables();
        ////////////////////////////////////////////////////

        echo "Setup wurde durchgefüht. Datentabellen wurden installiert / aktualisiert. ACHTUNG allenfalls muss vorher via setup/setup.php die Ctrl-Struktur neu geladen werden. In diesem Fall dieses Setup erneut ausführen.<br><br>";
        echo "Es müsste nun neben dem Setup / Resetup ASQ ein neuer Tab 'exAsqExamplesGUI' angezeigt werden<br><br>";

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

        // answer specific feedbacks
        // old
        $DIC->database()->manipulateF(
            "DELETE FROM page_object WHERE parent_type = %s",
            ['text'], ['qfbs']
        );
        // new
        $DIC->database()->manipulateF(
            "DELETE FROM page_object WHERE parent_type = %s",
            ['text'], ['asqa']
        );
    }

    protected function changeTestObjectTables()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        if( $DIC->database()->tableColumnExists('tst_test_question', 'question_uid') )
        {
            $DIC->database()->dropTableColumn('tst_test_question', 'question_uid');
        }

        if( !$DIC->database()->tableColumnExists('tst_test_question', 'question_uid') )
        {
            $DIC->database()->addTableColumn('tst_test_question', 'question_uid', array(
                'type' => 'text',
                'notnull' => false,
                'length' => 64,
                'default' => ''
            ));
        }

        if( !$DIC->database()->tableColumnExists('tst_test_question', 'revision_id') )
        {
            $DIC->database()->addTableColumn('tst_test_question', 'revision_id', array(
                'type' => 'text',
                'notnull' => false,
                'length' => 64,
                'default' => ''
            ));
        }
    }

    protected function cleanTestObjectTables()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $DIC->database()->manipulate("TRUNCATE TABLE tst_test_question");
    }
}