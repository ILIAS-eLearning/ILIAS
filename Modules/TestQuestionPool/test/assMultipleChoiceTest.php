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
class assMultipleChoiceTest extends PHPUnit_Framework_TestCase
{
    protected $backupGlobals = false;

    protected function setUp()
    {
        require_once './Modules/TestQuestionPool/classes/class.assMultipleChoice.php';
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
    /*	public static function createSampleQuestion($obj_id = null)
        {
            $obj_id = ($obj_id) ? $obj_id : 99999999;
            include_once './Modules/TestQuestionPool/classes/class.assMultipleChoice.php';

            $mc = new assMultipleChoice('unit test multiple choice question', 'unit test multiple choice question comment', 'Helmut Schottmüller', -1, '<p><strong>unit tests</strong> are...</p>');
            $mc->addAnswer(
                'important',
                0.5,
                -0.5,
                1
            );
            $mc->addAnswer(
                'useless',
                -0.5,
                0.5,
                2
            );
            $mc->addAnswer(
                'stupid',
                -0.5,
                0.5,
                3
            );
            $mc->addAnswer(
                'cool',
                0.5,
                -0.5,
                4
            );
            $mc->setObjId($obj_id);
            $mc->saveToDb();
            return $mc->getId();
        }
    */
    /**
     * Question creation test
     * @param
     * @return
     */
    /*	public function t_e_stCreation()
        {
            global $DIC;
            $ilDB = $DIC['ilDB'];

            include_once './Modules/TestQuestionPool/classes/class.assMultipleChoice.php';
            $insert_id = self::createSampleQuestion(null);
            $this->assertGreaterThan(0, $insert_id);
            if ($insert_id > 0)
            {
                $mc = new assMultipleChoice();
                $mc->loadFromDb($insert_id);
                $this->assertEquals($mc->getPoints(),2);
                $this->assertEquals($mc->getTitle(),"unit test multiple choice question");
                $this->assertEquals($mc->getComment(),"unit test multiple choice question comment");
                $this->assertEquals($mc->getAuthor(),"Helmut Schottmüller");
                $this->assertEquals($mc->getQuestion(),"<p><strong>unit tests</strong> are...</p>");
                $this->assertEquals(count($mc->getAnswers()), 4);
                $result = $mc->delete($insert_id);
                $this->assertEquals($result,true);
            }
        }
    */
    public function test_isComplete_shouldReturnTrue()
    {
        $obj = new assMultipleChoice();
        $this->assertEquals(false, $obj->isComplete());
        $obj->setTitle('Tilte');
        $obj->setAuthor('Me or another');
        $obj->setQuestion('My great Question.');
        $obj->addAnswer('Super simple single Choice', 1);

        $this->assertEquals(true, $obj->isComplete());
    }
    
    public function test_getThumbPrefix_shouldReturnString()
    {
        $obj = new assMultipleChoice();
        $this->assertEquals('thumb.', $obj->getThumbPrefix());
    }

    public function test_setOutputType_shouldReturngetOutputType()
    {
        $obj = new assMultipleChoice();
        $obj->setOutputType(0);
        $this->assertEquals(0, $obj->getOutputType());
    }
    public function test_getAnswerCount_shouldReturnCount()
    {
        $obj = new assMultipleChoice();
        $this->assertEquals(0, $obj->getAnswerCount());
        $obj->addAnswer('Points for checked', 1, 0, 0);
        $obj->addAnswer('Points for unchecked', 0, 1, 1);
        $this->assertEquals(2, $obj->getAnswerCount());
        $obj->deleteAnswer(0);
        $this->assertEquals(1, $obj->getAnswerCount());
    }

    public function test_flushAnswers_shouldClearAnswers()
    {
        $obj = new assMultipleChoice();
        $obj->addAnswer('1', 1, 0, 0);
        $obj->addAnswer('1', 1, 0, 1);
        $this->assertEquals(2, $obj->getAnswerCount());
        $obj->flushAnswers();
        $this->assertEquals(0, $obj->getAnswerCount());
    }

    public function test_getQuestionType_shouldReturnQuestionType()
    {
        $obj = new assMultipleChoice();
        $this->assertEquals('assMultipleChoice', $obj->getQuestionType());
    }

    public function test_getAdditionalTableName_shouldReturnAdditionalTableName()
    {
        $obj = new assMultipleChoice();
        $this->assertEquals('qpl_qst_mc', $obj->getAdditionalTableName());
    }

    public function test_getAnswerTableName_shouldReturnAnswerTableName()
    {
        $obj = new assMultipleChoice();
        $this->assertEquals('qpl_a_mc', $obj->getAnswerTableName());
    }

    public function test_getMaximumPoints_shouldReturnAnswerTableName()
    {
        $obj = new assMultipleChoice();
        $obj->addAnswer('Points for checked', 1, 0, 0);
        $obj->addAnswer('Points for checked', 1, 0, 1);
        $this->assertEquals(2, $obj->getMaximumPoints());
    }
    public function test_getMaximumPointsIfMoreForUnchecked_shouldReturnAnswerTableName()
    {
        $obj = new assMultipleChoice();
        $obj->addAnswer('Points for unchecked', 0, 1, 0);
        $obj->addAnswer('Points for unchecked', 0, 1, 1);
        $this->assertEquals(2, $obj->getMaximumPoints());
    }
    public function test_getMaximumPointsMixed_shouldReturnAnswerTableName()
    {
        $obj = new assMultipleChoice();
        $obj->addAnswer('Points for unchecked', 0, 1, 0);
        $obj->addAnswer('Points for unchecked', 0, 1, 1);
        $this->assertEquals(2, $obj->getMaximumPoints());
        $obj->addAnswer('Points for checked', 1, 0, 2);
        $obj->addAnswer('Points for checked', 1, 0, 3);
        $this->assertEquals(4, $obj->getMaximumPoints());
        $obj->addAnswer('Points for checked', 1, 1, 4);
        $obj->addAnswer('Points for checked', 1, 1, 5);
        $this->assertEquals(6, $obj->getMaximumPoints());
    }
}
