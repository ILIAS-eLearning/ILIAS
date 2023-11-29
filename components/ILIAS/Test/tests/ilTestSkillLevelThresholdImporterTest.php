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
 * Class ilTestSkillLevelThresholdImporterTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSkillLevelThresholdImporterTest extends ilTestBaseTestCase
{
    private ilTestSkillLevelThresholdImporter $testObj;

    protected function setUp(): void
    {
        global $DIC;
        parent::setUp();

        $this->testObj = new ilTestSkillLevelThresholdImporter($DIC['ilDB']);
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestSkillLevelThresholdImporter::class, $this->testObj);
    }

    public function testTargetTestId(): void
    {
        $targetTestId = 12;
        $this->testObj->setTargetTestId($targetTestId);
        $this->assertEquals($targetTestId, $this->testObj->getTargetTestId());
    }

    public function testImportInstallationId(): void
    {
        $importInstallationId = 12;
        $this->testObj->setImportInstallationId($importInstallationId);
        $this->assertEquals($importInstallationId, $this->testObj->getImportInstallationId());
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
