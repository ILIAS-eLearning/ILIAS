<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/feedback/class.ilAssConfigurableMultiOptionQuestionFeedback.php';
/**
 * @version		$Id$
 *
 * @package     Modules/TestQuestionPool
 */
class ilAssLongMenuFeedback  extends ilAssConfigurableMultiOptionQuestionFeedback
{

	const SPECIFIC_QUESTION_TABLE_NAME = 'qpl_qst_lome';

	protected function getSpecificQuestionTableName()
	{
		return self::SPECIFIC_QUESTION_TABLE_NAME;
	}
}