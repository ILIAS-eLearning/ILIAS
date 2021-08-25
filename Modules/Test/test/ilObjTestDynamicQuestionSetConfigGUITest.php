<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjTestDynamicQuestionSetConfigGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilObjTestDynamicQuestionSetConfigGUITest extends ilTestBaseTestCase
{
    private ilObjTestDynamicQuestionSetConfigGUI $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilObjTestDynamicQuestionSetConfigGUI(
            $this->createMock(ilCtrl::class),
            $this->createMock(ilAccessHandler::class),
            $this->createMock(ilTabsGUI::class),
            $this->createMock(ilLanguage::class),
            $this->createMock(ilGlobalPageTemplate::class),
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilTree::class),
            $this->createMock(ilPluginAdmin::class),
            $this->createMock(ilObjTest::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilObjTestDynamicQuestionSetConfigGUI::class, $this->testObj);
    }
}
