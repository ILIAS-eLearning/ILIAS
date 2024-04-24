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

/**
* Unit tests
*
* @author Matheus Zych <mzych@databay.de>
*
* @ingroup components\ILIASTestQuestionPool
*
* This test was automatically generated.
*/
class assQuestionGUITest extends assBaseTestCase
{
    protected $backupGlobals = false;

    private assQuestionGUI $object;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilLog();

        $this->object = new class () extends assQuestionGUI {
            public function getSpecificFeedbackOutput(array $userSolution): string
            {
                return '';
            }

            public function getSolutionOutput(
                int $active_id,
                ?int $pass = null,
                bool $graphical_output = false,
                bool $result_output = false,
                bool $show_question_only = true,
                bool $show_feedback = false,
                bool $show_correct_solution = false,
                bool $show_manual_scoring = false,
                bool $show_question_text = true,
                bool $show_inline_feedback = true
            ): string {
                return '';
            }

            public function editQuestion(bool $checkonly = false): bool
            {
            }

            public function getPreview(
                bool $show_question_only = false,
                bool $show_inline_feedback = false
            ): string {
            }

            public function getTestOutput(
                int $active_id,
                int $pass,
                bool $is_question_postponed = false,
                array|bool $user_post_solutions = false,
                bool $show_specific_inline_feedback = false
            ): string {
            }
        };
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(assQuestionGUI::class, $this->object);
    }
}
