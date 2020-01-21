<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

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
     *
     * @param ilPropertyFormGUI $form
     *
     * @return ilPropertyFormGUI
     */
    public function populateAnswerSpecificFormPart(ilPropertyFormGUI $form);

    /**
     * Extracts the answer specific values from $_POST and applies them to the data object.
     *
     * @param bool $always If true, a check for form validity is omitted.
     *
     * @return void
     */
    public function writeAnswerSpecificPostData(ilPropertyFormGUI $form);

    /**
     * Returns a list of postvars which will be suppressed in the form output when used in scoring adjustment.
     * The form elements will be shown disabled, so the users see the usual form but can only edit the settings, which
     * make sense in the given context.
     *
     * E.g. array('cloze_type', 'image_filename')
     *
     * @return string[]
     */
    public function getAfterParticipationSuppressionAnswerPostVars();
}
