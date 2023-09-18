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
 * Interface ilGuiQuestionScoringAdjustable
 *
 * This is the extended interface for questions, which support the relevant object-class methods for post-test-scoring
 * adjustments. This is the gui-part of the interfaces.
 *
 * In order to implement this interface from the current state in ILIAS 4.3, you need to refactor methods and extract
 * code. populateQuestionSpecificFormPart and populateAnswerSpecificFormPart reside in editQuestion.
 * The other methods, writeQuestionSpecificPostData and writeAnswerSpecificPostData are in writePostData.
 * A good example how this is done can be found in class.assClozeTestGUI.php.
 *
 * @see ObjScoringAdjustable
 *
 * @author		Maximilian Becker <mbecker@databay.de>
 *
 * @version		$Id$
 *
 * @ingroup 	ModulesTestQuestionPool
 */
interface ilGuiQuestionScoringAdjustable
{
    /**
     * Adds the question specific forms parts to a question property form gui.
     */
    public function populateQuestionSpecificFormPart(ilPropertyFormGUI $form): ilPropertyFormGUI;

    /**
     * Extracts the question specific values from $_POST and applies them
     * to the data object.
     */
    public function writeQuestionSpecificPostData(ilPropertyFormGUI $form): void;

    /**
     * Returns a list of postvars which will be suppressed in the form output when used in scoring adjustment.
     * The form elements will be shown disabled, so the users see the usual form but can only edit the settings, which
     * make sense in the given context.
     *
     * E.g. array('cloze_type', 'image_filename')
     *
     * @return string[]
     */
    public function getAfterParticipationSuppressionQuestionPostVars(): array;

    /**
     * Returns an html string containing a question specific representation of the answers so far
     * given in the test for use in the right column in the scoring adjustment user interface.
     */
    public function getAggregatedAnswersView(array $relevant_answers): string;
}
