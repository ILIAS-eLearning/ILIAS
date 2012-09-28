<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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
	function __construct($a_content_obj, $a_page)
	{
		$this->content_obj = $a_content_obj;
		$this->page = $a_page;
		parent::__construct($a_content_obj->getMediaObject());
				
		$this->std_alias_item = new ilMediaAliasItem($this->content_obj->dom,
			$this->content_obj->hier_id, "Standard", $this->content_obj->getPcId(),
			$this->getParentNodeName());
	}
	
	/**
	 * Get parent node name
	 *
	 * @return string name of parent node
	 */
	function getParentNodeName()
	{
		return "MediaObject";
	}

	/**
	* Get table HTML
	*/
	function getImageMapTableHTML()
	{
		include_once("./Services/COPage/classes/class.ilPCImageMapTableGUI.php");
		$image_map_table = new ilPCImageMapTableGUI($this, "editMapAreas", $this->content_obj,
			$this->getParentNodeName());
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
//				$std_alias_item = new ilMediaAliasItem($this->content_obj->dom,
//					$this->content_obj->hier_id, "Standard", $this->content_obj->getPcId());

				if ($_POST["area_link_type"] == IL_INT_LINK)
				{
					$this->std_alias_item->setAreaIntLink($_SESSION["il_map_area_nr"],
						$_SESSION["il_map_il_type"], $_SESSION["il_map_il_target"],
						$_SESSION["il_map_il_targetframe"]);
				}
				else if ($_POST["area_link_type"] == IL_NO_LINK)
				{
					$this->std_alias_item->setAreaExtLink($_SESSION["il_map_area_nr"],
						"");
				}
				else
				{
					$this->std_alias_item->setAreaExtLink($_SESSION["il_map_area_nr"],
						ilUtil::stripSlashes($_POST["area_link_ext"]));
				}
				$this->updated = $this->page->update();
				break;

			// save edited shape
			case "edit_shape":
//				$std_alias_item = new ilMediaAliasItem($this->content_obj->dom,
//					$this->content_obj->hier_id, "Standard", $this->content_obj->getPcId());
				$this->std_alias_item->setShape($_SESSION["il_map_area_nr"],
					$_SESSION["il_map_edit_area_type"], $_SESSION["il_map_edit_coords"]);
				$this->updated = $this->page->update();
				break;

			// save new area
			default:
				$area_type = $_SESSION["il_map_edit_area_type"];
				$coords = $_SESSION["il_map_edit_coords"];

				switch($_POST["area_link_type"])
				{
					case IL_EXT_LINK:
						$link = array(
							"LinkType" => IL_EXT_LINK,
							"Href" => ilUtil::stripSlashes($_POST["area_link_ext"]));
						break;

					case IL_NO_LINK:
						$link = array(
							"LinkType" => IL_EXT_LINK,
							"Href" => "");
						break;

					case IL_INT_LINK:
						$link = array(
							"LinkType" => IL_INT_LINK,
							"Type" => $_SESSION["il_map_il_type"],
							"Target" => $_SESSION["il_map_il_target"],
							"TargetFrame" => $_SESSION["il_map_il_targetframe"]);
						break;
				}

//				$std_alias_item = new ilMediaAliasItem($this->content_obj->dom,
//					$this->content_obj->hier_id, "Standard", $this->content_obj->getPcId());
				$this->std_alias_item->addMapArea($area_type, $coords,
					ilUtil::stripSlashes($_POST["area_name"]), $link);
				$this->updated = $this->page->update();

				break;
		}

		//$this->initMapParameters();
		ilUtil::sendSuccess($lng->txt("cont_saved_map_area"), true);
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
			ilUtil::sendFailure($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "editMapAreas");
		}

