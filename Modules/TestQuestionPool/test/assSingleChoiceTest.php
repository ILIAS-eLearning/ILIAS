<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Unit tests for single choice questions
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
*
*
* @ingroup ServicesTree
*/
class assSingleChoiceTest extends PHPUnit_Framework_TestCase
{
    protected $backupGlobals = false;

    protected function setUp()
    {
        require_once './Modules/TestQuestionPool/classes/class.assSingleChoice.php';
        if (defined('ILIAS_PHPUNIT_CONTEXT')) {
            include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
            ilUnitUtil::performInitialisation();
        } else {
            chdir(dirname(__FILE__));
            chdir('../../../');
        }
        require_once './Services/Utilities/classes/class.ilUtil.php';
    }

    /**
    * Create a sample question and save it to the database
    *
    * @param integer $obj_id Object ID of the containing question pool object (optional)
    * @return integer ID of the newly created question
    */
    /*public static function createSampleQuestion($obj_id = null)
    {
        $obj_id = ($obj_id) ? $obj_id : 99999999;
        include_once './Modules/TestQuestionPool/classes/class.assSingleChoice.php';
        $sc = new assSingleChoice('unit test single choice question', 'unit test single choice question comment', 'Helmut Schottmüller', -1, '<p>is a <strong>unit test</strong> required?</p>');
        $sc->addAnswer(
            'Yes',
            1,
            0,
            1
        );
        $sc->addAnswer(
            'No',
            -1,
            0,
            2
        );
        $sc->setObjId($obj_id);
        $sc->saveToDb();
        return $sc->getId();
    }*/

    /**
     * Question creation test
     * @param
     * @return
     */
    /*public function t_e_stCreation()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        include_once './Modules/TestQuestionPool/classes/class.assSingleChoice.php';
        $insert_id = ilassSingleChoiceTest::createSampleQuestion();
        $this->assertGreaterThan(0, $insert_id);
        if ($insert_id > 0)
        {
            $sc = new assSingleChoice();
            $sc->loadFromDb($insert_id);
            $this->assertEquals($sc->getPoints(),1);
            $this->assertEquals($sc->getTitle(),"unit test single choice question");
            $this->assertEquals($sc->getComment(),"unit test single choice question comment");
            $this->assertEquals($sc->getAuthor(),"Helmut Schottmüller");
            $this->assertEquals($sc->getQuestion(),"<p>is a <strong>unit test</strong> required?</p>");
            $this->assertEquals(count($sc->getAnswers()), 2);
            $result = $sc->delete($insert_id);
            $this->assertEquals($result,true);
        }
    }
*/
    public function test_isComplete_shouldReturnTrue()
    {
        $obj = new assSingleChoice();
        $this->assertEquals(false, $obj->isComplete());
        $obj->setTitle('Tilte');
        $obj->setAuthor('Me or another');
        $obj->setQuestion('My great Question.');
        $obj->addAnswer('Super simple single Choice', 1);

        $this->assertEquals(true, $obj->isComplete());
    }

    public function test_getThumbPrefix_shouldReturnString()
    {
        $obj = new assSingleChoice();
        $this->assertEquals('thumb.', $obj->getThumbPrefix());
    }

    public function test_setOutputType_shouldReturngetOutputType()
    {
        $obj = new assSingleChoice();
        $obj->setOutputType(0);
        $this->assertEquals(0, $obj->getOutputType());
    }

    public function test_getAnswerCount_shouldReturnCount()
    {
        $obj = new assSingleChoice();
        $this->assertEquals(0, $obj->getAnswerCount());
        $obj->addAnswer('1', 1, 0);
        $obj->addAnswer('1', 1, 1);
        $this->assertEquals(2, $obj->getAnswerCount());
        $obj->deleteAnswer(0);
        $this->assertEquals(1, $obj->getAnswerCount());
    }

    public function test_flushAnswers_shouldClearAnswers()
    {
        $obj = new assSingleChoice();
        $obj->addAnswer('1', 1, 0);
        $obj->addAnswer('1', 1, 1);
        $this->assertEquals(2, $obj->getAnswerCount());
        $obj->flushAnswers();
        $this->assertEquals(0, $obj->getAnswerCount());
    }

    public function test_getQuestionType_shouldReturnQuestionType()
    {
        $obj = new assSingleChoice();
        $this->assertEquals('assSingleChoice', $obj->getQuestionType());
    }

    public function test_getAdditionalTableName_shouldReturnAdditionalTableName()
    {
        $obj = new assSingleChoice();
        $this->assertEquals('qpl_qst_sc', $obj->getAdditionalTableName());
    }

    public function test_getAnswerTableName_shouldReturnAnswerTableName()
    {
        $obj = new assSingleChoice();
        $this->assertEquals('qpl_a_sc', $obj->getAnswerTableName());
    }
}
