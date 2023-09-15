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

use ILIAS\Services\Logging\NullLogger;

/**
 * Class ilTestVerificationTableGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestVerificationTableGUITest extends ilTestBaseTestCase
{
    private ilTestVerificationTableGUI $tableGui;

    protected function setUp(): void
    {
        global $DIC;
        parent::setUp();

        $this->addGlobal_ilDB();
        $this->addGlobal_ilUser();
        $this->addGlobal_lng();
        $this->addGlobal_tpl();
        $this->addGlobal_ilComponentRepository();
        $this->addGlobal_ilComponentFactory();

        $ctrl_mock = $this->createMock(ilCtrl::class);
        $ctrl_mock
            ->method("getFormAction")
            ->willReturnCallback(function () {
                return "testFormAction";
            });
        $this->setGlobalVariable("ilCtrl", $ctrl_mock);

        $test_gui = $this->getMockBuilder(ilObjTestVerificationGUI::class)->disableOriginalConstructor()->getMock();
        $this->tableGui = new ilTestVerificationTableGUI($test_gui, '', $DIC['ilDB'], $DIC['ilUser'], new NullLogger());
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestVerificationTableGUI::class, $this->tableGui);
    }
}
