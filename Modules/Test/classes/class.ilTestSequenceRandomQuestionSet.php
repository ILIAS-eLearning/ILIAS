<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestSequence.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestSequenceRandomQuestionSet extends ilTestSequence
{
	/**
	 * !!! LEGACY CODE !!!
	 *
	 * Checkes wheather a random test has already created questions for a given pass or not
	 *
	 * @access private
	 * @param $active_id Active id of the test
	 * @param $pass Pass of the test
	 * @return boolean TRUE if the test already contains questions, FALSE otherwise
	 */
	function hasRandomQuestionsForPass($active_id, $pass)
	{
		global $ilDB;
		$result = $ilDB->queryF("SELECT test_random_question_id FROM tst_test_rnd_qst WHERE active_fi = %s AND pass = %s",
			array('integer','integer'),
			array($active_id, $pass)
		);
		return ($result->numRows() > 0) ? true : false;
	}
}