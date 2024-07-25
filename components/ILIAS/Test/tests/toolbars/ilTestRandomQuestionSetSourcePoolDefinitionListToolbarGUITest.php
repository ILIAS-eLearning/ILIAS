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

use PHPUnit\Framework\MockObject\Exception;

/**
 * Class ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUITest extends ilTestBaseTestCase
{
    /**
     * @throws Exception
     */
    public function testConstruct(): void
    {
        $il_test_random_question_set_source_pool_definition_list_toolbar_gui = new ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUI(
            $this->createMock(ilCtrlInterface::class),
            $this->createMock(ilLanguage::class),
            $this->createMock(ilTestRandomQuestionSetConfigGUI::class),
            $this->createMock(ilTestRandomQuestionSetConfig::class)
        );

        $this->assertInstanceOf(
            ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUI::class,
            $il_test_random_question_set_source_pool_definition_list_toolbar_gui
        );
    }

    /**
     * @throws Exception
     */
    public function testBuild(): void
    {
        $il_test_random_question_set_config_gui = $this->createMock(ilTestRandomQuestionSetConfigGUI::class);
        $il_ctrl = $this->createMock(ilCtrlInterface::class);
        $il_ctrl
            ->expects($this->exactly(2))
            ->method('getFormAction')
            ->with($il_test_random_question_set_config_gui)
            ->willReturn('form_action');
        $il_test_random_question_set_config = $this->createMock(ilTestRandomQuestionSetConfig::class);
        $il_test_random_question_set_config
            ->expects($this->exactly(2))
            ->method('doesSelectableQuestionPoolsExist')
            ->willReturn(true, false);

        $il_test_random_question_set_source_pool_definition_list_toolbar_gui = new ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUI(
            $il_ctrl,
            $this->createMock(ilLanguage::class),
            $il_test_random_question_set_config_gui,
            $il_test_random_question_set_config
        );

        $this->assertNull($il_test_random_question_set_source_pool_definition_list_toolbar_gui->build());
        $this->assertNull($il_test_random_question_set_source_pool_definition_list_toolbar_gui->build());
    }

    /**
     * @dataProvider buildSourcePoolSelectOptionsArrayDataProvider
     * @throws Exception|ReflectionException
     */
    public function testBuildSourcePoolSelectOptionsArray(array $input, array $output): void
    {
        $il_test_random_question_set_source_pool_definition_list_toolbar_gui = new ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUI(
            $this->createMock(ilCtrlInterface::class),
            $this->createMock(ilLanguage::class),
            $this->createMock(ilTestRandomQuestionSetConfigGUI::class),
            $this->createMock(ilTestRandomQuestionSetConfig::class)
        );

        $this->assertEquals(
            $output,
            self::callMethod(
                $il_test_random_question_set_source_pool_definition_list_toolbar_gui,
                'buildSourcePoolSelectOptionsArray',
                [$input]
            )
        );
    }

    public static function buildSourcePoolSelectOptionsArrayDataProvider(): array
    {
        return [
            'empty' => [[], []],
            'single_string' => [[0 => ['title' => 'string']], [0 => 'string']],
            'single_strING' => [[0 => ['title' => 'strING']], [0 => 'strING']],
            'multiple_string_string' => [[0 => ['title' => 'string'], 1 => ['title' => 'string']], [0 => 'string', 1 => 'string']],
            'multiple_strING_strING' => [[0 => ['title' => 'strING'], 1 => ['title' => 'strING']], [0 => 'strING', 1 => 'strING']],
            'multiple_string_strING' => [[0 => ['title' => 'string'], 1 => ['title' => 'strING']], [0 => 'string', 1 => 'strING']],
            'multiple_strING_string' => [[0 => ['title' => 'strING'], 1 => ['title' => 'string']], [0 => 'strING', 1 => 'string']],
            'single_string_1' => [[1 => ['title' => 'string']], [1 => 'string']],
            'single_string_2' => [[2 => ['title' => 'string']], [2 => 'string']]
        ];
    }

    /**
     * @throws Exception|ReflectionException
     */
    public function testPopulateNewQuestionSelectionRuleInputs(): void
    {
        $il_language = $this->createMock(ilLanguage::class);
        $il_language
            ->expects($this->once())
            ->method('txt')
            ->with('tst_rnd_quest_set_tb_add_pool_btn')
            ->willReturn('tst_rnd_quest_set_tb_add_pool_btn_x');
        $this->setGlobalVariable('lng', $il_language);
        $il_test_random_question_set_source_pool_definition_list_toolbar_gui = new ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUI(
            $this->createMock(ilCtrlInterface::class),
            $il_language,
            $this->createMock(ilTestRandomQuestionSetConfigGUI::class),
            $this->createMock(ilTestRandomQuestionSetConfig::class)
        );

        $this->assertNull(
            self::callMethod(
                $il_test_random_question_set_source_pool_definition_list_toolbar_gui,
                'populateNewQuestionSelectionRuleInputs'
            )
        );
        $this->assertEquals(
            [
                [
                    'type' => 'fbutton',
                    'txt' => 'tst_rnd_quest_set_tb_add_pool_btn_x',
                    'cmd' => 'showPoolSelectorExplorer',
                    'acc_key' => null,
                    'primary' => false,
                    'class' => null
                ]
            ],
            $il_test_random_question_set_source_pool_definition_list_toolbar_gui->items
        );
    }
}
