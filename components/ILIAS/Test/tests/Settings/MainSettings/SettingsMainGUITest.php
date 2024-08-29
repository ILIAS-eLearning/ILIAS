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

namespace Settings\MainSettings;

use ILIAS\Test\Settings\MainSettings\MainSettings;
use ILIAS\Test\Settings\MainSettings\MainSettingsRepository;
use ILIAS\Test\Settings\MainSettings\SettingsMainGUI;
use ilObjectProperties;
use ilObjTest;
use ilObjTestGUI;
use ilTestBaseTestCase;
use PHPUnit\Framework\MockObject\Exception;
use ReflectionException;

class SettingsMainGUITest extends ilTestBaseTestCase
{
    /**
     * @throws ReflectionException|Exception
     */
    public function testConstruct(): void
    {
        $il_obj_test = $this->createMock(ilObjTest::class);
        $il_obj_test
            ->expects($this->once())
            ->method('getObjectProperties')
            ->willReturn($this->createMock(ilObjectProperties::class));
        $il_obj_test
            ->expects($this->once())
            ->method('getMainSettings')
            ->willReturn($this->createMock(MainSettings::class));
        $il_obj_test
            ->expects($this->once())
            ->method('getMainSettingsRepository')
            ->willReturn($this->createMock(MainSettingsRepository::class));
        $test_gui = $this->createMock(ilObjTestGUI::class);
        $test_gui
            ->expects($this->exactly(5))
            ->method('getTestObject')
            ->willReturn($il_obj_test);

        $this->assertInstanceOf(SettingsMainGUI::class, $this->createInstanceOf(SettingsMainGUI::class, ['test_gui' => $test_gui]));
    }
}
