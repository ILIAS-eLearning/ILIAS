<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjWorkspaceFolderTableGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* $Id: class.ilObjFolderListGUI.php 26089 2010-10-20 08:08:05Z smeyer $
*
* @extends ilTable2GUI
*/

include_once "Services/Table/classes/class.ilTable2GUI.php";

class ilObjWorkspaceFolderTableGUI extends ilTable2GUI
{
	function __construct($a_parent_obj, $a_parent_cmd, $a_node_id)
	{
		global $ilCtrl;
		
		$this->node_id = $a_node_id;
		$this->setId("tbl_wfld");

		parent::__construct($a_parent_obj, $a_parent_cmd);

		// $this->setTitle(":TODO:");
		$this->setLimit(999);

		$this->addColumn($this->lng->txt("content"));

		// $this->setEnableHeader(true);
		// $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.list_row.html", "Modules/WorkspaceFolder");
		//$this->disable("footer");
		// $this->setEnableTitle(true);
		$this->setEnableNumInfo(false);

		$this->getItems();
	}

	protected function getItems()
	{
		global $ilUser;
		
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
		$tree = new ilWorkspaceTree($ilUser->getId());
		$nodes = $tree->getChilds($this->node_id, "title");
		
		$this->setData($nodes);
	}

	protected function fillRow($node)
	{
		global $objDefinition;
		
		$class = $objDefinition->getClassName($node["type"]);
		$location = $objDefinition->getLocation($node["type"]);
		$full_class = "ilObj".$class."ListGUI";

		include_once($location."/class.".$full_class.".php");
		$item_list_gui = new $full_class();
		
		$item_list_gui->setDetailsLevel(ilObjectListGUI::DETAILS_ALL);
		$item_list_gui->enableDelete(true);
		$item_list_gui->enableCut(true);
		$item_list_gui->enableCopy(false);
		$item_list_gui->enableSubscribe(false);
		$item_list_gui->enablePayment(false);
		$item_list_gui->enableLink(false);
		$item_list_gui->enablePath(false);
		$item_list_gui->enableLinkedPath(false);
		$item_list_gui->enableSearchFragments(true);
		$item_list_gui->enableRelevance(false);
		$item_list_gui->enableIcon(true);
		// $item_list_gui->enableCheckbox(false);
		// $item_list_gui->setSeparateCommands(true);
		
		if($node["type"] == "file")
		{
			$item_list_gui->enableRepositoryTransfer(true);
		}

		$item_list_gui->setContainerObject($this->parent_obj);

		if($html = $item_list_gui->getListItemHTML($node["wsp_id"], $node["obj_id"],
				$node["title"], $node["description"], false, false, "", ilObjectListGUI::CONTEXT_WORKSPACE))
		{
			$this->tpl->setVariable("ITEM_LIST_NODE", $html);
		} 
	}
	
}

?>