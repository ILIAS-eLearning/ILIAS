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

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\HTTP\Services;
use ILIAS\Refinery\Factory;

class TestPersonalDefaultSettingsGUITest extends ilTestBaseTestCase
{
    private TestPersonalDefaultSettingsGUI $tableGUI;

    private const PARENT_OBJ_ID = 1;

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilCtrl();
        $this->addGlobal_ilComponentRepository();
        $this->setGlobalVariable('rbacsystem', $this->createMock(ilRbacSystem::class));
        $this->addGlobal_ilLoggerFactory();
        $this->addGlobal_ilUser();

        $ctrl_mock = $this->createMock(ilCtrl::class);
        $ctrl_mock
            ->method('getFormAction')
            ->willReturnCallback(function () {
                return 'testFormAction';
            });

        $this->tableGUI = new TestPersonalDefaultSettingsGUI(
            $this->dic->language(),
            $this->dic->ui()->factory(),
            self::PARENT_OBJ_ID,
            $this->dic->ui()->renderer(),
            $this->createMock(GlobalHttpState::class),
            $this->createMock(ilGlobalTemplateInterface::class),
            $ctrl_mock,
            $this->createMock(ilToolbarGUI::class),
            $this->createMock(ilObjTest::class),
            $this->createMock(Services::class),
            $this->createMock(Factory::class),
            $this->createMock(ilTestQuestionSetConfigFactory::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(TestPersonalDefaultSettingsGUI::class, $this->tableGUI);
    }
}
