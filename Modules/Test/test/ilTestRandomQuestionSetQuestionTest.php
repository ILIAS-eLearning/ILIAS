<?php

declare(strict_types=1);

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
        $this->testObj->setQuestionId(125);
        $this->assertEquals(125, $this->testObj->getQuestionId());
    }

    public function testSequencePosition(): void
    {
        $this->testObj->setSequencePosition(125);
        $this->assertEquals(125, $this->testObj->getSequencePosition());
    }

    public function testSourcePoolDefinitionId(): void
    {
        $this->testObj->setSourcePoolDefinitionId(125);
        $this->assertEquals(125, $this->testObj->getSourcePoolDefinitionId());
    }
}
