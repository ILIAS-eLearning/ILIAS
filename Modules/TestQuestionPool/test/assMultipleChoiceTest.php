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
class assMultipleChoiceTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setGlobalVariable('ilias', $this->getIliasMock());
        $this->setGlobalVariable('tpl', $this->getGlobalTemplateMock());
    }

    /* Removed by @kergomard 17 NOV 2022, we should introduce this again
    public function test_isComplete_shouldReturnTrue(): void
    {
        $obj = new assMultipleChoice();
        $this->assertEquals(false, $obj->isComplete());
        $obj->setTitle('Tilte');
        $obj->setAuthor('Me or another');
        $obj->setQuestion('My great Question.');
        $obj->addAnswer('Super simple single Choice', 1);

        $this->assertEquals(true, $obj->isComplete());
    } */

    public function test_getThumbPrefix_shouldReturnString(): void
    {
        $obj = new assMultipleChoice();
        $this->assertEquals('thumb.', $obj->getThumbPrefix());
    }

    public function test_setOutputType_shouldReturngetOutputType(): void
    {
        $obj = new assMultipleChoice();
        $obj->setOutputType(0);
        $this->assertEquals(0, $obj->getOutputType());
    }

    /* Removed by @kergomard 17 NOV 2022, we should introduce this again
    public function test_getAnswerCount_shouldReturnCount(): void
    {
        $obj = new assMultipleChoice();
        $this->assertEquals(0, $obj->getAnswerCount());
        $obj->addAnswer('Points for checked', 1, 0, 0);
        $obj->addAnswer('Points for unchecked', 0, 1, 1);
        $this->assertEquals(2, $obj->getAnswerCount());
        $obj->deleteAnswer(0);
        $this->assertEquals(1, $obj->getAnswerCount());
    }

    public function test_flushAnswers_shouldClearAnswers(): void
    {
        $obj = new assMultipleChoice();
        $obj->addAnswer('1', 1, 0, 0);
        $obj->addAnswer('1', 1, 0, 1);
        $this->assertEquals(2, $obj->getAnswerCount());
        $obj->flushAnswers();
        $this->assertEquals(0, $obj->getAnswerCount());
    } */

    public function test_getQuestionType_shouldReturnQuestionType(): void
    {
        $obj = new assMultipleChoice();
        $this->assertEquals('assMultipleChoice', $obj->getQuestionType());
    }

    public function test_getAdditionalTableName_shouldReturnAdditionalTableName(): void
    {
        $obj = new assMultipleChoice();
        $this->assertEquals('qpl_qst_mc', $obj->getAdditionalTableName());
    }

    public function test_getAnswerTableName_shouldReturnAnswerTableName(): void
    {
        $obj = new assMultipleChoice();
        $this->assertEquals('qpl_a_mc', $obj->getAnswerTableName());
    }

    /* Removed by @kergomard 17 NOV 2022, we should introduce this again
    public function test_getMaximumPoints_shouldReturnAnswerTableName(): void
    {
        $obj = new assMultipleChoice();
        $obj->addAnswer('Points for checked', 1, 0, 0);
        $obj->addAnswer('Points for checked', 1, 0, 1);
        $this->assertEquals(2, $obj->getMaximumPoints());
    }
    public function test_getMaximumPointsIfMoreForUnchecked_shouldReturnAnswerTableName(): void
    {
        $obj = new assMultipleChoice();
        $obj->addAnswer('Points for unchecked', 0, 1, 0);
        $obj->addAnswer('Points for unchecked', 0, 1, 1);
        $this->assertEquals(2, $obj->getMaximumPoints());
    }
    public function test_getMaximumPointsMixed_shouldReturnAnswerTableName(): void
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
    } */
}