//		$std_alias_item = new ilMediaAliasItem($this->content_obj->dom,
//			$this->content_obj->hier_id, "Standard", $this->content_obj->getPcId());

		if (count($_POST["area"]) > 0)
		{
			$i = 0;
			arsort($_POST["area"]);
			foreach ($_POST["area"] as $area_nr)
			{
				$this->std_alias_item->deleteMapArea($area_nr);
			}
			$this->updated = $this->page->update();
			ilUtil::sendSuccess($lng->txt("cont_areas_deleted"), true);
		}

		$ilCtrl->redirect($this, "editMapAreas");
	}

	/**
	* Get Link Type of Area
	*/
	function getLinkTypeOfArea($a_nr)
	{
//		$std_alias_item = new ilMediaAliasItem($this->content_obj->dom,
//			$this->content_obj->hier_id, "Standard", $this->content_obj->getPcId());
		return $this->std_alias_item->getLinkTypeOfArea($a_nr);
	}

	/**
	* Get Type of Area (only internal link)
	*/
	function getTypeOfArea($a_nr)
	{
//		$std_alias_item = new ilMediaAliasItem($this->content_obj->dom,
//			$this->content_obj->hier_id, "Standard", $this->content_obj->getPcId());
		return $this->std_alias_item->getTypeOfArea($a_nr);
	}

	/**
	* Get Target of Area (only internal link)
	*/
	function getTargetOfArea($a_nr)
	{
//		$std_alias_item = new ilMediaAliasItem($this->content_obj->dom,
//			$this->content_obj->hier_id, "Standard", $this->content_obj->getPcId());
		return $this->std_alias_item->getTargetOfArea($a_nr);
	}

	/**
	* Get TargetFrame of Area (only internal link)
	*/
	function getTargetFrameOfArea($a_nr)
	{
//		$std_alias_item = new ilMediaAliasItem($this->content_obj->dom,
//			$this->content_obj->hier_id, "Standard", $this->content_obj->getPcId());
		return $this->std_alias_item->getTargetFrameOfArea($a_nr);
	}

	/**
	* Get Href of Area (only external link)
	*/
	function getHrefOfArea($a_nr)
	{
//		$std_alias_item = new ilMediaAliasItem($this->content_obj->dom,
//			$this->content_obj->hier_id, "Standard", $this->content_obj->getPcId());
		return $this->std_alias_item->getHrefOfArea($a_nr);
	}

	/**
	* Update map areas
	*/
	function updateAreas()
	{
		global $lng, $ilCtrl;
		
//		$std_alias_item = new ilMediaAliasItem($this->content_obj->dom,
//			$this->content_obj->hier_id, "Standard", $this->content_obj->getPcId());
		$areas = $this->std_alias_item->getMapAreas();
		foreach($areas as $area)
		{
			$this->std_alias_item->setAreaTitle($area["Nr"],
				ilUtil::stripSlashes($_POST["name_".$area["Nr"]]));
			$this->std_alias_item->setAreaHighlightMode($area["Nr"],
				ilUtil::stripSlashes($_POST["hl_mode_".$area["Nr"]]));
			$this->std_alias_item->setAreaHighlightClass($area["Nr"],
				ilUtil::stripSlashes($_POST["hl_class_".$area["Nr"]]));
		}
		$this->page->update();
		
		ilUtil::sendSuccess($lng->txt("cont_saved_map_data"), true);
		$ilCtrl->redirect($this, "editMapAreas");
	}
	
	/**
	* Make work file for editing
	*/
	function makeMapWorkCopy($a_edit_property = "", $a_area_nr = 0,
		$a_output_new_area = false, $a_area_type = "", $a_coords = "")
	{
// old for pc media object
//		$media_object = $this->media_object->getMediaItem("Standard");
		$media_object = $this->content_obj->getMediaObject();
		
		// create/update imagemap work copy
		$st_item = $media_object->getMediaItem("Standard");
		$st_alias_item = new ilMediaAliasItem($this->content_obj->dom,
			$this->content_obj->hier_id, "Standard", $this->content_obj->getPcId(),
			$this->getParentNodeName());

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
		return $this->content_obj->dumpXML();
	}
}
?>