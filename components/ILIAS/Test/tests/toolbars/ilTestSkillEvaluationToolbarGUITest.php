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
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ilTestSkillEvaluationToolbarGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSkillEvaluationToolbarGUITest extends ilTestBaseTestCase
{
    private ilTestSkillEvaluationToolbarGUI $ilTestSkillEvaluationToolbarGui;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ilTestSkillEvaluationToolbarGui = $this->createInstanceOf(ilTestSkillEvaluationToolbarGUI::class);
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilTestSkillEvaluationToolbarGUI::class, $this->ilTestSkillEvaluationToolbarGui);
    }

    /**
     * @dataProvider setAndGetAvailableSkillProfilesDataProvider
     */
    public function testSetAndGetAvailableSkillProfiles(array $IO): void
    {
        $this->ilTestSkillEvaluationToolbarGui->setAvailableSkillProfiles($IO);

        $this->assertEquals($IO, $this->ilTestSkillEvaluationToolbarGui->getAvailableSkillProfiles());
    }

    public static function setAndGetAvailableSkillProfilesDataProvider(): array
    {
        return [
            'empty' => [[]],
            'array_string' => [['string']],
            'array_strING' => [['strING']]
        ];
    }

    /**
     * @dataProvider setAndGetNoSkillProfileOptionEnabledDataProvider
     */
    public function testSetAndGetNoSkillProfileOptionEnabled(bool $IO): void
    {
        $this->ilTestSkillEvaluationToolbarGui->setNoSkillProfileOptionEnabled($IO);

        $this->assertEquals($IO, $this->ilTestSkillEvaluationToolbarGui->isNoSkillProfileOptionEnabled());
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
     */
    public function testSetAndGetSelectedEvaluationMode(int $IO): void
    {
        $this->ilTestSkillEvaluationToolbarGui->setSelectedEvaluationMode($IO);

        $this->assertEquals($IO, $this->ilTestSkillEvaluationToolbarGui->getSelectedEvaluationMode());
    }

    public static function setAndGetSelectedEvaluationModeDataProvider(): array
    {
        return [
            'minus_one' => [-1],
            'zero' => [0],
            'one' => [1]
        ];
    }

    /**
     * @dataProvider buildEvaluationModeOptionsArrayDataProvider
     * @throws ReflectionException
     * @throws \Exception
     */
    public function testBuildEvaluationModeOptionsArray(array $input, array $output): void
    {
        $available_skill_profiles = $input['available_skill_profiles'];
        $no_skill_profile_option_enabled = $input['no_skill_profile_option_enabled'];

        $this->adaptDICServiceMock(ilLanguage::class, function (ilLanguage|MockObject $mock) use ($available_skill_profiles, $no_skill_profile_option_enabled) {
            $mock
                ->expects($this->exactly(count($available_skill_profiles) + ((int) $no_skill_profile_option_enabled)))
                ->method('txt')
                ->willReturnCallback(fn($topic) => $topic . '_x');
        });

        $this->ilTestSkillEvaluationToolbarGui->setNoSkillProfileOptionEnabled($no_skill_profile_option_enabled);
        $this->ilTestSkillEvaluationToolbarGui->setAvailableSkillProfiles($available_skill_profiles);

        $this->assertEquals($output, self::callMethod($this->ilTestSkillEvaluationToolbarGui, 'buildEvaluationModeOptionsArray'));
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
