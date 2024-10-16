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

namespace ILIAS\Test\Tests\Administration;

use assFormulaQuestion;
use assNumeric;
use ILIAS\Test\Administration\TestGlobalSettingsRepository;
use ILIAS\Test\Logging\TestLogViewer;
use ilObjTestFolder;
use ilTestBaseTestCase;
use PHPUnit\Framework\MockObject\Exception;
use ReflectionException;

/**
 * Class ilObjTestFolderTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilObjTestFolderTest extends ilTestBaseTestCase
{
    /**
     * @throws ReflectionException|Exception
     */
    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilObjTestFolder::class, $this->createInstanceOf(ilObjTestFolder::class));
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testGetGlobalSettingsRepository(): void
    {
        $il_obj_test_folder = $this->createInstanceOf(ilObjTestFolder::class);

        $this->assertInstanceOf(TestGlobalSettingsRepository::class, $il_obj_test_folder->getGlobalSettingsRepository());
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testGetTestLogViewer(): void
    {
        $il_obj_test_folder = $this->createInstanceOf(ilObjTestFolder::class);

        $this->assertInstanceOf(TestLogViewer::class, $il_obj_test_folder->getTestLogViewer());
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testGetSkillTriggerAnswerNumberBarrier(): void
    {
        $il_obj_test_folder = $this->createInstanceOf(ilObjTestFolder::class);

        $this->assertEquals(1, $il_obj_test_folder->getSkillTriggerAnswerNumberBarrier());
    }

    public function testGetManualScoringTypes(): void
    {
        $this->assertEmpty(ilObjTestFolder::_getManualScoringTypes());
    }

    public function testIsAdditionalQuestionContentEditingModePageObjectEnabled(): void
    {
        $this->assertFalse(ilObjTestFolder::isAdditionalQuestionContentEditingModePageObjectEnabled());
    }

    public function testGetValidAssessmentProcessLockModes(): void
    {
        $this->assertSame([
            ilObjTestFolder::ASS_PROC_LOCK_MODE_NONE,
            ilObjTestFolder::ASS_PROC_LOCK_MODE_FILE,
            ilObjTestFolder::ASS_PROC_LOCK_MODE_DB
        ], ilObjTestFolder::getValidAssessmentProcessLockModes());
    }

    /**
     * @dataProvider provideQuestionTypeArrays
     * @throws ReflectionException|Exception
     */
    public function testFetchScoringAdjustableTypes(array $input, array $output): void
    {
        $il_obj_test_folder = $this->createInstanceOf(ilObjTestFolder::class);

        $this->assertSame($output, $il_obj_test_folder->fetchScoringAdjustableTypes($input));
    }

    public static function provideQuestionTypeArrays(): array
    {
        return [
            'assNumeric' => [
                [
                    ['type_tag' => assNumeric::class]
                ],
                [
                    ['type_tag' => assNumeric::class]
                ]
            ],
            'assNumeric_assFormulaQuestion' => [
                [
                    ['type_tag' => assNumeric::class],
                    ['type_tag' => assFormulaQuestion::class]
                ],
                [
                    ['type_tag' => assNumeric::class]
                ]
            ],
            'assFormulaQuestion' => [
                [
                    ['type_tag' => assFormulaQuestion::class]
                ],
                []
            ]
        ];
    }
}
