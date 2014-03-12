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

}

?>
