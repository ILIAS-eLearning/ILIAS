<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestVirtualSequenceTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestVirtualSequenceTest extends ilTestBaseTestCase
{
    private ilTestVirtualSequence $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestVirtualSequence(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilObjTest::class),
            $this->createMock(ilTestSequenceFactory::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestVirtualSequence::class, $this->testObj);
    }

    public function testActiveId(): void
    {
        $this->testObj->setActiveId(12);
        $this->assertEquals(12, $this->testObj->getActiveId());
    }
}
