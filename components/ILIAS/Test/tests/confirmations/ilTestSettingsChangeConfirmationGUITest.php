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

use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ilTestSettingsChangeConfirmationGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSettingsChangeConfirmationGUITest extends ilTestBaseTestCase
{
    private ilTestSettingsChangeConfirmationGUI $testSettingsChangeConfirmationGUI;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testSettingsChangeConfirmationGUI = new ilTestSettingsChangeConfirmationGUI(
            $this->getMockBuilder(ilObjTest::class)->disableOriginalConstructor()->getMock()
        );
    }

    public function testSetAndGetOldQuestionSetType(): void
    {
        $expect = 'testType';

        $this->testSettingsChangeConfirmationGUI->setOldQuestionSetType($expect);

        $this->assertEquals($expect, $this->testSettingsChangeConfirmationGUI->getOldQuestionSetType());
    }

    public function testSetAndGetNewQuestionSetType(): void
    {
        $expect = 'testType';

        $this->testSettingsChangeConfirmationGUI->setNewQuestionSetType($expect);

        $this->assertEquals($expect, $this->testSettingsChangeConfirmationGUI->getNewQuestionSetType());
    }

    public function testSetAndIsQuestionLossInfoEnabled(): void
    {
        $expect = true;

        $this->testSettingsChangeConfirmationGUI->setQuestionLossInfoEnabled($expect);

        $this->assertEquals($expect, $this->testSettingsChangeConfirmationGUI->isQuestionLossInfoEnabled());
    }

    /**
     * @dataProvider buildHeaderTextDataProvider
     * @throws ReflectionException|\PHPUnit\Framework\MockObject\Exception|Exception
     */
    public function testBuildHeaderText(bool $input, string $output): void
    {
        $this->adaptDICServiceMock(ilLanguage::class, function (ilLanguage|MockObject $mockObject) use ($input) {
            $mockObject
                ->expects($this->exactly($input ? 2 : 1))
                ->method('txt')
                ->willReturnCallback(function (string $key) {
                    return $key . '_x';
                });
        });
        $il_test_settings_change_confirmation_gui = $this->createInstanceOf(ilTestSettingsChangeConfirmationGUI::class);
        $il_test_settings_change_confirmation_gui->setQuestionLossInfoEnabled($input);
        $il_test_settings_change_confirmation_gui->setOldQuestionSetType('old');
        $il_test_settings_change_confirmation_gui->setNewQuestionSetType('new');

        $this->assertEquals($output, self::callMethod($il_test_settings_change_confirmation_gui, 'buildHeaderText'));
    }

    public static function buildHeaderTextDataProvider(): array
    {
        return [
            'question_loss_info_enabled' => [true, 'tst_change_quest_set_type_from_old_to_new_with_conflict_x<br /><br />tst_nonpool_questions_get_lost_warning_x'],
            'question_loss_info_disabled' => [false, 'tst_change_quest_set_type_from_old_to_new_with_conflict_x']
        ];
    }

    /**
     * @dataProvider buildDataProvider
     * @throws ReflectionException|\PHPUnit\Framework\MockObject\Exception|Exception
     */
    public function testBuild(bool $input, string $output): void
    {
        $this->adaptDICServiceMock(ilLanguage::class, function (ilLanguage|MockObject $mockObject) use ($input) {
            $mockObject
                ->expects($this->exactly($input ? 2 : 1))
                ->method('txt')
                ->willReturnCallback(function (string $key) {
                    return $key . '_x';
                });
        });
        $il_test_settings_change_confirmation_gui = $this->createInstanceOf(ilTestSettingsChangeConfirmationGUI::class);
        $il_test_settings_change_confirmation_gui->setQuestionLossInfoEnabled($input);
        $il_test_settings_change_confirmation_gui->setOldQuestionSetType('old');
        $il_test_settings_change_confirmation_gui->setNewQuestionSetType('new');

        $this->assertNull($il_test_settings_change_confirmation_gui->build());
        $this->assertEquals($output, $il_test_settings_change_confirmation_gui->getHeaderText());
    }

    public static function buildDataProvider(): array
    {
        return [
            'question_loss_info_enabled' => [true, 'tst_change_quest_set_type_from_old_to_new_with_conflict_x<br /><br />tst_nonpool_questions_get_lost_warning_x'],
            'question_loss_info_disabled' => [false, 'tst_change_quest_set_type_from_old_to_new_with_conflict_x']
        ];
    }

    /**
     * @dataProvider populateParametersFromPostDataProvider
     * @throws ReflectionException|\PHPUnit\Framework\MockObject\Exception
     */
    public function testPopulateParametersFromPost(array $input, array $output): void
    {
        $il_test_settings_change_confirmation_gui = $this->createInstanceOf(ilTestSettingsChangeConfirmationGUI::class);
        $_POST = $input;

        $this->assertNull($il_test_settings_change_confirmation_gui->populateParametersFromPost());
        $this->assertEquals($output, self::getNonPublicPropertyValue($il_test_settings_change_confirmation_gui, 'hidden_item'));
    }

    public static function populateParametersFromPostDataProvider(): array
    {
        return [
            'empty' => [[], []],
            'cmd' => [['cmd' => ''], []],
            'key_value' => [['key' => 'value'], [['var' => 'key', 'value' => 'value']]],
            'key1_value_key2_value' => [['key1' => 'value', 'key2' => 'value'], [['var' => 'key1', 'value' => 'value'], ['var' => 'key2', 'value' => 'value']]],
            'array' => [['key' => ['key' => 'value']], [['var' => 'key[key]', 'value' => 'value']]]
        ];
    }

    /**
     * @dataProvider populateParametersFromPropertyFormDataProvider
     * @throws ReflectionException|\PHPUnit\Framework\MockObject\Exception
     */
    public function testPopulateParametersFromPropertyForm(array $input): void
    {
        $il_test_settings_change_confirmation_gui = $this->createInstanceOf(ilTestSettingsChangeConfirmationGUI::class);
        $type = $input['type'] ?? null;
        $items = $input['items'] ?? [];
        $select = [['key' => 'value'], 'value'];
        $datetime = [
            ['null' => true, 'className' => ilDateTime::class],
            ['null' => false, 'className' => ilDateTime::class],
            ['null' => false, 'className' => ilDate::class]
        ];
        foreach ($items as $item) {
            $item_mock = $this->createMock($item);
            $item_mock
                ->expects($this->once())
                ->method('getType')
                ->willReturn($type);

            if ($type === 'datetime') {
                $datetime_item = array_shift($datetime);
                $il_datetime_mock = $this->createMock($datetime_item['className']);
                $il_datetime_mock
                    ->expects($this->once())
                    ->method('isNull')
                    ->willReturn($datetime_item['null']);
                $il_datetime_mock
                    ->method('get')
                    ->with(1)
                    ->willReturn('x x');

                $item_mock
                    ->method('getDate')
                    ->willReturn($il_datetime_mock);
            }

            if ($type === 'dateduration') {
                $datetime_item = array_shift($datetime);
                $il_datetime_mock = $this->createMock($datetime_item['className']);
                $il_datetime_mock
                    ->expects($this->exactly(2))
                    ->method('isNull')
                    ->willReturn($datetime_item['null']);
                $il_datetime_mock
                    ->method('get')
                    ->with(1)
                    ->willReturn('x x');

                $item_mock
                    ->method('getStart')
                    ->willReturn($il_datetime_mock);
                $item_mock
                    ->method('getEnd')
                    ->willReturn($il_datetime_mock);
            }

            $item_mock
                ->method('getPostVar')
                ->willReturn('post_var');

            if ($type === 'duration') {
                $item_mock
                    ->method('getMonths')
                    ->willReturn(1);
                $item_mock
                    ->method('getDays')
                    ->willReturn(1);
                $item_mock
                    ->method('getHours')
                    ->willReturn(1);
                $item_mock
                    ->method('getMinutes')
                    ->willReturn(1);
                $item_mock
                    ->method('getSeconds')
                    ->willReturn(1);
            }
            if ($type === 'checkboxgroup') {
                $item_mock
                    ->method('getValue')
                    ->willReturn(['key' => 'value']);
            } elseif ($type === 'select') {
                $item_mock
                    ->method('getValue')
                    ->willReturn(array_shift($select));
            } elseif ($type === 'default') {
                $item_mock
                    ->method('getValue')
                    ->willReturn('value');
            }

            if ($type === 'checkbox') {
                $item_mock
                    ->method('getChecked')
                    ->willReturn(true);
            }

            $input_items_recursive[] = $item_mock;
        }
        $il_property_form_gui_mock = $this->createMock(ilPropertyFormGUI::class);
        $il_property_form_gui_mock
            ->expects($this->once())
            ->method('getInputItemsRecursive')
            ->willReturn($input_items_recursive ?? []);

        $this->assertNull($il_test_settings_change_confirmation_gui->populateParametersFromPropertyForm($il_property_form_gui_mock, null));
    }

    public static function populateParametersFromPropertyFormDataProvider(): array
    {
        return [
            'empty' => [[]],
            'section_header' => [['type' => 'section_header', 'items' => [ilFormSectionHeaderGUI::class]]],
            'datetime' => [['type' => 'datetime', 'items' => [ilDateTimeInputGUI::class, ilDateTimeInputGUI::class, ilDateTimeInputGUI::class]]],
            'duration' => [['type' => 'duration', 'items' => [ilDurationInputGUI::class]]],
            'dateduration' => [['type' => 'dateduration' , 'items' => [ilDateDurationInputGUI::class, ilDateDurationInputGUI::class, ilDateDurationInputGUI::class]]],
            'checkboxgroup' => [['type' => 'checkboxgroup', 'items' => [ilCheckboxGroupInputGUI::class]]],
            'select' => [['type' => 'select', 'items' => [ilSelectInputGUI::class, ilSelectInputGUI::class]]],
            'checkbox' => [['type' => 'checkbox', 'items' => [ilCheckboxInputGUI::class]]],
            'default' => [['type' => 'default', 'items' => [ilHiddenInputGUI::class]]]
        ];
    }
}
