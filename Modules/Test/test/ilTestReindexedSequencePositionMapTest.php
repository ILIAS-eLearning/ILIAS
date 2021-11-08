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
}
