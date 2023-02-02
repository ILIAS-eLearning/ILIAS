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
 * Class ilTestParticipantsTimeExtensionGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestParticipantsTimeExtensionGUITest extends ilTestBaseTestCase
{
    private ilTestParticipantsTimeExtensionGUI $testObj;
    private $backup_dic;

    protected function setUp(): void
    {
        parent::setUp();
        global $DIC;

        $this->backup_dic = $DIC;
        $DIC = new ILIAS\DI\Container([
            'tpl' => $this->getMockBuilder(ilGlobalTemplateInterface::class)
                          ->getMock(),

            'ilCtrl' => $this->getMockBuilder(ilCtrl::class)
                ->disableOriginalConstructor()
                ->getMock(),

            'lng' => $this->getMockBuilder(ilLanguage::class)
                ->disableOriginalConstructor()
                ->getMock()
        ]);
        $this->testObj = new ilTestParticipantsTimeExtensionGUI($this->createMock(ilObjTest::class));
    }

    protected function tearDown(): void
    {
        global $DIC;
        $DIC = $this->backup_dic;
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestParticipantsTimeExtensionGUI::class, $this->testObj);
    }

    public function testTestObj(): void
    {
        $mock = $this->createMock(ilObjTest::class);
        $this->testObj->setTestObj($mock);
        $this->assertEquals($mock, $this->testObj->getTestObj());
    }
}
