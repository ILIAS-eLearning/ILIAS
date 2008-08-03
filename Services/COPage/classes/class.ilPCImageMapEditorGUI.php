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

include_once("./Services/MediaObjects/classes/class.ilImageMapEditorGUI.php");

/**
* User interface class for page content map editor
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilPCImageMapEditorGUI: ilInternalLinkGUI
*
* @ingroup ServicesCOPage
*/
class ilPCImageMapEditorGUI extends ilImageMapEditorGUI
{
	/**
	* Constructor
	*/
	function __construct($a_pc_media_object, $a_page)
	{
		$this->pc_media_object = $a_pc_media_object;
		$this->page = $a_page;
		parent::__construct($a_pc_media_object->getMediaObject());
	}
	

	/**
	* Get table HTML
	*/
	function getImageMapTableHTML()
	{
		include_once("./Services/COPage/classes/class.ilPCImageMapTableGUI.php");
		$image_map_table = new ilPCImageMapTableGUI($this, "editMapAreas", $this->pc_media_object);
		return $image_map_table->getHTML();
	}

	/**
	* Save new or updated map area
	*/
	function saveArea()
	{
		global $lng, $ilCtrl;
		
		switch ($_SESSION["il_map_edit_mode"])
		{
			// save edited link
			case "edit_link":
				$std_alias_item = new ilMediaAliasItem($this->pc_media_object->dom,
					$this->pc_media_object->hier_id, "Standard");

				if ($_POST["area_link_type"] == IL_INT_LINK)
				{
					$std_alias_item->setAreaIntLink($_SESSION["il_map_area_nr"],
						$_SESSION["il_map_il_type"], $_SESSION["il_map_il_target"],
						$_SESSION["il_map_il_targetframe"]);
				}
				else
				{
					$std_alias_item->setAreaExtLink($_SESSION["il_map_area_nr"],
						ilUtil::stripSlashes($_POST["area_link_ext"]));
				}
				$this->updated = $this->page->update();
				break;

			// save edited shape
			case "edit_shape":
				$std_alias_item = new ilMediaAliasItem($this->pc_media_object->dom,
					$this->pc_media_object->hier_id, "Standard");
				$std_alias_item->setShape($_SESSION["il_map_area_nr"],
					$_SESSION["il_map_edit_area_type"], $_SESSION["il_map_edit_coords"]);
				$this->updated = $this->page->update();
				break;

			// save new area
			default:
				$area_type = $_SESSION["il_map_edit_area_type"];
				$coords = $_SESSION["il_map_edit_coords"];

				switch($_POST["area_link_type"])
				{
					case "ext":
						$link = array(
							"LinkType" => IL_EXT_LINK,
							"Href" => ilUtil::stripSlashes($_POST["area_link_ext"]));
						break;

					case "int":
						$link = array(
							"LinkType" => IL_INT_LINK,
							"Type" => $_SESSION["il_map_il_type"],
							"Target" => $_SESSION["il_map_il_target"],
							"TargetFrame" => $_SESSION["il_map_il_targetframe"]);
						break;
				}

				$std_alias_item = new ilMediaAliasItem($this->pc_media_object->dom,
					$this->pc_media_object->hier_id, "Standard");
				$std_alias_item->addMapArea($area_type, $coords,
					ilUtil::stripSlashes($_POST["area_name"]), $link);
				$this->updated = $this->page->update();

				break;
		}

		//$this->initMapParameters();
		ilUtil::sendInfo($lng->txt("cont_saved_map_area"), true);
		$ilCtrl->redirect($this, "editMapAreas");
	}

	/**
	* Delete map areas
	*/
	function deleteAreas()
	{
		global $ilCtrl, $lng;
		
		if (!isset($_POST["area"]))
		{
			ilUtil::sendInfo($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "editMapAreas");
		}

		$std_alias_item = new ilMediaAliasItem($this->pc_media_object->dom,
			$this->pc_media_object->hier_id, "Standard");

		if (count($_POST["area"]) > 0)
		{
			$i = 0;

			foreach ($_POST["area"] as $area_nr)
			{
				$std_alias_item->deleteMapArea($area_nr - $i);
				$i++;
			}
			$this->updated = $this->page->update();
			ilUtil::sendInfo($lng->txt("cont_areas_deleted"), true);
		}

		$ilCtrl->redirect($this, "editMapAreas");
	}

	/**
	* Get Link Type of Area
	*/
	function getLinkTypeOfArea($a_nr)
	{
		$std_alias_item = new ilMediaAliasItem($this->pc_media_object->dom,
			$this->pc_media_object->hier_id, "Standard");
		return $std_alias_item->getLinkTypeOfArea($a_nr);
	}

	/**
	* Get Type of Area (only internal link)
	*/
	function getTypeOfArea($a_nr)
	{
		$std_alias_item = new ilMediaAliasItem($this->pc_media_object->dom,
			$this->pc_media_object->hier_id, "Standard");
		return $std_alias_item->getTypeOfArea($a_nr);
	}

	/**
	* Get Target of Area (only internal link)
	*/
	function getTargetOfArea($a_nr)
	{
		$std_alias_item = new ilMediaAliasItem($this->pc_media_object->dom,
			$this->pc_media_object->hier_id, "Standard");
		return $std_alias_item->getTargetOfArea($a_nr);
	}

	/**
	* Get TargetFrame of Area (only internal link)
	*/
	function getTargetFrameOfArea($a_nr)
	{
		$std_alias_item = new ilMediaAliasItem($this->pc_media_object->dom,
			$this->pc_media_object->hier_id, "Standard");
		return $std_alias_item->getTargetFrameOfArea($a_nr);
	}

	/**
	* Get Href of Area (only external link)
	*/
	function getHrefOfArea($a_nr)
	{
		$std_alias_item = new ilMediaAliasItem($this->pc_media_object->dom,
			$this->pc_media_object->hier_id, "Standard");
		return $std_alias_item->getHrefOfArea($a_nr);
	}

	/**
	* Update map areas
	*/
	function updateAreas()
	{
		global $lng, $ilCtrl;
		
		$std_alias_item = new ilMediaAliasItem($this->pc_media_object->dom,
			$this->pc_media_object->hier_id, "Standard");
		$areas = $std_alias_item->getMapAreas();
		foreach($areas as $area)
		{
			$std_alias_item->setAreaTitle($area["Nr"],
				ilUtil::stripSlashes($_POST["name_".$area["Nr"]]));
		}
		$this->page->update();
		
		ilUtil::sendInfo($lng->txt("cont_saved_map_data"), true);
		$ilCtrl->redirect($this, "editMapAreas");
	}
	
	/**
	* Make work file for editing
	*/
	function makeMapWorkCopy($a_edit_property = "", $a_area_nr = 0,
		$a_output_new_area = false, $a_area_type = "", $a_coords = "")
	{
		// create/update imagemap work copy
		$st_item = $this->media_object->getMediaItem("Standard");
		$st_alias_item = new ilMediaAliasItem($this->pc_media_object->dom,
			$this->pc_media_object->hier_id, "Standard");

		if ($a_edit_property == "shape")
		{
			$st_alias_item->makeMapWorkCopy($st_item, $a_area_nr, true,
				$a_output_new_area, $a_area_type, $a_coords);	// exclude area currently being edited
		}
		else
		{
			$st_alias_item->makeMapWorkCopy($st_item, $a_area_nr, false,
				$a_output_new_area, $a_area_type, $a_coords);
		}
	}

	function getAliasXML()
	{
		return $this->pc_media_object->dumpXML();
	}
}
?>
