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
class assSingleChoiceTest extends assBaseTestCase
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
