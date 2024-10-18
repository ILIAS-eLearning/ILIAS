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

namespace ILIAS\Test\Tests\Toolbars;

use ilLanguage;
use ilTestBaseTestCase;
use ilTestSkillEvaluationToolbarGUI;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

/**
 * Class ilTestSkillEvaluationToolbarGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSkillEvaluationToolbarGUITest extends ilTestBaseTestCase
{
    /**
     * @throws ReflectionException|Exception
     */
    public function testConstruct(): void
    {
        $il_test_skill_evaluation_toolbar_gui = $this->createInstanceOf(ilTestSkillEvaluationToolbarGUI::class);
        $this->assertInstanceOf(ilTestSkillEvaluationToolbarGUI::class, $il_test_skill_evaluation_toolbar_gui);
    }

    /**
     * @dataProvider setAndGetAvailableSkillProfilesDataProvider
     * @throws ReflectionException|Exception
     */
    public function testSetAndGetAvailableSkillProfiles(array $IO): void
    {
        $il_test_skill_evaluation_toolbar_gui = $this->createInstanceOf(ilTestSkillEvaluationToolbarGUI::class);
        $il_test_skill_evaluation_toolbar_gui->setAvailableSkillProfiles($IO);

        $this->assertEquals($IO, $il_test_skill_evaluation_toolbar_gui->getAvailableSkillProfiles());
    }

    public static function setAndGetAvailableSkillProfilesDataProvider(): array
    {
        return [
            'empty' => [[]],
            'array_string' => [['string']],
            'array_strING' => [['strING']],
            'array_STRING' => [['STRING']]
        ];
    }

    /**
     * @dataProvider setAndGetNoSkillProfileOptionEnabledDataProvider
     * @throws ReflectionException|Exception
     */
    public function testSetAndGetNoSkillProfileOptionEnabled(bool $IO): void
    {
        $il_test_skill_evaluation_toolbar_gui = $this->createInstanceOf(ilTestSkillEvaluationToolbarGUI::class);
        $il_test_skill_evaluation_toolbar_gui->setNoSkillProfileOptionEnabled($IO);

        $this->assertEquals($IO, $il_test_skill_evaluation_toolbar_gui->isNoSkillProfileOptionEnabled());
    }

    public static function setAndGetNoSkillProfileOptionEnabledDataProvider(): array
    {
        return [
            'true' => [true],
            'false' => [false]
        ];
    }

    /**
     * @dataProvider setAndGetSelectedEvaluationModeDataProvider
     * @throws ReflectionException|Exception
     */
    public function testSetAndGetSelectedEvaluationMode(int $IO): void
    {
        $il_test_skill_evaluation_toolbar_gui = $this->createInstanceOf(ilTestSkillEvaluationToolbarGUI::class);
        $il_test_skill_evaluation_toolbar_gui->setSelectedEvaluationMode($IO);

        $this->assertEquals($IO, $il_test_skill_evaluation_toolbar_gui->getSelectedEvaluationMode());
    }

    public static function setAndGetSelectedEvaluationModeDataProvider(): array
    {
        return [
            'negative_one' => [-1],
            'zero' => [0],
            'one' => [1]
        ];
    }

    /**
     * @dataProvider buildEvaluationModeOptionsArrayDataProvider
     * @throws ReflectionException|Exception
     */
    public function testBuildEvaluationModeOptionsArray(array $input, array $output): void
    {
        $il_test_skill_evaluation_toolbar_gui = $this->createInstanceOf(ilTestSkillEvaluationToolbarGUI::class);
        $available_skill_profiles = $input['available_skill_profiles'];
        $no_skill_profile_option_enabled = $input['no_skill_profile_option_enabled'];

        $this->adaptDICServiceMock(ilLanguage::class, function (ilLanguage|MockObject $mock) use ($available_skill_profiles, $no_skill_profile_option_enabled) {
            $mock
                ->expects($this->exactly(count($available_skill_profiles) + ((int) $no_skill_profile_option_enabled)))
                ->method('txt')
                ->willReturnCallback(fn($topic) => $topic . '_x');
        });

        $il_test_skill_evaluation_toolbar_gui->setNoSkillProfileOptionEnabled($no_skill_profile_option_enabled);
        $il_test_skill_evaluation_toolbar_gui->setAvailableSkillProfiles($available_skill_profiles);

        $this->assertEquals($output, self::callMethod($il_test_skill_evaluation_toolbar_gui, 'buildEvaluationModeOptionsArray'));
    }

    public static function buildEvaluationModeOptionsArrayDataProvider(): array
    {
        return [
            'no_skill_profile_option_enabled_true_empty' => [
                [
                    'no_skill_profile_option_enabled' => true,
                    'available_skill_profiles' => []
                ],
                [
                    0 => 'tst_all_test_competences_x'
                ]
            ],
            'no_skill_profile_option_enabled_false_empty' => [
                [
                    'no_skill_profile_option_enabled' => false,
                    'available_skill_profiles' => []
                ],
                []
            ],
            'no_skill_profile_option_enabled_true_one' => [
                [
                    'no_skill_profile_option_enabled' => true,
                    'available_skill_profiles' => [
                        1 => 'string'
                    ]
                ],
                [
                    0 => 'tst_all_test_competences_x',
                    1 => 'tst_gap_analysis_x: string'
                ]
            ],
            'no_skill_profile_option_enabled_false_one' => [
                [
                    'no_skill_profile_option_enabled' => false,
                    'available_skill_profiles' => [
                        1 => 'string'
                    ]
                ],
                [
                    1 => 'tst_gap_analysis_x: string'
                ]
            ],
            'no_skill_profile_option_enabled_true_multiple' => [
                [
                    'no_skill_profile_option_enabled' => true,
                    'available_skill_profiles' => [
                        1 => 'string',
                        2 => 'strING'
                    ]
                ],
                [
                    0 => 'tst_all_test_competences_x',
                    1 => 'tst_gap_analysis_x: string',
                    2 => 'tst_gap_analysis_x: strING'
                ]
            ],
            'no_skill_profile_option_enabled_false_multiple' => [
                [
                    'no_skill_profile_option_enabled' => false,
                    'available_skill_profiles' => [
                        1 => 'string',
                        2 => 'strING'
                    ]
                ],
                [
                    1 => 'tst_gap_analysis_x: string',
                    2 => 'tst_gap_analysis_x: strING'
                ]
            ],
            'no_skill_profile_option_enabled_true_overwrite' => [
                [
                    'no_skill_profile_option_enabled' => true,
                    'available_skill_profiles' => [
                        0 => 'string'
                    ]
                ],
                [
                    0 => 'tst_gap_analysis_x: string'
                ]
            ],
            'no_skill_profile_option_enabled_false_overwrite' => [
                [
                    'no_skill_profile_option_enabled' => false,
                    'available_skill_profiles' => [
                        0 => 'string'
                    ]
                ],
                [
                    0 => 'tst_gap_analysis_x: string'
                ]
            ]
        ];
    }
}
