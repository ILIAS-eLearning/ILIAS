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
 * Class ilTestSkillEvaluationToolbarGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSkillEvaluationToolbarGUITest extends ilTestBaseTestCase
{
    private ilTestSkillEvaluationToolbarGUI $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestSkillEvaluationToolbarGUI(
            $this->createMock(ilCtrlInterface::class)
        );
    }
    /**
     * @throws Exception
     */
    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilTestSkillEvaluationToolbarGUI::class, $this->testObj);
    }

    /**
     * @dataProvider setAndGetAvailableSkillProfilesDataProvider
     * @throws Exception
     */
    public function testSetAndGetAvailableSkillProfiles(array $IO): void
    {
        $this->testObj->setAvailableSkillProfiles($IO);

        $this->assertEquals($IO, $this->testObj->getAvailableSkillProfiles());
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
     * @throws Exception
     */
    public function testSetAndGetNoSkillProfileOptionEnabled(bool $IO): void
    {
        $this->testObj->setNoSkillProfileOptionEnabled($IO);

        $this->assertEquals($IO, $this->testObj->isNoSkillProfileOptionEnabled());
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
     * @throws Exception
     */
    public function testSetAndGetSelectedEvaluationMode(int $IO): void
    {
        $this->testObj->setSelectedEvaluationMode($IO);

        $this->assertEquals($IO, $this->testObj->getSelectedEvaluationMode());
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
     * @throws Exception|ReflectionException
     */
    public function testBuildEvaluationModeOptionsArray(array $input, array $output): void
    {
        $available_kill_profiles = $input['available_kill_profiles'];
        $no_skill_profile_option_enabled = $input['no_skill_profile_option_enabled'];
        $this->mockServiceMethod(
            service_name: 'lng',
            method: 'txt',
            expects: $this->exactly(count($available_kill_profiles) + ((int) $no_skill_profile_option_enabled)),
            will_return_callback: fn($topic) => $topic . '_x'
        );

        $this->testObj->setNoSkillProfileOptionEnabled($no_skill_profile_option_enabled);
        $this->testObj->setAvailableSkillProfiles($available_kill_profiles);

        $this->assertEquals($output, self::callMethod($this->testObj, 'buildEvaluationModeOptionsArray'));
    }

    public static function buildEvaluationModeOptionsArrayDataProvider(): array
    {
        return [
            'no_skill_profile_option_enabled_true_empty' => [
                [
                    'no_skill_profile_option_enabled' => true,
                    'available_kill_profiles' => []
                ],
                [
                    0 => 'tst_all_test_competences_x'
                ]
            ],
            'no_skill_profile_option_enabled_false_empty' => [
                [
                    'no_skill_profile_option_enabled' => false,
                    'available_kill_profiles' => []
                ],
                []
            ],
            'no_skill_profile_option_enabled_true_one' => [
                [
                    'no_skill_profile_option_enabled' => true,
                    'available_kill_profiles' => [
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
                    'available_kill_profiles' => [
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
                    'available_kill_profiles' => [
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
                    'available_kill_profiles' => [
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
                    'available_kill_profiles' => [
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
                    'available_kill_profiles' => [
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
