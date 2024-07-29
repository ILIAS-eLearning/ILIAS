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
    private ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUI $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        global $DIC;

        $this->testObj = new ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUI(
            $DIC['ilCtrl'],
            $DIC['lng'],
            $this->createMock(ilTestRandomQuestionSetConfigGUI::class),
            $this->createMock(ilTestRandomQuestionSetConfig::class)
        );
    }

    /**
     * @throws Exception
     */
    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUI::class,
            $this->testObj
        );
    }

    /**
     * @throws Exception
     */
    public function testBuild(): void
    {
        global $DIC;
        $il_test_random_question_set_config_gui = $this->createMock(ilTestRandomQuestionSetConfigGUI::class);
        $this->mockServiceMethod(service_name: 'ilCtrl', method: 'getFormAction', expects: $this->exactly(2), with: [$il_test_random_question_set_config_gui], will_return: 'form_action');
        $il_test_random_question_set_config = $this->createMock(ilTestRandomQuestionSetConfig::class);
        $il_test_random_question_set_config
            ->expects($this->exactly(2))
            ->method('doesSelectableQuestionPoolsExist')
            ->willReturn(true, false);

        $il_test_random_question_set_source_pool_definition_list_toolbar_gui = new ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUI(
            $DIC['ilCtrl'],
            $DIC['lng'],
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
        $this->assertEquals(
            $output,
            self::callMethod(
                $this->testObj,
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
        global $DIC;
        $this->mockServiceMethod(
            service_name: 'lng',
            method: 'txt',
            expects: $this->once(),
            with: ['tst_rnd_quest_set_tb_add_pool_btn'],
            will_return: 'tst_rnd_quest_set_tb_add_pool_btn_x'
        );
        $il_test_random_question_set_source_pool_definition_list_toolbar_gui = new ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUI(
            $DIC['ilCtrl'],
            $DIC['lng'],
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
