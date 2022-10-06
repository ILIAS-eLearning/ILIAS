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
 * Class ilTestSkillLevelThresholdImportTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSkillLevelThresholdImportTest extends ilTestBaseTestCase
{
    private ilTestSkillLevelThresholdImport $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestSkillLevelThresholdImport();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestSkillLevelThresholdImport::class, $this->testObj);
    }

    public function testImportSkillBaseId(): void
    {
        $this->testObj->setImportSkillBaseId(12);
        $this->assertEquals(12, $this->testObj->getImportSkillBaseId());
    }

    public function testImportSkillTrefId(): void
    {
        $this->testObj->setImportSkillTrefId(12);
        $this->assertEquals(12, $this->testObj->getImportSkillTrefId());
    }

    public function testImportLevelId(): void
    {
        $this->testObj->setImportLevelId(12);
        $this->assertEquals(12, $this->testObj->getImportLevelId());
    }

    public function testOrderIndex(): void
    {
        $this->testObj->setOrderIndex(12);
        $this->assertEquals(12, $this->testObj->getOrderIndex());
    }

    public function testThreshold(): void
    {
        $this->testObj->setThreshold(12);
        $this->assertEquals(12, $this->testObj->getThreshold());
    }

    public function testOriginalLevelTitle(): void
    {
        $this->testObj->setOriginalLevelTitle("test");
        $this->assertEquals("test", $this->testObj->getOriginalLevelTitle());
    }

    public function testOriginalLevelDescription(): void
    {
        $this->testObj->setOriginalLevelDescription("test");
        $this->assertEquals("test", $this->testObj->getOriginalLevelDescription());
    }
}
