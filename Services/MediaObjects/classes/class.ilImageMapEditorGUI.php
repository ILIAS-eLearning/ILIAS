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

/**
* User interface class for map editor
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilImageMapEditorGUI: ilInternalLinkGUI
*
* @ingroup ServicesMediaObjects
*/
class ilImageMapEditorGUI
{
	/**
	* Constructor
	*/
	function __construct($a_media_object)
	{
		$this->media_object = $a_media_object;
	}
	
	/**
	* Execute current command
	*/
	function executeCommand()
	{
		global $ilCtrl;
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();

		switch($next_class)
		{
			case "ilinternallinkgui":
				require_once("./Modules/LearningModule/classes/class.ilInternalLinkGUI.php");
				$link_gui = new ilInternalLinkGUI("Media_Media", 0);
				$link_gui->setMode("link");
				$link_gui->setSetLinkTargetScript(
					$ilCtrl->getLinkTarget($this,
					"setInternalLink"));
				$link_gui->filterLinkType("Media");
				$ret = $ilCtrl->forwardCommand($link_gui);
				break;

			default:
				if (isset($_POST["editImagemapForward"]) ||
					isset($_POST["editImagemapForward_x"]) ||
					isset($_POST["editImagemapForward_y"]))
				{
					$cmd = "editImagemapForward";
				}
				$ret = $this->$cmd();
				break;
		}
		
		return $ret;
	}
	
	/**
	* Show map areas
	*/
	function editMapAreas()
	{
		global $ilCtrl, $lng;
		
		$_SESSION["il_map_edit_target_script"] = $ilCtrl->getLinkTarget($this, "addArea");
		$this->handleMapParameters();

		$this->tpl = new ilTemplate("tpl.map_edit.html", true, true, "Services/MediaObjects");
		$this->tpl->setVariable("FORMACTION", $ilCtrl->getFormAction($this));

		$this->tpl->setVariable("TXT_IMAGEMAP", $lng->txt("cont_imagemap"));

		// create/update imagemap work copy
		$this->makeMapWorkCopy();

		$output = $this->getImageMapOutput();
		$this->tpl->setVariable("IMAGE_MAP", $output);

		$this->tpl->setVariable("MAP_AREA_TABLE", $this->getImageMapTableHTML());
		
		return $this->tpl->get();
	}

	/**
	* Get table HTML
	*/
	function getImageMapTableHTML()
	{
		include_once("./Services/MediaObjects/classes/class.ilImageMapTableGUI.php");
		$image_map_table = new ilImageMapTableGUI($this, "editMapAreas", $this->media_object);
		return $image_map_table->getHTML();
	}
	
	/**
	* handle parameter during map area editing (storing to session)
	*/
	function handleMapParameters()
	{
		if($_GET["ref_id"] != "")
		{
			$_SESSION["il_map_edit_ref_id"] = $_GET["ref_id"];
		}

		if($_GET["obj_id"] != "")
		{
			$_SESSION["il_map_edit_obj_id"] = $_GET["obj_id"];
		}

		if($_GET["hier_id"] != "")
		{
			$_SESSION["il_map_edit_hier_id"] = $_GET["hier_id"];
		}
		
		if($_GET["pc_id"] != "")
		{
			$_SESSION["il_map_edit_pc_id"] = $_GET["pc_id"];
		}
	}

	/**
	* show image map
	*/
	function showImageMap()
	{
		$item =& new ilMediaItem($_GET["item_id"]);
		$item->outputMapWorkCopy();
	}

	/**
	* Update map areas
	*/
	function updateAreas()
	{
		global $lng, $ilCtrl;
		
		$st_item =& $this->media_object->getMediaItem("Standard");
		$max = ilMapArea::_getMaxNr($st_item->getId());
		for ($i=1; $i<=$max; $i++)
		{
			$area =& new ilMapArea($st_item->getId(), $i);
			$area->setTitle(ilUtil::stripSlashes($_POST["name_".$i]));
			$area->update();
		}

		ilUtil::sendInfo($lng->txt("cont_saved_map_data"), true);
		$ilCtrl->redirect($this, "editMapAreas");
	}
	
