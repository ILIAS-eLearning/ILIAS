<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageObjectGUI.php");
include_once("./Modules/Glossary/classes/class.ilGlossaryDefPage.php");

/**
 * Glossary definition page GUI class
 * 
 * @author Alex Killing <alex.killing@gmx.de> 
 *
 * @ilCtrl_Calls ilGlossaryDefPageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMDEditorGUI
 * @ilCtrl_Calls ilGlossaryDefPageGUI: ilPublicUserProfileGUI, ilNoteGUI
 * @ilCtrl_Calls ilGlossaryDefPageGUI: ilPropertyFormGUI, ilInternalLinkGUI
 *
 * @ingroup ModulesGlossary
 */
class ilGlossaryDefPageGUI extends ilPageObjectGUI
{
	/**
	* Constructor
	*/
	function __construct($a_id = 0, $a_old_nr = 0)
	{
		global $tpl;

		parent::__construct("gdf", $a_id, $a_old_nr);	
	}
	
} 
?>
