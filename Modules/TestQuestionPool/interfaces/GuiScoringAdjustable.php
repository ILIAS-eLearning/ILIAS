<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface GuiScoringAdjustable
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
interface GuiScoringAdjustable 
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
	 * Adds the answer specific form parts to a question property form gui.
	 * 
	 * @param ilPropertyFormGUI $form
	 *
	 * @return ilPropertyFormGUI
	 */
	public function populateAnswerSpecificFormPart(ilPropertyFormGUI $form );

	/**
	 * Extracts the question specific values from $_POST and applies them
	 * to the data object.
	 * 
	 * @param bool $always If true, a check for form validity is omitted.
	 *
	 * @return void
	 */
	public function writeQuestionSpecificPostData($always);

	/**
	 * Extracts the answer specific values from $_POST and applies them 
	 * to the data object.
	 * 
	 * @param bool $always If true, a check for form validity is omitted.
	 *
	 * @return void
	 */
	public function writeAnswerSpecificPostData($always);
}