	/**
	* Add a new rectangle
	*/
	function addRectangle()
	{
		$this->clearSessionVars();
		$_SESSION["il_map_edit_area_type"] = "Rect";
		return $this->addArea(false);
	}

	/**
	* Add a new circle
	*/
	function addCircle()
	{
		$this->clearSessionVars();
		$_SESSION["il_map_edit_area_type"] = "Circle";
		return $this->addArea(false);
	}

	/**
	* Add a new polygon
	*/
	function addPolygon()
	{
		$this->clearSessionVars();
		$_SESSION["il_map_edit_area_type"] = "Poly";
		return $this->addArea(false);
	}

	/**
	* Clear Session Vars
	*/
	function clearSessionVars()
	{
		$_SESSION["il_map_area_nr"] = "";
		$_SESSION["il_map_edit_coords"] = "";
		$_SESSION["il_map_edit_mode"] = "";
		$_SESSION["il_map_el_href"] = "";
		$_SESSION["il_map_il_type"] = "";
		$_SESSION["il_map_il_ltype"] = "";
		$_SESSION["il_map_il_target"] = "";
		$_SESSION["il_map_il_targetframe"] = "";
		$_SESSION["il_map_edit_area_type"] = "";
	}
	
	/**
	* Handle adding new area process
	*/
	function addArea($a_handle = true)
	{

		// handle map parameters
		if($a_handle)
		{
			$this->handleMapParameters();
		}

		$area_type = $_SESSION["il_map_edit_area_type"];
		$coords = $_SESSION["il_map_edit_coords"];
		include_once("./Services/MediaObjects/classes/class.ilMapArea.php");
		$cnt_coords = ilMapArea::countCoords($coords);

		// decide what to do next
		switch ($area_type)
		{
			// Rectangle
			case "Rect" :
				if ($cnt_coords < 2)
				{
					$html = $this->editMapArea(true, false, false);
					return $html;
				}
				else if ($cnt_coords == 2)
				{
					return $this->editMapArea(false, true, true);
				}
				break;

			// Circle
			case "Circle":
				if ($cnt_coords <= 1)
				{
					return $this->editMapArea(true, false, false);
				}
				else
				{
					if ($cnt_coords == 2)
					{
						$c = explode(",",$coords);
						$coords = $c[0].",".$c[1].",";	// determine radius
						$coords .= round(sqrt(pow(abs($c[3]-$c[1]),2)+pow(abs($c[2]-$c[0]),2)));
					}
					$_SESSION["il_map_edit_coords"] = $coords;

					return $this->editMapArea(false, true, true);
				}
				break;

			// Polygon
			case "Poly":
				if ($cnt_coords < 1)
				{
					return $this->editMapArea(true, false, false);
				}
				else if ($cnt_coords < 3)
				{
					return $this->editMapArea(true, true, false);
				}
				else
				{
					return $this->editMapArea(true, true, true);
				}
				break;
		}
	}

