<?php

declare(strict_types=1);

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
 * Class ilTestRandomQuestionSetConfigGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetConfigGUITest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetConfigGUI $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_objDefinition();

        $this->testObj = new ilTestRandomQuestionSetConfigGUI(
            $this->getMockBuilder(ilCtrl::class)->disableOriginalConstructor()->getMock(),
            $this->createMock(ilAccessHandler::class),
            $this->getMockBuilder(ilTabsGUI::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock(),
            $this->createMock(ilGlobalTemplateInterface::class),
            $this->createMock(ilDBInterface::class),
            $this->getMockBuilder(ilTree::class)->disableOriginalConstructor()->getMock(),
            $this->createMock(ilComponentRepository::class),
            $this->getMockBuilder(ilObjTest::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ilTestProcessLockerFactory::class)->disableOriginalConstructor()->getMock()
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetConfigGUI::class, $this->testObj);
    }

    public function testGetGeneralConfigTabLabel(): void
    {
        $lng_mock = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock();
        $lng_mock->expects($this->once())
                 ->method("txt")
                 ->with("tst_rnd_quest_cfg_tab_general")
                 ->willReturn("testString");

        $this->testObj->lng = $lng_mock;

        $this->assertEquals("testString", $this->testObj->getGeneralConfigTabLabel());
    }

    public function testPoolConfigTabLabel(): void
    {
        $lng_mock = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock();
        $lng_mock->expects($this->once())
                 ->method("txt")
                 ->with("tst_rnd_quest_cfg_tab_pool")
                 ->willReturn("testString");

        $this->testObj->lng = $lng_mock;

        $this->assertEquals("testString", $this->testObj->getPoolConfigTabLabel());
    }
}
