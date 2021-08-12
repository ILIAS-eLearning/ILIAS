<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestQuestionNavigationGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestQuestionNavigationGUITest extends ilTestBaseTestCase
{
    private ilTestQuestionNavigationGUI $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestQuestionNavigationGUI($this->createMock(ilLanguage::class));
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestQuestionNavigationGUI::class, $this->testObj);
    }
}