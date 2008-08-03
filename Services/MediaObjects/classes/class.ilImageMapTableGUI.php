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

/**
* TableGUI class for image map editor
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesMediaObjects
*/
class ilImageMapTableGUI extends ilTable2GUI
{
	
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd, $a_media_object)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->media_object = $a_media_object;
		
		$this->addColumn("", "", "1");	// checkbox
		$this->addColumn($this->lng->txt("cont_name"), "", "");
		$this->addColumn($this->lng->txt("cont_shape"), "", "");
		$this->addColumn($this->lng->txt("cont_coords"), "", "");
		$this->addColumn($this->lng->txt("cont_link"), "", "");
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.image_map_table_row.html", "Services/MediaObjects");
		$this->getItems();

		// action commands
		$this->addMultiCommand("deleteAreas", $lng->txt("delete"));
		$this->addMultiCommand("editLink", $lng->txt("cont_set_link"));
		$this->addMultiCommand("editShapeRectangle", $lng->txt("cont_edit_shape_rectangle"));
		$this->addMultiCommand("editShapeCircle", $lng->txt("cont_edit_shape_circle"));
		$this->addMultiCommand("editShapePolygon", $lng->txt("cont_edit_shape_polygon"));
		
		$data = $this->getData();
		if (count($data) > 0)
		{
			$this->addCommandButton("updateAreas", $this->lng->txt("cont_update_names"));
		}
		$this->addCommandButton("addRectangle", $this->lng->txt("cont_add_rectangle"));
		$this->addCommandButton("addCircle", $this->lng->txt("cont_add_circle"));
		$this->addCommandButton("addPolygon", $this->lng->txt("cont_add_polygon"));
		
		$this->setEnableTitle(false);
	}

	/**
	* Get items of current folder
	*/
	function getItems()
	{
		$st_item =& $this->media_object->getMediaItem("Standard");
		$max = ilMapArea::_getMaxNr($st_item->getId());
		$areas = array();
		
		include_once("./Services/MediaObjects/classes/class.ilMapArea.php");
		for ($i=1; $i<=$max; $i++)
		{
			$area = new ilMapArea($st_item->getId(), $i);
			$areas[] = array("nr" => $i, "area" => $area);
		}

		$this->setData($areas);
	}
	
	/**
	* Standard Version of Fill Row. Most likely to
	* be overwritten by derived class.
	*/
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl, $ilAccess;

		$area = $a_set["area"];
		$i = $a_set["nr"];
		$this->tpl->setVariable("CHECKBOX",
			ilUtil::formCheckBox("", "area[]", $i));
		$this->tpl->setVariable("VAR_NAME", "name_".$i);
		$this->tpl->setVariable("VAL_NAME", $area->getTitle());
		$this->tpl->setVariable("VAL_SHAPE", $area->getShape());
		$this->tpl->setVariable("VAL_COORDS",
			implode(explode(",", $area->getCoords()), ", "));
		switch ($area->getLinkType())
		{
			case "ext":
				$this->tpl->setVariable("VAL_LINK", $area->getHRef());
				break;

			case "int":
				$link_str = $this->parent_obj->getMapAreaLinkString($area->getTarget(),
					$area->getType(), $area->getTargetFrame());
				$this->tpl->setVariable("VAL_LINK", $link_str);
				break;
		}
	}

}
?>
