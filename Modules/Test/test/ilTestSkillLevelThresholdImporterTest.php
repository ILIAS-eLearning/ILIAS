<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestSkillLevelThresholdImporterTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSkillLevelThresholdImporterTest extends ilTestBaseTestCase
{
    private ilTestSkillLevelThresholdImporter $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestSkillLevelThresholdImporter();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestSkillLevelThresholdImporter::class, $this->testObj);
    }

    public function testTargetTestId(): void
    {
        $this->testObj->setTargetTestId(12);
        $this->assertEquals(12, $this->testObj->getTargetTestId());
    }

    public function testImportInstallationId(): void
    {
        $this->testObj->setImportInstallationId(12);
        $this->assertEquals(12, $this->testObj->getImportInstallationId());
    }

    public function testImportMappingRegistry(): void
    {
        $mock = $this->createMock(ilImportMapping::class);
        $this->testObj->setImportMappingRegistry($mock);
        $this->assertEquals($mock, $this->testObj->getImportMappingRegistry());
    }

    public function testImportedQuestionSkillAssignmentList(): void
    {
        $mock = $this->createMock(ilAssQuestionSkillAssignmentList::class);
        $this->testObj->setImportedQuestionSkillAssignmentList($mock);
        $this->assertEquals($mock, $this->testObj->getImportedQuestionSkillAssignmentList());
    }

    public function testImportThresholdList(): void
    {
        $mock = $this->createMock(ilTestSkillLevelThresholdImportList::class);
        $this->testObj->setImportThresholdList($mock);
        $this->assertEquals($mock, $this->testObj->getImportThresholdList());
    }

    public function testFailedThresholdImportSkillList(): void
    {
        $mock = $this->createMock(ilAssQuestionAssignedSkillList::class);
        $this->testObj->setFailedThresholdImportSkillList($mock);
        $this->assertEquals($mock, $this->testObj->getFailedThresholdImportSkillList());
    }
}
