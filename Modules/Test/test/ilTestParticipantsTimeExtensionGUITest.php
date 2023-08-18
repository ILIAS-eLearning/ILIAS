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
 * Class ilTestParticipantsTimeExtensionGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestParticipantsTimeExtensionGUITest extends ilTestBaseTestCase
{
    private ilTestParticipantsTimeExtensionGUI $testObj;

    protected function setUp(): void
    {
        global $DIC;
        parent::setUp();

        $this->addGlobal_ilUser();
        $this->addGlobal_ilCtrl();
        $this->addGlobal_lng();
        $this->addGlobal_tpl();

        $this->testObj = new ilTestParticipantsTimeExtensionGUI(
            $this->createMock(ilObjTest::class),
            $DIC['ilUser'],
            $DIC['ilCtrl'],
            $DIC['lng'],
            $DIC['ilDB'],
            $DIC['tpl'],
            $this->createMock(ilTestParticipantAccessFilterFactory::class)
        );
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
