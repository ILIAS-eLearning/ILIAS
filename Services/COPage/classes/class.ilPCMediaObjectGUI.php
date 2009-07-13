<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once ("./Services/COPage/classes/class.ilPageContentGUI.php");
require_once ("./Services/COPage/classes/class.ilMediaAliasItem.php");
require_once ("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");

/**
* Class ilPCMediaObjectGUI
*
* Editing User Interface for MediaObjects within LMs (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilPCMediaObjectGUI: ilObjMediaObjectGUI, ilPCImageMapEditorGUI
*
* @ingroup ServicesCOPage
*/
// Todo: extend ilObjMediaObjectGUI !?
class ilPCMediaObjectGUI extends ilPageContentGUI
{
	var $header;
	var $ctrl;

	function ilPCMediaObjectGUI(&$a_pg_obj, &$a_content_obj, $a_hier_id = 0, $a_pc_id = "")
	{
		global $ilCtrl;

		$this->ctrl =& $ilCtrl;

//echo "constructor target:".$_SESSION["il_map_il_target"].":<br>";
		parent::ilPageContentGUI($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
		
		$this->setCharacteristics(array("Media" => $this->lng->txt("cont_Media")));

	}

	function setHeader($a_title = "")
	{
		$this->header = $a_title;
	}

	function getHeader()
	{
		return $this->header;
	}

	/**
	* Set Enable map areas.
	*
	* @param	boolean	$a_enabledmapareas	Enable map areas
	*/
	function setEnabledMapAreas($a_enabledmapareas)
	{
		$this->enabledmapareas = $a_enabledmapareas;
	}

	/**
	* Get Enable map areas.
	*
	* @return	boolean	Enable map areas
	*/
	function getEnabledMapAreas()
	{
		return $this->enabledmapareas;
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $tpl, $lng, $ilTabs;
		
		$this->getCharacteristicsOfCurrentStyle("media_cont");	// scorm-2004
		
		// get next class that processes or forwards current command
		$next_class = $this->ctrl->getNextClass($this);

		// get current command
		$cmd = $this->ctrl->getCmd();

		if (is_object ($this->content_obj))
		{
			$tpl->setTitleIcon(ilUtil::getImagePath("icon_mob_b.gif"));
			$this->getTabs($this->tabs_gui);

			$mob = $this->content_obj->getMediaObject();
			if (is_object($mob))
			{
				$tpl->setVariable("HEADER", $lng->txt("mob").": ".
					$this->content_obj->getMediaObject()->getTitle());
				$mob_gui =& new ilObjMediaObjectGUI("", $this->content_obj->getMediaObject()->getId(),false, false);
				$mob_gui->setBackTitle($this->page_back_title);
				$mob_gui->setEnabledMapAreas($this->getEnabledMapAreas());
				$mob_gui->getTabs($this->tabs_gui);
			}
		}
		else
		{
		}

		switch($next_class)
		{
			case "ilobjmediaobjectgui":
				include_once ("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");
				$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_mob_b.gif"));
				$this->tpl->setTitle($this->lng->txt("mob").": ".
					$this->content_obj->getMediaObject()->getTitle());
				$mob_gui =& new ilObjMediaObjectGUI("", $this->content_obj->getMediaObject()->getId(),false, false);
				$mob_gui->setBackTitle($this->page_back_title);
				$mob_gui->setEnabledMapAreas($this->getEnabledMapAreas());
				$ret =& $this->ctrl->forwardCommand($mob_gui);
				break;

			// instance image map editing
			case "ilpcimagemapeditorgui":
				require_once("./Services/COPage/classes/class.ilPCImageMapEditorGUI.php");
				$ilTabs->setTabActive("cont_inst_map_areas");
				$image_map_edit = new ilPCImageMapEditorGUI($this->content_obj,
					$this->pg_obj);
				$ret = $this->ctrl->forwardCommand($image_map_edit);
				$tpl->setContent($ret);
				break;
			
			default:
				$ret =& $this->$cmd();
				break;
		}

		return $ret;
	}

	/**
	* Insert new media object form.
	*/
	function insert($a_post_cmd = "edpost", $a_submit_cmd = "create_mob")
	{
		global $ilTabs, $tpl, $ilCtrl, $lng;
		
		if ($_GET["subCmd"] == "insertNew")
		{
			$_SESSION["cont_media_insert"] = "insertNew";
		}
		if ($_GET["subCmd"] == "insertFromPool")
		{
			$_SESSION["cont_media_insert"] = "insertFromPool";
		}
		
		if (($_GET["subCmd"] == "") && $_SESSION["cont_media_insert"] != "")
		{
			$_GET["subCmd"] = $_SESSION["cont_media_insert"];
		}
		
		switch ($_GET["subCmd"])
		{
			case "insertFromPool":
				$this->insertFromPool($a_post_cmd, $a_submit_cmd);
				break;

			case "poolSelection":
				$this->poolSelection();
				break;

			case "selectPool":
				$this->selectPool();
				break;
			
			case "insertNew":
			default:
				$this->getTabs($ilTabs, true);
				$ilTabs->setSubTabActive("cont_new_mob");
				
				include_once("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");
				$mob_gui = new ilObjMediaObjectGUI("");
				$mob_gui->initForm("create");
				$form = $mob_gui->getForm();
				$form->setFormAction($ilCtrl->getFormAction($this));
				$form->clearCommandButtons();
				$form->addCommandButton("create_mob", $lng->txt("save"));
				$form->addCommandButton("cancelCreate", $lng->txt("cancel"));

				$this->displayValidationError();
				
				$tpl->setContent($form->getHTML());

				break;
		}
	}

	/**
	* Insert media object from pool
	*/
	function insertFromPool($a_post_cmd = "edpost", $a_submit_cmd = "create_mob")
	{
		global $ilCtrl, $ilAccess, $ilTabs, $tpl, $lng;
		

		if ($_SESSION["cont_media_pool"] != "" &&
			$ilAccess->checkAccess("write", "", $_SESSION["cont_media_pool"])
			&& ilObject::_lookupType(ilObject::_lookupObjId($_SESSION["cont_media_pool"])) == "mep")
		{
			$tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
			$tpl->setCurrentBlock("btn_cell");
			$ilCtrl->setParameter($this, "subCmd", "poolSelection");
			$tpl->setVariable("BTN_LINK",
				$ilCtrl->getLinkTarget($this, "insert"));
			$ilCtrl->setParameter($this, "subCmd", "");
			$tpl->setVariable("BTN_TXT", $lng->txt("cont_select_media_pool"));
			$tpl->parseCurrentBlock();

			$this->getTabs($ilTabs, true);
			$ilTabs->setSubTabActive("cont_mob_from_media_pool");
			
			include_once("./Modules/MediaPool/classes/class.ilObjMediaPool.php");
			include_once("./Modules/MediaPool/classes/class.ilMediaPoolTableGUI.php");
			$pool = new ilObjMediaPool($_SESSION["cont_media_pool"]);
			$ilCtrl->setParameter($this, "subCmd", "insertFromPool");
			$mpool_table = new ilMediaPoolTableGUI($this, "insert", $pool, "mep_folder",
				ilMediaPoolTableGUI::IL_MEP_SELECT);
			
			$tpl->setContent($mpool_table->getHTML());
		}
		else
		{
			$this->poolSelection();
		}
	}
	
	/**
	* Select concrete pool
	*/
	function selectPool()
	{
		global $ilCtrl;
		
		$_SESSION["cont_media_pool"] = $_GET["pool_ref_id"];
		$ilCtrl->setParameter($this, "subCmd", "insertFromPool");
		$ilCtrl->redirect($this, "insert");
	}
	
	/**
	* Pool Selection
	*/
	function poolSelection()
	{
		global $ilCtrl, $tree, $tpl, $ilTabs;

		$this->getTabs($ilTabs, true);
		$ilTabs->setSubTabActive("cont_mob_from_media_pool");

		include_once "./Services/COPage/classes/class.ilPoolSelectorGUI.php";
		$exp = new ilPoolSelectorGUI($this->ctrl->getLinkTarget($this, "insert"));
		if ($_GET["expand"] == "")
		{
			$expanded = $tree->readRootId();
		}
		else
		{
			$expanded = $_GET["expand"];
		}
		$exp->setExpand($expanded);

		$exp->setTargetGet("sel_id");
		$this->ctrl->setParameter($this, "target_type", $a_type);
		$ilCtrl->setParameter($this, "subCmd", "poolSelection");
		$exp->setParamsGet($this->ctrl->getParameterArray($this, "insert"));
		
		// filter
		$exp->setFiltered(true);
		$exp->setFilterMode(IL_FM_POSITIVE);
		$exp->addFilter("root");
		$exp->addFilter("cat");
		$exp->addFilter("grp");
		$exp->addFilter("fold");
		$exp->addFilter("crs");
		$exp->addFilter("mep");

		$sel_types = array('mep');

		$exp->setOutput(0);

		$tpl->setContent($exp->getOutput());
	}

	
	/**
	* create new media object in dom and update page in db
	*/
	function &create($a_create_alias = true)
	{
		global $ilCtrl, $lng;
		
		if ($_GET["subCmd"] == "insertFromPool")
		{
			if (is_array($_POST["id"]))
			{
				for($i = count($_POST["id"]) - 1; $i>=0; $i--)
				{
					include_once("./Modules/MediaPool/classes/class.ilMediaPoolItem.php");
					$fid = ilMediaPoolItem::lookupForeignId($_POST["id"][$i]);
					include_once("./Services/COPage/classes/class.ilPCMediaObject.php");
					$this->content_obj = new ilPCMediaObject($this->dom);
					$this->content_obj->readMediaObject($fid);
					$this->content_obj->createAlias($this->pg_obj, $_GET["hier_id"], $this->pc_id);
				}
				$this->updated = $this->pg_obj->update();
			}

			$ilCtrl->returnToParent($this);
		}
		
		// create dummy object in db (we need an id)
		include_once("./Services/COPage/classes/class.ilPCMediaObject.php");
		$this->content_obj = new ilPCMediaObject($this->dom);
		$this->content_obj->createMediaObject();
		$media_obj = $this->content_obj->getMediaObject();
		
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");
		ilObjMediaObjectGUI::setObjectPerCreationForm($media_obj);

		if ($a_create_alias)
		{
			// need a pcmediaobject here
			//$this->node = $this->createPageContentNode();
			
			$this->content_obj->createAlias($this->pg_obj, $this->hier_id, $this->pc_id);
			$this->updated = $this->pg_obj->update();
			if ($this->updated === true)
			{
				$this->pg_obj->stripHierIDs();
				$this->pg_obj->addHierIDs();
				$ilCtrl->setParameter($this, "hier_id", $this->content_obj->readHierId());
				$ilCtrl->setParameter($this, "pc_id", $this->content_obj->readPCId());
				$this->content_obj->setHierId($this->content_obj->readHierId());
				$this->setHierId($this->content_obj->readHierId());
				$this->content_obj->setPCId($this->content_obj->readPCId());
				ilUtil::sendSuccess($lng->txt("saved_media_object"), true);
				$this->ctrl->redirectByClass("ilobjmediaobjectgui", "edit");

				//$this->ctrl->returnToParent($this, "jump".$this->hier_id);
			}
			else
			{
				$this->insert();
			}
		}
		else
		{
			return $this->content_obj;
		}
	}


	/**
	* edit properties form
	*/
	function editAlias()
	{
		global $tpl;
		
		$this->initAliasForm();
		$this->getAliasValues();
		$tpl->setContent($this->form_gui->getHTML());
		return;
		
		//add template for view button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		//$item_nr = $this->content_obj->getMediaItemNr("Standard");
		$std_alias_item =& new ilMediaAliasItem($this->dom, $this->getHierId(), "Standard",
			$this->content_obj->getPcId());
		$std_item = $this->content_obj->getMediaObject()->getMediaItem("Standard");

		// edit media alias template
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.mob_alias_properties.html", "Services/COPage");
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_edit_mob_alias_prop"));
		$this->tpl->setVariable("TXT_STANDARD_VIEW", $this->lng->txt("cont_std_view"));
		$this->tpl->setVariable("TXT_DERIVE", $this->lng->txt("cont_derive_from_obj"));
		$this->tpl->setVariable("TXT_TYPE", $this->lng->txt("cont_".strtolower($std_item->getLocationType())));
		$this->tpl->setVariable("TXT_LOCATION", $std_item->getLocation());
		$this->tpl->setVariable("TXT_FORMAT", $this->lng->txt("cont_format"));
		$this->tpl->setVariable("VAL_FORMAT", $std_item->getFormat());
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		$this->displayValidationError();

		// width
		$this->tpl->setVariable("TXT_MOB_WIDTH", $this->lng->txt("cont_width"));
		$this->tpl->setVariable("INPUT_MOB_WIDTH", "mob_width");
		$this->tpl->setVariable("VAL_MOB_WIDTH", $std_alias_item->getWidth());

		// height
		$this->tpl->setVariable("TXT_MOB_HEIGHT", $this->lng->txt("cont_height"));
		$this->tpl->setVariable("INPUT_MOB_HEIGHT", "mob_height");
		$this->tpl->setVariable("VAL_MOB_HEIGHT", $std_alias_item->getHeight());

		// caption
		$this->tpl->setVariable("TXT_CAPTION", $this->lng->txt("cont_caption"));
		$this->tpl->setVariable("INPUT_CAPTION", "mob_caption");
		$this->tpl->setVariable("VAL_CAPTION", $std_alias_item->getCaption());
//		$this->tpl->parseCurrentBlock();

		// parameters
		$this->tpl->setVariable("TXT_PARAMETER", $this->lng->txt("cont_parameter"));
		$this->tpl->setVariable("INPUT_PARAMETERS", "mob_parameters");
		$this->tpl->setVariable("VAL_PARAMETERS", $std_alias_item->getParameterString());
//		$this->tpl->parseCurrentBlock();

		// object default values
		$this->tpl->setVariable("VAL_OBJ_ST_SIZE", $std_item->getWidth()." / ".$std_item->getHeight());
		$this->tpl->setVariable("VAL_OBJ_ST_CAPTION", $std_item->getCaption());
		$this->tpl->setVariable("VAL_OBJ_ST_PARAMETERS", $std_item->getParameterString());
		if ($std_alias_item->definesSize())
		{
			$this->tpl->setVariable("DERIVE_ST_SIZE_N", "checked=\"1\"");
		}
		else
		{
			$this->tpl->setVariable("DERIVE_ST_SIZE_Y", "checked=\"1\"");
		}
		if ($std_alias_item->definesCaption())
		{
			$this->tpl->setVariable("DERIVE_ST_CAPTION_N", "checked=\"1\"");
		}
		else
		{
			$this->tpl->setVariable("DERIVE_ST_CAPTION_Y", "checked=\"1\"");
		}
		if ($std_alias_item->definesParameters())
		{
			$this->tpl->setVariable("DERIVE_ST_PARAMETER_N", "checked=\"1\"");
		}
		else
		{
			$this->tpl->setVariable("DERIVE_ST_PARAMETER_Y", "checked=\"1\"");
		}

		// fullscreen view
		if ($this->content_obj->getMediaObject()->hasFullScreenItem())
		{
			$this->tpl->setCurrentBlock("fullscreen");
			$full_alias_item =& new ilMediaAliasItem($this->dom, $this->getHierId(), "Fullscreen",
				$this->content_obj->getPcId());
			$full_item =& $this->content_obj->getMediaObject()->getMediaItem("Fullscreen");

			$this->tpl->setVariable("TXT_FULLSCREEN_VIEW", $this->lng->txt("cont_fullscreen"));
			$this->tpl->setVariable("TXT_FULL_TYPE", $this->lng->txt("cont_".strtolower($full_item->getLocationType())));
			$this->tpl->setVariable("TXT_FULL_LOCATION", $full_item->getLocation());

			$this->tpl->setVariable("TXT_FULL_FORMAT", $this->lng->txt("cont_format"));
			$this->tpl->setVariable("VAL_FULL_FORMAT", $full_item->getFormat());

			// width text
			$this->tpl->setVariable("TXT_FULL_WIDTH", $this->lng->txt("cont_width"));
			$this->tpl->setVariable("INPUT_FULL_WIDTH", "full_width");

			// height text
			$this->tpl->setVariable("TXT_FULL_HEIGHT", $this->lng->txt("cont_height"));
			$this->tpl->setVariable("INPUT_FULL_HEIGHT", "full_height");

			// caption text
			$this->tpl->setVariable("TXT_FULL_CAPTION", $this->lng->txt("cont_caption"));
			$this->tpl->setVariable("INPUT_FULL_CAPTION", "full_caption");

			// parameters text
			$this->tpl->setVariable("TXT_FULL_PARAMETER", $this->lng->txt("cont_parameter"));
			$this->tpl->setVariable("INPUT_FULL_PARAMETERS", "full_parameters");

			// object default values
			$this->tpl->setVariable("VAL_OBJ_FULL_SIZE", $full_item->getWidth()." / ".$full_item->getHeight());
			$this->tpl->setVariable("VAL_OBJ_FULL_CAPTION", $full_item->getCaption());
			$this->tpl->setVariable("VAL_OBJ_FULL_PARAMETERS", $full_item->getParameterString());
			if ($full_alias_item->definesSize())
			{
				$this->tpl->setVariable("DERIVE_FULL_SIZE_N", "checked=\"1\"");
			}
			else
			{
				$this->tpl->setVariable("DERIVE_FULL_SIZE_Y", "checked=\"1\"");
			}
			if ($full_alias_item->definesCaption())
			{
				$this->tpl->setVariable("DERIVE_FULL_CAPTION_N", "checked=\"1\"");
			}
			else
			{
				$this->tpl->setVariable("DERIVE_FULL_CAPTION_Y", "checked=\"1\"");
			}
			if ($full_alias_item->definesParameters())
			{
				$this->tpl->setVariable("DERIVE_FULL_PARAMETER_N", "checked=\"1\"");
			}
			else
			{
				$this->tpl->setVariable("DERIVE_FULL_PARAMETER_Y", "checked=\"1\"");
			}

			if ($full_alias_item->exists())
			{
				$this->tpl->setVariable("FULLSCREEN_CHECKED", "checked=\"1\"");

				// width
				$this->tpl->setVariable("VAL_FULL_WIDTH", $full_alias_item->getWidth());

				// height
				$this->tpl->setVariable("VAL_FULL_HEIGHT", $full_alias_item->getHeight());

				// caption
				$this->tpl->setVariable("VAL_FULL_CAPTION", $full_alias_item->getCaption());

				// parameters
				$this->tpl->setVariable("VAL_FULL_PARAMETERS", $full_alias_item->getParameterString());
			}

			$this->tpl->parseCurrentBlock();
		}

		// operations
		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_NAME", "saveAliasProperties");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->parseCurrentBlock();

	}

	/**
	* Init alias form
	*/
	function initAliasForm()
	{
		global $lng, $ilCtrl;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		
		$this->form_gui = new ilPropertyFormGUI();
		
		// standard view resource
		$std_alias_item =& new ilMediaAliasItem($this->dom, $this->getHierId(), "Standard",
			$this->content_obj->getPcId());
		$std_item = $this->content_obj->getMediaObject()->getMediaItem("Standard");

		// title, location and format
		$title = new ilNonEditableValueGUI($lng->txt("title"), "title");
		$this->form_gui->addItem($title);
		$loc = new ilNonEditableValueGUI(
			$this->lng->txt("cont_".strtolower($std_item->getLocationType())), "st_location");
		$this->form_gui->addItem($loc);
		$format = new ilNonEditableValueGUI(
			$this->lng->txt("cont_format"), "st_format");
		$this->form_gui->addItem($format);
		
		// standard size
		$radio_size = new ilRadioGroupInputGUI($lng->txt("size"), "st_derive_size");
		$op1 = new ilRadioOption($lng->txt("cont_default").
			" (".$std_item->getWidth()." x ".$std_item->getHeight().")", "y");
		$op2 = new ilRadioOption($lng->txt("cont_custom"), "n");
		$radio_size->addOption($op1);
		
			// width height
			include_once("./Services/MediaObjects/classes/class.ilWidthHeightInputGUI.php");
			$width_height = new ilWidthHeightInputGUI($lng->txt("cont_width").
				" / ".$lng->txt("cont_height"), "st_width_height");
			$width_height->setConstrainProportions(true);
			$op2->addSubItem($width_height);

		$radio_size->addOption($op2);
		$this->form_gui->addItem($radio_size);
		
		// standard caption
		$rad_caption = new ilRadioGroupInputGUI($lng->txt("cont_caption"), "st_derive_caption");
		$op1 = new ilRadioOption($lng->txt("cont_default"), "y");
			$def_cap = new ilNonEditableValueGUI("", "def_caption");
			$op1->addSubItem($def_cap);
		$op2 = new ilRadioOption($lng->txt("cont_custom"), "n");
			$caption = new ilTextInputGUI("", "st_caption");
		$rad_caption->addOption($op1);
			$caption->setSize(40);
			$caption->setMaxLength(200);
			$op2->addSubItem($caption);
		$rad_caption->addOption($op2);
		$this->form_gui->addItem($rad_caption);

		// standard text representation
		if (substr($std_item->getFormat(), 0, 5) == "image")
		{
			$rad_tr = new ilRadioGroupInputGUI($lng->txt("text_repr"), "st_derive_text_representation");
			$op1 = new ilRadioOption($lng->txt("cont_default"), "y");
				$def_tr = new ilNonEditableValueGUI("", "def_text_representation");
				$op1->addSubItem($def_tr);
			$op2 = new ilRadioOption($lng->txt("cont_custom"), "n");
				$tr = new ilTextAreaInputGUI("", "st_text_representation");
				$tr->setCols(30);
				$tr->setRows(2);
			$rad_tr->addOption($op1);
				$op2->addSubItem($tr);
			$rad_tr->addOption($op2);
			$this->form_gui->addItem($rad_tr);
			$rad_tr->setInfo($lng->txt("text_repr_info"));
		}

		// standard parameters
		if (!in_array($std_item->getFormat(), ilObjMediaObject::_getSimpleMimeTypes()))
		{
			if (ilObjMediaObject::_useAutoStartParameterOnly($std_item->getLocation(),
				$std_item->getFormat()))	// autostart
			{
				$par = $std_item->getParameters();
				$def_str = ($par["autostart"] == "true")
					? " (".$lng->txt("yes").")"
					: " (".$lng->txt("no").")";
				$rad_auto = new ilRadioGroupInputGUI($lng->txt("cont_autostart"),
					"st_derive_parameters");
				$op1 = new ilRadioOption($lng->txt("cont_default").$def_str, "y");
				$rad_auto->addOption($op1);
				$op2 = new ilRadioOption($lng->txt("cont_custom"), "n");
					$auto = new ilCheckboxInputGUI($lng->txt("enabled"), "st_autostart");
					$op2->addSubItem($auto);
				$rad_auto->addOption($op2);
				$this->form_gui->addItem($rad_auto);
			}
			else							// parameters
			{
				$rad_parameters = new ilRadioGroupInputGUI($lng->txt("cont_parameter"), "st_derive_parameters");
				$op1 = new ilRadioOption($lng->txt("cont_default"), "y");
					$def_par = new ilNonEditableValueGUI("", "def_parameters");
					$op1->addSubItem($def_par);
				$rad_parameters->addOption($op1);
				$op2 = new ilRadioOption($lng->txt("cont_custom"), "n");
					$par = new ilTextAreaInputGUI("", "st_parameters");
					$par->setRows(5);
					$par->setCols(50);
					$op2->addSubItem($par);
				$rad_parameters->addOption($op2);
				$this->form_gui->addItem($rad_parameters);
			}
		}
		
		// fullscreen view
		if($this->content_obj->getMediaObject()->hasFullScreenItem())
		{
			$full_alias_item =& new ilMediaAliasItem($this->dom, $this->getHierId(), "Fullscreen",
				$this->content_obj->getPcId());
			$full_item = $this->content_obj->getMediaObject()->getMediaItem("Fullscreen");
			
			$fs_sec = new ilFormSectionHeaderGUI();
			$fs_sec->setTitle($lng->txt("cont_fullscreen"));
			$this->form_gui->addItem($fs_sec);

			
			// resource
			$radio_prop = new ilRadioGroupInputGUI($lng->txt("cont_resource"), "fullscreen");
			$op1 = new ilRadioOption($lng->txt("cont_none"), "n");
			$radio_prop->addOption($op1);
			$op2 = new ilRadioOption($this->lng->txt("cont_".strtolower($full_item->getLocationType())).": ".
				$full_item->getLocation(), "y");
			$radio_prop->addOption($op2);
			$this->form_gui->addItem($radio_prop);

			// format
			$format = new ilNonEditableValueGUI(
				$this->lng->txt("cont_format"), "full_format");
			$this->form_gui->addItem($format);
			
			// full size
			$radio_size = new ilRadioGroupInputGUI($lng->txt("size"), "full_derive_size");
			$op1 = new ilRadioOption($lng->txt("cont_default").
				" (".$full_item->getWidth()." x ".$full_item->getHeight().")", "y");
			$op2 = new ilRadioOption($lng->txt("cont_custom"), "n");
			$radio_size->addOption($op1);
			
				// width height
				include_once("./Services/MediaObjects/classes/class.ilWidthHeightInputGUI.php");
				$width_height = new ilWidthHeightInputGUI($lng->txt("cont_width").
					" / ".$lng->txt("cont_height"), "full_width_height");
				$width_height->setConstrainProportions(true);
				$op2->addSubItem($width_height);
	
			$radio_size->addOption($op2);
			$this->form_gui->addItem($radio_size);
			
			// fullscreen caption
			$rad_caption = new ilRadioGroupInputGUI($lng->txt("cont_caption"), "full_derive_caption");
			$op1 = new ilRadioOption($lng->txt("cont_default"), "y");
				$def_cap = new ilNonEditableValueGUI("", "full_def_caption");
				$op1->addSubItem($def_cap);
			$op2 = new ilRadioOption($lng->txt("cont_custom"), "n");
				$caption = new ilTextInputGUI("", "full_caption");
			$rad_caption->addOption($op1);
				$caption->setSize(40);
				$caption->setMaxLength(200);
				$op2->addSubItem($caption);
			$rad_caption->addOption($op2);
			$this->form_gui->addItem($rad_caption);
			
			// fullscreen text representation
			if (substr($full_item->getFormat(), 0, 5) == "image")
			{
				$rad_tr = new ilRadioGroupInputGUI($lng->txt("text_repr"), "full_derive_text_representation");
				$op1 = new ilRadioOption($lng->txt("cont_default"), "y");
					$def_tr = new ilNonEditableValueGUI("", "full_def_text_representation");
					$op1->addSubItem($def_tr);
				$op2 = new ilRadioOption($lng->txt("cont_custom"), "n");
					$tr = new ilTextAreaInputGUI("", "full_text_representation");
					$tr->setCols(30);
					$tr->setRows(2);
				$rad_tr->addOption($op1);
					$op2->addSubItem($tr);
				$rad_tr->addOption($op2);
				$this->form_gui->addItem($rad_tr);
				$rad_tr->setInfo($lng->txt("text_repr_info"));
			}
	
			// fullscreen parameters
			if (!in_array($full_item->getFormat(), ilObjMediaObject::_getSimpleMimeTypes()))
			{
				if (ilObjMediaObject::_useAutoStartParameterOnly($full_item->getLocation(),
					$full_item->getFormat()))	// autostart
				{
					$par = $full_item->getParameters();
					$def_str = ($par["autostart"] == "true")
						? " (".$lng->txt("yes").")"
						: " (".$lng->txt("no").")";
					$rad_auto = new ilRadioGroupInputGUI($lng->txt("cont_autostart"),
						"full_derive_parameters");
					$op1 = new ilRadioOption($lng->txt("cont_default").$def_str, "y");
					$rad_auto->addOption($op1);
					$op2 = new ilRadioOption($lng->txt("cont_custom"), "n");
						$auto = new ilCheckboxInputGUI($lng->txt("enabled"), "full_autostart");
						$op2->addSubItem($auto);
					$rad_auto->addOption($op2);
					$this->form_gui->addItem($rad_auto);
				}
				else							// parameters
				{
					$rad_parameters = new ilRadioGroupInputGUI($lng->txt("cont_parameter"), "full_derive_parameters");
					$op1 = new ilRadioOption($lng->txt("cont_default"), "y");
						$def_par = new ilNonEditableValueGUI("", "full_def_parameters");
						$op1->addSubItem($def_par);
					$rad_parameters->addOption($op1);
					$op2 = new ilRadioOption($lng->txt("cont_custom"), "n");
						$par = new ilTextAreaInputGUI("", "full_parameters");
						$par->setRows(5);
						$par->setCols(50);
						$op2->addSubItem($par);
					$rad_parameters->addOption($op2);
					$this->form_gui->addItem($rad_parameters);
				}
			}
		}

		$this->form_gui->setTitle($lng->txt("cont_edit_mob_alias_prop"));
		$this->form_gui->addCommandButton("saveAliasProperties", $lng->txt("save"));
		$this->form_gui->setFormAction($ilCtrl->getFormAction($this));
		
	}

	/**
	* Put alias values into form
	*/
	function getAliasValues()
	{
		global $lng;
		
		// standard view resource
		$std_alias_item =& new ilMediaAliasItem($this->dom, $this->getHierId(), "Standard",
			$this->content_obj->getPcId());
		$std_item = $this->content_obj->getMediaObject()->getMediaItem("Standard");

		$values["title"] = $this->content_obj->getMediaObject()->getTitle();
		$values["st_location"] = $std_item->getLocation();
		$values["st_format"] = $std_item->getFormat();
		
		// size
		$values["st_width_height"]["width"] = $std_alias_item->getWidth();
		$values["st_width_height"]["height"] = $std_alias_item->getHeight();
		$values["st_width_height"]["constr_prop"] = true;
		
		// caption
		$values["st_caption"] = $std_alias_item->getCaption();
		if (trim($std_item->getCaption()) == "")
		{
			$values["def_caption"] = "<i>".$lng->txt("cont_no_caption")."</i>";
		}
		else
		{
			$values["def_caption"] = $std_item->getCaption();
		}

		// text representation
		$values["st_text_representation"] = $std_alias_item->getTextRepresentation();
		if (trim($std_item->getTextRepresentation()) == "")
		{
			$values["def_text_representation"] = "<i>".$lng->txt("cont_no_text")."</i>";
		}
		else
		{
			$values["def_text_representation"] = $std_item->getTextRepresentation();
		}
		
		// parameters / autostart
		if (ilObjMediaObject::_useAutoStartParameterOnly($std_item->getLocation(),
			$std_item->getFormat()))	// autostart
		{
			$par = $std_alias_item->getParameters();
			if ($par["autostart"] == "true")
			{
				$values["st_autostart"] = true;
			}
		}
		else				// parameters
		{
			$values["st_parameters"] = $std_alias_item->getParameterString();
		}
		
		// size
		$values["st_derive_size"] = $std_alias_item->definesSize()
			? "n"
			: "y";
		if ($values["st_derive_size"] == "y")
		{
			$values["st_width_height"]["width"] = $std_item->getWidth();
			$values["st_width_height"]["height"] = $std_item->getHeight();
		}
		$values["st_derive_caption"] = $std_alias_item->definesCaption()
			? "n"
			: "y";
		$values["st_derive_text_representation"] = $std_alias_item->definesTextRepresentation()
			? "n"
			: "y";
		$values["st_derive_parameters"] = $std_alias_item->definesParameters()
			? "n"
			: "y";
		if (trim($std_item->getParameterString()) == "")
		{
			$values["def_parameters"] = "<i>".$lng->txt("cont_no_parameters")."</i>";
		}
		else
		{
			$values["def_parameters"] = $std_item->getParameterString();
		}
			
		// fullscreen properties
		if($this->content_obj->getMediaObject()->hasFullScreenItem())
		{
			$full_alias_item =& new ilMediaAliasItem($this->dom, $this->getHierId(), "Fullscreen",
				$this->content_obj->getPcId());
			$full_item = $this->content_obj->getMediaObject()->getMediaItem("Fullscreen");

			$values["fullscreen"] = "n";
			if ($full_alias_item->exists())
			{
				$values["fullscreen"] = "y";
			}

			$values["full_location"] = $full_item->getLocation();
			$values["full_format"] = $full_item->getFormat();
			$values["full_width_height"]["width"] = $full_alias_item->getWidth();
			$values["full_width_height"]["height"] = $full_alias_item->getHeight();
			$values["full_width_height"]["constr_prop"] = true;
			$values["full_caption"] = $full_alias_item->getCaption();
			if (trim($full_item->getCaption()) == "")
			{
				$values["full_def_caption"] = "<i>".$lng->txt("cont_no_caption")."</i>";
			}
			else
			{
				$values["full_def_caption"] = $full_item->getCaption();
			}
			$values["full_text_representation"] = $full_alias_item->getTextRepresentation();
			if (trim($full_item->getTextRepresentation()) == "")
			{
				$values["full_def_text_representation"] = "<i>".$lng->txt("cont_no_text")."</i>";
			}
			else
			{
				$values["full_def_text_representation"] = $full_item->getTextRepresentation();
			}
			$values["full_parameters"] = $full_alias_item->getParameterString();
			$values["full_derive_size"] = $full_alias_item->definesSize()
				? "n"
				: "y";
			if ($values["full_derive_size"] == "y")
			{
				$values["full_width_height"]["width"] = $full_item->getWidth();
				$values["full_width_height"]["height"] = $full_item->getHeight();
			}
			$values["full_derive_caption"] = $full_alias_item->definesCaption()
				? "n"
				: "y";
			$values["full_derive_text_representation"] = $full_alias_item->definesTextRepresentation()
				? "n"
				: "y";
				
			// parameters
			if (ilObjMediaObject::_useAutoStartParameterOnly($full_item->getLocation(),
				$full_item->getFormat()))	// autostart
			{
				$par = $full_alias_item->getParameters();
				if ($par["autostart"] == "true")
				{
					$values["full_autostart"] = true;
				}
			}
			else				// parameters
			{
				$values["full_parameters"] = $full_alias_item->getParameterString();
			}

			$values["full_derive_parameters"] = $full_alias_item->definesParameters()
				? "n"
				: "y";
			if (trim($full_item->getParameterString()) == "")
			{
				$values["full_def_parameters"] = "<i>".$lng->txt("cont_no_parameters")."</i>";
			}
			else
			{
				$values["full_def_parameters"] = $full_item->getParameterString();
			}

		}

		$this->form_gui->setValuesByArray($values);
	}

	/**
	* save table properties in db and return to page edit screen
	*/
	function saveAliasProperties()
	{
		$std_alias_item =& new ilMediaAliasItem($this->dom, $this->getHierId(), "Standard",
			$this->content_obj->getPcId());
		$full_alias_item =& new ilMediaAliasItem($this->dom, $this->getHierId(), "Fullscreen",
			$this->content_obj->getPcId());
		$std_item = $this->content_obj->getMediaObject()->getMediaItem("Standard");
		$full_item = $this->content_obj->getMediaObject()->getMediaItem("Fullscreen");

		// standard size
		if($_POST["st_derive_size"] == "y")
		{
			$std_alias_item->deriveSize();
		}
		else
		{
			$std_alias_item->setWidth($_POST["st_width_height"]["width"]);
			$std_alias_item->setHeight($_POST["st_width_height"]["height"]);
		}

		// standard caption
		if($_POST["st_derive_caption"] == "y")
		{
			$std_alias_item->deriveCaption();
		}
		else
		{
			$std_alias_item->setCaption($_POST["st_caption"]);
		}

		// text representation
		if($_POST["st_derive_text_representation"] == "y")
		{
			$std_alias_item->deriveTextRepresentation();
		}
		else
		{
			$std_alias_item->setTextRepresentation($_POST["st_text_representation"]);
		}

		// standard parameters
		if($_POST["st_derive_parameters"] == "y")
		{
			$std_alias_item->deriveParameters();
		}
		else
		{
			if (ilObjMediaObject::_useAutoStartParameterOnly($std_item->getLocation(),
				$std_item->getFormat()))	// autostart
			{
				if ($_POST["st_autostart"])
				{
					$std_alias_item->setParameters(ilUtil::extractParameterString('autostart="true"'));
				}
				else
				{
					$std_alias_item->setParameters(ilUtil::extractParameterString('autostart="false"'));
				}
			}
			else				// parameters
			{
				$std_alias_item->setParameters(ilUtil::extractParameterString(ilUtil::stripSlashes(utf8_decode($_POST["st_parameters"]))));
			}
		}

		if($this->content_obj->getMediaObject()->hasFullscreenItem())
		{
			if ($_POST["fullscreen"] ==  "y")
			{
				if (!$full_alias_item->exists())
				{
					$full_alias_item->insert();
				}

				// fullscreen size
				if($_POST["full_derive_size"] == "y")
				{
					$full_alias_item->deriveSize();
				}
				else
				{
					$full_alias_item->setWidth($_POST["full_width_height"]["width"]);
					$full_alias_item->setHeight($_POST["full_width_height"]["height"]);
				}

				// fullscreen caption
				if($_POST["full_derive_caption"] == "y")
				{
					$full_alias_item->deriveCaption();
				}
				else
				{
					$full_alias_item->setCaption($_POST["full_caption"]);
				}

				// fullscreen text representation
				if($_POST["full_derive_text_representation"] == "y")
				{
					$full_alias_item->deriveTextRepresentation();
				}
				else
				{
					$full_alias_item->setTextRepresentation($_POST["full_text_representation"]);
				}

				// fullscreen parameters
				if($_POST["full_derive_parameters"] == "y")
				{
					$full_alias_item->deriveParameters();
				}
				else
				{
					if (ilObjMediaObject::_useAutoStartParameterOnly($full_item->getLocation(),
						$full_item->getFormat()))	// autostart
					{
						if ($_POST["full_autostart"])
						{
							$full_alias_item->setParameters(ilUtil::extractParameterString('autostart="true"'));
						}
						else
						{
							$full_alias_item->setParameters(ilUtil::extractParameterString('autostart="false"'));
						}
					}
					else
					{
						$full_alias_item->setParameters(ilUtil::extractParameterString(ilUtil::stripSlashes(utf8_decode($_POST["full_parameters"]))));
					}
				}
			}
			else
			{
				if ($full_alias_item->exists())
				{
					$full_alias_item->delete();
				}
			}
		}

		$this->updated = $this->pg_obj->update();
		if ($this->updated === true)
		{
			$this->ctrl->returnToParent($this, "jump".$this->hier_id);
		}
		else
		{
			$this->pg_obj->addHierIDs();
			$this->editAlias();
		}
	}

	/**
	* copy media object to clipboard
	*/
	function copyToClipboard()
	{
		$this->ilias->account->addObjectToClipboard($this->content_obj->getMediaObject()->getId(), $this->content_obj->getMediaObject()->getType()
			, $this->content_obj->getMediaObject()->getTitle());
		ilUtil::sendSuccess($this->lng->txt("copied_to_clipboard"), true);
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

	/**
	* align media object to center
	*/
	function centerAlign()
	{
		$std_alias_item =& new ilMediaAliasItem($this->dom, $this->getHierId(), "Standard",
			$this->content_obj->getPcId());
		$std_alias_item->setHorizontalAlign("Center");
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

	/**
	* align media object to left
	*/
	function leftAlign()
	{
		$std_alias_item =& new ilMediaAliasItem($this->dom, $this->getHierId(), "Standard",
			$this->content_obj->getPcId());
		$std_alias_item->setHorizontalAlign("Left");
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

	/**
	* align media object to right
	*/
	function rightAlign()
	{
		$std_alias_item =& new ilMediaAliasItem($this->dom, $this->getHierId(), "Standard",
			$this->content_obj->getPcId());
		$std_alias_item->setHorizontalAlign("Right");
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

	/**
	* align media object to left, floating text
	*/
	function leftFloatAlign()
	{
		$std_alias_item =& new ilMediaAliasItem($this->dom, $this->getHierId(), "Standard",
			$this->content_obj->getPcId());
		$std_alias_item->setHorizontalAlign("LeftFloat");
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

	/**
	* align media object to right, floating text
	*/
	function rightFloatAlign()
	{
		$std_alias_item =& new ilMediaAliasItem($this->dom, $this->getHierId(), "Standard",
			$this->content_obj->getPcId());
		$std_alias_item->setHorizontalAlign("RightFloat");
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

	/**
	* Checks whether style selection shoudl be available or not
	*/
	function checkStyleSelection()
	{
		// check whether there is more than one style class
		$chars = $this->getCharacteristics();

		if (count($chars) > 1 ||
			($this->content_obj->getClass() != "" && $this->content_obj->getClass() != "Media"))
		{
			return true;
		}
		return false;
	}
	
	/**
	* Edit Style
	*/
	function editStyle()
	{
		global $ilCtrl, $tpl, $lng;
		
		$this->displayValidationError();
		
		// edit form
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		$form->setTitle($this->lng->txt("cont_edit_style"));
		
		// characteristic selection
		require_once("./Services/Form/classes/class.ilAdvSelectInputGUI.php");
		$char_prop = new ilAdvSelectInputGUI($this->lng->txt("cont_characteristic"),
			"characteristic");
			
		$chars = $this->getCharacteristics();
		if (is_object($this->content_obj))
		{
			if ($chars[$a_seleted_value] == "" && ($this->content_obj->getClass() != ""))
			{
				$chars = array_merge(
					array($this->content_obj->getClass() => $this->content_obj->getClass()),
					$chars);
			}
		}

		$selected = $this->content_obj->getClass();
		if ($selected == "")
		{
			$selected = "MediaContainer";
		}
			
		foreach ($chars as $k => $char)
		{
			$html = '<table class="ilc_media_cont_'.$k.'"><tr><td>'.
				$char.'</td></tr></table>';
			$char_prop->addOption($k, $char, $html);
		}

		$char_prop->setValue($selected);
		$form->addItem($char_prop);
		
		// save button
		$form->addCommandButton("saveStyle", $lng->txt("save"));

		$html = $form->getHTML();
		$tpl->setContent($html);
		return $ret;
	}
	
	/**
	* Save Style
	*/
	function saveStyle()
	{
		$this->content_obj->setClass($_POST["characteristic"]);
		$this->updated = $this->pg_obj->update();
		if ($this->updated === true)
		{
			$this->ctrl->returnToParent($this, "jump".$this->hier_id);
		}
		else
		{
			$this->pg_obj->addHierIDs();
			$this->editStyle();
		}
	}

	/**
	* add tabs to ilTabsGUI object
	*
	* @param	object		$tab_gui		ilTabsGUI object
	* @param	boolean		$a_create		new creation true/false
	*/
	function getTabs(&$tab_gui, $a_create = false)
	{
		global $ilCtrl, $ilTabs;

		if (!$a_create)
		{
			if ($this->checkStyleSelection())
			{
				$ilTabs->addTarget("cont_style",
					$ilCtrl->getLinkTarget($this, "editStyle"), "editStyle",
					get_class($this));
			}
			
			$ilTabs->addTarget("cont_mob_inst_prop",
				$ilCtrl->getLinkTarget($this, "editAlias"), "editAlias",
				get_class($this));

			if ($this->getEnabledMapAreas())
			{
				$st_item = $this->content_obj->getMediaObject()->getMediaItem("Standard");
				if (is_object($st_item))
				{
					$format = $st_item->getFormat();
					if (substr($format, 0, 5) == "image")
					{
						$ilTabs->addTarget("cont_inst_map_areas",
							$ilCtrl->getLinkTargetByClass("ilpcimagemapeditorgui", "editMapAreas"), array(),
							get_class("ilpcimagemapeditorgui"));
					}
				}
			}
		}
		else
		{
			$ilCtrl->setParameter($this, "subCmd", "insertNew");
			$ilTabs->addSubTabTarget("cont_new_mob",
				$ilCtrl->getLinkTarget($this, "insert"), "insert");

			$ilCtrl->setParameter($this, "subCmd", "insertFromPool");
			$ilTabs->addSubTabTarget("cont_mob_from_media_pool",
				$ilCtrl->getLinkTarget($this, "insert"), "insert");
			$ilCtrl->setParameter($this, "subCmd", "");
		}
	}

}
?>
