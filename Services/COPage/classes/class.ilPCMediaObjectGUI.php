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
	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;

	/**
	 * @var ilAccessHandler
	 */
	protected $access;

	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;

	/**
	 * @var ilObjUser
	 */
	protected $user;

	var $header;
	var $ctrl;

	function __construct($a_pg_obj, $a_content_obj, $a_hier_id = 0, $a_pc_id = "")
	{
		global $DIC;

		$this->tpl = $DIC["tpl"];
		$this->lng = $DIC->language();
		$this->tabs = $DIC->tabs();
		$this->access = $DIC->access();
		$this->toolbar = $DIC->toolbar();
		$this->user = $DIC->user();
		$ilCtrl = $DIC->ctrl();

		$this->ctrl = $ilCtrl;
//		var_dump($_POST);
//ilUtil::printBacktrace(10); exit;
//echo "constructor target:".$_SESSION["il_map_il_target"].":<br>";
		parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
		
		$this->setCharacteristics(array(
			"MediaContainer" => $this->lng->txt("cont_Media"),
			"MediaContainerMax50" => "MediaContainerMax50",
			"MediaContainerFull100" => "MediaContainerFull100"
		));

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
	function executeCommand()
	{ 
		$tpl = $this->tpl;
		$lng = $this->lng;
		$ilTabs = $this->tabs;

		$this->getCharacteristicsOfCurrentStyle("media_cont");	// scorm-2004
		
		// get next class that processes or forwards current command
		$next_class = $this->ctrl->getNextClass($this);

		// get current command
		$cmd = $this->ctrl->getCmd();

		if (is_object ($this->content_obj))
		{
			$this->tpl->clearHeader();
			$tpl->setTitleIcon(ilUtil::getImagePath("icon_mob.svg"));
			$this->getTabs($this->tabs_gui);

			$mob = $this->content_obj->getMediaObject();
			if (is_object($mob))
			{
				$tpl->setVariable("HEADER", $lng->txt("mob").": ".
					$this->content_obj->getMediaObject()->getTitle());
				$mob_gui = new ilObjMediaObjectGUI("", $this->content_obj->getMediaObject()->getId(),false, false);
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
				$this->tpl->clearHeader();
				$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_mob.svg"));
				$this->tpl->setTitle($this->lng->txt("mob").": ".
					$this->content_obj->getMediaObject()->getTitle());
				$mob_gui = new ilObjMediaObjectGUI("", $this->content_obj->getMediaObject()->getId(),false, false);
				$mob_gui->setBackTitle($this->page_back_title);
				$mob_gui->setEnabledMapAreas($this->getEnabledMapAreas());
				$ret = $this->ctrl->forwardCommand($mob_gui);
				break;

			// instance image map editing
			case "ilpcimagemapeditorgui":
				require_once("./Services/COPage/classes/class.ilPCImageMapEditorGUI.php");
				$ilTabs->setTabActive("cont_inst_map_areas");
				$image_map_edit = new ilPCImageMapEditorGUI($this->content_obj,
					$this->pg_obj);
				$ret = $this->ctrl->forwardCommand($image_map_edit);
				$tpl->setContent($ret);
				$this->checkFixSize();
				break;
			
			default:
				$ret = $this->$cmd();
				break;
		}

		return $ret;
	}

	/**
	* Insert new media object form.
	*/
	function insert($a_post_cmd = "edpost", $a_submit_cmd = "create_mob", $a_input_error = false)
	{
		$ilTabs = $this->tabs;
		$tpl = $this->tpl;
		$ilCtrl = $this->ctrl;
		$lng = $this->lng;
		
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
				$this->insertFromPool();
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
				if ($a_input_error)
				{
					$form = $this->form;
				}
				else
				{
					$mob_gui = new ilObjMediaObjectGUI("");
					$mob_gui->initForm("create");
					$form = $mob_gui->getForm();
				}
				$form->setFormAction($ilCtrl->getFormAction($this, "create_mob"));
				$form->clearCommandButtons();
				$form->addCommandButton("create_mob", $lng->txt("save"));
				$form->addCommandButton("cancelCreate", $lng->txt("cancel"));

				$this->displayValidationError();
				
				$tpl->setContent($form->getHTML());

				break;
		}
	}

	/**
	 * Change object reference
	 */
	function changeObjectReference()
	{
		$ilTabs = $this->tabs;
		$ilCtrl = $this->ctrl;
		$lng = $this->lng;
		
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
				$this->insertFromPool(true);
				break;

			case "poolSelection":
				$this->poolSelection(true);
				break;

			case "selectPool":
				$this->selectPool(true);
				break;
			
			case "insertNew":
			default:
				$ilCtrl->setParameter($this, "subCmd", "changeObjectReference");
				$this->getTabs($ilTabs, true, true);
				$ilTabs->setSubTabActive("cont_new_mob");
		
				$this->displayValidationError();
						
				$mob_gui = new ilObjMediaObjectGUI("");
				$mob_gui->initForm("create");
				$form = $mob_gui->getForm();
				$form->setFormAction($ilCtrl->getFormAction($this));
				$form->clearCommandButtons();
				$form->addCommandButton("createNewObjectReference", $lng->txt("save"));
				$form->addCommandButton("cancelCreate", $lng->txt("cancel"));
				$this->tpl->setContent($form->getHTML());				
		}
	}


	/**
	 * Check fix size
	 */
	protected function checkFixSize()
	{
		$std_alias_item = new ilMediaAliasItem($this->dom, $this->getHierId(), "Standard",
			$this->content_obj->getPcId());
		$std_item = $this->content_obj->getMediaObject()->getMediaItem("Standard");

		$ok = false;
		if (($std_alias_item->getWidth() != "" && $std_alias_item->getHeight() != ""))
		{
			$ok = true;
		}
		if ($std_alias_item->getWidth() == "" && $std_alias_item->getHeight() == ""
			&& $std_item->getWidth() != "" && $std_item->getHeight() != "")
		{
			$ok = true;
		}

		if (!$ok)
		{
			ilUtil::sendFailure($this->lng->txt("mob_no_fixed_size_map_editing"));
		}
	}

	/**
	* Insert media object from pool
	*/
	function insertFromPool($a_change_obj_ref = false)
	{
		$ilCtrl = $this->ctrl;
		$ilAccess = $this->access;
		$ilTabs = $this->tabs;
		$tpl = $this->tpl;
		$lng = $this->lng;
		$ilToolbar = $this->toolbar;

		if ($_SESSION["cont_media_pool"] != "" &&
			$ilAccess->checkAccess("write", "", $_SESSION["cont_media_pool"])
			&& ilObject::_lookupType(ilObject::_lookupObjId($_SESSION["cont_media_pool"])) == "mep")
		{
			$html = "";
			$tb = new ilToolbarGUI();

			$ilCtrl->setParameter($this, "subCmd", "poolSelection");
			if ($a_change_obj_ref)
			{
				$tb->addButton($lng->txt("cont_switch_to_media_pool"),
					$ilCtrl->getLinkTarget($this, "changeObjectReference"));
			}
			else
			{
				$tb->addButton($lng->txt("cont_switch_to_media_pool"),
					$ilCtrl->getLinkTarget($this, "insert"));

			}
			$ilCtrl->setParameter($this, "subCmd", "");

			$html = $tb->getHTML();

			$this->getTabs($ilTabs, true, $a_change_obj_ref);
			$ilTabs->setSubTabActive("cont_mob_from_media_pool");
			
			include_once("./Modules/MediaPool/classes/class.ilObjMediaPool.php");
			include_once("./Modules/MediaPool/classes/class.ilMediaPoolTableGUI.php");
			$pool = new ilObjMediaPool($_SESSION["cont_media_pool"]);
			$ilCtrl->setParameter($this, "subCmd", "insertFromPool");
			$tcmd = ($a_change_obj_ref)
				? "changeObjectReference"
				: "insert";
			$tmode = ($a_change_obj_ref)
				? ilMediaPoolTableGUI::IL_MEP_SELECT_SINGLE
				: ilMediaPoolTableGUI::IL_MEP_SELECT;
			$mpool_table = new ilMediaPoolTableGUI($this, $tcmd, $pool, "mep_folder",
				$tmode);

			$html.= $mpool_table->getHTML();

			$tpl->setContent($html);
		}
		else
		{
			$this->poolSelection($a_change_obj_ref);
		}
	}
	
	/**
	* Select concrete pool
	*/
	function selectPool($a_change_obj_ref = false)
	{
		$ilCtrl = $this->ctrl;
		
		$_SESSION["cont_media_pool"] = $_GET["pool_ref_id"];
		$ilCtrl->setParameter($this, "subCmd", "insertFromPool");
		if ($a_change_obj_ref)
		{
			$ilCtrl->redirect($this, "changeObjectReference");
		}
		else
		{
			$ilCtrl->redirect($this, "insert");
		}
	}
	
	/**
	* Pool Selection
	*/
	function poolSelection($a_change_obj_ref = false)
	{
		$tpl = $this->tpl;
		$ilTabs = $this->tabs;
		$ilCtrl = $this->ctrl;

		$this->getTabs($ilTabs, true, $a_change_obj_ref);
		$ilTabs->setSubTabActive("cont_mob_from_media_pool");

		include_once "./Services/COPage/classes/class.ilPoolSelectorGUI.php";

		if ($a_change_obj_ref)
		{
			$ilCtrl->setParameter($this, "subCmd", "poolSelection");
			$exp = new ilPoolSelectorGUI($this, "changeObjectReference", $this, "changeObjectReference");
		}
		else
		{
			$ilCtrl->setParameter($this, "subCmd", "poolSelection");
			$exp = new ilPoolSelectorGUI($this, "insert");
		}

		// filter
		$exp->setTypeWhiteList(array("root", "cat", "grp", "fold", "crs", "mep"));
		$exp->setClickableTypes(array('mep'));

		if (!$exp->handleCommand())
		{
			$tpl->setContent($exp->getHTML());
		}
	}

	
	/**
	* Create new media object and replace currrent media item with it.
	* (keep all instance parameters)
	*/
	function createNewObjectReference()
	{
		$this->create(false, true);
	}

	/**
	* Create new media object and replace currrent media item with it.
	* (keep all instance parameters)
	*/
	function selectObjectReference()
	{
		$ilCtrl = $this->ctrl;
		$lng = $this->lng;
		if (is_array($_POST["id"]) && count($_POST["id"]) == 1)
		{
			include_once("./Services/COPage/classes/class.ilPCMediaObject.php");
			include_once("./Modules/MediaPool/classes/class.ilMediaPoolItem.php");
			$fid = ilMediaPoolItem::lookupForeignId($_POST["id"][0]);
			$this->content_obj->readMediaObject($fid);
			$this->content_obj->updateObjectReference();
			$this->updated = $this->pg_obj->update();
		}
		else
		{
			ilUtil::sendInfo($lng->txt("cont_select_max_one_item"), true);
			$ilCtrl->redirect($this, "changeObjectReference");

		}
		$ilCtrl->redirect($this, "editAlias");
	}
	
	/**
	* create new media object in dom and update page in db
	*/
	function &create($a_create_alias = true, $a_change_obj_ref = false)
	{
		$ilCtrl = $this->ctrl;
		$lng = $this->lng;
		
		if ($_GET["subCmd"] == "insertFromPool")
		{
			if (is_array($_POST["id"]))
			{
				for($i = count($_POST["id"]) - 1; $i>=0; $i--)
				{
					include_once("./Modules/MediaPool/classes/class.ilMediaPoolItem.php");
					$fid = ilMediaPoolItem::lookupForeignId($_POST["id"][$i]);
					include_once("./Services/COPage/classes/class.ilPCMediaObject.php");
					$this->content_obj = new ilPCMediaObject($this->getPage());
					$this->content_obj->readMediaObject($fid);
					$this->content_obj->createAlias($this->pg_obj, $_GET["hier_id"], $this->pc_id);
				}
				$this->updated = $this->pg_obj->update();
			}

			$ilCtrl->returnToParent($this);
		}
		
		// check form input
		$mob_gui = new ilObjMediaObjectGUI("");
		$mob_gui->initForm("create");

		if (!$mob_gui->checkFormInput())
		{
			$this->form = $mob_gui->getForm();
			$this->insert("edpost", "create_mob", true);
			return;
		}
		// create dummy object in db (we need an id)
		include_once("./Services/COPage/classes/class.ilPCMediaObject.php");
		if ($a_change_obj_ref != true)
		{
			$this->content_obj = new ilPCMediaObject($this->getPage());
		}
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
			if ($a_change_obj_ref == true)
			{
				$this->content_obj->updateObjectReference();
				$this->updated = $this->pg_obj->update();
				$this->ctrl->redirect($this, "editAlias");
			}
			return $this->content_obj;
		}
	}


	/**
	* edit properties form
	*/
	function editAlias()
	{
		$tpl = $this->tpl;
		
		$this->initAliasForm();
		$this->getAliasValues();
		$tpl->setContent($this->form_gui->getHTML());
	}

	/**
	* Init alias form
	*/
	function initAliasForm()
	{
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		
		$this->form_gui = new ilPropertyFormGUI();

		// standard view resource
		$std_alias_item = new ilMediaAliasItem($this->dom, $this->getHierId(), "Standard",
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
		$orig_size = $std_item->getOriginalSize();
		$add_str = ($orig_size["width"] != "" && $orig_size["height"] != "")
			? " (".$orig_size["width"]." x ".$orig_size["height"].")"
			: "";
		$op1 = new ilRadioOption($lng->txt("cont_default").$add_str, "y");
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
		$rad_caption->addOption($op1);

			$caption = new ilTextAreaInputGUI("", "st_caption");
			$caption->setCols(30);
			$caption->setRows(2);
			$op2->addSubItem($caption);

			/*$caption = new ilTextInputGUI("", "st_caption");
			$caption->setSize(40);
			$caption->setMaxLength(200);
			$op2->addSubItem($caption);*/
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
			$full_alias_item = new ilMediaAliasItem($this->dom, $this->getHierId(), "Fullscreen",
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
			$fw_size = $std_item->getOriginalSize();
			$add_str = ($fw_size["width"] != "" && $fw_size["height"] != "")
				? " (".$fw_size["width"]." x ".$fw_size["height"].")"
				: "";
			$op1 = new ilRadioOption($lng->txt("cont_default").$add_str, "y");
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
			$rad_caption->addOption($op1);

				$caption = new ilTextAreaInputGUI("", "full_caption");
				$caption->setCols(30);
				$caption->setRows(2);
				$op2->addSubItem($caption);

				/*$caption = new ilTextInputGUI("", "full_caption");
				$caption->setSize(40);
				$caption->setMaxLength(200);
				$op2->addSubItem($caption);*/
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
		$lm_set = new ilSetting("lm");
		if ($lm_set->get("replace_mob_feature"))
		{
			$this->form_gui->addCommandButton("changeObjectReference", $lng->txt("cont_change_object_reference"));
		}
		$this->form_gui->setFormAction($ilCtrl->getFormAction($this));		
	}

	/**
	* Put alias values into form
	*/
	function getAliasValues()
	{
		$lng = $this->lng;
		
		// standard view resource
		$std_alias_item = new ilMediaAliasItem($this->dom, $this->getHierId(), "Standard",
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
			$full_alias_item = new ilMediaAliasItem($this->dom, $this->getHierId(), "Fullscreen",
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
		$std_alias_item = new ilMediaAliasItem($this->dom, $this->getHierId(), "Standard",
			$this->content_obj->getPcId());
		$full_alias_item = new ilMediaAliasItem($this->dom, $this->getHierId(), "Fullscreen",
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
			$std_alias_item->setWidth(ilUtil::stripSlashes($_POST["st_width_height"]["width"]));
			$std_alias_item->setHeight(ilUtil::stripSlashes($_POST["st_width_height"]["height"]));
		}

		// standard caption
		if($_POST["st_derive_caption"] == "y")
		{
			$std_alias_item->deriveCaption();
		}
		else
		{
			$std_alias_item->setCaption(ilUtil::stripSlashes($_POST["st_caption"]));
		}

		// text representation
		if($_POST["st_derive_text_representation"] == "y")
		{
			$std_alias_item->deriveTextRepresentation();
		}
		else
		{
			$std_alias_item->setTextRepresentation(ilUtil::stripSlashes($_POST["st_text_representation"]));
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
					$full_alias_item->setWidth(ilUtil::stripSlashes($_POST["full_width_height"]["width"]));
					$full_alias_item->setHeight(ilUtil::stripSlashes($_POST["full_width_height"]["height"]));
				}

				// fullscreen caption
				if($_POST["full_derive_caption"] == "y")
				{
					$full_alias_item->deriveCaption();
				}
				else
				{
					$full_alias_item->setCaption(ilUtil::stripSlashes($_POST["full_caption"]));
				}

				// fullscreen text representation
				if($_POST["full_derive_text_representation"] == "y")
				{
					$full_alias_item->deriveTextRepresentation();
				}
				else
				{
					$full_alias_item->setTextRepresentation(ilUtil::stripSlashes($_POST["full_text_representation"]));
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
			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
			$this->ctrl->redirect($this, "editAlias");
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
		$ilUser = $this->user;

		$ilUser->addObjectToClipboard($this->content_obj->getMediaObject()->getId(), $this->content_obj->getMediaObject()->getType()
			, $this->content_obj->getMediaObject()->getTitle());
		ilUtil::sendSuccess($this->lng->txt("copied_to_clipboard"), true);
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

	/**
	* align media object to center
	*/
	function centerAlign()
	{
		$std_alias_item = new ilMediaAliasItem($this->dom, $this->getHierId(), "Standard",
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
		$std_alias_item = new ilMediaAliasItem($this->dom, $this->getHierId(), "Standard",
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
		$std_alias_item = new ilMediaAliasItem($this->dom, $this->getHierId(), "Standard",
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
		$std_alias_item = new ilMediaAliasItem($this->dom, $this->getHierId(), "Standard",
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
		$std_alias_item = new ilMediaAliasItem($this->dom, $this->getHierId(), "Standard",
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
		$ilCtrl = $this->ctrl;
		$tpl = $this->tpl;
		$lng = $this->lng;
		
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
			$html = '<div class="ilCOPgEditStyleSelectionItem">'.
				$char.'</div>';
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
	function getTabs(&$tab_gui, $a_create = false, $a_change_obj_ref = false)
	{
		$ilCtrl = $this->ctrl;
		$ilTabs = $this->tabs;

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
					if (substr($format, 0, 5) == "image" && !is_int(strpos($format, "svg")))
					{
						$ilTabs->addTarget("cont_inst_map_areas",
							$ilCtrl->getLinkTargetByClass("ilpcimagemapeditorgui", "editMapAreas"), array(),
							"ilpcimagemapeditorgui");
					}
				}
			}
		}
		else
		{
			if ($a_change_obj_ref)
			{
				$cmd = "changeObjectReference";
			}
			else
			{
				$cmd = "insert";
			}
			
			$ilCtrl->setParameter($this, "subCmd", "insertNew");
			$ilTabs->addSubTabTarget("cont_new_mob",
				$ilCtrl->getLinkTarget($this, $cmd), $cmd);

			$ilCtrl->setParameter($this, "subCmd", "insertFromPool");
			$ilTabs->addSubTabTarget("cont_mob_from_media_pool",
				$ilCtrl->getLinkTarget($this, $cmd), $cmd);
			$ilCtrl->setParameter($this, "subCmd", "");
		}
	}

}
?>
