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
 * Interface ilGuiAnswerScoringAdjustable
 *
 * This is the extended interface for questions, which support the relevant object-class methods for post-test-scoring
 * adjustments. This is the gui-part of the interfaces.
 *
 * @see ObjScoringAdjustable
 * @see ilGuiQuestionScoringAdjustable
 *
 * @author		Maximilian Becker <mbecker@databay.de>
 *
 * @version		$Id$
 *
 * @ingroup 	ModulesTestQuestionPool
 */
interface ilGuiAnswerScoringAdjustable
{
    /**
     * Adds the answer specific form parts to a question property form gui.
     */
    public function populateAnswerSpecificFormPart(ilPropertyFormGUI $form): ilPropertyFormGUI;

    /**
     * Extracts the answer specific values from $_POST and applies them to the data object.
     */
    public function writeAnswerSpecificPostData(ilPropertyFormGUI $form): void;

    /**
     * Returns a list of postvars which will be suppressed in the form output when used in scoring adjustment.
     * The form elements will be shown disabled, so the users see the usual form but can only edit the settings, which
     * make sense in the given context.
     *
     * E.g. array('cloze_type', 'image_filename')
     *
     * @return string[]
     */
    public function getAfterParticipationSuppressionAnswerPostVars(): array;
}
