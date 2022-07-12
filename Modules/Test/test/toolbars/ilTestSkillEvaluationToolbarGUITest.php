<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestSkillEvaluationToolbarGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSkillEvaluationToolbarGUITest extends ilTestBaseTestCase
{
    private ilTestSkillEvaluationToolbarGUI $toolbarGUI;

    protected function setUp() : void
    {
        parent::setUp();

        $ctrl_mock = $this->createMock(ilCtrl::class);
        $lng_mock = $this->createMock(ilLanguage::class);
        $this->setGlobalVariable("lng", $lng_mock);

        $parentGui_mock = $this->createMock(ilTestSkillEvaluationGUI::class);
        $this->toolbarGUI = new ilTestSkillEvaluationToolbarGUI(
            $ctrl_mock,
            $lng_mock,
            $parentGui_mock,
            ilTestSkillEvaluationGUI::CMD_SHOW
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestSkillEvaluationToolbarGUI::class, $this->toolbarGUI);
    }

    public function testAvailableSkillProfiles() : void
    {
        $expected = ["test1", "test2", "test3"];

        $this->toolbarGUI->setAvailableSkillProfiles($expected);

        $this->assertEquals($expected, $this->toolbarGUI->getAvailableSkillProfiles());
    }

    public function testNoSkillProfileOptionEnabled() : void
    {
        $this->toolbarGUI->setNoSkillProfileOptionEnabled(true);
        $this->assertTrue($this->toolbarGUI->isNoSkillProfileOptionEnabled());

        $this->toolbarGUI->setNoSkillProfileOptionEnabled(false);
        $this->assertFalse($this->toolbarGUI->isNoSkillProfileOptionEnabled());
    }

    public function testSelectedEvaluationMode() : void
    {
        $this->toolbarGUI->setSelectedEvaluationMode("testString");
        $this->assertEquals("testString", $this->toolbarGUI->getSelectedEvaluationMode());
    }

    public function testFetchSkillProfileParam() : void
    {
        $result = ilTestSkillEvaluationToolbarGUI::fetchSkillProfileParam(
            [ilTestSkillEvaluationToolbarGUI::SKILL_PROFILE_PARAM => "102"]
        );

        $this->assertEquals(102, $result);

        $result = ilTestSkillEvaluationToolbarGUI::fetchSkillProfileParam(
            ["randomKey" => "102"]
        );

        $this->assertEquals(0, $result);
    }
}
