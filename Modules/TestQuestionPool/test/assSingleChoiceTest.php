<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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

    protected function setUp(): void
    {
        parent::setUp();
        $this->setGlobalVariable('ilias', $this->getIliasMock());
        $this->setGlobalVariable('tpl', $this->getGlobalTemplateMock());
    }

    public function test_isComplete_shouldReturnTrue(): void
    {
        $obj = new assSingleChoice();
        $this->assertEquals(false, $obj->isComplete());
        $obj->setTitle('Tilte');
        $obj->setAuthor('Me or another');
        $obj->setQuestion('My great Question.');
        $obj->addAnswer('Super simple single Choice', 1);

        $this->assertEquals(true, $obj->isComplete());
    }

    public function test_getThumbPrefix_shouldReturnString(): void
    {
        $obj = new assSingleChoice();
        $this->assertEquals('thumb.', $obj->getThumbPrefix());
    }

    public function test_setOutputType_shouldReturngetOutputType(): void
    {
        $obj = new assSingleChoice();
        $obj->setOutputType(0);
        $this->assertEquals(0, $obj->getOutputType());
    }

    public function test_getAnswerCount_shouldReturnCount(): void
    {
        $obj = new assSingleChoice();
        $this->assertEquals(0, $obj->getAnswerCount());
        $obj->addAnswer('1', 1, 0);
        $obj->addAnswer('1', 1, 1);
        $this->assertEquals(2, $obj->getAnswerCount());
        $obj->deleteAnswer(0);
        $this->assertEquals(1, $obj->getAnswerCount());
    }

    public function test_flushAnswers_shouldClearAnswers(): void
    {
        $obj = new assSingleChoice();
        $obj->addAnswer('1', 1, 0);
        $obj->addAnswer('1', 1, 1);
        $this->assertEquals(2, $obj->getAnswerCount());
        $obj->flushAnswers();
        $this->assertEquals(0, $obj->getAnswerCount());
    }

    public function test_getQuestionType_shouldReturnQuestionType(): void
    {
        $obj = new assSingleChoice();
        $this->assertEquals('assSingleChoice', $obj->getQuestionType());
    }

    public function test_getAdditionalTableName_shouldReturnAdditionalTableName(): void
    {
        $obj = new assSingleChoice();
        $this->assertEquals('qpl_qst_sc', $obj->getAdditionalTableName());
    }

    public function test_getAnswerTableName_shouldReturnAnswerTableName(): void
    {
        $obj = new assSingleChoice();
        $this->assertEquals('qpl_a_sc', $obj->getAnswerTableName());
    }
}
