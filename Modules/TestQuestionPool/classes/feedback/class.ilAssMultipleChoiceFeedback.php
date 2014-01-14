<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/feedback/class.ilAssConfigurableMultiOptionQuestionFeedback.php';

/**
 * feedback class for assMultipleChoice questions
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 * 
 * @package		Modules/TestQuestionPool
 */
class ilAssMultipleChoiceFeedback extends ilAssConfigurableMultiOptionQuestionFeedback
{
	/**
	 * table name for specific feedback
	 */
	const SPECIFIC_QUESTION_TABLE_NAME = 'qpl_qst_mc';
	
	/**
	 * returns the table name for specific question itself
	 * 
	 * @return string $specificFeedbackTableName
	 */
	protected function getSpecificQuestionTableName()
	{
		return self::SPECIFIC_QUESTION_TABLE_NAME;
	}
}
