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
        $testId = 12;
        $this->testObj->setTestId($testId);
        $this->assertEquals($testId, $this->testObj->getTestId());
    }

    public function testSkillBaseId(): void
    {
        $skillBaseId = 12;
        $this->testObj->setSkillBaseId($skillBaseId);
        $this->assertEquals($skillBaseId, $this->testObj->getSkillBaseId());
    }

    public function testSkillTrefId(): void
    {
        $skillTrefId = 12;
        $this->testObj->setSkillTrefId($skillTrefId);
        $this->assertEquals($skillTrefId, $this->testObj->getSkillTrefId());
    }

    public function testSkillLevelId(): void
    {
        $skillLevelId = 12;
        $this->testObj->setSkillLevelId($skillLevelId);
        $this->assertEquals($skillLevelId, $this->testObj->getSkillLevelId());
    }

    public function testThreshold(): void
    {
        $threshold = 12;
        $this->testObj->setThreshold($threshold);
        $this->assertEquals($threshold, $this->testObj->getThreshold());
    }
}
