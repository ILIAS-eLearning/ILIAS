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
 * Class ilTestInfoScreenToolbarGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestInfoScreenToolbarGUITest extends ilTestBaseTestCase
{
    private ilTestInfoScreenToolbarGUI $testInfoScreenToolbarGUI;
    /**
     * @var \ILIAS\DI\Container|mixed
     */
    private $backup_dic;

    protected function setUp(): void
    {
        parent::setUp();
        global $DIC;

        $this->backup_dic = $DIC;
        $DIC = new ILIAS\DI\Container([
            'tpl' => $this->getMockBuilder(ilGlobalTemplateInterface::class)
                          ->getMock()
        ]);
        $db_mock = $this->createMock(ilDBInterface::class);
        $access_mock = $this->createMock(ilAccessHandler::class);
        $ctrl_mock = $this->createMock(ilCtrl::class);
        $lng_mock = $this->createMock(ilLanguage::class);

        $this->testInfoScreenToolbarGUI = new ilTestInfoScreenToolbarGUI(
            $db_mock,
            $access_mock,
            $ctrl_mock,
            $lng_mock,
            $this->createMock(ilComponentRepository::class)
        );
    }

    protected function tearDown(): void
    {
        global $DIC;
        $DIC = $this->backup_dic;
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestInfoScreenToolbarGUI::class, $this->testInfoScreenToolbarGUI);
    }

    public function testGlobalToolbar(): void
    {
        $this->assertNull($this->testInfoScreenToolbarGUI->getGlobalToolbar());

        $expected_mock = $this->createMock(ilToolbarGUI::class);
        $this->testInfoScreenToolbarGUI->setGlobalToolbar($expected_mock);

        $this->assertEquals($expected_mock, $this->testInfoScreenToolbarGUI->getGlobalToolbar());
    }

    public function testTestOBJ(): void
    {
        $this->assertNull($this->testInfoScreenToolbarGUI->getTestOBJ());

        $expected_mock = $this->createMock(ilObjTest::class);
        $this->testInfoScreenToolbarGUI->setTestOBJ($expected_mock);

        $this->assertEquals($expected_mock, $this->testInfoScreenToolbarGUI->getTestOBJ());
    }

    public function testTestQuestionSetConfig(): void
    {
        $this->assertNull($this->testInfoScreenToolbarGUI->getTestQuestionSetConfig());

        $expected_mock = $this->createMock(ilTestQuestionSetConfig::class);
        $this->testInfoScreenToolbarGUI->setTestQuestionSetConfig($expected_mock);

        $this->assertEquals($expected_mock, $this->testInfoScreenToolbarGUI->getTestQuestionSetConfig());
    }

    public function testTestPlayerGUI(): void
    {
        $this->assertNull($this->testInfoScreenToolbarGUI->getTestPlayerGUI());

        $expected_mock = $this->createMock(ilTestPlayerAbstractGUI::class);
        $this->testInfoScreenToolbarGUI->setTestPlayerGUI($expected_mock);

        $this->assertEquals($expected_mock, $this->testInfoScreenToolbarGUI->getTestPlayerGUI());
    }

    public function testTestSession(): void
    {
        $this->assertNull($this->testInfoScreenToolbarGUI->getTestSession());

        $expected_mock = $this->createMock(ilTestSession::class);
        $this->testInfoScreenToolbarGUI->setTestSession($expected_mock);

        $this->assertEquals($expected_mock, $this->testInfoScreenToolbarGUI->getTestSession());
    }

    public function testTestSequence(): void
    {
        $this->assertNull($this->testInfoScreenToolbarGUI->getTestSequence());

        $expected_mock = $this->createMock(ilTestSequence::class);
        $this->testInfoScreenToolbarGUI->setTestSequence($expected_mock);

        $this->assertEquals($expected_mock, $this->testInfoScreenToolbarGUI->getTestSequence());

        $expected_mock = $this->createMock(ilTestSequenceDynamicQuestionSet::class);
        $this->testInfoScreenToolbarGUI->setTestSequence($expected_mock);

        $this->assertEquals($expected_mock, $this->testInfoScreenToolbarGUI->getTestSequence());
    }

    public function testSessionLockString(): void
    {
        $this->assertNull($this->testInfoScreenToolbarGUI->getSessionLockString());

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
