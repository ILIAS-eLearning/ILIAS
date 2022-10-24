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
