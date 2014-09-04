<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/feedback/class.ilAssConfigurableMultiOptionQuestionFeedback.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/TestQuestionPool
 */
class ilAssKprimChoiceFeedback extends ilAssConfigurableMultiOptionQuestionFeedback
{
	const SPECIFIC_QUESTION_TABLE_NAME = 'qpl_qst_kprim';

	protected function getSpecificQuestionTableName()
	{
		return self::SPECIFIC_QUESTION_TABLE_NAME;
	}
}
