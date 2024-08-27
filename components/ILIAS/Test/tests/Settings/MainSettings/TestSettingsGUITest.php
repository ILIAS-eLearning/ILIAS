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

use ILIAS\Test\Settings\TestSettingsGUI;
use PHPUnit\Framework\MockObject\Exception;

class TestSettingsGUITest extends ilTestBaseTestCase
{
    /**
     * @throws ReflectionException|Exception
     */
    public function testConstruct(): void
    {
        $il_obj_test = $this->createMock(ilObjTest::class);
        $test_settings_gui = new class($il_obj_test) extends TestSettingsGUI {};

        $this->assertInstanceOf(TestSettingsGUI::class, $test_settings_gui);
    }

    /**
     * @dataProvider formPropertyExistsDataProvider
     * @throws ReflectionException|Exception
     */
    public function testFormPropertyExists(?string $input, bool $output): void
    {
        $il_obj_test = $this->createMock(ilObjTest::class);
        $test_settings_gui = new class($il_obj_test) extends TestSettingsGUI {};
        $form = $this->createConfiguredMock(ilPropertyFormGUI::class, ['getItemByPostVar' => $input ? $this->createMock($input) : null]);

        $this->assertEquals($output, self::callMethod($test_settings_gui, 'formPropertyExists', [$form, '']));
    }

    public static function formPropertyExistsDataProvider(): array
    {
        return [
            'ilFormPropertyGUI' => ['input' => ilFormPropertyGUI::class, 'output' => true],
            'null' => ['input' => null, 'output' => false],
        ];
    }
}
