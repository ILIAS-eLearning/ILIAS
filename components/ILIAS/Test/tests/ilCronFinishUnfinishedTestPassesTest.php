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

use ILIAS\Cron\Schedule\CronJobScheduleType;

/**
 * Class ilCronFinishUnfinishedTestPassesTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilCronFinishUnfinishedTestPassesTest extends ilTestBaseTestCase
{
    private ilCronFinishUnfinishedTestPasses $test_obj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilObjDataCache();
        $this->addGlobal_ilUser();
        $this->addGlobal_lng();
        $this->addGlobal_ilDB();

        $this->test_obj = new ilCronFinishUnfinishedTestPasses();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilCronFinishUnfinishedTestPasses::class, $this->test_obj);
    }

    public function testGetId(): void
    {
        $this->assertEquals('finish_unfinished_passes', $this->test_obj->getId());
    }

    public function testGetTitle(): void
    {
        $lng_mock = $this->createMock(ilLanguage::class);
        $lng_mock
            ->expects($this->any())
            ->method('txt')
            ->with('finish_unfinished_passes')
            ->willReturn('testString')
        ;

        $this->setGlobalVariable('lng', $lng_mock);
        $test_obj = new ilCronFinishUnfinishedTestPasses();

        $this->assertEquals('testString', $test_obj->getTitle());
    }

    public function testGetDescription(): void
    {
        $lng_mock = $this->createMock(ilLanguage::class);
        $lng_mock
            ->expects($this->any())
            ->method('txt')
            ->with('finish_unfinished_passes_desc')
            ->willReturn('testString')
        ;

        $this->setGlobalVariable('lng', $lng_mock);
        $test_obj = new ilCronFinishUnfinishedTestPasses();

        $this->assertEquals('testString', $test_obj->getDescription());
    }

    public function testGetDefaultScheduleType(): void
    {
        $this->assertEquals(CronJobScheduleType::SCHEDULE_TYPE_DAILY, $this->test_obj->getDefaultScheduleType());
    }

    public function testHasAutoActivation(): void
    {
        $this->assertFalse($this->test_obj->hasAutoActivation());
    }

    public function testHasFlexibleSchedule(): void
    {
        $this->assertTrue($this->test_obj->hasFlexibleSchedule());
    }

    public function testHasCustomSettings(): void
    {
        $this->assertTrue($this->test_obj->hasCustomSettings());
    }

    public function testRun(): void
    {
        $this->assertInstanceOf(ilCronJobResult::class, $this->test_obj->run());
    }
}
