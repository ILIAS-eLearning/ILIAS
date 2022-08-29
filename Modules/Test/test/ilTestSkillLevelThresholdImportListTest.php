<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestSkillLevelThresholdImportListTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSkillLevelThresholdImportListTest extends ilTestBaseTestCase
{
    private ilTestSkillLevelThresholdImportList $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestSkillLevelThresholdImportList();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestSkillLevelThresholdImportList::class, $this->testObj);
    }

    public function testAddOriginalSkillTitle(): void
    {
        $this->testObj->addOriginalSkillTitle(17, 15, "Test");

        $reflProp = new ReflectionProperty($this->testObj, "originalSkillTitles");
        $reflProp->setAccessible(true);
        $value = $reflProp->getValue($this->testObj);

        $this->assertEquals(["17:15" => "Test"], $value);
    }

    public function testAddOriginalSkillPath(): void
    {
        $this->testObj->addOriginalSkillPath(17, 15, "test/path");

        $reflProp = new ReflectionProperty($this->testObj, "originalSkillPaths");
        $reflProp->setAccessible(true);
        $value = $reflProp->getValue($this->testObj);

        $this->assertEquals(["17:15" => "test/path"], $value);
    }

    public function testAddSkillLevelThreshold(): void
    {
        $testSkillLevelThresholdImport = new ilTestSkillLevelThresholdImport();
        $this->testObj->addSkillLevelThreshold($testSkillLevelThresholdImport);

        $reflProp = new ReflectionProperty($this->testObj, "importedSkillLevelThresholds");
        $reflProp->setAccessible(true);
        $value = $reflProp->getValue($this->testObj);

        $this->assertEquals([$testSkillLevelThresholdImport], $value);
    }

    public function testCurrent(): void
    {
        $testSkillLevelThresholdImport = new ilTestSkillLevelThresholdImport();
        $this->testObj->addSkillLevelThreshold($testSkillLevelThresholdImport);

        $this->assertEquals($testSkillLevelThresholdImport, $this->testObj->current());
    }

    public function testNext(): void
    {
        $testSkillLevelThresholdImport1 = new ilTestSkillLevelThresholdImport();
        $testSkillLevelThresholdImport2 = new ilTestSkillLevelThresholdImport();

        $this->testObj->addSkillLevelThreshold($testSkillLevelThresholdImport1);
        $this->testObj->addSkillLevelThreshold($testSkillLevelThresholdImport2);

        $this->testObj->next();
        $this->assertEquals($testSkillLevelThresholdImport2, $this->testObj->current());
    }

    public function testKey(): void
    {
        $testSkillLevelThresholdImport1 = new ilTestSkillLevelThresholdImport();
        $testSkillLevelThresholdImport2 = new ilTestSkillLevelThresholdImport();

        $this->testObj->addSkillLevelThreshold($testSkillLevelThresholdImport1);
        $this->testObj->addSkillLevelThreshold($testSkillLevelThresholdImport2);

        $this->testObj->next();
        $this->assertEquals(1, $this->testObj->key());
    }

    public function testValid(): void
    {
        $this->assertFalse($this->testObj->valid());
        $testSkillLevelThresholdImport1 = new ilTestSkillLevelThresholdImport();
        $testSkillLevelThresholdImport2 = new ilTestSkillLevelThresholdImport();

        $this->testObj->addSkillLevelThreshold($testSkillLevelThresholdImport1);
        $this->testObj->addSkillLevelThreshold($testSkillLevelThresholdImport2);

        $this->assertTrue($this->testObj->valid());
    }

    public function testRewind(): void
    {
        $testSkillLevelThresholdImport1 = new ilTestSkillLevelThresholdImport();
        $testSkillLevelThresholdImport2 = new ilTestSkillLevelThresholdImport();

        $this->testObj->addSkillLevelThreshold($testSkillLevelThresholdImport1);
        $this->testObj->addSkillLevelThreshold($testSkillLevelThresholdImport2);

        $this->testObj->next();
        $this->testObj->next();
        $this->testObj->rewind();
        $this->assertEquals($testSkillLevelThresholdImport1, $this->testObj->current());
    }
}
