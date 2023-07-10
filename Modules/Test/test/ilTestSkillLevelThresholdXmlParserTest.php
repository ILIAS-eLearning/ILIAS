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
 * Class ilTestSkillLevelThresholdXmlParserTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSkillLevelThresholdXmlParserTest extends ilTestBaseTestCase
{
    private ilTestSkillLevelThresholdXmlParser $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestSkillLevelThresholdXmlParser();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestSkillLevelThresholdXmlParser::class, $this->testObj);
    }

    public function testParsingActive(): void
    {
        $this->testObj->setParsingActive(false);
        $this->assertFalse($this->testObj->isParsingActive());

        $this->testObj->setParsingActive(true);
        $this->assertTrue($this->testObj->isParsingActive());
    }

    public function testInitSkillLevelThresholdImportList(): void
    {
        $this->addGlobal_ilDB();
        $this->testObj->initSkillLevelThresholdImportList();
        $this->assertInstanceOf(
            ilTestSkillLevelThresholdImportList::class,
            $this->testObj->getSkillLevelThresholdImportList()
        );
    }

    public function testCurSkillBaseId(): void
    {
        $this->testObj->setCurSkillBaseId(12);
        $this->assertEquals(12, $this->testObj->getCurSkillBaseId());
    }

    public function testCurSkillTrefId(): void
    {
        $this->testObj->setCurSkillTrefId(12);
        $this->assertEquals(12, $this->testObj->getCurSkillTrefId());
    }

    public function testCurSkillLevelThreshold(): void
    {
        $mock = $this->createMock(ilTestSkillLevelThresholdImport::class);
        $this->testObj->setCurSkillLevelThreshold($mock);
        $this->assertEquals($mock, $this->testObj->getCurSkillLevelThreshold());
    }
}
