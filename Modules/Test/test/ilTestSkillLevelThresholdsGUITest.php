<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestSkillLevelThresholdsGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSkillLevelThresholdsGUITest extends ilTestBaseTestCase
{
    private ilTestSkillLevelThresholdsGUI $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestSkillLevelThresholdsGUI(
            $this->createMock(ilCtrl::class),
            $this->createMock(ilGlobalPageTemplate::class),
            $this->createMock(ilLanguage::class),
            $this->createMock(ilDBInterface::class),
            0
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestSkillLevelThresholdsGUI::class, $this->testObj);
    }
}