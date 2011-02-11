<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* GUI class for personal workspace
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilPersonalDesktopGUI.php 26976 2010-12-16 13:24:38Z akill $
*
* @ilCtrl_Calls ilPersonalWorkspaceGUI: ilObjFileGUI
*/
class ilPersonalWorkspaceGUI
{
	protected $tree; // [ilTree]
	protected $root_id; // [int]
	
	/**
	 * constructor
	 */
	public function __construct()
	{
		$this->initTree();
	}
	
	/**
	 * execute command
	 */
	public function executeCommand()
	{
		global $ilCtrl, $ilTabs, $lng;

		$next_class = $ilCtrl->getNextClass();
		$ilCtrl->setReturn($this, "show");
		$cmd = $ilCtrl->getCmd("show");

		switch($next_class)
		{
			case "ilobjfilegui":
				$ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "show"));
				include_once "Modules/File/classes/class.ilObjFileGUI.php";
				if($cmd == "create" || $cmd == "save")
				{
					$gui = new ilObjFileGUI("", 0, false);
					$gui->setCreationMode();
				}
				else
				{

				}
				$ilCtrl->forwardCommand($gui);
				break;

			default:
				$this->$cmd();
				break;
		}
	}

	/**
	 * Init personal tree
	 */
	protected function initTree()
	{
		global $ilUser;

		$user_id = $ilUser->getId();

		include_once "Services/Tree/classes/class.ilTree.php";
		$this->tree = new ilTree($user_id);
		$this->tree->setTableNames("tree_workspace", "object_data");

		$this->root_id = $this->tree->readRootId();
		if(!$this->root_id)
		{
			$this->tree->addTree($user_id, $user_id);
			$this->root_id = $this->tree->readRootId();
		}
	}
	
	/**
	 * show workspace
	 */
	protected function show()
	{
		global $tpl, $lng, $ilCtrl;

		$tpl->setTitle($lng->txt("personal_workspace"));

		$tpl->setContent("<a href=\"".$ilCtrl->getLinkTargetByClass("ilobjfilegui", "create")."\">datei test</a>");
		
	}
}

?>