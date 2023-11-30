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
        $id = 222;
        $this->testObj->setId($id);
        $this->assertEquals($id, $this->testObj->getId());
    }

    public function testTitle(): void
    {
        $title = 'Test';
        $this->testObj->setTitle($title);
        $this->assertEquals($title, $this->testObj->getTitle());
    }

    public function testPath(): void
    {
        $path = 'Test';
        $this->testObj->setPath($path);
        $this->assertEquals($path, $this->testObj->getPath());
    }

    public function testUnavailabilityStatus(): void
    {
        $unavailabilityStatus = 'Test';
        $this->testObj->setUnavailabilityStatus($unavailabilityStatus);
        $this->assertEquals($unavailabilityStatus, $this->testObj->getUnavailabilityStatus());
    }
}
