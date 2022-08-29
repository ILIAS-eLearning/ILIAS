<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestPersonalSkillsGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestPersonalSkillsGUITest extends ilTestBaseTestCase
{
    private ilTestPersonalSkillsGUI $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestPersonalSkillsGUI(
            $this->createMock(ilLanguage::class),
            0
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestPersonalSkillsGUI::class, $this->testObj);
    }

    public function testAvailableSkills(): void
    {
        $expected = [
            "test123" => "test12",
            "2" => 21,
        ];
        $this->testObj->setAvailableSkills($expected);
        $this->assertEquals($expected, $this->testObj->getAvailableSkills());
    }

    public function testSelectedSkillProfile(): void
    {
        $this->testObj->setSelectedSkillProfile("testString");
        $this->assertEquals("testString", $this->testObj->getSelectedSkillProfile());
    }

    public function testReachedSkillLevels(): void
    {
        $expected = [
            "test123" => "test12",
            "2" => 21,
        ];
        $this->testObj->setReachedSkillLevels($expected);
        $this->assertEquals($expected, $this->testObj->getReachedSkillLevels());
    }

    public function testUsrId(): void
    {
        $this->testObj->setUsrId(212);
        $this->assertEquals(212, $this->testObj->getUsrId());
    }
}
