<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject2GUI.php";

/**
* Class ilObjWorkspaceFolderGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id: class.ilObjFolderGUI.php 25134 2010-08-13 14:22:11Z smeyer $
*
* @ilCtrl_Calls ilObjWorkspaceFolderGUI: ilInfoScreenGUI, ilPermissionGUI, 
*
* @extends ilObject2GUI
*/
class ilObjWorkspaceFolderGUI extends ilObject2GUI
{
	var $folder_tree;		// folder tree

	function getType()
	{
		return "wsfold";
	}

	function setTabs()
	{
		global $lng;

		$this->ctrl->setParameter($this,"wsp_id",$this->node_id);

		if ($this->getAccessHandler()->checkAccess('read', '', $this->node_id))
		{
			$this->tabs_gui->addTab('view_content', $lng->txt("content"),
				$this->ctrl->getLinkTarget($this, ""));
		}

		if ($this->getAccessHandler()->checkAccess('write', '', $this->node_id))
		{
			$force_active = ($_GET["cmd"] == "edit")
				? true
				: false;
			$this->tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "edit"), "edit", get_class($this)
				, "", $force_active);
		}
	}

	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			default:
				$this->prepareOutput();
				if(!$cmd)
				{
					$cmd = "render";
				}
				$this->$cmd();
				break;
		}

		return true;
	}

	/**
	* Render root folder
	*/
	function render()
	{
		global $ilUser, $tpl, $lng, $ilCtrl, $objDefinition;

		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
		$tree = new ilWorkspaceTree($ilUser->getId());
		$node = $tree->getNodeData($this->node_id);
		$nodes = $tree->getSubTree($node);
		if(sizeof($nodes) > 1)
		{
			// remove current node (== root of subtree)
			array_shift($nodes);

			$template = new ilTemplate("tpl.list_row.html", true, true, "Modules/WorkspaceFolder");

			foreach($nodes as $node)
			{
				$ilCtrl->setParameter($this, "wsp_id", $node["wsp_id"]);

				$template->setCurrentBlock("node_action");
				$template->setVariable("NODE_ACTION_URL", $ilCtrl->getLinkTarget($this, "edit"));
				$template->setVariable("NODE_ACTION_CAPTION", $lng->txt("edit"));
				$template->parseCurrentBlock();

				if($objDefinition->isContainer($node["type"]))
				{
					$template->setVariable("NODE_ACTION_URL", $ilCtrl->getLinkTarget($this, "render"));
					$template->setVariable("NODE_ACTION_CAPTION", "&raquo;");
					$template->parseCurrentBlock();
				}

				$ilCtrl->setParameter($this, "wsp_id", "");

				$template->setCurrentBlock("node");
				$template->setVariable("NODE_ICON_SRC", ilObject::_getIcon($node["obj_id"], "small"));
				$template->setVariable("NODE_ICON_ALT", $lng->txt("obj_".$node["type"]));
				$template->setVariable("NODE_CAPTION", $node["title"]);
				$template->parseCurrentBlock();
			}

			$tpl->setContent($template->get());
		}
	}

	
} // END class.ilObjFolderGUI
?>
