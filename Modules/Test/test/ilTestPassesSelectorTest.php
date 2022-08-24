<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestPassesSelectorTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestPassesSelectorTest extends ilTestBaseTestCase
{
    private ilTestPassesSelector $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestPassesSelector(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilObjTest::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestPassesSelector::class, $this->testObj);
    }

    public function testActiveId(): void
    {
        $this->testObj->setActiveId(125);
        $this->assertEquals(125, $this->testObj->getActiveId());
    }

    public function testLastFinishedPass(): void
    {
        $this->testObj->setLastFinishedPass(125);
        $this->assertEquals(125, $this->testObj->getLastFinishedPass());
    }
}
