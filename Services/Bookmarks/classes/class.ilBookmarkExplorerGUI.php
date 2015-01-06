<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/UIComponent/Explorer2/classes/class.ilTreeExplorerGUI.php");

/**
 * Bookmark explorer GUI class
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 * @version	$Id$
 *
 * @ingroup ServicesBookmarks
 */
class ilBookmarkExplorerGUI extends ilTreeExplorerGUI
{
	/**
	 * Constructor
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_user_id = 0)
	{
		global $ilUser;
		
		if ($a_user_id == 0)
		{
			$a_user_id = $ilUser->getId();
		}
		include_once("./Services/Bookmarks/classes/class.ilBookmarkTree.php");
		$tree = new ilBookmarkTree($a_user_id);
		parent::__construct("bm_exp", $a_parent_obj, $a_parent_cmd, $tree);

		$this->setTypeWhiteList(array("bmf", "dum"));
		
		$this->setSkipRootNode(false);
		$this->setAjax(false);
		$this->setOrderField("title");
	}

	/**
	 * Get node content
	 *
	 * @param array 
	 * @return
	 */
	function getNodeContent($a_node)
	{
		global $lng;
		
		if ($a_node["child"] == $this->getNodeId($this->getRootNode()))
		{
			return $lng->txt("bookmarks");
		}
				
		return $a_node["title"];
	}
	
	/**
	 * Get node icon
	 *
	 * @param array 
	 * @return
	 */
	function getNodeIcon($a_node)
	{
		$icon = ilUtil::getImagePath("icon_".$a_node["type"].".svg");
		
		return $icon;
	}
	
	/**
	 * Get node icon alt attribute
	 *
	 * @param mixed $a_node node object/array
	 * @return string image alt attribute
	 */
	function getNodeIconAlt($a_node)
	{
		global $lng;
		
		return $lng->txt("icon")." ".$lng->txt($a_node["type"]);
	}


	/**
	 * Is node highlighted?
	 *
	 * @param mixed $a_node node object/array
	 * @return boolean node visible true/false
	 */
	function isNodeHighlighted($a_node)
	{
		if ($a_node["child"] == $_GET["bmf_id"] ||
			($_GET["bmf_id"] == "" && $a_node["child"] == $this->getNodeId($this->getRootNode())))
		{
			return true;
		}
		return false;
	}	
	
	/**
	 * Get href for node
	 *
	 * @param mixed $a_node node object/array
	 * @return string href attribute
	 */
	function getNodeHref($a_node)
	{
		global $ilCtrl;
		
		switch($a_node["type"])
		{
			// bookmark folder
			case "bmf":
			// dummy root
			case "dum":
				$ilCtrl->setParameterByClass("ilbookmarkadministrationgui", "bmf_id", $a_node["child"]);
				$ret = $ilCtrl->getLinkTargetByClass("ilbookmarkadministrationgui", "");
				$ilCtrl->setParameterByClass("ilbookmarkadministrationgui", "bmf_id", $_GET["bmf_id"]);
				return $ret;
				break;
		}
	}

}

?>
