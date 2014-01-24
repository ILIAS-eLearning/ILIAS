<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObject2GUI.php");

/*
* Dispatcher to all repository object plugins
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ilCtrl_Calls ilObjPluginDispatchGUI:
* @ingroup ServicesRepository
*/
class ilObjPluginDispatchGUI
{
	/**
	* Constructor.
	*/
	function __construct()
	{
	}
	
	function &executeCommand()
	{
		global $ilCtrl;
		
		$next_class = $ilCtrl->getNextClass();
		$cmd_class = $ilCtrl->getCmdClass();

		if ($cmd_class != "ilobjplugindispatchgui" && $cmd_class != "")
		{
			$class_path = $ilCtrl->lookupClassPath($next_class);
			include_once($class_path);
			$class_name = $ilCtrl->getClassForClasspath($class_path);
//echo "-".$class_name."-".$class_path."-";
			$this->gui_obj = new $class_name($_GET["ref_id"]);
			$ilCtrl->forwardCommand($this->gui_obj);
		}
		else
		{
			$this->processCommand($ilCtrl->getCmd());
		}
	}
	
	/**
	* Process command
	*/
	function processCommand($a_cmd)
	{
		switch ($a_cmd)
		{
			case "forward":
				$this->forward();
				break;
		}
	}
	
	/**
	* Forward command to plugin
	*/
	function forward()
	{
		global $ilCtrl;
		
		$type = ilObject::_lookupType($_GET["ref_id"], true);
		if ($type != "")
		{
			include_once("./Services/Component/classes/class.ilPlugin.php");
			$pl_name = ilPlugin::lookupNameForId(IL_COMP_SERVICE, "Repository", "robj", $type);
			if ($pl_name != "")
			{
				$gui_cn = "ilObj".$pl_name."GUI";
				$ilCtrl->setParameterByClass($gui_cn, "ref_id", $_GET["ref_id"]);
				$ilCtrl->redirectByClass($gui_cn, $_GET["forwardCmd"]);
			}
		}
	}
	
	
}