	/**
	* Edit a single map area
	*
	* @param	boolean		$a_get_next_coordinate		enable next coordinate input
	* @param	boolean		$a_output_new_area			output the new area
	* @param	boolean		$a_save_from				output save form
	* @param	string		$a_edit_property			"" | "link" | "shape"
	*/
	function editMapArea($a_get_next_coordinate = false, $a_output_new_area = false,
		$a_save_form = false, $a_edit_property = "", $a_area_nr = 0)
	{
		global $ilCtrl, $lng;
		
		$area_type = $_SESSION["il_map_edit_area_type"];
		$coords = $_SESSION["il_map_edit_coords"];
		include_once("./Services/MediaObjects/classes/class.ilMapArea.php");
		$cnt_coords = ilMapArea::countCoords($coords);

		$this->tpl = new ilTemplate("tpl.map_edit.html", true, true, "Services/MediaObjects");

		$this->tpl->setVariable("FORMACTION", $ilCtrl->getFormAction($this));

		$this->tpl->setVariable("TXT_IMAGEMAP", $lng->txt("cont_imagemap"));

		$this->tpl->setCurrentBlock("instruction");
		if ($a_edit_property != "link")
		{
			switch ($area_type)
			{
				// rectangle
				case "Rect" :
					if ($cnt_coords == 0)
					{
						$this->tpl->setVariable("INSTRUCTION", $lng->txt("cont_click_tl_corner"));
					}
					if ($cnt_coords == 1)
					{
						$this->tpl->setVariable("INSTRUCTION", $lng->txt("cont_click_br_corner"));
					}
					break;

				// circle
				case "Circle" :
					if ($cnt_coords == 0)
					{
						$this->tpl->setVariable("INSTRUCTION", $lng->txt("cont_click_center"));
					}
					if ($cnt_coords == 1)
					{
						$this->tpl->setVariable("INSTRUCTION", $lng->txt("cont_click_circle"));
					}
					break;

				// polygon
				case "Poly" :
					if ($cnt_coords == 0)
					{
						$this->tpl->setVariable("INSTRUCTION", $lng->txt("cont_click_starting_point"));
					}
					else if ($cnt_coords < 3)
					{
						$this->tpl->setVariable("INSTRUCTION", $lng->txt("cont_click_next_point"));
					}
					else
					{
						$this->tpl->setVariable("INSTRUCTION", $lng->txt("cont_click_next_or_save"));
					}
					break;
			}
		}
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("adm_content");


		// map properties input fields (name and link)
		if ($a_save_form)
		{
			if ($a_edit_property != "link" && $a_edit_property != "shape")
			{
				$this->tpl->setCurrentBlock("edit_name");
				$this->tpl->setVariable("VAR_NAME2", "area_name");
				$this->tpl->setVariable("TXT_NAME2", $lng->txt("cont_name"));
				$this->tpl->parseCurrentBlock();
			}

			if ($a_edit_property != "shape")
			{
				$this->tpl->setCurrentBlock("edit_link");
				$this->tpl->setVariable("TXT_LINK_EXT", $lng->txt("cont_link_ext"));
				$this->tpl->setVariable("TXT_LINK_INT", $lng->txt("cont_link_int"));
				if ($_SESSION["il_map_el_href"] != "")
				{
					$this->tpl->setVariable("VAL_LINK_EXT", $_SESSION["il_map_el_href"]);
				}
				else
				{
					$this->tpl->setVariable("VAL_LINK_EXT", "http://");
				}
				$this->tpl->setVariable("VAR_LINK_EXT", "area_link_ext");
				$this->tpl->setVariable("VAR_LINK_TYPE", "area_link_type");
				if ($_SESSION["il_map_il_ltype"] != "int")
				{
					$this->tpl->setVariable("EXT_CHECKED", "checked=\"1\"");
				}
				else
				{
					$this->tpl->setVariable("INT_CHECKED", "checked=\"1\"");
				}

				// internal link
				$link_str = "";
				if($_SESSION["il_map_il_target"] != "")
				{
					$link_str = $this->getMapAreaLinkString($_SESSION["il_map_il_target"],
						$_SESSION["il_map_il_type"], $_SESSION["il_map_il_targetframe"]);
					$this->tpl->setVariable("VAL_LINK_INT", $link_str);
				}

				// internal link list
				$ilCtrl->setParameter($this, "linkmode", "map");
				$this->tpl->setVariable("LINK_ILINK",
					$ilCtrl->getLinkTargetByClass("ilInternalLinkGUI", "showLinkHelp",
					array("ilObjMediaObjectGUI"), true));
				$this->tpl->setVariable("TXT_ILINK", "[".$lng->txt("cont_get_link")."]");

				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("new_area");
			$this->tpl->setVariable("TXT_SAVE", $lng->txt("save"));
			$this->tpl->setVariable("BTN_SAVE", "saveArea");
			if ($a_edit_property == "")
			{
				$this->tpl->setVariable("TXT_NEW_AREA", $lng->txt("cont_new_area"));
			}
			else
			{
				$this->tpl->setVariable("TXT_NEW_AREA", $lng->txt("cont_edit_area"));
			}
			$this->tpl->parseCurrentBlock();
		}
		
		$this->makeMapWorkCopy($a_edit_property, $a_area_nr,
			$a_output_new_area, $area_type, $coords);
		
		$edit_mode = ($a_get_next_coordinate)
			? "get_coords"
			: "";
		$output = $this->getImageMapOutput($edit_mode);
		$this->tpl->setVariable("IMAGE_MAP", $output);

		return $this->tpl->get();
	}
	
	/**
	* Make work file for editing
	*/
	function makeMapWorkCopy($a_edit_property = "", $a_area_nr = 0,
		$a_output_new_area = false, $a_area_type = "", $a_coords = "")
	{
		// create/update imagemap work copy
		$st_item = $this->media_object->getMediaItem("Standard");

		if ($a_edit_property == "shape")
		{
			$st_item->makeMapWorkCopy($a_area_nr, true);	// exclude area currently being edited
		}
		else
		{
			$st_item->makeMapWorkCopy($a_area_nr, false);
		}

		if ($a_output_new_area)
		{
			$st_item->addAreaToMapWorkCopy($a_area_type, $a_coords);
		}
	}
	
	/**
	* Render the image map.
	*/
	function getImageMapOutput($a_map_edit_mode = "")
	{
		global $ilCtrl;
		
		$st_item = $this->media_object->getMediaItem("Standard");
		
		// output image map
		$xml = "<dummy>";
		$xml.= $this->getAliasXML();
		$xml.= $this->media_object->getXML(IL_MODE_OUTPUT);
		$xml.="</dummy>";
		$xsl = file_get_contents("./Services/COPage/xsl/page.xsl");
		$args = array( '/_xml' => $xml, '/_xsl' => $xsl );
		$xh = xslt_create();
		$wb_path = ilUtil::getWebspaceDir("output");
		$mode = "media";
		$params = array ('map_edit_mode' => $a_map_edit_mode,
			'map_item' => $st_item->getId(),
			'mode' => $mode,
			'image_map_link' => $ilCtrl->getLinkTarget($this, "showImageMap"),
			'link_params' => "ref_id=".$_GET["ref_id"]."&rand=".rand(1,999999),
			'ref_id' => $_GET["ref_id"],
			'pg_frame' => "",
			'webspace_path' => $wb_path);
		$output = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args, $params);
		echo xslt_error($xh);
		xslt_free($xh);
		
		return $output;
	}

