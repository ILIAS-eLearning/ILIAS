<?php

declare(strict_types=1);

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
