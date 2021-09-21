<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjTestSettingsGeneralGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilObjTestSettingsGeneralGUITest extends ilTestBaseTestCase
{
    private ilObjTestSettingsGeneralGUI $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->addGlobal_tpl();

        $objTestGui_mock = $this->createMock(ilObjTestGUI::class);
        $objTestGui_mock->object = $this->createMock(ilObjTest::class);

        $this->testObj = new ilObjTestSettingsGeneralGUI(
            $this->createMock(ilCtrl::class),
            $this->createMock(ilAccessHandler::class),
            $this->createMock(ilLanguage::class),
            $this->createMock(ilTree::class),
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilPluginAdmin::class),
            $this->createMock(ilObjUser::class),
            $objTestGui_mock
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilObjTestSettingsGeneralGUI::class, $this->testObj);
    }
}
