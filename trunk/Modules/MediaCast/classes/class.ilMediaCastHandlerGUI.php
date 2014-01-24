<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Handles user interface for media casts
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilMediaCastHandlerGUI: ilObjMediaCastGUI
*
* @ingroup ModulesMediaCast
*/
class ilMediaCastHandlerGUI
{
	function ilMediaCastHandlerGUI()
	{
		global $ilCtrl, $lng, $ilAccess, $ilias, $ilNavigationHistory;

		// initialisation stuff
		$this->ctrl =&  $ilCtrl;
		
	}
	
	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $lng, $ilAccess, $tpl, $ilNavigationHistory;
		
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass($this);
		if ($next_class == "")
		{
			$this->ctrl->setCmdClass("ilobjmediacastgui");
			$next_class = $this->ctrl->getNextClass($this);
		}

		// add entry to navigation history
		if ($ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			$ilNavigationHistory->addItem($_GET["ref_id"],
				"ilias.php?baseClass=ilMediaCastHandlerGUI&cmd=listItems&ref_id=".$_GET["ref_id"], "mcst");
		}

		switch ($next_class)
		{
			case 'ilobjmediacastgui':
				require_once "./Modules/MediaCast/classes/class.ilObjMediaCastGUI.php";
				$mc_gui =& new ilObjMediaCastGUI("", (int) $_GET["ref_id"], true, false);
				$this->ctrl->forwardCommand($mc_gui);
				break;
		}

		$tpl->show();
	}

}
