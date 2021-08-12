<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestPersonalSkillsGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestPersonalSkillsGUITest extends ilTestBaseTestCase
{
    private ilTestPersonalSkillsGUI $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestPersonalSkillsGUI(
            $this->createMock(ilLanguage::class),
            0
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestPersonalSkillsGUI::class, $this->testObj);
    }
}