<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

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
