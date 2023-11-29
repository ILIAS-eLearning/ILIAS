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
        $importSkillBaseId = 12;
        $this->testObj->setImportSkillBaseId($importSkillBaseId);
        $this->assertEquals($importSkillBaseId, $this->testObj->getImportSkillBaseId());
    }

    public function testImportSkillTrefId(): void
    {
        $importSkillTrefId = 12;
        $this->testObj->setImportSkillTrefId($importSkillTrefId);
        $this->assertEquals($importSkillTrefId, $this->testObj->getImportSkillTrefId());
    }

    public function testImportLevelId(): void
    {
        $importLevelId = 12;
        $this->testObj->setImportLevelId($importLevelId);
        $this->assertEquals($importLevelId, $this->testObj->getImportLevelId());
    }

    public function testOrderIndex(): void
    {
        $orderIndex = 12;
        $this->testObj->setOrderIndex($orderIndex);
        $this->assertEquals($orderIndex, $this->testObj->getOrderIndex());
    }

    public function testThreshold(): void
    {
        $threshold = 12;
        $this->testObj->setThreshold($threshold);
        $this->assertEquals($threshold, $this->testObj->getThreshold());
    }

    public function testOriginalLevelTitle(): void
    {
        $originalLevelTitle = "test";
        $this->testObj->setOriginalLevelTitle($originalLevelTitle);
        $this->assertEquals($originalLevelTitle, $this->testObj->getOriginalLevelTitle());
    }

    public function testOriginalLevelDescription(): void
    {
        $originalLevelDescription = "test";
        $this->testObj->setOriginalLevelDescription($originalLevelDescription);
        $this->assertEquals($originalLevelDescription, $this->testObj->getOriginalLevelDescription());
    }
}
