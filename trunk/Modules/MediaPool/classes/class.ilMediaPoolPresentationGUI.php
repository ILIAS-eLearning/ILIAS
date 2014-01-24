<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Top level GUI class for media pools.
*
* @author Alex Killing <alex.killing@gmx.de>
*
* @version $Id$
*
* @ilCtrl_Calls ilMediaPoolPresentationGUI: ilObjMediaPoolGUI
*
* @ingroup ModulesMediaPool
*/
class ilMediaPoolPresentationGUI
{
	/**
	* ilias object
	* @var object ilias
	* @access public
	*/
	var $ilias;
	var $tpl;
	var $lng;
	var $objDefinition;

	/**
	* Constructor
	* @access	public
	*/
	function ilMediaPoolPresentationGUI()
	{
		global $ilias, $tpl, $lng, $objDefinition, $ilCtrl,
			$rbacsystem;
		
		$lng->loadLanguageModule("content");

		$this->ctrl =& $ilCtrl;

		// initiate variables
		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->objDefinition =& $objDefinition;
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $tpl, $ilCtrl, $ilAccess, $ilNavigationHistory;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd("");

		// add entry to navigation history
		if ($ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			$ilNavigationHistory->addItem($_GET["ref_id"],
				"ilias.php?baseClass=ilMediaPoolPresentationGUI&ref_id=".$_GET["ref_id"], "mep");
		}

		switch($next_class)
		{
			case "ilobjmediapoolgui":
				require_once ("./Modules/MediaPool/classes/class.ilObjMediaPoolGUI.php");
				$mep_gui =& new ilObjMediaPoolGUI($_GET["ref_id"]);
				$ilCtrl->forwardCommand($mep_gui);
				break;

			default:
				$this->ctrl->setCmdClass("ilobjmediapoolgui");
				//$this->ctrl->setCmd("");
				return $this->executeCommand();
				break;
		}
	}

}
?>