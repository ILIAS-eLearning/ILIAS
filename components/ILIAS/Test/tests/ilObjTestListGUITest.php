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

class ilObjTestListGUITest extends ilTestBaseTestCase
{
    private ilObjTestListGUI $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilAccess();
        $this->addGlobal_ilUser();
        $this->addGlobal_ilSetting();
        $this->addGlobal_rbacsystem();
        $this->addGlobal_ilCtrl();
        $this->addGlobal_ilLoggerFactory();
        $this->addGlobal_filesystem();
        $this->addGlobal_rbacreview();
        $this->addGlobal_ilObjDataCache();

        $this->testObj = new ilObjTestListGUI(1);
    }
    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilObjTestListGUI::class, $this->testObj);
    }

    /**
     * @dataProvider createDefaultCommandDataProvider
     */
    public function testCreateDefaultCommand(array $IO): void
    {
        $this->assertEquals($IO, $this->testObj->createDefaultCommand($IO));
    }

    public function createDefaultCommandDataProvider()
    {
        return [
            [[]],
            [[1]],
            [[1, 2]],
            [[1, 2, 3]]
        ];
    }
}