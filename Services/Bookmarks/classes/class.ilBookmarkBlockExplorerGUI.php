<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Bookmarks/classes/class.ilBookmarkExplorerGUI.php");

/**
 * Bookmark block explorer GUI class
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 * @version	$Id$
 *
 * @todo: target, onclick
 *
 * @ingroup ServicesBookmarks
 */
class ilBookmarkBlockExplorerGUI extends ilBookmarkExplorerGUI
{
	/**
	 * Constructor
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_user_id = 0)
	{
		global $ilUser;
		
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_user_id);

		$this->setTypeWhiteList(array("bmf", "dum", "bm"));
		$this->setOrderField("type DESC, title");
		$this->setSkipRootNode(true);
	}

	/**
	 * Is node highlighted?
	 *
	 * @param mixed $a_node node object/array
	 * @return boolean node visible true/false
	 */
	function isNodeHighlighted($a_node)
	{
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
				return "#";
				break;
				
			// bookmark
			case "bm":
				return $a_node["target"];
				break;
		}
	}

	/**
	 * Get target for node
	 *
	 * @param mixed $a_node node object/array
	 * @return string target
	 */
	function getNodeTarget($a_node)
	{
		if ($a_node["type"] == "bm")
		{
			return "_blank";
		}
		return "";
	}

	/**
	 * Get onclick for node
	 *
	 * @param mixed $a_node node object/array
	 * @return string onclick attribute
	 */
	function getNodeOnClick($a_node)
	{
		if ($a_node["type"] == "bmf")
		{
			return $this->getNodeToggleOnClick($a_node);
		}
		return "";
	}

}

?>