	function getAliasXML()
	{
		return $this->media_object->getXML(IL_MODE_ALIAS);
	}

	/**
	* Get text name of internal link
	*
	* @param	string		$a_target		target object link id
	* @param	string		$a_type			type
	* @param	string		$a_frame		target frame
	*
	* @access	private
	*/
	function getMapAreaLinkString($a_target, $a_type, $a_frame)
	{
		global $lng;
		
		$t_arr = explode("_", $a_target);
		if ($a_frame != "")
		{
			$frame_str = " (".$a_frame." Frame)";
		}
		switch($a_type)
		{
			case "StructureObject":
				require_once("./Modules/LearningModule/classes/class.ilLMObject.php");
				$title = ilLMObject::_lookupTitle($t_arr[count($t_arr) - 1]);
				$link_str = $lng->txt("chapter").
					": ".$title." [".$t_arr[count($t_arr) - 1]."]".$frame_str;
				break;

			case "PageObject":
				require_once("./Modules/LearningModule/classes/class.ilLMObject.php");
				$title = ilLMObject::_lookupTitle($t_arr[count($t_arr) - 1]);
				$link_str = $lng->txt("page").
					": ".$title." [".$t_arr[count($t_arr) - 1]."]".$frame_str;
				break;

			case "GlossaryItem":
				require_once("./Modules/Glossary/classes/class.ilGlossaryTerm.php");
				$term =& new ilGlossaryTerm($t_arr[count($t_arr) - 1]);
				$link_str = $lng->txt("term").
					": ".$term->getTerm()." [".$t_arr[count($t_arr) - 1]."]".$frame_str;
				break;

			case "MediaObject":
				require_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
				$mob =& new ilObjMediaObject($t_arr[count($t_arr) - 1]);
				$link_str = $lng->txt("mob").
					": ".$mob->getTitle()." [".$t_arr[count($t_arr) - 1]."]".$frame_str;
				break;
				
			case "RepositoryItem":
				$title = ilObject::_lookupTitle(
					ilObject::_lookupObjId($t_arr[count($t_arr) - 1]));
				$link_str = $lng->txt("obj_".$t_arr[count($t_arr) - 2]).
					": ".$title." [".$t_arr[count($t_arr) - 1]."]".$frame_str;
				break;
		}

		return $link_str;
	}

