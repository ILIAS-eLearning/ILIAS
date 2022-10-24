<?php

declare(strict_types=1);

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

/**
 * Class ilTestQuestionRelatedObjectivesListTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestQuestionRelatedObjectivesListTest extends ilTestBaseTestCase
{
    private ilTestQuestionRelatedObjectivesList $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestQuestionRelatedObjectivesList();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestQuestionRelatedObjectivesList::class, $this->testObj);
    }

    public function testAddQuestionRelatedObjectives(): void
    {
        $expected = [
            1 => [1, 2, 3, 4],
            2 => [5, 6, 7, 8],
            1236 => [9, 10, 11, 12],
            12 => [13, 14, 15, 16]
        ];
        foreach ($expected as $key => $value) {
            $this->testObj->addQuestionRelatedObjectives($key, $value);
        }

        $this->assertEquals($expected[1236], $this->testObj->getQuestionRelatedObjectives(1236));
    }

    public function testHasQuestionRelatedObjectives(): void
    {
        $expected = [
            1 => [1, 2, 3, 4],
            2 => [5, 6, 7, 8],
            1236 => [9, 10, 11, 12],
            12 => [13, 14, 16]
        ];
        foreach ($expected as $key => $value) {
            $this->testObj->addQuestionRelatedObjectives($key, $value);
        }

        $this->assertEquals(3, $this->testObj->hasQuestionRelatedObjectives(12));
    }
}
