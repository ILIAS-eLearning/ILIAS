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
 * Class ilTestReindexedSequencePositionMapTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestReindexedSequencePositionMapTest extends ilTestBaseTestCase
{
    private ilTestReindexedSequencePositionMap $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestReindexedSequencePositionMap();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestReindexedSequencePositionMap::class, $this->testObj);
    }

    public function testSequenceCanBeSetAndRetrieved(): void
    {
        $oldSequencePosition = 1;
        $newSequencePosition = 2;
        $this->testObj->addPositionMapping($oldSequencePosition, $newSequencePosition);
        $this->assertEquals($newSequencePosition, $this->testObj->getNewSequencePosition($oldSequencePosition));
    }

    public function testNullIsReturnedIfSequenceDoesNotExistInMap(): void
    {
        $this->assertNull($this->testObj->getNewSequencePosition(5));
    }
}