	/**
	* Get image map coordinates.
	*/
	function editImagemapForward()
	{
		ilImageMapEditorGUI::_recoverParameters();

		if ($_SESSION["il_map_edit_coords"] != "")
		{
			$_SESSION["il_map_edit_coords"] .= ",";
		}

		$_SESSION["il_map_edit_coords"] .= $_POST["editImagemapForward_x"].",".
			$_POST["editImagemapForward_y"];

		// call editing script
		ilUtil::redirect($_SESSION["il_map_edit_target_script"]);
	}

	/**
	* Recover parameters from session variables (static)
	*/
	function _recoverParameters()
	{
		$_GET["ref_id"] = $_SESSION["il_map_edit_ref_id"];
		$_GET["obj_id"] = $_SESSION["il_map_edit_obj_id"];
		$_GET["hier_id"] = $_SESSION["il_map_edit_hier_id"];
		$_GET["pc_id"] = $_SESSION["il_map_edit_pc_id"];
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
				$st_item = $this->media_object->getMediaItem("Standard");
				$max = ilMapArea::_getMaxNr($st_item->getId());
				$area = new ilMapArea($st_item->getId(), $_SESSION["il_map_area_nr"]);

				if ($_POST["area_link_type"] == IL_INT_LINK)
				{
					$area->setLinkType(IL_INT_LINK);
					$area->setType($_SESSION["il_map_il_type"]);
					$area->setTarget($_SESSION["il_map_il_target"]);
					$area->setTargetFrame($_SESSION["il_map_il_targetframe"]);
				}
				else
				{
					$area->setLinkType(IL_EXT_LINK);
					$area->setHref(ilUtil::stripSlashes($_POST["area_link_ext"]));
				}
				$area->update();
				break;

			// save edited shape
			case "edit_shape":
				$st_item = $this->media_object->getMediaItem("Standard");
				$max = ilMapArea::_getMaxNr($st_item->getId());
				$area =& new ilMapArea($st_item->getId(), $_SESSION["il_map_area_nr"]);

				$area->setShape($_SESSION["il_map_edit_area_type"]);
				$area->setCoords($_SESSION["il_map_edit_coords"]);
				$area->update();
				break;

			// save new area
			default:
				$area_type = $_SESSION["il_map_edit_area_type"];
				$coords = $_SESSION["il_map_edit_coords"];

				$st_item = $this->media_object->getMediaItem("Standard");
				$max = ilMapArea::_getMaxNr($st_item->getId());

				// make new area object
				$area = new ilMapArea();
				$area->setItemId($st_item->getId());
				$area->setShape($area_type);
				$area->setCoords($coords);
				$area->setNr($max + 1);
				$area->setTitle(ilUtil::stripSlashes($_POST["area_name"]));
				switch($_POST["area_link_type"])
				{
					case "ext":
						$area->setLinkType(IL_EXT_LINK);
						$area->setHref($_POST["area_link_ext"]);
						break;

					case "int":
						$area->setLinkType(IL_INT_LINK);
						$area->setType($_SESSION["il_map_il_type"]);
						$area->setTarget($_SESSION["il_map_il_target"]);
						$area->setTargetFrame($_SESSION["il_map_il_targetframe"]);
						break;
				}

				// put area into item and update media object
				$st_item->addMapArea($area);
				$this->media_object->update();
				break;
		}

