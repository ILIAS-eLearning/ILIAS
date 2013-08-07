<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageObjectGUI.php");
include_once("./Services/Payment/classes/class.ilShopPage.php");

/**
 * Shop page GUI class
 * 
 * @author Alex Killing <alex.killing@gmx.de> 
 *
 * @ilCtrl_Calls ilShopPageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMDEditorGUI
 * @ilCtrl_Calls ilShopPageGUI: ilPublicUserProfileGUI, ilNoteGUI
 * @ilCtrl_Calls ilShopPageGUI: ilPropertyFormGUI, ilInternalLinkGUI
 *
 * @ingroup ServicesPayment
 */
class ilShopPageGUI extends ilPageObjectGUI
{
	/**
	 * Constructor
	 */
	function __construct($a_id = 0, $a_old_nr = 0)
	{
		global $tpl;

		parent::__construct("shop", $a_id, $a_old_nr);
		$this->setTemplateOutput(false);
	}
} 
?>
