<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestSkillPointAccountTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSkillPointAccountTest extends ilTestBaseTestCase
{
    private ilTestSkillPointAccount $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestSkillPointAccount();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestSkillPointAccount::class, $this->testObj);
    }

    public function testAddBooking(): void
    {
        $this->testObj->addBooking(80, 20);
        $this->assertEquals(80, $this->testObj->getTotalMaxSkillPoints());
        $this->assertEquals(20, $this->testObj->getTotalReachedSkillPoints());

        $this->testObj->addBooking(50, 10);
        $this->assertEquals(130, $this->testObj->getTotalMaxSkillPoints());
        $this->assertEquals(30, $this->testObj->getTotalReachedSkillPoints());
    }

    public function testGetTotalReachedSkillPercent(): void
    {
        $this->testObj->addBooking(80, 20);
        $this->testObj->addBooking(20, 30);
        $this->assertEquals(50, $this->testObj->getTotalReachedSkillPercent());
    }
}