		//$this->initMapParameters();
		ilUtil::sendInfo($lng->txt("cont_saved_map_area"), true);
		$ilCtrl->redirect($this, "editMapAreas");
	}

	/**
	* Set internal link
	*/
	function setInternalLink()
	{
		$_SESSION["il_map_il_type"] = $_GET["linktype"];
		$_SESSION["il_map_il_ltype"] = "int";

		$_SESSION["il_map_il_target"] = $_GET["linktarget"];
		$_SESSION["il_map_il_targetframe"] = $_GET["linktargetframe"];
		switch ($_SESSION["il_map_edit_mode"])
		{
			case "edit_link":
				return $this->setLink();
				break;

			default:
				return $this->addArea();
				break;
		}
	}
	
	/**
	* Set link
	*/
	function setLink($a_handle = true)
	{
		global $lng, $ilCtrl;

		if($a_handle)
		{
			$this->handleMapParameters();
		}
		if ($_SESSION["il_map_area_nr"] != "")
		{
			$_POST["area"][0] = $_SESSION["il_map_area_nr"];
		}
		if (!isset($_POST["area"]))
		{
			ilUtil::sendInfo($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "editMapAreas");
		}

		if (count($_POST["area"]) > 1)
		{
			//$this->ilias->raiseError($this->lng->txt("cont_select_max_one_item"),$this->ilias->error_obj->MESSAGE);
			ilUtil::sendInfo($lng->txt("cont_select_max_one_item"), true);
			$ilCtrl->redirect($this, "editMapAreas");
		}


		if ($_SESSION["il_map_edit_mode"] != "edit_link")
		{
			$_SESSION["il_map_area_nr"] = $_POST["area"][0];
			$_SESSION["il_map_il_ltype"] = $this->getLinkTypeOfArea($_POST["area"][0]);
			$_SESSION["il_map_edit_mode"] = "edit_link";
			$_SESSION["il_map_edit_target_script"] = $ilCtrl->getLinkTarget($this, "setLink");
			if ($_SESSION["il_map_il_ltype"] == IL_INT_LINK)
			{
				$_SESSION["il_map_il_type"] = $this->getTypeOfArea($_POST["area"][0]);
				$_SESSION["il_map_il_target"] = $this->getTargetOfArea($_POST["area"][0]);
				$_SESSION["il_map_il_targetframe"] = $this->getTargetFrameOfArea($_POST["area"][0]);
			}
			else
			{
				$_SESSION["il_map_el_href"] = $this->getHrefOfArea($_POST["area"][0]);
			}
		}

		return $this->editMapArea(false, false, true, "link", $_POST["area"][0]);
	}

	/**
	* Get Link Type of Area
	*/
	function getLinkTypeOfArea($a_nr)
	{
		$st_item = $this->media_object->getMediaItem("Standard");
		$area = $st_item->getMapArea($a_nr);
		return $area->getLinkType();
	}

	/**
	* Get Type of Area (only internal link)
	*/
	function getTypeOfArea($a_nr)
	{
		$st_item = $this->media_object->getMediaItem("Standard");
		$area = $st_item->getMapArea($a_nr);
		return $area->getType();
	}

	/**
	* Get Target of Area (only internal link)
	*/
	function getTargetOfArea($a_nr)
	{
		$st_item = $this->media_object->getMediaItem("Standard");
		$area = $st_item->getMapArea($a_nr);
		return $area->getTarget();
	}

	/**
	* Get TargetFrame of Area (only internal link)
	*/
	function getTargetFrameOfArea($a_nr)
	{
		$st_item = $this->media_object->getMediaItem("Standard");
		$area = $st_item->getMapArea($a_nr);
		return $area->getTargetFrame();
	}

	/**
	* Get Href of Area (only external link)
	*/
	function getHrefOfArea($a_nr)
	{
		$st_item = $this->media_object->getMediaItem("Standard");
		$area = $st_item->getMapArea($a_nr);
		return $area->getHref();
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

		$st_item = $this->media_object->getMediaItem("Standard");
		$max = ilMapArea::_getMaxNr($st_item->getId());

		if (count($_POST["area"]) > 0)
		{
			$i = 0;

			foreach ($_POST["area"] as $area_nr)
			{
				$st_item->deleteMapArea($area_nr - $i);
				$i++;
			}

			$this->media_object->update();
			ilUtil::sendInfo($lng->txt("cont_areas_deleted"), true);
		}

		$ilCtrl->redirect($this, "editMapAreas");
	}

	/**
	* Edit existing link
	*/
	function editLink()
	{
		$_SESSION["il_map_edit_coords"] = "";
		$_SESSION["il_map_edit_mode"] = "";
		$_SESSION["il_map_el_href"] = "";
		$_SESSION["il_map_il_type"] = "";
		$_SESSION["il_map_il_ltype"] = "";
		$_SESSION["il_map_il_target"] = "";
		$_SESSION["il_map_il_targetframe"] = "";
		$_SESSION["il_map_area_nr"] = "";
		return $this->setLink(false);
	}

	/**
	* Edit an existing shape (make it a rectangle)
	*/
	function editShapeRectangle()
	{
		$this->clearSessionVars();
		$_SESSION["il_map_edit_area_type"] = "Rect";
		return $this->setShape(false);
	}

	/**
	* Edit an existing shape (make it a circle)
	*/
	function editShapeCircle()
	{
		$this->clearSessionVars();
		$_SESSION["il_map_edit_area_type"] = "Circle";
		return $this->setShape(false);
	}

	/**
	* Edit an existing shape (make it a polygon)
	*/
	function editShapePolygon()
	{
		$this->clearSessionVars();
		$_SESSION["il_map_edit_area_type"] = "Poly";
		return $this->setShape(false);
	}

	/**
	* edit shape of existing map area
	*/
	function setShape($a_handle = true)
	{
		global $lng, $ilCtrl;
		
		if($a_handle)
		{
			$this->handleMapParameters();
		}
		if($_POST["areatype2"] != "")
		{
			$_SESSION["il_map_edit_area_type"] = $_POST["areatype2"];
		}
		if ($_SESSION["il_map_area_nr"] != "")
		{
			$_POST["area"][0] = $_SESSION["il_map_area_nr"];
		}
		if (!isset($_POST["area"]))
		{
			ilUtil::sendInfo($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "editMapAreas");
		}

		if (count($_POST["area"]) > 1)
		{
			ilUtil::sendInfo($lng->txt("cont_select_max_one_item"), true);
			$ilCtrl->redirect($this, "editMapAreas");
		}

		if ($_SESSION["il_map_edit_mode"] != "edit_shape")
		{
			$_SESSION["il_map_area_nr"] = $_POST["area"][0];
			$_SESSION["il_map_edit_mode"] = "edit_shape";
			$_SESSION["il_map_edit_target_script"] = $ilCtrl->getLinkTarget($this, "setShape");
		}


		$area_type = $_SESSION["il_map_edit_area_type"];
		$coords = $_SESSION["il_map_edit_coords"];
		$cnt_coords = ilMapArea::countCoords($coords);

		// decide what to do next
		switch ($area_type)
		{
			// Rectangle
			case "Rect" :
				if ($cnt_coords < 2)
				{
					return $this->editMapArea(true, false, false, "shape", $_POST["area"][0]);
				}
				else if ($cnt_coords == 2)
				{
					return $this->saveArea();
				}
				break;

			// Circle
			case "Circle":
				if ($cnt_coords <= 1)
				{
					return $this->editMapArea(true, false, false, "shape", $_POST["area"][0]);
				}
				else
				{
					if ($cnt_coords == 2)
					{
						$c = explode(",",$coords);
						$coords = $c[0].",".$c[1].",";	// determine radius
						$coords .= round(sqrt(pow(abs($c[3]-$c[1]),2)+pow(abs($c[2]-$c[0]),2)));
					}
					$_SESSION["il_map_edit_coords"] = $coords;

					return $this->saveArea();
				}
				break;

			// Polygon
			case "Poly":
				if ($cnt_coords < 1)
				{
					return $this->editMapArea(true, false, false, "shape", $_POST["area"][0]);
				}
				else if ($cnt_coords < 3)
				{
					return $this->editMapArea(true, true, false, "shape", $_POST["area"][0]);
				}
				else
				{
					return $this->editMapArea(true, true, true, "shape", $_POST["area"][0]);
				}
				break;
		}

	}

}
?>
