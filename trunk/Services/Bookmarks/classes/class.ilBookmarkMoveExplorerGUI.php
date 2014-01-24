<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Bookmarks/classes/class.ilBookmarkExplorerGUI.php");

/**
 * Bookmark explorer GUI class
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 * @version	$Id$
 *
 * @ingroup ServicesBookmarks
 */
class ilBookmarkMoveExplorerGUI extends ilBookmarkExplorerGUI
{

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
				$ilCtrl->setParameterByClass("ilbookmarkadministrationgui", "bmfmv_id", $a_node["child"]);
				$ret = $ilCtrl->getLinkTargetByClass("ilbookmarkadministrationgui", "confirmedMove");
				$ilCtrl->setParameterByClass("ilbookmarkadministrationgui", "bmfmv_id", "");
				return $ret;
				break;
		}
	}

}

?>
