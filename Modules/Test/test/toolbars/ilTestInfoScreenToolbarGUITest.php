<?php

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

declare(strict_types=1);

/**
 * Class ilTestInfoScreenToolbarGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestInfoScreenToolbarGUITest extends ilTestBaseTestCase
{
    private ilTestInfoScreenToolbarGUI $testInfoScreenToolbarGUI;

    protected function setUp(): void
    {
        global $DIC;
        parent::setUp();

        $this->addGlobal_ilAccess();
        $this->addGlobal_ilCtrl();
        $this->addGlobal_lng();
        $this->addGlobal_uiFactory();
        $this->addGlobal_uiRenderer();
        $this->addGlobal_ilUser();
        $this->addGlobal_tpl();
        $this->addGlobal_ilToolbar();


        $this->testInfoScreenToolbarGUI = new ilTestInfoScreenToolbarGUI(
            $this->createMock(ilObjTest::class),
            $this->createMock(ilTestPlayerFixedQuestionSetGUI::class),
            $this->createMock(ilTestQuestionSetConfig::class),
            $this->createMock(ilTestSession::class),
            $DIC['ilDB'],
            $DIC['ilAccess'],
            $DIC['ilCtrl'],
            $DIC['lng'],
            $DIC['ui.factory'],
            $DIC['ui.renderer'],
            $DIC['ilUser'],
            $DIC['tpl'],
            $DIC['ilToolbar']
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestInfoScreenToolbarGUI::class, $this->testInfoScreenToolbarGUI);
    }

    public function testSessionLockString(): void
    {
        $this->assertEquals('', $this->testInfoScreenToolbarGUI->getSessionLockString());

        $this->testInfoScreenToolbarGUI->setSessionLockString("testString");

        $this->assertEquals("testString", $this->testInfoScreenToolbarGUI->getSessionLockString());
    }

    public function testInfoMessages(): void
    {
        $this->assertIsArray($this->testInfoScreenToolbarGUI->getInfoMessages());

        $expected = ["test1", "test2", "3test", "4test"];

        foreach ($expected as $value) {
            $this->testInfoScreenToolbarGUI->addInfoMessage($value);
        }

        $this->assertEquals($expected, $this->testInfoScreenToolbarGUI->getInfoMessages());
    }

    public function testFailureMessages(): void
    {
        $this->assertIsArray($this->testInfoScreenToolbarGUI->getFailureMessages());

        $expected = ["test1", "test2", "3test", "4test"];

        foreach ($expected as $value) {
            $this->testInfoScreenToolbarGUI->addFailureMessage($value);
        }

        $this->assertEquals($expected, $this->testInfoScreenToolbarGUI->getFailureMessages());
    }
}
