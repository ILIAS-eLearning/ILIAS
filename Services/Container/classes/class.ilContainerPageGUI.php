<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageObjectGUI.php");
include_once("./Services/Container/classes/class.ilContainerPage.php");

/**
 * Container page GUI class
 * 
 * @author Alex Killing <alex.killing@gmx.de> 
 *
 * @ilCtrl_Calls ilContainerPageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMDEditorGUI
 * @ilCtrl_Calls ilContainerPageGUI: ilPublicUserProfileGUI, ilNoteGUI
 * @ilCtrl_Calls ilContainerPageGUI: ilPropertyFormGUI, ilInternalLinkGUI
 *
 * @ingroup ServicesContainer
 */
class ilContainerPageGUI extends ilPageObjectGUI
{
	/**
	* Constructor
	*/
	function __construct($a_type, $a_id = 0, $a_old_nr = 0)
	{
		global $tpl;

		parent::__construct($a_type, $a_id, $a_old_nr);	
	}
	
	/**
	 * Init page config
	 *
	 * @param
	 * @return
	 */
	function initPageConfig()
	{
		include_once("./Services/Container/classes/class.ilContainerPageConfig.php");
		$cfg = new ilContainerPageConfig();
		$this->setPageConfig($cfg);
	}	

} 
?>
