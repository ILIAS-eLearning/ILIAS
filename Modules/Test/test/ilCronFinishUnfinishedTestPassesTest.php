<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCronFinishUnfinishedTestPassesTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilCronFinishUnfinishedTestPassesTest extends ilTestBaseTestCase
{
    private ilCronFinishUnfinishedTestPasses $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilObjDataCache();
        $this->addGlobal_lng();
        $this->addGlobal_ilDB();

        $this->testObj = new ilCronFinishUnfinishedTestPasses();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilCronFinishUnfinishedTestPasses::class, $this->testObj);
    }

    public function testGetId(): void
    {
        $this->assertEquals("finish_unfinished_passes", $this->testObj->getId());
    }

    public function testGetTitle(): void
    {
        $lng_mock = $this->createMock(ilLanguage::class);
        $lng_mock
            ->expects($this->any())
            ->method("txt")
            ->with("finish_unfinished_passes")
            ->willReturn("testString");

        $this->setGlobalVariable("lng", $lng_mock);

        $this->assertEquals("testString", $this->testObj->getTitle());
    }

    public function testGetDescription(): void
    {
        $lng_mock = $this->createMock(ilLanguage::class);
        $lng_mock
            ->expects($this->any())
            ->method("txt")
            ->with("finish_unfinished_passes_desc")
            ->willReturn("testString");

        $this->setGlobalVariable("lng", $lng_mock);

        $this->assertEquals("testString", $this->testObj->getDescription());
    }

    public function testGetDefaultScheduleType(): void
    {
        $this->assertEquals(
            ilCronFinishUnfinishedTestPasses::SCHEDULE_TYPE_DAILY,
            $this->testObj->getDefaultScheduleType()
        );
    }

    public function testHasAutoActivation(): void
    {
        $this->assertFalse($this->testObj->hasAutoActivation());
    }

    public function testHasFlexibleSchedule(): void
    {
        $this->assertTrue($this->testObj->hasFlexibleSchedule());
    }

    public function testHasCustomSettings(): void
    {
        $this->assertTrue($this->testObj->hasCustomSettings());
    }

    public function testRun(): void
    {
        $this->assertInstanceOf(ilCronJobResult::class, $this->testObj->run());
    }
}
