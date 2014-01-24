<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* User Interface Class for Navigation History
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilNavigationHistoryGUI: 
*/
class ilNavigationHistoryGUI
{

	private $items;

	/**
	 * Constructor.
	 *	
	 */
	public function __construct()
	{
	}

	/**
	 * Execute command
	 */
	function executeCommand()
	{
		global $ilCtrl;

		$next_class = $ilCtrl->getNextClass();
		$cmd = $ilCtrl->getCmd();

		switch($next_class)
		{
			default:
				$this->$cmd();
				break;
		}
	}

	/**
	* Get HTML for navigation history
	*/
	function getHTML()
	{
		global $ilNavigationHistory, $lng;
		
		include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
		$selection = new ilAdvancedSelectionListGUI();
		$selection->setFormSelectMode("url_ref_id", "ilNavHistorySelect", true,
			"goto.php?target=navi_request", "ilNavHistory", "ilNavHistoryForm",
			"_top", $lng->txt("go"), "ilNavHistorySubmit");
		$selection->setListTitle($lng->txt("last_visited"));
		$selection->setId("lastvisited");
		$selection->setSelectionHeaderClass("MMInactive");
		$selection->setHeaderIcon(ilAdvancedSelectionListGUI::NO_ICON);
		$selection->setItemLinkClass("small");
		$selection->setUseImages(true);
		include_once("./Services/Accessibility/classes/class.ilAccessKey.php");
		$selection->setAccessKey(ilAccessKey::LAST_VISITED);
		
		$items = $ilNavigationHistory->getItems();
		//$sel_arr = array(0 => "-- ".$lng->txt("last_visited")." --");
		reset($items);
		$cnt = 0;
		foreach($items as $k => $item)
		{
			if ($cnt++ > 20) break;
			if (!isset($item["ref_id"]) || !isset($_GET["ref_id"]) ||
				$item["ref_id"] != $_GET["ref_id"] || $k > 0)			// do not list current item
			{
				$obj_id = ilObject::_lookupObjId($item["ref_id"]);
				$selection->addItem($item["title"], $item["ref_id"], $item["link"],
					ilObject::_getIcon($obj_id, "tiny", $item["type"]),
					$lng->txt("obj_".$item["type"]), "_top");
			}
		}
		$html = $selection->getHTML();
		
		if ($html == "")
		{
			$selection->addItem($lng->txt("no_items"), "", "#",
				"", "", "_top");
			$selection->setUseImages(false);
			$html = $selection->getHTML();
		}
		return $html;
	}
	
	/**
	* Handle navigation request
	*/
	function handleNavigationRequest()
	{
		global $ilNavigationHistory, $ilCtrl;
		
		if ($_GET["target"] == "navi_request")
		{
			$items = $ilNavigationHistory->getItems();
			foreach($items as $item)
			{
				if ($item["ref_id"] == $_POST["url_ref_id"])
				{
					ilUtil::redirect($item["link"]);
				}
			}
			reset($items);
			$item = current($items);
			if ($_POST["url_ref_id"] == 0 && $item["ref_id"] == $_GET["ref_id"])
			{
				$item = next($items);		// omit current item
			}
			if ($_POST["url_ref_id"] == 0 && $item["link"] != "")
			{
				ilUtil::redirect($item["link"]);
			}
			
			$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", "");
			$ilCtrl->setParameterByClass("ilrepositorygui", "getlast", "true");
			$ilCtrl->redirectByClass("ilrepositorygui", "frameset");
		}
	}
	
	/**
	 * Remove all entries form list
	 *
	 * @param
	 * @return
	 */
	function removeEntries()
	{
		global $ilNavigationHistory;
		
		$ilNavigationHistory->deleteDBEntries();
		$ilNavigationHistory->deleteSessionEntries();
	}
	
}
?>
