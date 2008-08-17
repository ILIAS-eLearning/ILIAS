<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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

include_once("Services/Table/classes/class.ilTable2GUI.php");
include_once("Services/MediaObjects/classes/class.ilImageMapTableGUI.php");

/**
* TableGUI class for pc image map editor
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCImageMapTableGUI extends ilImageMapTableGUI
{
	
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd, $a_pc_media_object)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;

		$this->pc_media_object = $a_pc_media_object;
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_pc_media_object->getMediaObject());
	}

	/**
	* Get items of current folder
	*/
	function getItems()
	{
		$std_alias_item = new ilMediaAliasItem($this->pc_media_object->dom,
			$this->pc_media_object->hier_id, "Standard", $this->pc_media_object->getPcId());
		$areas = $std_alias_item->getMapAreas();

		$this->setData($areas);
	}
	
	/**
	* Standard Version of Fill Row. Most likely to
	* be overwritten by derived class.
	*/
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl, $ilAccess;

		$i = $a_set["Nr"];
		$this->tpl->setVariable("CHECKBOX",
			ilUtil::formCheckBox("", "area[]", $i));
		$this->tpl->setVariable("VAR_NAME", "name_".$i);
		$this->tpl->setVariable("VAL_NAME", $a_set["Link"]["Title"]);
		$this->tpl->setVariable("VAL_SHAPE", $a_set["Shape"]);
		$this->tpl->setVariable("VAL_COORDS",
			implode(explode(",", $a_set["Coords"]), ", "));
		switch ($a_set["Link"]["LinkType"])
		{
			case "ExtLink":
				$this->tpl->setVariable("VAL_LINK", $a_set["Link"]["Href"]);
				break;

			case "IntLink":
				$link_str = $this->parent_obj->getMapAreaLinkString($a_set["Link"]["Target"],
					$a_set["Link"]["Type"], $a_set["Link"]["TargetFrame"]);
				$this->tpl->setVariable("VAL_LINK", $link_str);
				break;
		}
	}

}
?>
