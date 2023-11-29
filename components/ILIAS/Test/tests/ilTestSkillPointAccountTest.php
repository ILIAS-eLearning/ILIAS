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
        $maxSkillPoints1 = 80;
        $reachedSkillPoints1 = 20;
        $this->testObj->addBooking($maxSkillPoints1, $reachedSkillPoints1);
        $this->assertEquals($maxSkillPoints1, $this->testObj->getTotalMaxSkillPoints());
        $this->assertEquals($reachedSkillPoints1, $this->testObj->getTotalReachedSkillPoints());

        $maxSkillPoints2 = 50;
        $reachedSkillPoints2 = 10;
        $this->testObj->addBooking($maxSkillPoints2, $reachedSkillPoints2);
        $this->assertEquals($maxSkillPoints1 + $maxSkillPoints2, $this->testObj->getTotalMaxSkillPoints());
        $this->assertEquals($reachedSkillPoints1 + $reachedSkillPoints2, $this->testObj->getTotalReachedSkillPoints());
    }

    public function testGetTotalReachedSkillPercent(): void
    {
        $maxSkillPoints1 = 80;
        $reachedSkillPoints1 = 20;
        $maxSkillPoints2 = 20;
        $reachedSkillPoints2 = 30;

        $this->testObj->addBooking($maxSkillPoints1, $reachedSkillPoints1);
        $this->testObj->addBooking($maxSkillPoints2, $reachedSkillPoints2);
        $this->assertEquals(($reachedSkillPoints1 + $reachedSkillPoints2) / ($maxSkillPoints1 + $maxSkillPoints2) * 100, $this->testObj->getTotalReachedSkillPercent());
    }
}
