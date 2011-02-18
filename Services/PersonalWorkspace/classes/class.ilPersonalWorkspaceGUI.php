<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* GUI class for personal workspace
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilPersonalDesktopGUI.php 26976 2010-12-16 13:24:38Z akill $
*
* @ilCtrl_Calls ilPersonalWorkspaceGUI: ilObjWorkspaceRootFolder, ilObjFileGUI, ilObjWorkspaceFolderGUI
*/
class ilPersonalWorkspaceGUI
{
	protected $tree; // [ilTree]
	protected $node_id; // [int]
	
	/**
	 * constructor
	 */
	public function __construct()
	{
		global $ilCtrl;

		$this->initTree();

		$ilCtrl->saveParameter($this, "wsp_id");

		$this->node_id = $_REQUEST["wsp_id"];
		if(!$this->node_id)
		{
			$this->node_id = $this->tree->getRootId();
		}
	}
	
	/**
	 * execute command
	 */
	public function executeCommand()
	{
		global $ilCtrl, $ilTabs, $lng;

		$ilCtrl->setReturn($this, "show");

		$next_class = $ilCtrl->getNextClass();		
		$cmd = $ilCtrl->getCmd("show");

		if($next_class)
		{
			// $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "show"));

			$class_path = $ilCtrl->lookupClassPath($next_class);
			include_once($class_path);
			$class_name = $ilCtrl->getClassForClasspath($class_path);
			if($cmd == "create" || $cmd == "save")
			{
				$gui = new $class_name(null, ilObject2GUI::WORKSPACE_NODE_ID, $this->node_id);
				$gui->setCreationMode();
			}
			else
			{
				$gui = new $class_name($this->node_id, ilObject2GUI::WORKSPACE_NODE_ID, false);
			}
			$ilCtrl->forwardCommand($gui);
		}
		else
		{
			$this->$cmd();
		}

		$this->buildLocator();
	}

	/**
	 * Init personal tree
	 */
	protected function initTree()
	{
		global $ilUser;

		$user_id = $ilUser->getId();

		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
		$this->tree = new ilWorkspaceTree($user_id);
		if(!$this->tree->readRootId())
		{
			// create (workspace) root folder
			$root = ilObjectFactory::getClassByType("wsrt");
			$root = new $root(null);
			$root->create();

			$root_id = $this->tree->createReference($root->getId());
			$this->tree->addTree($user_id, $root_id);
			$this->tree->setRootId($root_id);
		}
	}
	
	/**
	 * show workspace
	 */
	protected function show()
	{
		global $tpl, $lng, $ilCtrl, $objDefinition;

		// title/icon
		$root = $this->tree->getNodeData($this->node_id);
		if($root["type"] == "wsrt")
		{
			$title = $lng->txt("wsp_personal_workspace");
			$icon = ilObject::_getIcon(ROOT_FOLDER_ID, "big");
		}
		else
		{
			$title = $root["title"];
			$icon = ilObject::_getIcon($root["obj_id"], "big");
		}
		$tpl->setTitle($title);
		$tpl->setTitleIcon($icon, $title);

		$tree_tpl = new ilTemplate("tpl.workspace_node.html", true, true, "Services/PersonalWorkspace");

		// create subtypes
		$subtypes = $objDefinition->getCreatableSubObjects("wsrt", ilObjectDefinition::MODE_WORKSPACE);
		if($subtypes)
		{
			$tree_tpl->setCurrentBlock("action_item");
			foreach(array_keys($subtypes) as $type)
			{
				$class = $objDefinition->getClassName($type);
				$tree_tpl->setVariable("ACTION_ITEM_URL", $ilCtrl->getLinkTargetByClass("ilobj".$class."gui", "create"));
				$tree_tpl->setVariable("ACTION_ITEM_CAPTION", $lng->txt("wsp_add_".$type));
				$tree_tpl->parseCurrentBlock();
			}
		}

		$nodes = $this->tree->getSubTree($root);
		if($nodes)
		{
			// first node == root
			array_shift($nodes);

			foreach($nodes as $node)
			{
				$ilCtrl->setParameter($this, "wsp_id", $node["wsp_id"]);
				$obj_class = "ilObj".$node["type"]."GUI";

				// edit
				$tree_tpl->setCurrentBlock("node_action");
				$tree_tpl->setVariable("NODE_ACTION_URL", $ilCtrl->getLinkTargetByClass($obj_class, "edit"));
				$tree_tpl->setVariable("NODE_ACTION_CAPTION", $lng->txt("edit"));
				$tree_tpl->parseCurrentBlock();

				// open
				if($node["type"] == "fld")
				{
					$tree_tpl->setCurrentBlock("node_action");
					$tree_tpl->setVariable("NODE_ACTION_URL", $ilCtrl->getLinkTarget($this, "show"));
					$tree_tpl->setVariable("NODE_ACTION_CAPTION", "&raquo;");
					$tree_tpl->parseCurrentBlock();
				}

				// icon/title
				$tree_tpl->setCurrentBlock("node");
				$tree_tpl->setVariable("NODE_CAPTION", $node["title"]);
				$tree_tpl->setVariable("NODE_ICON_SRC", ilObject::_getIcon($node["obj_id"], "small"));
				$tree_tpl->setVariable("NODE_ICON_ALT", $lng->txt("obj_".$node["type"]));
				$tree_tpl->parseCurrentBlock();
			}
		}

		$tpl->setContent($tree_tpl->get());
	}

	/**
	 * Build locator for current node
	 */
	protected function buildLocator()
	{
		global $lng, $ilCtrl, $ilLocator, $tpl;

		$ilLocator->clearItems();
		
		$path = $this->tree->getPathFull($this->node_id);
		foreach($path as $node)
		{
			switch($node["type"])
			{
				case "file":
					$obj_class = "ilObj".$node["type"]."GUI";
					$ilCtrl->setParameter($this, "wsp_id", $node["wsp_id"]);
					$ilLocator->addItem($node["title"], $ilCtrl->getLinkTargetByClass($obj_class, "edit"));
					break;

				case "usr":
					$ilCtrl->setParameter($this, "wsp_id", "");
					$ilLocator->addItem($lng->txt("wsp_personal_workspace"), $ilCtrl->getLinkTarget($this, "show"));
					break;

				case "fld":
					$ilCtrl->setParameter($this, "wsp_id", $node["wsp_id"]);
					$ilLocator->addItem($node["title"], $ilCtrl->getLinkTarget($this, "show"));
					break;
			}
		}

		$tpl->setLocator();
		$ilCtrl->setParameter($this, "wsp_id", $this->node_id);
	}
}

?>