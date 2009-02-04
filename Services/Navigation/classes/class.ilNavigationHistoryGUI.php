<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/


/**
* User Interface Class for Navigation History
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
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
	* Get HTML for navigation history
	*/
	function getHTML()
	{
		global $ilNavigationHistory, $lng;
		
		include_once("./Services/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
		$selection = new ilAdvancedSelectionListGUI();
		$selection->setListTitle($lng->txt("last_visited"));
		$selection->setId("lastvisited");
		$selection->setSelectionHeaderClass("MMInactive");
		$selection->setHeaderIcon(ilAdvancedSelectionListGUI::NO_ICON);
		$selection->setItemLinkClass("small");
		$selection->setUseImages(true);
		
		$items = $ilNavigationHistory->getItems();
		//$sel_arr = array(0 => "-- ".$lng->txt("last_visited")." --");
		reset($items);
		$cnt = 0;
		foreach($items as $item)
		{
			if ($cnt++ > 20) break;
			
			if ($item["ref_id"] != $_GET["ref_id"])			// do not list current item
			{
				$obj_id = ilObject::_lookupObjId($item["ref_id"]);
				$selection->addItem($item["title"], $item["ref_id"], $item["link"],
					ilObject::_getIcon($obj_id, "tiny", $item["type"]),
					$lng->txt("obj_".$item["type"]), "_top");
			}
		}

		return $selection->getHTML();
	}
	
	/**
	* Get HTML for navigation history.
	*/
	public function getHTMLOld()
	{
		global $ilNavigationHistory, $lng;
		
		$items = $ilNavigationHistory->getItems();

		// do not show list, if no item is in list
		if (count($items) == 0)
		{
			return "";
		}
		
		// do not show list, if only current item is in list
		$item = current($items);
		if (count($items) == 1 && $item["ref_id"] == $_GET["ref_id"])
		{
			return "";
		}
		
		$GLOBALS["tpl"]->addJavascript("./Services/Navigation/js/ServiceNavigation.js");

		$tpl = new ilTemplate("tpl.navigation_history.html", true, true,
			"Services/Navigation");
			
		$sel_arr = array(0 => "-- ".$lng->txt("last_visited")." --");
		reset($items);

		$cnt = 0;
		foreach($items as $item)
		{
			if ($cnt++ > 20) break;
			
			if ($item["ref_id"] != $_GET["ref_id"])			// do not list current item
			{
				$sel_arr[$item["ref_id"]] = $item["title"];
				$this->css_row = ($this->css_row != "tblrow1_mo")
					? "tblrow1_mo"
					: "tblrow2_mo";
				$tpl->setCurrentBlock("item");
				$tpl->setVariable("HREF_ITEM", $item["link"]);
				$tpl->setVariable("CSS_ROW", $this->css_row);
				$tpl->setVariable("TXT_ITEM", $item["title"]);
				
				$obj_id = ilObject::_lookupObjId($item["ref_id"]);
				$tpl->setVariable("IMG_ITEM",
					ilObject::_getIcon($obj_id, "tiny", $item["type"]));
				$tpl->setVariable("ALT_ITEM", $lng->txt("obj_".$item["type"]));
				$tpl->parseCurrentBlock();
			}
		}
		$select = ilUtil::formSelect("", "url_ref_id", $sel_arr, false, true, "0", "ilEditSelect");
		$tpl->setVariable("TXT_LAST_VISITED", $lng->txt("last_visited"));
		$tpl->setVariable("IMG_DOWN", ilUtil::getImagePath("mm_down_arrow.gif"));
		$tpl->setVariable("NAVI_SELECT", $select);
		$tpl->setVariable("TXT_GO", $lng->txt("go"));
		$tpl->setVariable("ACTION", "goto.php?target=navi_request&ref_id=".$_GET["ref_id"]);
		
		return $tpl->get();
	}
	
	/**
	* Handle navigation request
	*/
	function handleNavigationRequest()
	{
		global $ilNavigationHistory;
		
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
			ilUtil::redirect("repository.php?cmd=frameset&getlast=true");
		}
	}
	
}
?>
