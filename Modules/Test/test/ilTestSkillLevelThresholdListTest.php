<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestSkillLevelThresholdListTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSkillLevelThresholdListTest extends ilTestBaseTestCase
{
    private ilTestSkillLevelThresholdList $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestSkillLevelThresholdList(
            $this->createMock(ilDBInterface::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestSkillLevelThresholdList::class, $this->testObj);
    }

    public function testTestId(): void
    {
        $this->testObj->setTestId(20);
        $this->assertEquals(20, $this->testObj->getTestId());
    }
}
