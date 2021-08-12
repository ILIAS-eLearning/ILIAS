<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestQuestionSideListGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestQuestionSideListGUITest extends ilTestBaseTestCase
{
    private ilTestQuestionSideListGUI $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestQuestionSideListGUI(
            $this->createMock(ilCtrl::class),
            $this->createMock(ilLanguage::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestQuestionSideListGUI::class, $this->testObj);
    }
}