<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

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
	 * 
	 * @param ilPropertyFormGUI $form
	 *
	 * @return ilPropertyFormGUI
	 */
	public function populateQuestionSpecificFormPart(ilPropertyFormGUI $form );

	/**
	 * Extracts the question specific values from $_POST and applies them
	 * to the data object.
	 * 
	 * @param bool $always If true, a check for form validity is omitted.
	 *
	 * @return void
	 */
	public function writeQuestionSpecificPostData(ilPropertyFormGUI $form);

	/**
	 * Returns a list of postvars which will be suppressed in the form output when used in scoring adjustment.
	 * The form elements will be shown disabled, so the users see the usual form but can only edit the settings, which
	 * make sense in the given context.
	 *
	 * E.g. array('cloze_type', 'image_filename')
	 *
	 * @return string[]
	 */
	public function getAfterParticipationSuppressionQuestionPostVars();

	/**
	 * Returns an html string containing a question specific representation of the answers so far
	 * given in the test for use in the right column in the scoring adjustment user interface.
	 * 
	 * @param array $relevant_answers
	 *
	 * @return string
	 */
	public function getAggregatedAnswersView($relevant_answers);
}