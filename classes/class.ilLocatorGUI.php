<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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
* locator handling class
*
* This class supplies an implementation for the locator.
* The locator will send its output to ist own frame, enabling more flexibility in
* the design of the desktop.
*
* @author Arjan Ammerlaan <a.l.ammerlaan@web.de>
* @version $Id$
* 
*/
class ilLocatorGUI
{
	protected $lng;
	protected $entries;
	
	/**
	* Constructor
	*
	*/
	function ilLocatorGUI()
	{
		global $lng;

		$this->lng =& $lng;	
		$this->entries = array();
		$this->setTextOnly(false);
	}

	/**
	* Set Only text, no HTML.
	*
	* @param	boolean	$a_textonly	Only text, no HTML
	*/
	function setTextOnly($a_textonly)
	{
		$this->textonly = $a_textonly;
	}

	/**
	* Get Only text, no HTML.
	*
	* @return	boolean	Only text, no HTML
	*/
	function getTextOnly()
	{
		return $this->textonly;
	}

	/**
	* add repository item
	*
	* @param	int		$a_ref_id	current ref id (optional);
	*								if empty $_GET["ref_id"] is used
	*/
	function addRepositoryItems($a_ref_id = 0)
	{
		global $tree;

		if ($a_ref_id == 0)
		{
			$a_ref_id = $_GET["ref_id"];
		}
		
		$pre = "";
		if (defined("ILIAS_MODULE"))
		{
			$pre = "../";
		}
		
		if ($a_ref_id > 0)
		{
			$path = $tree->getPathFull($a_ref_id);
			
			// add item for each node on path
			foreach ($path as $key => $row)
			{
				if (!in_array($row["type"], array("root", "cat","crs", "fold", "grp", "icrs")))
				{
					continue;
				}
				if ($row["title"] == "ILIAS" && $row["type"] == "root")
				{
					$row["title"] = $this->lng->txt("repository");
				}
				
				$this->addItem($row["title"],
					$pre."repository.php?cmd=frameset&amp;ref_id=".$row["child"],
					ilFrameTargetInfo::_getFrame("MainContent"), $row["child"]);
			}
		}
	}
	
	/**
	* add administration tree items
	*
	* @param	int		$a_ref_id	current ref id (optional);
	*								if empty $_GET["ref_id"] is used
	*/
	function addAdministrationItems($a_ref_id = 0)
	{
		global $tree, $ilCtrl, $objDefinition, $lng;

		if ($a_ref_id == 0)
		{
			$a_ref_id = $_GET["ref_id"];
		}

		if ($a_ref_id > 0)
		{
			$path = $tree->getPathFull($a_ref_id);
			
			// add item for each node on path
			foreach ($path as $key => $row)
			{
				if (!in_array($row["type"], array("root", "cat", "crs", "fold", "grp", "icrs")))
				{
					continue;
				}
				
				if ($row["child"] == ROOT_FOLDER_ID)
				{
					$row["title"] = $lng->txt("repository"); 
				}
				
				$class_name = $objDefinition->getClassName($row["type"]);
				$class = strtolower("ilObj".$class_name."GUI");
				$ilCtrl->setParameterByClass($class, "ref_id", $row["child"]);
				$this->addItem($row["title"],
					$ilCtrl->getLinkTargetbyClass($class, "view"), "", $row["child"]);
			}
		}
	}
	
	function addContextItems($a_ref_id, $a_omit_node = false, $a_stop = 0)
	{
		global $tree;
		
		if ($a_ref_id > 0)
		{
			$path = $tree->getPathFull($a_ref_id);
			
			// we want to show the full path, from the major container to the item
			// (folders are not! treated as containers here), at least one parent item
			$r_path = array_reverse($path);
			$first = "";
			$omit = array();
			$do_omit = false;
			foreach ($r_path as $key => $row)
			{
				if ($first == "")
				{
					if (in_array($row["type"], array("root", "cat", "grp", "crs")) &&
						$row["child"] != $a_ref_id)
					{
						$first = $row["child"];
					}
				}
				if ($a_stop == $row["child"])
				{
					$do_omit = true;
				}
				$omit[$row["child"]] = $do_omit;
			}

			$add_it = false;
			foreach ($path as $key => $row)
			{
				if ($first == $row["child"])
				{
					$add_it = true;
				}
				
				
				if ($add_it && !$omit[$row["child"]] &&
					(!$a_omit_node || ($row["child"] != $a_ref_id)))
				{
//echo "-".ilObject::_lookupTitle($row["obj_id"])."-";
					if ($row["title"] == "ILIAS" && $row["type"] == "root")
					{
						$row["title"] = $this->lng->txt("repository");
					}
					$this->addItem($row["title"],
						"./goto.php?client_id=".rawurlencode(CLIENT_ID)."&target=".$row["type"]."_".$row["child"],
						"_top", $row["child"]);
				}
			}
		}
	}
	
	/**
	* add locator item
	*
	* @param	string	$a_title		item title
	* @param	string	$a_link			item link
	* @param	string	$a_frame		frame target
	*/
	function addItem($a_title, $a_link, $a_frame = "", $a_ref_id = 0)
	{
		$this->entries[] = array("title" => $a_title,
			"link" => $a_link, "frame" => $a_frame, "ref_id" => $a_ref_id); 
	}
	
	/**
	* Clear all Items
	*/
	function clearItems()
	{
		$this->entries = array();
	}
	
	/**
	* Get all locator entries.
	*/
	function getItems()
	{
		return $this->entries;
	}
	
	/**
	* Get locator HTML
	*/
	function getHTML()
	{
		global $lng, $ilSetting;
		
		if ($this->getTextOnly())
		{
			$loc_tpl = new ilTemplate("tpl.locator_text_only.html", true, true);
		}
		else
		{
			$loc_tpl = new ilTemplate("tpl.locator.html", true, true);
		}
		
		$items = $this->getItems();
		$first = true;

		if (is_array($items))
		{
			foreach($items as $item)
			{
				if (!$first)
				{
					$loc_tpl->touchBlock("locator_separator_prefix");
				}
				
				if ($item["ref_id"] > 0)
				{
					$obj_id = ilObject::_lookupObjId($item["ref_id"]);
					$type = ilObject::_lookupType($obj_id);
					
					$icon_path = ilObject::_getIcon($obj_id, "tiny", $type);
					
					$loc_tpl->setCurrentBlock("locator_img");					
					$loc_tpl->setVariable("IMG_SRC", $icon_path);
					$loc_tpl->setVariable("IMG_ALT",
						$lng->txt("obj_".$type));
					$loc_tpl->parseCurrentBlock();
				}
				
				$loc_tpl->setCurrentBlock("locator_item");
				if ($item["link"] != "")
				{
					$loc_tpl->setVariable("LINK_ITEM", $item["link"]);
					if ($item["frame"] != "")
					{
						$loc_tpl->setVariable("LINK_TARGET", ' target="'.$item["frame"].'" ');
					}
					$loc_tpl->setVariable("ITEM", $item["title"]);
				}
				else
				{
					$loc_tpl->setVariable("PREFIX", $item["title"]);
				}
				$loc_tpl->parseCurrentBlock();
				
				$first = false;
			}
		}
		else
		{
			$loc_tpl->setVariable("NOITEM", "&nbsp;");
			$loc_tpl->touchBlock("locator");
		}
		
		return trim($loc_tpl->get());
	}


} // END class.LocatorGUI
?>
