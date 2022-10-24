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
