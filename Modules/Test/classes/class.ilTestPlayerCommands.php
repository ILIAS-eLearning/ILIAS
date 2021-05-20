<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author		Björn Heyser <bheyser@databay.de>
 *
 * @version		$Id$
 *
 * @package		Modules/Test
 */
class ilTestPlayerCommands
{
    const START_TEST = 'startTest';
    const INIT_TEST = 'initTest';
    const START_PLAYER = 'startPlayer';
    const RESUME_PLAYER = 'resumePlayer';
    
    const DISPLAY_ACCESS_CODE = 'displayAccessCode';
    const ACCESS_CODE_CONFIRMED = 'accessCodeConfirmed';
    
    const SHOW_QUESTION = 'showQuestion';
    
    const PREVIOUS_QUESTION = 'previousQuestion';
    const NEXT_QUESTION = 'nextQuestion';

    const EDIT_SOLUTION = 'editSolution';
    const MARK_QUESTION = 'markQuestion';
    const MARK_QUESTION_SAVE = 'markQuestionAndSaveIntermediate';
    const UNMARK_QUESTION = 'unmarkQuestion';
    const UNMARK_QUESTION_SAVE = 'unmarkQuestionAndSaveIntermediate';

    const SUBMIT_INTERMEDIATE_SOLUTION = 'submitIntermediateSolution';
    const SUBMIT_SOLUTION = 'submitSolution';
    const SUBMIT_SOLUTION_AND_NEXT = 'submitSolutionAndNext';
    // fau: testNav - define new commands
    const REVERT_CHANGES = 'revertChanges';
    const DETECT_CHANGES = 'detectChanges';
    // fau.
    const DISCARD_SOLUTION = 'discardSolution';
    const SKIP_QUESTION = 'skipQuestion';
    const SHOW_INSTANT_RESPONSE = 'showInstantResponse';
    
    const CONFIRM_HINT_REQUEST = 'confirmHintRequest';
    const SHOW_REQUESTED_HINTS_LIST = 'showRequestedHintList';

    const QUESTION_SUMMARY = 'outQuestionSummary';
    const QUESTION_SUMMARY_INC_OBLIGATIONS = 'outQuestionSummaryWithObligationsInfo';
    const QUESTION_SUMMARY_OBLIGATIONS_ONLY = 'outObligationsOnlySummary';
    const TOGGLE_SIDE_LIST = 'toggleSideList';
    
    const SHOW_QUESTION_SELECTION = 'showQuestionSelection';
    const UNFREEZE_ANSWERS = 'unfreezeCheckedQuestionsAnswers';
    
    const AUTO_SAVE = 'autosave';
    const AUTO_SAVE_ON_TIME_LIMIT = 'autosaveOnTimeLimit';
    const REDIRECT_ON_TIME_LIMIT = 'redirectAfterAutosave';

    const SUSPEND_TEST = 'suspendTest';
    const FINISH_TEST = 'finishTest';
    const AFTER_TEST_PASS_FINISHED = 'afterTestPassFinished';
    const SHOW_FINAL_STATMENT = 'showFinalStatement';
    
    const BACK_TO_INFO_SCREEN = 'backToInfoScreen';
    const BACK_FROM_FINISHING = 'backFromFinishing';

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
    public static function isTestExecutionCommand($cmd)
    {
        return !in_array($cmd, self::$nonExecutionCommands);
    }
}
