<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestDashboardGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestDashboardGUITest extends ilTestBaseTestCase
{
    private ilTestDashboardGUI $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestDashboardGUI(
            $this->createMock(ilObjTest::class),
            $this->createMock(ilTestQuestionSetConfig::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestDashboardGUI::class, $this->testObj);
    }
}