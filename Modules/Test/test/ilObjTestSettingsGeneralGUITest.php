<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
        $this->addGlobal_ilDB();
        $this->addGlobal_ilias();
        $this->addGlobal_ilLog();
        $this->addGlobal_ilErr();
        $this->addGlobal_tree();
        $this->addGlobal_ilAppEventHandler();
        $this->addGlobal_objDefinition();

        $objTestGui_mock = $this->getMockBuilder(ilObjTestGUI::class)->disableOriginalConstructor()->onlyMethods(array('getTestObject'))->getMock();
        $objTestGui_mock->method('getTestObject')->willReturn($this->createMock(ilObjTest::class));

        $this->testObj = new ilObjTestSettingsGeneralGUI(
            $this->createMock(ilCtrl::class),
            $this->createMock(ilAccessHandler::class),
            $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ilTree::class)->disableOriginalConstructor()->getMock(),
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilComponentRepository::class),
            $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->getMock(),
            $objTestGui_mock
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilObjTestSettingsGeneralGUI::class, $this->testObj);
    }
}
