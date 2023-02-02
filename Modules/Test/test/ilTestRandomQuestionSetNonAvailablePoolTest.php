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
 * Class ilTestRandomQuestionSetNonAvailablePoolTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetNonAvailablePoolTest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetNonAvailablePool $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestRandomQuestionSetNonAvailablePool();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetNonAvailablePool::class, $this->testObj);
    }

    public function testId(): void
    {
        $this->testObj->setId(222);
        $this->assertEquals(222, $this->testObj->getId());
    }

    public function testTitle(): void
    {
        $this->testObj->setTitle("Test");
        $this->assertEquals("Test", $this->testObj->getTitle());
    }

    public function testPath(): void
    {
        $this->testObj->setPath("Test");
        $this->assertEquals("Test", $this->testObj->getPath());
    }

    public function testUnavailabilityStatus(): void
    {
        $this->testObj->setUnavailabilityStatus("Test");
        $this->assertEquals("Test", $this->testObj->getUnavailabilityStatus());
    }
}
