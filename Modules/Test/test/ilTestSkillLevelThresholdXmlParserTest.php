<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

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
