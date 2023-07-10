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
 * Class ilTestSkillLevelThresholdExporterTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSkillLevelThresholdExporterTest extends ilTestBaseTestCase
{
    private ilTestSkillLevelThresholdExporter $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestSkillLevelThresholdExporter();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestSkillLevelThresholdExporter::class, $this->testObj);
    }

    public function testXmlWriter(): void
    {
        $xmlWriter = new ilXmlWriter();
        $this->testObj->setXmlWriter($xmlWriter);
        $this->assertEquals($xmlWriter, $this->testObj->getXmlWriter());
    }

    public function testAssignmentList(): void
    {
        $mock = $this->createMock(ilAssQuestionSkillAssignmentList::class);
        $this->testObj->setAssignmentList($mock);
        $this->assertEquals($mock, $this->testObj->getAssignmentList());
    }

    public function testThresholdList(): void
    {
        $mock = $this->createMock(ilTestSkillLevelThresholdList::class);
        $this->testObj->setThresholdList($mock);
        $this->assertEquals($mock, $this->testObj->getThresholdList());
    }
}
