<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

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
		global $ilCtrl, $tpl;
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();

		switch($next_class)
		{
			case "ilinternallinkgui":
				require_once("./Services/Link/classes/class.ilInternalLinkGUI.php");
				$link_gui = new ilInternalLinkGUI("Media_Media", 0);
				$link_gui->setMode("link");
				$link_gui->setSetLinkTargetScript(
					$ilCtrl->getLinkTarget($this,
					"setInternalLink"));
				$link_gui->filterLinkType("File");
				$link_gui->setMode("asynch");
				$ret = $ilCtrl->forwardCommand($link_gui);
				break;

			default:
				require_once("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");
				ilObjMediaObjectGUI::includePresentationJS();
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
		global $ilCtrl, $lng, $ilToolbar;
		
		$_SESSION["il_map_edit_target_script"] = $ilCtrl->getLinkTarget($this, "addArea",
			"", false, false);
		$this->handleMapParameters();

		$this->tpl = new ilTemplate("tpl.map_edit.html", true, true, "Services/MediaObjects");
		$this->tpl->setVariable("FORMACTION", $ilCtrl->getFormAction($this));

		// create/update imagemap work copy
		$this->makeMapWorkCopy();

		$output = $this->getImageMapOutput();
		$this->tpl->setVariable("IMAGE_MAP", $output);
		
		$this->tpl->setVariable("TOOLBAR", $this->getToolbar()->getHTML());
		
		// table
		$this->tpl->setVariable("MAP_AREA_TABLE", $this->getImageMapTableHTML());
		
		return $this->tpl->get();
	}

	/**
	 * Get toolbar
	 *
	 * @return object toolbar
	 */
	function getToolbar()
	{
		global $ilCtrl, $lng, $tpl;
		
		// toolbar
		$tb = new ilToolbarGUI();
		$tb->setFormAction($ilCtrl->getFormAction($this));
		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
		$options = array(
			"WholePicture" => $lng->txt("cont_WholePicture"),
			"Rect" => $lng->txt("cont_Rect"),
			"Circle" => $lng->txt("cont_Circle"),
			"Poly" => $lng->txt("cont_Poly"),
			);
		$si = new ilSelectInputGUI($lng->txt("cont_shape"), "shape");
		$si->setOptions($options);
		$tb->addInputItem($si, true);
		$tb->addFormButton($lng->txt("cont_add_area"), "addNewArea");
		
		
		// highlight mode
/*		if (strtolower(get_class($this)) == "ilimagemapeditorgui")
		{
			$st_item = $this->media_object->getMediaItem("Standard");
			$tb->addSeparator();
			$options = ilMapArea::getAllHighlightModes();
			$hl = new ilSelectInputGUI($lng->txt("cont_highlight_mode"), "highlight_mode");
			$hl->setOptions($options);
//			$hl->setValue($st_item->getHighlightMode());
			$tb->addInputItem($hl, true);
			$options = ilMapArea::getAllHighlightClasses();
			$hc = new ilSelectInputGUI($lng->txt("cont_highlight_class"), "highlight_class");
			$hc->setOptions($options);
//			$hc->setValue($st_item->getHighlightClass());
			$tb->addInputItem($hc, false);
			$tb->addFormButton($lng->txt("cont_set"), "setHighlight");
		}*/
		
		return $tb;
	}
	
	
	/**
	 * Get editor title
	 *
	 * @return string editor title
	 */
	function getEditorTitle()
	{
		global $lng;
		
		return $lng->txt("cont_imagemap");
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
			$area->setHighlightMode(ilUtil::stripSlashes($_POST["hl_mode_".$i]));
			$area->setHighlightClass(ilUtil::stripSlashes($_POST["hl_class_".$i]));
			$area->update();
		}

		ilUtil::sendSuccess($lng->txt("cont_saved_map_data"), true);
		$ilCtrl->redirect($this, "editMapAreas");
	}

	/**
	 * Add area
	 */
	function addNewArea()
	{
		switch ($_POST["shape"])
		{
			case "WholePicture": return $this->linkWholePicture();
			case "Rect": return $this->addRectangle();
			case "Circle": return $this->addCircle();
			case "Poly": return $this->addPolygon();
		}
	}
	
	
	/**
	 * Link the whole picture
	 */
	function linkWholePicture()
	{
		$this->clearSessionVars();
		$_SESSION["il_map_edit_area_type"] = "WholePicture";

		return $this->editMapArea(false, false, true);
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

			// Whole picture
			case "WholePicture":
				return $this->editMapArea(false, false, true);
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
		global $ilCtrl, $lng, $tpl;
		
		$area_type = $_SESSION["il_map_edit_area_type"];
		$coords = $_SESSION["il_map_edit_coords"];
		include_once("./Services/MediaObjects/classes/class.ilMapArea.php");
		$cnt_coords = ilMapArea::countCoords($coords);

		$this->tpl = new ilTemplate("tpl.map_edit.html", true, true, "Services/MediaObjects");

		$this->tpl->setVariable("FORMACTION", $ilCtrl->getFormAction($this));

		if ($a_edit_property != "link")
		{
			switch ($area_type)
			{
				// rectangle
				case "Rect" :
					if ($cnt_coords == 0)
					{
						ilUtil::sendInfo($lng->txt("cont_click_tl_corner"));
					}
					if ($cnt_coords == 1)
					{
						ilUtil::sendInfo($lng->txt("cont_click_br_corner"));
					}
					break;

				// circle
				case "Circle" :
					if ($cnt_coords == 0)
					{
						ilUtil::sendInfo($lng->txt("cont_click_center"));
					}
					if ($cnt_coords == 1)
					{
						ilUtil::sendInfo($lng->txt("cont_click_circle"));
					}
					break;

				// polygon
				case "Poly" :
					if ($cnt_coords == 0)
					{
						ilUtil::sendInfo($lng->txt("cont_click_starting_point"));
					}
					else if ($cnt_coords < 3)
					{
						ilUtil::sendInfo($lng->txt("cont_click_next_point"));
					}
					else
					{
						ilUtil::sendInfo($lng->txt("cont_click_next_or_save"));
					}
					break;
			}
		}


		// map properties input fields (name and link)
		if ($a_save_form)
		{
			if ($a_edit_property != "shape")
			{
				// prepare link gui
				$ilCtrl->setParameter($this, "linkmode", "map");
				include_once("./Services/Link/classes/class.ilInternalLinkGUI.php");
				$this->tpl->setCurrentBlock("int_link_prep");
				$this->tpl->setVariable("INT_LINK_PREP", ilInternalLinkGUI::getInitHTML(
					$ilCtrl->getLinkTargetByClass("ilinternallinkgui",
							"", false, true, false)));
				$this->tpl->parseCurrentBlock();
			}
			$form = $this->initAreaEditingForm($a_edit_property);
			$this->tpl->setVariable("FORM", $form->getHTML());
		}
		
		$this->makeMapWorkCopy($a_edit_property, $a_area_nr,
			$a_output_new_area, $area_type, $coords);
		
		$edit_mode = ($a_get_next_coordinate)
			? "get_coords"
			: (($a_output_new_area)
				? "new_area"
				:"");
		$output = $this->getImageMapOutput($edit_mode);
		$this->tpl->setVariable("IMAGE_MAP", $output);

		return $this->tpl->get();
	}
	
	/**
	 * Init area editing form.
	 *
	 * @param        int        $a_mode        Edit Mode
	 */
	public function initAreaEditingForm($a_edit_property)
	{
		global $lng, $ilCtrl;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setOpenTag(false);
		$form->setCloseTag(false);
		
		// link
		if ($a_edit_property != "shape")
		{
			// 
			$radg = new ilRadioGroupInputGUI($lng->txt("cont_link"), "area_link_type");
			if ($_SESSION["il_map_il_ltype"] != "int")
			{
				if ($_SESSION["il_map_el_href"] == "")
				{
					$radg->setValue("no");
				}
				else
				{
					$radg->setValue("ext");
				}
			}
			else
			{
				$radg->setValue("int");
			}
			
			// external link
			$ext = new ilRadioOption($lng->txt("cont_link_ext"), "ext");
			$radg->addOption($ext);
			
				$ti = new ilTextInputGUI("", "area_link_ext");
				$ti->setMaxLength(200);
				$ti->setSize(50);
				if ($_SESSION["il_map_el_href"] != "")
				{
					$ti->setValue($_SESSION["il_map_el_href"]);
				}
				else
				{
					$ti->setValue("http://");
				}
				$ext->addSubItem($ti);
			
			// internal link
			$int = new ilRadioOption($lng->txt("cont_link_int"), "int");
			$radg->addOption($int);
			
				$ne = new ilNonEditableValueGUI("", "", true);
				$link_str = "";
				if($_SESSION["il_map_il_target"] != "")
				{
					$link_str = $this->getMapAreaLinkString($_SESSION["il_map_il_target"],
						$_SESSION["il_map_il_type"], $_SESSION["il_map_il_targetframe"]);
				}
				$ne->setValue($link_str.
					'&nbsp;<a id="iosEditInternalLinkTrigger" href="#">'.
					"[".$lng->txt("cont_get_link")."]".
					'</a>'
					);
				$int->addSubItem($ne);
				
			// no link
			$no = new ilRadioOption($lng->txt("cont_link_no"), "no");
			$radg->addOption($no);
			
			$form->addItem($radg);
		}

		
		// name
		if ($a_edit_property != "link" && $a_edit_property != "shape")
		{ 
			$ti = new ilTextInputGUI($lng->txt("cont_name"), "area_name");
			$ti->setMaxLength(200);
			$ti->setSize(20);
			$form->addItem($ti);
		}
		
		// save and cancel commands
		if ($a_edit_property == "")
		{
			$form->setTitle($lng->txt("cont_new_area"));
			$form->addCommandButton("saveArea", $lng->txt("save"));
		}
		else
		{
			$form->setTitle($lng->txt("cont_new_area"));
			$form->addCommandButton("saveArea", $lng->txt("save"));
		}
	                
//		$form->setFormAction($ilCtrl->getFormAction($this));
		
		return $form;
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
		$xml.= $this->getAdditionalPageXML();
		$xml.="</dummy>";
		$xsl = file_get_contents("./Services/COPage/xsl/page.xsl");
//echo htmlentities($xml);
		$args = array( '/_xml' => $xml, '/_xsl' => $xsl );
		$xh = xslt_create();
		$wb_path = ilUtil::getWebspaceDir("output")."/";
		$mode = "media";
//echo htmlentities($ilCtrl->getLinkTarget($this, "showImageMap"));
		$params = array ('map_edit_mode' => $a_map_edit_mode,
			'map_item' => $st_item->getId(),
			'map_mob_id' => $this->media_object->getId(),
			'mode' => $mode,
			'media_mode' => 'enable',
			'image_map_link' => $ilCtrl->getLinkTarget($this, "showImageMap", "", false, false),
			'link_params' => "ref_id=".$_GET["ref_id"]."&rand=".rand(1,999999),
			'ref_id' => $_GET["ref_id"],
			'pg_frame' => "",
			'enlarge_path' => ilUtil::getImagePath("enlarge.svg"),
			'webspace_path' => $wb_path);
		$output = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args, $params);
		echo xslt_error($xh);
		xslt_free($xh);
		
		$output = $this->outputPostProcessing($output);
		
		return $output;
	}
	
	/**
	 * Get additional page xml (to be overwritten)
	 *
	 * @return string additional page xml
	 */
	function getAdditionalPageXML()
	{
		return "";
	}
	
	/**
	 * Output post processing
	 *
	 * @param
	 * @return
	 */
	function outputPostProcessing($a_output)
	{
		return $a_output;
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
					if ($_POST["area_link_type"] != IL_NO_LINK)
					{
						$area->setHref(ilUtil::stripSlashes($_POST["area_link_ext"]));
					}
					else
					{
						$area->setHref("");
					}
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
		ilUtil::sendSuccess($lng->txt("cont_saved_map_area"), true);
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
			ilUtil::sendFailure($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "editMapAreas");
		}

		if (count($_POST["area"]) > 1)
		{
			//$this->ilias->raiseError($this->lng->txt("cont_select_max_one_item"),$this->ilias->error_obj->MESSAGE);
			ilUtil::sendFailure($lng->txt("cont_select_max_one_item"), true);
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
			ilUtil::sendFailure($lng->txt("no_checkbox"), true);
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
			ilUtil::sendSuccess($lng->txt("cont_areas_deleted"), true);
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
	 * Edit an existing shape (make it a whole picture link)
	 */
	function editShapeWholePicture()
	{
		$this->clearSessionVars();
		$_SESSION["il_map_edit_area_type"] = "WholePicture";
		return $this->setShape(false);
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
			ilUtil::sendFailure($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "editMapAreas");
		}

		if (count($_POST["area"]) > 1)
		{
			ilUtil::sendFailure($lng->txt("cont_select_max_one_item"), true);
			$ilCtrl->redirect($this, "editMapAreas");
		}

		if ($_SESSION["il_map_edit_mode"] != "edit_shape")
		{
			$_SESSION["il_map_area_nr"] = $_POST["area"][0];
			$_SESSION["il_map_edit_mode"] = "edit_shape";
			$_SESSION["il_map_edit_target_script"] = $ilCtrl->getLinkTarget($this, "setShape", "", false, false);
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
			
			// Whole Picture
			case "WholePicture":
				return $this->saveArea();
			}

	}

	/**
	 * Set highlight settings
	 *
	 * @param
	 * @return
	 */
	function setHighlight()
	{
		global $ilCtrl, $lng;
		
		$st_item = $this->media_object->getMediaItem("Standard");
		$st_item->setHighlightMode(ilUtil::stripSlashes($_POST["highlight_mode"]));
		$st_item->setHighlightClass(ilUtil::stripSlashes($_POST["highlight_class"]));
		$st_item->update();
		
		ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
		$ilCtrl->redirect($this, "editMapAreas");
	}
}
?>
