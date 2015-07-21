<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestOutputGUI.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 *
 * @ilCtrl_Calls ilTestPlayerRandomQuestionSetGUI: ilAssGenFeedbackPageGUI
 * @ilCtrl_Calls ilTestPlayerRandomQuestionSetGUI: ilAssSpecFeedbackPageGUI
 * @ilCtrl_Calls ilTestPlayerRandomQuestionSetGUI: ilAssQuestionHintRequestGUI
 * @ilCtrl_Calls ilTestPlayerRandomQuestionSetGUI: ilTestSignatureGUI
 * @ilCtrl_Calls ilTestPlayerRandomQuestionSetGUI: ilAssQuestionPageGUI
 * @ilCtrl_Calls ilTestPlayerRandomQuestionSetGUI: ilTestSubmissionReviewGUI
 * @ilCtrl_Calls ilTestPlayerRandomQuestionSetGUI: ilTestPasswordProtectionGUI
 */
class ilTestPlayerRandomQuestionSetGUI extends ilTestOutputGUI
{
	protected function buildTestPassQuestionList()
	{
		global $ilPluginAdmin;

		require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionList.php';
		$questionList = new ilAssQuestionList($this->db, $this->lng, $ilPluginAdmin);
		
		$questionList->setParentObjId($this->object->getId());

		$questionList->setQuestionInstanceTypeFilter(ilAssQuestionList::QUESTION_INSTANCE_TYPE_DUPLICATES);

		$questionList->setQuestionIdsFilter($this->testSequence->getQuestionIds());

		return $questionList;
	}
}