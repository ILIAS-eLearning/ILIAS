<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestCorrectionsGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestCorrectionsGUITest extends ilTestBaseTestCase
{
    private ilTestCorrectionsGUI $testObj;

    protected function setUp() : void
    {
        global $DIC;

        parent::setUp();

        $this->addGlobal_ilAccess();

        $this->testObj = new ilTestCorrectionsGUI(
            $DIC,
            $this->createMock(ilObjTest::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestCorrectionsGUI::class, $this->testObj);
    }
}
