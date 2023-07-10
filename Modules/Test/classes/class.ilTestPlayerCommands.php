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
 * @author		Björn Heyser <bheyser@databay.de>
 *
 * @version		$Id$
 *
 * @package		Modules/Test
 */
class ilTestPlayerCommands
{
    public const START_TEST = 'startTest';
    public const INIT_TEST = 'initTest';
    public const START_PLAYER = 'startPlayer';
    public const RESUME_PLAYER = 'resumePlayer';

    public const DISPLAY_ACCESS_CODE = 'displayAccessCode';
    public const ACCESS_CODE_CONFIRMED = 'accessCodeConfirmed';

    public const SHOW_QUESTION = 'showQuestion';

    public const PREVIOUS_QUESTION = 'previousQuestion';
    public const NEXT_QUESTION = 'nextQuestion';

    public const EDIT_SOLUTION = 'editSolution';
    public const MARK_QUESTION = 'markQuestion';
    public const MARK_QUESTION_SAVE = 'markQuestionAndSaveIntermediate';
    public const UNMARK_QUESTION = 'unmarkQuestion';
    public const UNMARK_QUESTION_SAVE = 'unmarkQuestionAndSaveIntermediate';

    public const SUBMIT_INTERMEDIATE_SOLUTION = 'submitIntermediateSolution';
    public const SUBMIT_SOLUTION = 'submitSolution';
    public const SUBMIT_SOLUTION_AND_NEXT = 'submitSolutionAndNext';
    // fau: testNav - define new commands
    public const REVERT_CHANGES = 'revertChanges';
    public const DETECT_CHANGES = 'detectChanges';
    // fau.
    public const DISCARD_SOLUTION = 'discardSolution';
    public const SKIP_QUESTION = 'skipQuestion';
    public const SHOW_INSTANT_RESPONSE = 'showInstantResponse';

    public const CONFIRM_HINT_REQUEST = 'confirmHintRequest';
    public const SHOW_REQUESTED_HINTS_LIST = 'showRequestedHintList';

    public const QUESTION_SUMMARY = 'outQuestionSummary';
    public const QUESTION_SUMMARY_INC_OBLIGATIONS = 'outQuestionSummaryWithObligationsInfo';
    public const QUESTION_SUMMARY_OBLIGATIONS_ONLY = 'outObligationsOnlySummary';
    public const TOGGLE_SIDE_LIST = 'toggleSideList';

    public const SHOW_QUESTION_SELECTION = 'showQuestionSelection';
    public const UNFREEZE_ANSWERS = 'unfreezeCheckedQuestionsAnswers';

    public const AUTO_SAVE = 'autosave';
    public const AUTO_SAVE_ON_TIME_LIMIT = 'autosaveOnTimeLimit';
    public const REDIRECT_ON_TIME_LIMIT = 'redirectAfterAutosave';

    public const SUSPEND_TEST = 'suspendTest';
    public const FINISH_TEST = 'finishTest';
    public const AFTER_TEST_PASS_FINISHED = 'afterTestPassFinished';
    public const SHOW_FINAL_STATMENT = 'showFinalStatement';

    public const BACK_TO_INFO_SCREEN = 'backToInfoScreen';
    public const BACK_FROM_FINISHING = 'backFromFinishing';

    /**
     * @var array
     */
    private static $nonExecutionCommands = array(
// fau: testNav - declare DETECT_CHANGES as non execution command
        self::DETECT_CHANGES,
// fau.
        self::AUTO_SAVE, self::AUTO_SAVE_ON_TIME_LIMIT, self::REDIRECT_ON_TIME_LIMIT,
        self::AFTER_TEST_PASS_FINISHED, self::SHOW_FINAL_STATMENT
    );

    /**
     * @param $cmd
     * @return bool
     */
    public static function isTestExecutionCommand($cmd): bool
    {
        return !in_array($cmd, self::$nonExecutionCommands);
    }
}
