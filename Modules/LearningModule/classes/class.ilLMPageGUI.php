<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageObjectGUI.php");
include_once("./Modules/LearningModule/classes/class.ilLMPage.php");

/**
 * Extension of ilPageObjectGUI for learning modules 
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ilCtrl_Calls ilLMPageGUI: ilPageEditorGUI, ilMDEditorGUI, ilEditClipboardGUI, ilMediaPoolTargetSelector, ilCommonActionDispatcherGUI, ilPageObjectGUI
 * @ilCtrl_Calls ilLMPageGUI: ilNewsItemGUI, ilQuestionEditGUI, ilAssQuestionFeedbackEditingGUI, ilPageMultiLangGUI, ilPropertyFormGUI
 * @ingroup ModuleLearningModule
 */
class ilLMPageGUI extends ilPageObjectGUI
{
	/**
	 * Constructor
	 */
	function __construct($a_id = 0, $a_old_nr = 0,
		$a_prevent_get_id = false, $a_lang = "")
	{
		parent::__construct("lm", $a_id, $a_old_nr, $a_prevent_get_id, $a_lang);

		include_once("./Modules/LearningModule/classes/class.ilObjContentObject.php");
		$this->getPageConfig()->setUseStoredQuestionTries(ilObjContentObject::_lookupStoreTries($this->getPageObject()->getParentId()));
	}

	/**
	 * On feedback editing forwarding
	 */
	function onFeedbackEditingForwarding()
	{
		global $lng;

		if (strtolower($_GET["cmdClass"]) == "ilassquestionfeedbackeditinggui")
		{
			include_once("./Modules/LearningModule/classes/class.ilObjContentObject.php");
			if (ilObjContentObject::_lookupDisableDefaultFeedback($this->getPageObject()->getParentId()))
			{
				ilUtil::sendInfo($lng->txt("cont_def_feedb_deactivated"));
			}
			else
			{
				ilUtil::sendInfo($lng->txt("cont_def_feedb_activated"));
			}
		}
	}

	/**
	 * Process answer
	 */
	function processAnswer()
	{
		global $ilUser, $ilDB, $lng, $ilPluginAdmin, $ilLog;

		parent::processAnswer();

		//
		// Send notifications to authors that want to be informed on blocked users
		//

		$parent_id = ilPageObject::lookupParentId((int) $_GET["page_id"], "lm");

		// is restriction mode set?
		include_once("./Modules/LearningModule/classes/class.ilObjContentObject.php");
		if (ilObjContentObject::_lookupRestrictForwardNavigation($parent_id))
		{
			// check if user is blocked
			$id = ilUtil::stripSlashes($_POST["id"]);

			include_once("./Services/COPage/classes/class.ilPageQuestionProcessor.php");
			$as = ilPageQuestionProcessor::getAnswerStatus($id, $ilUser->getId());
			// get question information
			include_once("./Modules/TestQuestionPool/classes/class.ilAssQuestionList.php");
			$qlist = new ilAssQuestionList($ilDB, $lng, $ilPluginAdmin, 0);
			$qlist->addFieldFilter("question_id", array($id));
			$qlist->load();
			$qdata = $qlist->getQuestionDataArray();
			// has the user been blocked?
			if ($as["try"] >= $qdata[$as["qst_id"]]["nr_of_tries"] && $qdata[$as["qst_id"]]["nr_of_tries"] > 0 && !$as["passed"])
			{
				include_once "./Services/Notification/classes/class.ilNotification.php";
				$users = ilNotification::getNotificationsForObject(ilNotification::TYPE_LM_BLOCKED_USERS, $parent_id);

				if (count($users) > 0)
				{
					include_once("./Modules/LearningModule/classes/class.ilLMMailNotification.php");
					$not = new ilLMMailNotification();
					$not->setType(ilLMMailNotification::TYPE_USER_BLOCKED);
					$not->setQuestionId($id);
					$not->setRefId((int) $_GET["ref_id"]);
					$not->setRecipients($users);
					$not->send();
				}
			}

		}
	}

}

?>
