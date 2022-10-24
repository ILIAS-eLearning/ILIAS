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
