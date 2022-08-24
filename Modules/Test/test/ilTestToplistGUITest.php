<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestToplistGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestToplistGUITest extends ilTestBaseTestCase
{
    private ilTestToplistGUI $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilCtrl();
        $this->addGlobal_tpl();
        $this->addGlobal_lng();
        $this->addGlobal_ilUser();
        $this->addGlobal_uiFactory();
        $this->addGlobal_uiRenderer();
        $this->addGlobal_ilDB();

        $this->testObj = new ilTestToplistGUI(
            $this->createMock(ilObjTest::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestToplistGUI::class, $this->testObj);
    }
}
