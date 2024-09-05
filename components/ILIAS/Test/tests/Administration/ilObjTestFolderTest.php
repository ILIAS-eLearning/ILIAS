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

namespace Administration;

use assFormulaQuestion;
use assNumeric;
use ILIAS\Test\Administration\TestGlobalSettingsRepository;
use ILIAS\Test\Logging\TestLogViewer;
use ilObjTestFolder;
use ilTestBaseTestCase;
use ilTestQuestionPoolInvalidArgumentException;

/**
 * Class ilObjTestFolderTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilObjTestFolderTest extends ilTestBaseTestCase
{
    private ilObjTestFolder $ilObjTestFolder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ilObjTestFolder = new ilObjTestFolder();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilObjTestFolder::class, $this->ilObjTestFolder);
    }

    public function test_GetterWithoutSetter(): void
    {
        $this->assertInstanceOf(TestGlobalSettingsRepository::class, $this->ilObjTestFolder->getGlobalSettingsRepository());
        $this->assertInstanceOf(TestLogViewer::class, $this->ilObjTestFolder->getTestLogViewer());
    }

    public function test_getSkillTriggerAnswerNumberBarrier(): void
    {
        $this->assertIsInt(ilObjTestFolder::getSkillTriggerAnswerNumberBarrier());
    }

    public function test_getManualScoringTypes(): void
    {
        $this->ilObjTestFolder->_setManualScoring([]);
        $this->assertEmpty(ilObjTestFolder::_getManualScoringTypes());
    }

    public function test_isAdditionalQuestionContentEditingModePageObjectEnabled(): void
    {
        $this->assertFalse(ilObjTestFolder::isAdditionalQuestionContentEditingModePageObjectEnabled());
    }

    public function test_getValidAssessmentProcessLockModes(): void
    {
        $this->assertSame([
            ilObjTestFolder::ASS_PROC_LOCK_MODE_NONE,
            ilObjTestFolder::ASS_PROC_LOCK_MODE_FILE,
            ilObjTestFolder::ASS_PROC_LOCK_MODE_DB
        ], ilObjTestFolder::getValidAssessmentProcessLockModes());
    }

    /**
     * @dataProvider provideQuestionTypeArrays
     * @throws ilTestQuestionPoolInvalidArgumentException
     */
    public function test_fetchScoringAdjustableTypes($questionTypes, $adjustableQuestionTypes): void
    {
        $this->assertSame($adjustableQuestionTypes, $this->ilObjTestFolder->fetchScoringAdjustableTypes($questionTypes));
    }

    public static function provideQuestionTypeArrays(): array
    {
        return [
            'dataset 1: only adjustable types' => [
                'questionTypes' => [
                    ['type_tag' => assNumeric::class]
                ],
                'adjustableQuestionTypes' => [
                    ['type_tag' => assNumeric::class]

                ]
            ],
            'dataset 2: both types' => [
                'questionTypes' => [
                    ['type_tag' => assNumeric::class],
                    ['type_tag' => assFormulaQuestion::class]
                ],
                'adjustableQuestionTypes' => [
                    ['type_tag' => assNumeric::class]
                ]
            ],
            'dataset 3: only not adjustable types' => [
                'questionTypes' => [
                    ['type_tag' => assFormulaQuestion::class]
                ],
                'adjustableQuestionTypes' => []
            ]
        ];
    }
}
