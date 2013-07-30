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
 * @ilCtrl_Calls ilLMPageGUI: ilNewsItemGUI
 * @ingroup ModuleLearningModule
 */
class ilLMPageGUI extends ilPageObjectGUI
{
	/**
	 * Constructor
	 */
	function __construct($a_id = 0, $a_old_nr = 0,
		$a_prevent_get_id = false)
	{
		parent::__construct("lm", $a_id, $a_old_nr, $a_prevent_get_id);
	}

	/**
	 * Init page config
	 *
	 * @param
	 * @return
	 */
	function initPageConfig()
	{
		include_once("./Modules/LearningModule/classes/class.ilLMPageConfig.php");
		$cfg = new ilLMPageConfig();
		$this->setPageConfig($cfg);
	}	
	
	/**
	 * Init page object
	 *
	 * @param
	 */
	function initPageObject()
	{
		$page = new ilLMPage($this->getId(), $this->getOldNr());
		$this->setPageObject($page);
	}
	
}

?>
