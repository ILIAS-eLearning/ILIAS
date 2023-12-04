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

declare(strict_types=1);

/**
 * Class ilTestRandomQuestionSetQuestionTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetQuestionTest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetQuestion $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestRandomQuestionSetQuestion();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetQuestion::class, $this->testObj);
    }

    public function testQuestionId(): void
    {
        $questionId = 125;
        $this->testObj->setQuestionId($questionId);
        $this->assertEquals($questionId, $this->testObj->getQuestionId());
    }

    public function testSequencePosition(): void
    {
        $sequencePosition = 125;
        $this->testObj->setSequencePosition($sequencePosition);
        $this->assertEquals($sequencePosition, $this->testObj->getSequencePosition());
    }

    public function testSourcePoolDefinitionId(): void
    {
        $sourcePoolDefinitionId = 125;
        $this->testObj->setSourcePoolDefinitionId($sourcePoolDefinitionId);
        $this->assertEquals($sourcePoolDefinitionId, $this->testObj->getSourcePoolDefinitionId());
    }
}
