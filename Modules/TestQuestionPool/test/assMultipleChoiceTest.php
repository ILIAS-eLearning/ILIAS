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
class assMultipleChoiceTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    protected function setUp() : void
    {
        parent::setUp();
        $this->setGlobalVariable('ilias', $this->getIliasMock());
        $this->setGlobalVariable('tpl', $this->getGlobalTemplateMock());
    }

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
