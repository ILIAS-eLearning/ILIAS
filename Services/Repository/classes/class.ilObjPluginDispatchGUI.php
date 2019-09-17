<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObject2GUI.php");
require_once('./Services/Repository/classes/class.ilObjectPlugin.php');

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
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	* Constructor.
	*/
	function __construct()
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
	}
	
	function executeCommand()
	{
		$ilCtrl = $this->ctrl;
		
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
		$ilCtrl = $this->ctrl;
		
		$type = ilObject::_lookupType($_GET["ref_id"], true);
		if ($type != "")
		{
			include_once("./Services/Component/classes/class.ilPlugin.php");
			$plugin = ilObjectPlugin::getPluginObjectByType($type);
			if ($plugin)
			{
				$gui_cn = "ilObj".$plugin->getPluginName()."GUI";
				$ilCtrl->setParameterByClass($gui_cn, "ref_id", $_GET["ref_id"]);
				$ilCtrl->redirectByClass($gui_cn, $_GET["forwardCmd"]);
			}
		}
	}
	
	
}
