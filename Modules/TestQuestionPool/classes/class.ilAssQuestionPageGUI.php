<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageObjectGUI.php");
include_once("./Modules/TestQuestionPool/classes/class.ilAssQuestionPage.php");

/**
 * Question page GUI class
 * 
 * @author Alex Killing <alex.killing@gmx.de> 
 *
 * @ilCtrl_Calls ilAssQuestionPageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMDEditorGUI
 * @ilCtrl_Calls ilAssQuestionPageGUI: ilPublicUserProfileGUI, ilNoteGUI
 * @ilCtrl_Calls ilAssQuestionPageGUI: ilPropertyFormGUI, ilInternalLinkGUI
 *
 * @ingroup ModulesTestQuestionPool
 */
class ilAssQuestionPageGUI extends ilPageObjectGUI
{
	/**
	 * Constructor
	 */
	function __construct($a_id = 0, $a_old_nr = 0)
	{
		global $tpl;

		parent::__construct("qpl", $a_id, $a_old_nr);
		$this->setEnabledPageFocus(false);
	}
	
	/**
	 * Init page config
	 */
	function initPageConfig()
	{
		include_once("./Modules/TestQuestionPool/classes/class.ilAssQuestionPageConfig.php");
		$cfg = new ilAssQuestionPageConfig();
		$this->setPageConfig($cfg);
	}	

	/**
	 * Init page object
	 */
	function initPageObject()
	{
		$page = new ilAssQuestionPage($this->getId(), $this->getOldNr());
		$this->setPageObject($page);
	}

} 
?>
