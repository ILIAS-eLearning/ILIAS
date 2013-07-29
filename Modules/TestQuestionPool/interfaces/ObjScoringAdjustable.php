<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/TestQuestionPool/interfaces/ObjQuestionSaveable.php';

/**
 * Interface ObjScoringAdjustable
 * 
 * This is the extended interface for questions, which support the relevant object-class methods for post-test-scoring
 * adjustments. This is the object-part.
 * 
 * @see GuiScoringAdjustable
 *
 * @author		Maximilian Becker <mbecker@databay.de>
 * 
 * @version		$Id$
 * 
 * @ingroup 	ModulesTestQuestionPool
 */
interface ObjScoringAdjustable extends ObjQuestionSaveable
{
	/**
	 * Saves a record to the question types additional data table.
	 * 
	 * @return mixed
	 */
	public function saveAdditionalQuestionDataToDb();

	/**
	 * Saves the answer specific records into a question types answer table.
	 * 
	 * @return mixed
	 */
	public function saveAnswerSpecificDataToDb();
}