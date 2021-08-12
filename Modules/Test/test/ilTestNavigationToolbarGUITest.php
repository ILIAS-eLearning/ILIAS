<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestNavigationToolbarGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestNavigationToolbarGUITest extends ilTestBaseTestCase
{
    private ilTestNavigationToolbarGUI $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->addGlobal_lng();

        $this->testObj = new ilTestNavigationToolbarGUI(
            $this->createMock(ilCtrl::class),
            $this->createMock(ilLanguage::class),
            $this->createMock(ilTestPlayerAbstractGUI::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestNavigationToolbarGUI::class, $this->testObj);
    }
}