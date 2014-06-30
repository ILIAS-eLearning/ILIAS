<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestOutputGUI.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 *
 * @ilCtrl_Calls ilTestPlayerFixedQuestionSetGUI: ilAssGenFeedbackPageGUI
 * @ilCtrl_Calls ilTestPlayerFixedQuestionSetGUI: ilAssSpecFeedbackPageGUI
 * @ilCtrl_Calls ilTestPlayerFixedQuestionSetGUI: ilAssQuestionHintRequestGUI
 * @ilCtrl_Calls ilTestPlayerFixedQuestionSetGUI: ilTestSignatureGUI
 * @ilCtrl_Calls ilTestPlayerFixedQuestionSetGUI: ilTestSubmissionReviewGUI
 */
class ilTestPlayerFixedQuestionSetGUI extends ilTestOutputGUI
{
	protected function performTestPassFinishedTasks($finishedPass)
	{
		if( $this->object->isSkillServiceToBeConsidered() )
		{
			$this->performSkillTriggering(
				$this->testSession->getActiveId(), $finishedPass, $this->testSession->getUserId()
			);
		}

		if( $this->object->getEnableArchiving() )
		{
			$this->archiveParticipantSubmission($this->testSession->getActiveId(), $finishedPass);
		}
	}

	private function performSkillTriggering($activeId, $finishedPass, $userId)
	{
		require_once 'Modules/Test/classes/class.ilTestSkillEvaluation.php';
		$skillEvaluation = new ilTestSkillEvaluation($this->db, $this->object);

		$skillEvaluation->init()->trigger($activeId, $finishedPass, $userId);
	}
}