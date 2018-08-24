<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageObjectGUI.php");
include_once("./Services/Authentication/classes/class.ilLoginPage.php");

/**
 * Login page GUI class
 * 
 * @author Alex Killing <alex.killing@gmx.de> 
 *
 * @ilCtrl_Calls ilLoginPageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMDEditorGUI
 * @ilCtrl_Calls ilLoginPageGUI: ilPublicUserProfileGUI, ilNoteGUI
 * @ilCtrl_Calls ilLoginPageGUI: ilPropertyFormGUI, ilInternalLinkGUI
 *
 * @ingroup ServicesAuthentication
 */
class ilLoginPageGUI extends ilPageObjectGUI
{
	/**
	* Constructor
	*/
	function __construct($a_id = 0, $a_old_nr = 0)
	{
		global $tpl;

		parent::__construct("auth", $a_id, $a_old_nr);	
	}
	
} 
?>
