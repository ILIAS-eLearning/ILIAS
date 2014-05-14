<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageConfig.php");

/**
 * Learning module page configuration 
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesLearningModule
 */
class ilLMPageConfig extends ilPageConfig
{
	/**
	 * Init
	 */
	function init()
	{
		$lm_set = new ilSetting("lm");
		
		$this->setPreventHTMLUnmasking(false);
		$this->setPreventRteUsage(true);
		$this->setUseAttachedContent(true);
		$this->setIntLinkHelpDefaultType("StructureObject");
		$this->setIntLinkHelpDefaultId($_GET["ref_id"]);
		$this->removeIntLinkFilter("File");
		$this->setEnableActivation(true);
		$this->setEnableSelfAssessment(true, false);
		$this->setEnableInternalLinks(true);
		$this->setEnableKeywords(true);
		$this->setEnableInternalLinks(true);
		$this->setEnableAnchors(true);
		$this->setMultiLangSupport(true);
		if ($lm_set->get("time_scheduled_page_activation"))
		{
			$this->setEnableScheduledActivation(true);
		}

		$mset = new ilSetting("mobs");
		if ($mset->get("mep_activate_pages"))
		{
			$this->setEnablePCType("ContentInclude", true);
		}
	}

	/**
	 * Object specific configuration 
	 *
	 * @param int $a_obj_id object id
	 */
	function configureByObjectId($a_obj_id)
	{
		if ($a_obj_id > 0)
		{
			include_once("./Modules/LearningModule/classes/class.ilObjLearningModule.php");
			$this->setDisableDefaultQuestionFeedback(ilObjLearningModule::_lookupDisableDefaultFeedback($a_obj_id));
			
			if (ilObjContentObject::isOnlineHelpModule($a_obj_id, true))
			{
				$this->setEnableSelfAssessment(false, false);
			}
		}
	}
	
}

?>
