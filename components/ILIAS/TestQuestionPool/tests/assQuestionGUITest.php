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

        $this->object = new class extends assQuestionGUI{
            public function getSpecificFeedbackOutput(array $userSolution): string
            {
                return '';
            }

            public function getSolutionOutput($active_id, $pass = null, $graphicalOutput = false, $result_output = false, $show_question_only = true, $show_feedback = false, $show_correct_solution = false, $show_manual_scoring = false, $show_question_text = true): string
            {
                return '';
            }

            public function getPreview($show_question_only = false, $showInlineFeedback = false): void {}

            public function getTestOutput($active_id, $pass, $is_question_postponed, $user_post_solutions, $show_specific_inline_feedback): void {}
        };
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(assQuestionGUI::class, $this->object);
    }
}