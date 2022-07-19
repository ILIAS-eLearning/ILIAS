<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestReindexedSequencePositionMapTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestReindexedSequencePositionMapTest extends ilTestBaseTestCase
{
    private ilTestReindexedSequencePositionMap $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestReindexedSequencePositionMap();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestReindexedSequencePositionMap::class, $this->testObj);
    }

    public function testSequenceCanBeSetAndRetrieved() : void
    {
        $this->testObj->addPositionMapping(1, 2);
        self::assertEquals(2, $this->testObj->getNewSequencePosition(1));
    }

    public function testNullIsReturnedIfSequenceDoesNotExistInMap() : void
    {
        self::assertNull($this->testObj->getNewSequencePosition(5));
    }
}
