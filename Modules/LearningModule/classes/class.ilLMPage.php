<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Extension of ilPageObject for learning modules 
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesLearningModule
 */
class ilLMPage extends ilPageObject
{
	/**
	 * Get parent type
	 *
	 * @return string parent type
	 */
	function getParentType()
	{
		return "lm";
	}
	
	/**
	 * After constructor
	 *
	 * @param
	 * @return
	 */
	function afterConstructor()
	{
		$this->getPageConfig()->configureByObjectId($this->getParentId());
	}


	/**
	 * Before page content update
	 *
	 * Note: This one is "work in progress", currently only text paragraphs call this hook
	 * It is called before the page content object invokes the update procedure of
	 * ilPageObject
	 *
	 * @param
	 * @return
	 */
	function beforePageContentUpdate($a_page_content)
	{
		if ($a_page_content->getType() == "par")
		{
			$glos = ilObjContentObject::lookupAutoGlossaries($this->getParentId());
			$a_page_content->autoLinkGlossaries($glos);
		}
	}

	/**
	 * After update content send notifications.
	 */
	function afterUpdate()
	{
		$references = ilObject::_getAllReferences($this->getParentId());
		$notification = new ilLearningModuleNotification(
			ilLearningModuleNotification::ACTION_UPDATE,
			ilNotification::TYPE_LM_PAGE,
			new ilObjLearningModule(reset($references)),
			$this->getId());

		$notification->send();
	}

}

?>
