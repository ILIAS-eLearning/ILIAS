<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestSkillLevelThresholdTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSkillLevelThresholdTest extends ilTestBaseTestCase
{
    private ilTestSkillLevelThreshold $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestSkillLevelThreshold($this->createMock(ilDBInterface::class));
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestSkillLevelThreshold::class, $this->testObj);
    }

    public function testTestId(): void
    {
        $this->testObj->setTestId(12);
        $this->assertEquals(12, $this->testObj->getTestId());
    }

    public function testSkillBaseId(): void
    {
        $this->testObj->setSkillBaseId(12);
        $this->assertEquals(12, $this->testObj->getSkillBaseId());
    }

    public function testSkillTrefId(): void
    {
        $this->testObj->setSkillTrefId(12);
        $this->assertEquals(12, $this->testObj->getSkillTrefId());
    }

    public function testSkillLevelId(): void
    {
        $this->testObj->setSkillLevelId(12);
        $this->assertEquals(12, $this->testObj->getSkillLevelId());
    }

    public function testThreshold(): void
    {
        $this->testObj->setThreshold(12);
        $this->assertEquals(12, $this->testObj->getThreshold());
    }
}
