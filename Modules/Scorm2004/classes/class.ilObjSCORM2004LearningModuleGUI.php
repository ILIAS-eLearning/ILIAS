<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/ScormAicc/classes/class.ilObjSCORMLearningModuleGUI.php");
require_once("./Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php");
require_once("./Modules/Scorm2004/classes/class.ilSCORM2004Export.php");
include_once("./Services/Style/classes/class.ilObjStyleSheetGUI.php");
include_once("./Services/Style/classes/class.ilPageLayout.php");

/**
* Class ilObjSCORMLearningModuleGUI
*
* @author Alex Killing <alex.killing@gmx.de>, Hendrik Holtmann <holtmann@mac.com>
* $Id: class.ilObjSCORMLearningModuleGUI.php 13133 2007-01-30 11:13:06Z akill $
*
* @ilCtrl_Calls ilObjSCORM2004LearningModuleGUI: ilFileSystemGUI, ilMDEditorGUI, ilPermissionGUI, ilLearningProgressGUI
* @ilCtrl_Calls ilObjSCORM2004LearningModuleGUI: ilInfoScreenGUI, ilSCORM2004ChapterGUI, ilSCORM2004SeqChapterGUI, ilSCORM2004PageNodeGUI, ilSCORM2004ScoGUI
* @ilCtrl_Calls ilObjSCORM2004LearningModuleGUI: ilCertificateGUI, ilObjStyleSheetGUI, ilNoteGUI
* @ilCtrl_Calls ilObjSCORM2004LearningModuleGUI: ilLicenseGUI
*
* @ingroup ModulesScormAicc
*/
class ilObjSCORM2004LearningModuleGUI extends ilObjSCORMLearningModuleGUI
{
	/**
	* Constructor
	*
	* @access	public
	*/
	function ilObjSCORM2004LearningModuleGUI($a_data,$a_id,$a_call_by_reference, $a_prepare_output = true)
	{
		global $lng;

		$lng->loadLanguageModule("content");
		$lng->loadLanguageModule("sahs");
		$lng->loadLanguageModule("search");	
		$this->type = "sahs";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,false);
		#$this->tabs_gui =& new ilTabsGUI();
	}

	/**
	 * execute command
	 */
	function &executeCommand()
	{
		global $ilAccess, $ilCtrl, $tpl, $ilTabs;

		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();

		// update expander
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004OrganizationHFormGUI.php");
		$form_gui = new ilSCORM2004OrganizationHFormGUI();
		$form_gui->setTree($this->getEditTree());
		$form_gui->updateExpanded();

		switch($next_class)
		{
			// notes
			case "ilnotegui":
				$this->getTemplate();
				$this->setLocator();
				$this->setTabs();
				switch($_GET["notes_mode"])
				{
					default:
						$ilTabs->setTabActive("sahs_organization");
						return $this->showOrganization();
				}
				break;

			// chapters
			case "ilscorm2004chaptergui":
				include_once("./Modules/Scorm2004/classes/class.ilSCORM2004ChapterGUI.php");
				$chap_gui = new ilSCORM2004ChapterGUI($this->object, $_GET["obj_id"]);
				$chap_gui->setParentGUI($this);
				return $ilCtrl->forwardCommand($chap_gui);
				break;

				// sequencing chapters
			case "ilscorm2004seqchaptergui":
				include_once("./Modules/Scorm2004/classes/class.ilSCORM2004SeqChapterGUI.php");
				$chap_gui = new ilSCORM2004SeqChapterGUI($this->object, $_GET["obj_id"]);
				$chap_gui->setParentGUI($this);
				return $ilCtrl->forwardCommand($chap_gui);
				break;

				// scos
			case "ilscorm2004scogui":
				include_once("./Modules/Scorm2004/classes/class.ilSCORM2004ScoGUI.php");
				$sco_gui = new ilSCORM2004ScoGUI($this->object, $_GET["obj_id"]);
				$sco_gui->setParentGUI($this);
				return $ilCtrl->forwardCommand($sco_gui);
				break;

				// pages
			case "ilscorm2004pagenodegui":
				include_once("./Modules/Scorm2004/classes/class.ilSCORM2004PageNodeGUI.php");
				$page_gui = new ilSCORM2004PageNodeGUI($this->object, $_GET["obj_id"]);
				$page_gui->setParentGUI($this);
				$html = $ilCtrl->forwardCommand($page_gui);
				break;

			default:
				return parent::executeCommand();
				break;
		}
	}

	/**
	 * output main frameset of media pool
	 * left frame: explorer tree of folders
	 * right frame: media pool content
	 */
	function frameset()
	{
		if ($this->object->getEditable())	// show editing frameset
		{
			include_once("./Services/Frameset/classes/class.ilFramesetGUI.php");
			$fs_gui = new ilFramesetGUI();
				
			$fs_gui->setFramesetTitle($this->lng->txt("editor"));
			$fs_gui->setMainFrameName("content");
			$fs_gui->setSideFrameName("tree");
			$this->ctrl->setParameter($this, "active_node", $_GET["obj_id"]);
			$fs_gui->setSideFrameSource($this->ctrl->getLinkTarget($this, "showTree"));
			$this->ctrl->setParameter($this, "activeNode", "");
			if ($_GET["obj_id"] > 0)
			{
				include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");
				$type = ilSCORM2004Node::_lookupType($_GET["obj_id"]);
			}
			if (in_array($type, array("sco", "chap", "seqc", "page")))
			{
				$this->ctrl->setParameter($this, "obj_id", $_GET["obj_id"]);
				$fs_gui->setMainFrameSource($this->ctrl->getLinkTarget($this, "jumpToNode"));
			}
			else
			{
				$fs_gui->setMainFrameSource($this->ctrl->getLinkTarget($this, "properties"));
			}
			$fs_gui->show();
			exit;
		}
		else						// otherwise show standard frameset
		{
			$this->tpl = new ilTemplate("tpl.sahs_edit_frameset.html", false, false, "Modules/ScormAicc");
			$this->tpl->setVariable("SRC",
			$this->ctrl->getLinkTarget($this, "properties"));
			$this->tpl->show("DEFAULT", false);
		}
		exit;
	}

	function jumpToNode($a_anchor_node = "", $a_highlight_ids = "")
	{
		global $ilCtrl;
		
		$anchor = ($a_anchor_node != "")
			? "node_".$a_anchor_node
			: "";
		
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");
		$type = ilSCORM2004Node::_lookupType($_GET["obj_id"]);
		$ilCtrl->setParameter($this, "obj_id", $_GET["obj_id"]);
		switch($type)
		{
			case "sco":
				$ilCtrl->setParameterByClass("ilscorm2004scogui", "highlight", $a_highlight_ids);
				$ilCtrl->redirectByClass("ilscorm2004scogui", "showOrganization", $anchor);
			case "chap":
				$ilCtrl->setParameterByClass("ilscorm2004chaptergui", "highlight", $a_highlight_ids);
				$ilCtrl->redirectByClass("ilscorm2004chaptergui", "showOrganization", $anchor);
			case "seqc":
				$ilCtrl->setParameterByClass("ilscorm2004seqchaptergui", "highlight", $a_highlight_ids);
				$ilCtrl->redirectByClass("ilscorm2004seqchaptergui", "showOrganization", $anchor);
			case "page":
				$ilCtrl->redirectByClass("ilscorm2004pagenodegui", "edit");
		}
	}

	/**
	* scorm 2004 module properties
	*/
	function properties()
	{
		global $rbacsystem, $tree, $tpl, $lng;
		
		$lng->loadLanguageModule("style");
		// edit button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		if ($this->object->editable!=1)
		{
			// view link
			$this->tpl->setCurrentBlock("btn_cell");
			$this->tpl->setVariable("BTN_LINK",
				"ilias.php?baseClass=ilSAHSPresentationGUI&amp;ref_id=".$this->object->getRefID());
			$this->tpl->setVariable("BTN_TARGET"," target=\"ilContObj".$this->object->getID()."\" ");
			$this->tpl->setVariable("BTN_TXT",$this->lng->txt("view"));
			$this->tpl->parseCurrentBlock();
	
			// upload new version
			$this->tpl->setCurrentBlock("btn_cell");
			$this->tpl->setVariable("BTN_LINK", $this->ctrl->getLinkTarget($this, "newModuleVersion"));
			$this->tpl->setVariable("BTN_TXT",$this->lng->txt("cont_sc_new_version"));
			$this->tpl->parseCurrentBlock();
		}
		
		if ($this->object->editable==1) {
			// preview link
			$this->tpl->setCurrentBlock("btn_cell");
			$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTarget($this, "preview"));
			$this->tpl->setVariable("BTN_TARGET"," target=\"ilContObj".$this->object->getID()."\" ");
			$this->tpl->setVariable("BTN_TXT",$this->lng->txt("cont_sc_preview"));
			$this->tpl->parseCurrentBlock();
		}

		
		// scorm lm properties
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.scorm2004_properties.html", "Modules/Scorm2004");
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_PROPERTIES", $this->lng->txt("cont_lm_properties"));

		// version
		$this->tpl->setVariable("TXT_VERSION", $this->lng->txt("cont_sc_version").":");
		$this->tpl->setVariable("VAL_VERSION", $this->object->getModuleVersion());
		
	


		//debug
		/*
		$this->tpl->setVariable("TXT_DEBUG", $this->lng->txt("cont_debug"));
		$this->tpl->setVariable("CBOX_DEBUG", "cobj_debug");
		$this->tpl->setVariable("VAL_DEBUG", "y");
		if ($this->object->getDebug())
		{
			$this->tpl->setVariable("CHK_DEBUG", "checked");
		}
		
		// debug pw
		$this->tpl->setVariable("TXT_DEBUGPW", $this->lng->txt("cont_debugpw"));
		$this->tpl->setVariable("VAL_DEBUGPW", $this->object->getDebugPw());
			
		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_NAME", "saveProperties");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->parseCurrentBlock();
		*/

		if ($this->object->editable!=1) {
			$this->tpl->setCurrentBlock("editable");
			// online
			$this->tpl->setVariable("TXT_ONLINE", $this->lng->txt("cont_online"));
			$this->tpl->setVariable("CBOX_ONLINE", "cobj_online");
			$this->tpl->setVariable("VAL_ONLINE", "y");
			if ($this->object->getOnline())
			{
				$this->tpl->setVariable("CHK_ONLINE", "checked");
			}
	
			// default lesson mode
			$this->tpl->setVariable("TXT_LESSON_MODE", $this->lng->txt("cont_def_lesson_mode"));
			$lesson_modes = array("normal" => $this->lng->txt("cont_sc_less_mode_normal"),
				"browse" => $this->lng->txt("cont_sc_less_mode_browse"));
			$sel_lesson = ilUtil::formSelect($this->object->getDefaultLessonMode(),
				"lesson_mode", $lesson_modes, false, true);
			$this->tpl->setVariable("SEL_LESSON_MODE", $sel_lesson);
	
			// credit mode
			$this->tpl->setVariable("TXT_CREDIT_MODE", $this->lng->txt("cont_credit_mode"));
			$credit_modes = array("credit" => $this->lng->txt("cont_credit_on"),
				"no_credit" => $this->lng->txt("cont_credit_off"));
			$sel_credit = ilUtil::formSelect($this->object->getCreditMode(),
				"credit_mode", $credit_modes, false, true);
			$this->tpl->setVariable("SEL_CREDIT_MODE", $sel_credit);
	
			// auto review mode
			$this->tpl->setVariable("TXT_AUTO_REVIEW", $this->lng->txt("cont_sc_auto_review"));
			$this->tpl->setVariable("CBOX_AUTO_REVIEW", "auto_review");
			$this->tpl->setVariable("VAL_AUTO_REVIEW", "y");
			if ($this->object->getAutoReview())
			{
				$this->tpl->setVariable("CHK_AUTO_REVIEW", "checked");
			}
			
			// max attempts
			$this->tpl->setVariable("MAX_ATTEMPTS", $this->lng->txt("cont_sc_max_attempt"));
			$this->tpl->setVariable("VAL_MAX_ATTEMPT", $this->object->getMaxAttempt());
			
					//unlimited session
		$this->tpl->setVariable("TXT_SESSION", $this->lng->txt("cont_sc_usession"));
		$this->tpl->setVariable("CBOX_SESSION", "cobj_session");
		$this->tpl->setVariable("VAL_SESSION", "y");
		if ($this->object->getSession())
		{
			$this->tpl->setVariable("CHK_SESSION", "checked");
		}
		
		
		// disable top menu
		$this->tpl->setVariable("TXT_NOMENU", $this->lng->txt("cont_nomenu"));
		$this->tpl->setVariable("CBOX_NOMENU", "cobj_nomenu");
		$this->tpl->setVariable("VAL_NOMENU", "y");
		if ($this->object->getNoMenu())
		{
			$this->tpl->setVariable("CHK_NOMENU", "checked");
		}
			
		//disable left-side navigation	
		$this->tpl->setVariable("TXT_HIDENAVIG", $this->lng->txt("cont_hidenavig"));
		$this->tpl->setVariable("CBOX_HIDENAVIG", "cobj_hidenavig");
		$this->tpl->setVariable("VAL_HIDENAVIG", "y");
		if ($this->object->getHideNavig())
		{
			$this->tpl->setVariable("CHK_HIDENAVIG", "checked");
		}
		
		}

		if ($this->object->editable==1) {
			//overwrite version
			$this->tpl->setVariable("TXT_VERSION", "");
			$this->tpl->setVariable("VAL_VERSION", "");
			
			$this->tpl->setCurrentBlock("noneditable");
		
			//glossary
			$this->tpl->setVariable("TXT_GLOSSARY", $this->lng->txt("glossary"));
			$available_glossaries = array(0 => $this->lng->txt("cont_no_glossary"));
			foreach($tree->getChildsByType($tree->getParentId($this->object->getRefID()),'glo') as $g)
			{
				$available_glossaries[$g['child']] = $g['title'];
			}
			$sel_glossary = ilUtil::formSelect($this->object->getAssignedGlossary(),
				"assigned_glossary", $available_glossaries, false, true);
			$this->tpl->setVariable("VAL_GLOSSARY", $sel_glossary);

			// style
			$this->tpl->setVariable("TXT_STYLE", $this->lng->txt("cont_style"));
			$fixed_style = $this->ilias->getSetting("fixed_content_style_id");


			// default number question tries
			$this->tpl->setVariable("TXT_QTRIES", $this->lng->txt("cont_qtries"));
			$this->tpl->setVariable("VAL_QTRIES", $this->object->getTries());
			
			
			if ($fixed_style > 0)
			{
				$this->tpl->setVariable("VAL_STYLE",
				ilObject::_lookupTitle($fixed_style)." (".
				$this->lng->txt("global_fixed").")");
			}
			else
			{
				$this->tpl->setCurrentBlock("style_edit");
				$style_id = $this->object->getStyleSheetId();

				$st_styles = ilObjStyleSheet::_getStandardStyles(true, false,
				$_GET["ref_id"]);

				$st_styles[0] = $this->lng->txt("default");
				ksort($st_styles);
				$style_sel = ilUtil::formSelect ($style_id, "style_id",
				$st_styles, false, true);

				if ($style_id > 0)
				{
					// standard style
					if (ilObjStyleSheet::_lookupStandard($style_id))
					{
						$this->tpl->setVariable("VAL_STYLE",
						$style_sel);
					}
					// individual style
					else
					{
						$this->tpl->setVariable("VAL_STYLE",
						ilObject::_lookupTitle($style_id));
						$this->tpl->setVariable("LINK_STYLE_EDIT",
						$this->ctrl->getLinkTargetByClass("ilObjStyleSheetGUI", "edit"));
						$this->tpl->setVariable("TXT_STYLE_EDIT",
						$this->lng->txt("edit"));
						//$this->tpl->setVariable("IMG_STYLE_EDIT",
						//	ilUtil::getImagePath("icon_pencil.gif"));

						// delete icon
						$this->tpl->setVariable("LINK_STYLE_DROP",
						$this->ctrl->getLinkTargetByClass("ilObjStyleSheetGUI", "delete"));
						$this->tpl->setVariable("TXT_STYLE_DROP",
						$this->lng->txt("delete"));
						//$this->tpl->setVariable("IMG_STYLE_DROP",
						//	ilUtil::getImagePath("delete.gif"));
					}
				}
				if ($style_id <= 0 || ilObjStyleSheet::_lookupStandard($style_id))
				{
					$this->tpl->setVariable("VAL_STYLE",
					$style_sel);
					$this->tpl->setVariable("LINK_STYLE_CREATE",
					$this->ctrl->getLinkTargetByClass("ilObjStyleSheetGUI", "create"));
					$this->tpl->setVariable("TXT_STYLE_CREATE",
					$this->lng->txt("sty_create_ind_style"));
				}
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->parseCurrentBlock();
		}

		
		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_NAME", "saveProperties");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->parseCurrentBlock();

	}

	/**
	* save scorm 2004 module properties
	*/
	function saveProperties()
	{
		global $ilSetting;
		
		if ($this->object->editable!=1) {
			$this->object->setOnline(ilUtil::yn2tf($_POST["cobj_online"]));
			$this->object->setCreditMode($_POST["credit_mode"]);
			$this->object->setMaxAttempt($_POST["max_attempt"]);
			$this->object->setAutoReview(ilUtil::yn2tf($_POST["auto_review"]));
			$this->object->setDefaultLessonMode($_POST["lesson_mode"]);
			$this->object->setSession(ilUtil::yn2tf($_POST["cobj_session"]));
			$this->object->setNoMenu(ilUtil::yn2tf($_POST["cobj_nomenu"]));
			$this->object->setHideNavig(ilUtil::yn2tf($_POST["cobj_hidenavig"]));
			//$this->object->setDebug(ilUtil::yn2tf($_POST["cobj_debug"]));
			//$this->object->setDebugPw($_POST["debug_pw"]);

		}
		else
		{
			if ($ilSetting->get("fixed_content_style_id") <= 0 &&
			(ilObjStyleSheet::_lookupStandard($this->object->getStyleSheetId())
			|| $this->object->getStyleSheetId() == 0))
			{
				$this->object->setStyleSheetId($_POST["style_id"]);
			}
			$this->object->setAssignedGlossary($_POST["assigned_glossary"]);
			$this->object->setTries($_POST["q_tries"]);
		}
		$this->object->update();
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		$this->ctrl->redirect($this, "properties");
	}
	
	/**
	* assign scorm object to scorm gui object
	*/
	function assignObject()
	{
		if ($this->id != 0)
		{
			if ($this->call_by_reference)
			{
				$this->object =& new ilObjSCORM2004LearningModule($this->id, true);
			}
			else
			{
				$this->object =& new ilObjSCORM2004LearningModule($this->id, false);
			}
		}
	}
	
/*

	function showTrackingItems()
	{
		global $lng, $tpl;
		
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004TrackingTableGUI.php");
		$table_gui = new ilSCORM2004TrackingTableGUI($this, "showTrackingItems");
				
		$tr_data_sets = $this->object->getTrackedUsers();
		$table_gui->setTitle($lng->txt("cont_tracking_data"));
		$table_gui->setData($tr_data_sets);
		$tpl->setContent($table_gui->getHTML());
	}
*/
/**
* show tracking data
*/
/**
* show tracking data
*/
function showTrackingItems()
{

	include_once "./Services/Table/classes/class.ilTableGUI.php";
	
	//set search
	
	if ($_POST["search_string"] != "")
	{
		$_SESSION["scorm_search_string"] = trim($_POST["search_string"]);
	} else 	if (isset($_POST["search_string"]) && $_POST["search_string"] == "") {
		unset($_SESSION["scorm_search_string"]);
	}

	// load template for search additions
	$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl_scorm_track_items_search.html","Modules/ScormAicc");
	// load template for table
	$this->tpl->addBlockfile("USR_TABLE", "usr_table", "tpl.table.html");
	// load template for table content data
	$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.scorm_track_items.html", "Modules/ScormAicc");

	$num = 1;

	$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

	// create table
	$tbl = new ilTableGUI();
	
	// title & header columns
	if (isset($_SESSION["scorm_search_string"])) {
		$tbl->setTitle($this->lng->txt("cont_tracking_items").' - Aktive Suche: "'.$_SESSION["scorm_search_string"].'"');
	} else {
		$tbl->setTitle($this->lng->txt("cont_tracking_items"));
	}
	
	$tbl->setHeaderNames(array("",$this->lng->txt("name"), $this->lng->txt("last_access"), $this->lng->txt("attempts"), $this->lng->txt("version")  ));


	$header_params = $this->ctrl->getParameterArray($this, "showTrackingItems");
			
	$tbl->setColumnWidth(array("1%", "50%", "29%", "10%","10%"));
		
	$cols = array("user_id","username","last_access","attempts","version");
	$tbl->setHeaderVars($cols, $header_params);

	//set defaults
	$_GET["sort_order"] = $_GET["sort_order"] ? $_GET["sort_order"] : "asc";
	$_GET["sort_by"] = $_GET["sort_by"] ? $_GET["sort_by"] : "username";

	// control
	$tbl->setOrderColumn($_GET["sort_by"]);
	$tbl->setOrderDirection($_GET["sort_order"]);
	$tbl->setLimit($_GET["limit"]);
	$tbl->setOffset($_GET["offset"]);
	$tbl->setMaxCount($this->maxcount);
	
	$this->tpl->setVariable("COLUMN_COUNTS", 5);
	
	// delete button
	$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
	$this->tpl->setCurrentBlock("tbl_action_btn");
	$this->tpl->setVariable("BTN_NAME", "deleteTrackingForUser");
	$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("delete"));
	$this->tpl->parseCurrentBlock();
	
	// decrease attempts
	$this->tpl->setCurrentBlock("tbl_action_btn");
	$this->tpl->setVariable("BTN_NAME", "decreaseAttempts");
	$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("decrease_attempts"));
	$this->tpl->parseCurrentBlock();
	
	// export aggregated data for selected users
	$this->tpl->setCurrentBlock("tbl_action_btn");
	$this->tpl->setVariable("BTN_NAME", "exportSelected");
	$this->tpl->setVariable("BTN_VALUE",  $this->lng->txt("export"));
	$this->tpl->parseCurrentBlock();
		
	// add search and export all
	// export aggregated data for all users
	$this->tpl->setVariable("EXPORT_ACTION",$this->ctrl->getFormAction($this));
	
	$this->tpl->setVariable("EXPORT_ALL_VALUE", $this->lng->txt('cont_export_all'));
	$this->tpl->setVariable("EXPORT_ALL_NAME", "exportAll");
	$this->tpl->setVariable("IMPORT_VALUE", $this->lng->txt('import'));
	$this->tpl->setVariable("IMPORT_NAME", "Import");
	
	$this->tpl->setVariable("SEARCH_TXT_SEARCH",$this->lng->txt('search'));
	$this->tpl->setVariable("SEARCH_ACTION",$this->ctrl->getFormAction($this));
	$this->tpl->setVariable("SEARCH_NAME",'showTrackingItems');
	if (isset($_SESSION["scorm_search_string"])) {
		$this->tpl->setVariable("STYLE",'display:inline;');
	} else {
		$this->tpl->setVariable("STYLE",'display:none;');
	}
	$this->tpl->setVariable("SEARCH_VAL", 	$_SESSION["scorm_search_string"]);
	$this->tpl->setVariable("SEARCH_VALUE",$this->lng->txt('search_users'));
	$this->tpl->parseCurrentBlock();
	
	// footer
	$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));

	$items = $this->object->getTrackedUsers($_SESSION["scorm_search_string"]);

	$tbl->setMaxCount(count($items));
	$items  = ilUtil::sortArray($items ,$_GET["sort_by"],$_GET["sort_order"]);
	$items = array_slice($items, $_GET["offset"], $_GET["limit"]);

	$tbl->render();
	
	if (count($items) > 0)
	{
		foreach ($items as $item)
		{		
			if (ilObject::_exists($item["user_id"])  && ilObject::_lookUpType($item["user_id"])=="usr") 
			{	
				$user = new ilObjUser($item["user_id"]);
			     $this->tpl->setCurrentBlock("tbl_content");
			     $this->tpl->setVariable("VAL_USERNAME", $item["username"]);
			     $this->tpl->setVariable("VAL_LAST", $item["last_access"]);
			     $this->tpl->setVariable("VAL_ATTEMPT", $item["attempts"]);
			     $this->tpl->setVariable("VAL_VERSION", $item['version']);
			     $this->ctrl->setParameter($this, "user_id", $item["user_id"]);
			     $this->ctrl->setParameter($this, "obj_id", $_GET["obj_id"]);
			     $this->tpl->setVariable("LINK_ITEM",
			     $this->ctrl->getLinkTarget($this, "showTrackingItem"));
			     $this->tpl->setVariable("CHECKBOX_ID", $item["user_id"]);
			     $css_row = ilUtil::switchColor($i++, "tblrow1", "tblrow2");
			     $this->tpl->setVariable("CSS_ROW", $css_row);
			     $this->tpl->parseCurrentBlock();
			}	
		}
		$this->tpl->setCurrentBlock("selectall");
		$this->tpl->setVariable("SELECT_ALL", $this->lng->txt("select_all"));
		$this->tpl->setVariable("CSS_ROW", $css_row);
		$this->tpl->parseCurrentBlock();
		
	} //if is_array
	else
	{
		$this->tpl->setCurrentBlock("notfound");
		$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
		$this->tpl->setVariable("NUM_COLS", $num);
		$this->tpl->parseCurrentBlock();
	}
	
}


function exportAll(){
	$this->object->exportSelected(1);
}

function exportSelected()
{
	if (!isset($_POST["user"]))
	{
		ilUtil::sendInfo($this->lng->txt("no_checkbox"),true);
		$this->ctrl->redirect($this, "showTrackingItems");
	} else {
		$this->object->exportSelected(0,$_POST["user"]);
	}	
}

/**
* show tracking data of item
*/
function showTrackingItem()
{

	include_once "./Services/Table/classes/class.ilTableGUI.php";

	// load template for table
	$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");
	// load template for table content data
	$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.scorm2004_track_item.html", "Modules/Scorm2004");

	$num = 2;

	$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id."$obj_str&cmd=gateway");

	// create table
	$tbl = new ilTableGUI();

	include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMItem.php");
	$sc_item =& new ilSCORMItem($_GET["obj_id"]);

	// title & header columns
	$user = new ilObjUser( $_GET["user_id"]);
	$tbl->setTitle($user->getLastname().", ".$user->getFirstname());

	$tbl->setHeaderNames(array($this->lng->txt("title"),
		$this->lng->txt("cont_status"), $this->lng->txt("cont_time"),
		$this->lng->txt("cont_score")));

	$header_params = array("ref_id" => $this->ref_id, "cmd" => $_GET["cmd"],
		"cmdClass" => get_class($this), "obj_id" => $_GET["obj_id"], "baseClass"=>"ilSAHSEditGUI", 'user_id'=>$_GET["user_id"]);
	
	$cols = array("title", "status", "time", "score");
	$tbl->setHeaderVars($cols, $header_params);
	//$tbl->setColumnWidth(array("25%",));

	// control
	$tbl->setOrderColumn($_GET["sort_by"]);
	$tbl->setOrderDirection($_GET["sort_order"]);
	$tbl->setLimit($_GET["limit"]);
	$tbl->setOffset($_GET["offset"]);
	$tbl->setMaxCount($this->maxcount);

	//$this->tpl->setVariable("COLUMN_COUNTS",count($this->data["cols"]));
	//$this->showActions(true);

	// footer
	$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
	#$tbl->disable("footer");

	$tr_data = $this->object->getTrackingDataAgg($_GET["user_id"]);

	//$objs = ilUtil::sortArray($objs, $_GET["sort_by"], $_GET["sort_order"]);
	$tbl->setMaxCount(count($tr_data));
	$tr_data = array_slice($tr_data, $_GET["offset"], $_GET["limit"]);

	$tbl->render();

	if (count($tr_data) > 0)
	{
		foreach ($tr_data as $data)
		{
				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->setVariable("VAL_TITLE", $data["title"]);
				$this->ctrl->setParameter($this, "user_id",  $_GET["user_id"]);
				$this->ctrl->setParameter($this, "obj_id",  $data["sco_id"]);
				
				$this->tpl->setVariable("LINK_SCO",
					$this->ctrl->getLinkTarget($this, "showTrackingItemPerUser"));
				$this->tpl->setVariable("VAL_TIME", $data["time"]);
				$this->tpl->setVariable("VAL_STATUS", $data["status"]);
				$this->tpl->setVariable("VAL_SCORE", $data["score"]);

				$css_row = ilUtil::switchColor($i++, "tblrow1", "tblrow2");
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->parseCurrentBlock();
		
		}
	} //if is_array
	else
	{
		$this->tpl->setCurrentBlock("notfound");
		$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
		$this->tpl->setVariable("NUM_COLS", $num);
		$this->tpl->parseCurrentBlock();
	}
}


/**
	* display deletion confirmation screen
	*/
	function deleteTrackingForUser()
	{
		if(!isset($_POST["user"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		// SAVE POST VALUES
		$_SESSION["scorm_user_delete"] = $_POST["user"];

		unset($this->data);
		$this->data["cols"] = array("type","title", "description");

		foreach($_POST["user"] as $id)
		{
			if (ilObject::_exists($id) && ilObject::_lookUpType($id)=="usr" ) {	
				$user = new ilObjUser($id);
				$this->data["data"]["$id"] = array(
					"type"		  => "sahs",
					"title"       => $user->getLastname().", ".$user->getFirstname(),
					"desc"        => $this->lng->txt("cont_tracking_data")
				);
			}
		}

		$this->data["buttons"] = array( "cancelDeleteTracking"  => $this->lng->txt("cancel"),
								  "confirmedDeleteTracking"  => $this->lng->txt("confirm"));

		$this->getTemplateFile("confirm");

		ilUtil::sendInfo($this->lng->txt("info_delete_sure"));

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));

		// BEGIN TABLE HEADER
		foreach ($this->data["cols"] as $key)
		{
			$this->tpl->setCurrentBlock("table_header");
			$this->tpl->setVariable("TEXT",$this->lng->txt($key));
			$this->tpl->parseCurrentBlock();
		}
		// END TABLE HEADER

		// BEGIN TABLE DATA
		$counter = 0;

		foreach($this->data["data"] as $key => $value)
		{
			// BEGIN TABLE CELL
			foreach($value as $key => $cell_data)
			{
				$this->tpl->setCurrentBlock("table_cell");

				// CREATE TEXT STRING
				if($key == "type")
				{
					$this->tpl->setVariable("TEXT_CONTENT",ilUtil::getImageTagByType($cell_data,$this->tpl->tplPath));
				}
				else
				{
					$this->tpl->setVariable("TEXT_CONTENT",$cell_data);
				}
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("table_row");
			$this->tpl->setVariable("CSS_ROW",ilUtil::switchColor(++$counter,"tblrow1","tblrow2"));
			$this->tpl->parseCurrentBlock();
			// END TABLE CELL
		}
		// END TABLE DATA

		// BEGIN OPERATION_BTN
		foreach($this->data["buttons"] as $name => $value)
		{
			$this->tpl->setCurrentBlock("operation_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}
	}
	
	function resetSearch() {
		unset($_SESSION["scorm_search_string"]);
		$this->ctrl->redirect($this, "showTrackingItems");
	}
	
	/**
	* cancel deletion of export files
	*/
	function cancelDeleteTracking()
	{
		session_unregister("scorm_user_delete");
		ilUtil::sendInfo($this->lng->txt("msg_cancel"),true);
		$this->ctrl->redirect($this, "showTrackingItems");
	}
	
	/**
	* Confirmed tracking deletion
	*/
	function confirmedDeleteTracking()
	{
	 	global $ilDB, $ilUser;
    
    	$scos = array();

		//get all SCO's of this object		
	
    	$val_set = $ilDB->queryF('
			SELECT cp_node_id FROM cp_node 
			WHERE nodename = %s 
			AND cp_node.slm_id = %s',
			array('text', 'integer'),
			array('item',$this->object->getId()));
			
		while ($val_rec = $ilDB->fetchAssoc($val_set)) 
		{
			array_push($scos,$val_rec['cp_node_id']);
		}
		
	 	foreach ($_SESSION["scorm_user_delete"] as $user)
	 	{
		
			foreach ($scos as $sco)
			{

				$ret = $ilDB->manipulateF('
				DELETE FROM cmi_node 
				WHERE user_id = %s
				AND cp_node_id = %s',
				array('integer','integer'),
				array($user,$sco));
 			}
	 	}
    
	 	$this->ctrl->redirect($this, "showTrackingItems");
	}
	
	function decreaseAttempts()
	{
		global $ilDB, $ilUser;
		
		if (!isset($_POST["user"]))
		{
			ilUtil::sendInfo($this->lng->txt("no_checkbox"),true);
		}
		
		foreach ($_POST["user"] as $user)
		{
			//first check if there is a package_attempts entry

			//get existing account - sco id is always 0

			$val_set = $ilDB->queryF('
			SELECT * FROM cmi_custom 
			WHERE user_id = %s
			AND sco_id = %s
			AND lvalue = %s
			 AND obj_id = %s',
			array('integer','integer','text','integer'),
			array($user,0,'package_attempts',$this->object->getID()));
			
			$val_rec = $ilDB->fetchAssoc($val_set);
			
			$val_rec["rvalue"] = str_replace("\r\n", "\n", $val_rec["rvalue"]);
			if ($val_rec["rvalue"] != null && $val_rec["rvalue"] != 0) 
			{
				$new_rec =  $val_rec["rvalue"]-1;
				//decrease attempt by 1
				$res = $ilDB->queryF('
				SELECT * FROM cmi_custom 
				WHERE user_id = %s
				AND lvalue = %s
				AND obj_id = %s
				AND sco_id = %s',
				array('integer','text','integer','integer'),
				array($user, 'package_attempts',$this->object->getID(),0));

				
				if($ilDB->numRows($res) > 0)
				{
					$val_set = $ilDB->manipulateF('
					UPDATE cmi_custom
					SET rvalue = %s,
						c_timestamp = %s
					WHERE user_id = %s
					AND sco_id = %s
					AND	obj_id = %s
					AND	lvalue = %s',
					array('text','timestamp','integer','integer','integer','text'),
					array($new_rec, date("Y-m-d H:i:s") ,$user,0,$this->object->getID(),'package_attempts'));
				}
				else
				{
					$val_set = $ilDB->manipulateF('
					INSERT INTO cmi_custom
					(rvalue,user_id,sco_id,obj_id,lvalue,c_timestamp) 
					VALUES(%s,%s,%s,%s,%s,%s)',
					array('text','integer','integer','integer','text','timestamp'),
					array($new_rec,$user,0,$this->object->getID(),'package_attempts',date("Y-m-d H:i:s")));
				}				
			}			
		}

		//$this->ctrl->saveParameter($this, "cdir");
		$this->ctrl->redirect($this, "showTrackingItems");
	}
	
	function deleteTrackingData()
	{
		if (is_array($_POST["id"]))
		{
			$this->object->deleteTrackingDataOfUsers($_POST["id"]);
		}
		$this->showTrackingItems();
	}

		/**
	 * Show Editing Tree
	 */
	function showTree()
	{
		global $ilUser, $ilias, $ilCtrl, $lng;

		$mtree = new ilTree($this->object->getId());
		$mtree->setTableNames('sahs_sc13_tree','sahs_sc13_tree_node');
		$mtree->setTreeTablePK("slm_id");

		if ($_POST["expandAll"] != "")
		{
			$_GET["scexpand"] = "";
			$stree = $mtree->getSubTree($mtree->getNodeData($mtree->readRootId()));
			$n_arr = array();
			foreach ($stree as $n)
			{
				$n_arr[] = $n["child"];
			}
			$_SESSION["scexpand"] = $n_arr;
		}

		if ($_POST["collapseAll"] != "")
		{
			$_GET["scexpand"] = "";
			$_SESSION["scexpand"] = array($mtree->readRootId());
		}
		
		$this->tpl = new ilTemplate("tpl.main.html", true, true);
		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());

		$ilCtrl->setParameter($this, "active_node", $_GET["active_node"]);

		$this->tpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");
		$this->tpl->setVariable("IMG_SPACE", ilUtil::getImagePath("spacer.gif", false));
		
		$this->tpl->setCurrentBlock("exp2_button");
		$this->tpl->setVariable("CMD_EXP2_BTN", "expandAll");
		$this->tpl->setVariable("TXT_EXP2_BTN", $lng->txt("expand_all"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("exp2_button");
		$this->tpl->setVariable("CMD_EXP2_BTN", "collapseAll");
		$this->tpl->setVariable("TXT_EXP2_BTN", $lng->txt("collapse_all"));
		$this->tpl->parseCurrentBlock();

		require_once ("./Modules/Scorm2004/classes/class.ilSCORM2004EditorExplorer.php");
		$exp = new ilSCORM2004EditorExplorer($this->ctrl->getLinkTarget($this, "edit"),
		$this->object);
		$exp->setFrameUpdater("content", "ilHierarchyFormUpdater");
		$exp->setTargetGet("obj_id");
		$exp->setExpandTarget($this->ctrl->getLinkTarget($this, "showTree"));
		
		if ($_GET["scexpand"] == "")
		{
			$expanded = $mtree->readRootId();
		}
		else
		{
			$expanded = $_GET["scexpand"];
		}

//echo "-".$_GET["active_node"]."-";
		if ($_GET["active_node"] != "")
		{
			$path = $mtree->getPathId($_GET["active_node"]);
			$exp->setForceOpenPath($path);

			$exp->highlightNode($_GET["active_node"]);
		}
		$exp->setExpand($expanded);

		// build html-output
		$exp->setOutput(0);
		$output = $exp->getOutput();

		// asynchronous output
		if ($ilCtrl->isAsynch())
		{
			echo $output; exit;
		}
		
		$this->tpl->setCurrentBlock("content");
		$this->tpl->setVariable("TXT_EXPLORER_HEADER", $this->lng->txt("sahs_organization"));
		$this->tpl->setVariable("EXP_REFRESH", $this->lng->txt("refresh"));
		$this->tpl->setVariable("EXPLORER",$output);
		$this->ctrl->setParameter($this, "scexpand", $_GET["scexpand"]);
		$this->tpl->setVariable("ACTION", $this->ctrl->getLinkTarget($this, "showTree"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->show(false);
		exit;
	}

	/**
	 * Show Sequencing
	 */
	function showSequencing()
	{
		global $tpl,$lng,$ilTabs;

		//navigation options
		
		$nav_settings = $this->object->getSequencingSettings();
		$tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.scormeditor_course_sequencing.html", "Modules/Scorm2004");
		$tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$tpl->setVariable("TXT_TITLE", "Sequencing Navigation Options for Module");

		$tpl->setVariable("BTN_NAME", "saveSequencing");
		$tpl->setVariable("TXT_SAVE", $lng->txt('save'));

		$tpl->setVariable("VAL_CHOICE", ilUtil::tf2yn($nav_settings->getChoice()));
		$tpl->setVariable("VAL_FLOW", ilUtil::tf2yn($nav_settings->getFlow()));
		$tpl->setVariable("VAL_FORWARDONLY", ilUtil::tf2yn($nav_settings->getForwardOnly()));

		$tpl->setVariable("VAL_CHOICE", "y");
		$tpl->setVariable("VAL_FLOW", "y");
		$tpl->setVariable("VAL_FORWARDONLY", "y");

		if ($nav_settings->getChoice())
		{
			$tpl->setVariable("CHK_CHOICE", "checked");
		}
		if ($nav_settings->getFlow())
		{
			$tpl->setVariable("CHK_FLOW", "checked");
		}
		if ($nav_settings->getForwardOnly())
		{
			$tpl->setVariable("CHK_FORWARDONLY", "checked");
		}
		$tpl->parseCurrentBlock();
		
		$ilTabs->setTabActive("sahs_sequencing");
			
	}

	function saveSequencing()
	{
		global $tpl,$lng;
		ilUtil::sendInfo($lng->txt("saved_successfully"),false);
		$this->object->updateSequencingSettings();
		$this->showSequencing();
	}

	/**
	 * Show Learning Objectives Alignment
	 */
	function showLearningObjectivesAlignment()
	{
		global $tpl, $lng, $ilCtrl;

		$oa_tpl = new ilTemplate("tpl.objectives_alignment.html", true, true, "Modules/Scorm2004");
		
		$chaps = $this->object->getTree()->getChilds(
			$this->object->getTree()->getRootId());
		$s_chaps = array();
		foreach($chaps as $chap)
		{
			$s_chaps[$chap["child"]] = $chap["title"];
		}
		reset($s_chaps);
		if (count($s_chaps) > 0)
		{
			$cur_chap = $_SESSION["sahs_cur_chap"]
				? $_SESSION["sahs_cur_chap"]
				: key($s_chaps);
			
			$oa_tpl->setCurrentBlock("chapter_selection");
			$oa_tpl->setVariable("CHAPTER_SELECTION",
				ilUtil::formSelect($cur_chap, "chapter", $s_chaps, false, true));
			$oa_tpl->setVariable("TXT_SELECT_CHAPTER",
				$lng->txt("chapter"));
			$oa_tpl->setVariable("TXT_CHANGE", $lng->txt("change"));
			$oa_tpl->setVariable("FORMACTION", $ilCtrl->getFormAction($this));
			$oa_tpl->setVariable("CMD_CHAP_SEL", "selectLObjChapter");
			$oa_tpl->parseCurrentBlock();
		
			include_once("./Modules/Scorm2004/classes/class.ilObjectivesAlignmentTableGUI.php");
			$obj_table = new ilObjectivesAlignmentTableGUI($this, "showLearningObjectivesAlignment",
				$this->getEditTree(), $this->object, $cur_chap);
			$oa_tpl->setVariable("LOBJ_TABLE", $obj_table->getHTML());
			$tpl->setContent($oa_tpl->get());
		}
		else
		{
			ilUtil::sendInfo($lng->txt("sahs_oa_no_chapters"));
		}
	}

	function selectLObjChapter()
	{
		global $ilCtrl;

		$_SESSION["sahs_cur_chap"] = $_POST["chapter"];
		$ilCtrl->redirect($this, "showLearningObjectivesAlignment");
	}
	
	/**
	 * Show Export List
	 */
	function showExportList()
	{
		global $tpl;

		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		//create SCORM 1.2 export file button
		$tpl->setCurrentBlock("btn_cell");
		$tpl->setVariable("BTN_LINK", $this->ctrl->getLinkTarget($this, "exportScorm12"));
		$tpl->setVariable("BTN_TXT", $this->lng->txt("scorm_create_export_file_scrom12"));
		$tpl->parseCurrentBlock();

		//create SCORM 2004 export file button
		$tpl->setCurrentBlock("btn_cell");
		$tpl->setVariable("BTN_LINK", $this->ctrl->getLinkTarget($this, "exportScorm2004"));
		$tpl->setVariable("BTN_TXT", $this->lng->txt("scorm_create_export_file_scrom2004"));
		$tpl->parseCurrentBlock();

		//create PDF export file button
		$tpl->setCurrentBlock("btn_cell");
		$tpl->setVariable("BTN_LINK", $this->ctrl->getLinkTarget($this, "exportPDF"));
		$tpl->setVariable("BTN_TXT", $this->lng->txt("scorm_create_export_file_pdf"));
		$tpl->parseCurrentBlock();

		//create ISO export file button
		$tpl->setCurrentBlock("btn_cell");
		$tpl->setVariable("BTN_LINK", $this->ctrl->getLinkTarget($this, "exportISO"));
		$tpl->setVariable("BTN_TXT", $this->lng->txt("scorm_create_export_file_iso"));
		$tpl->parseCurrentBlock();

		//create HTML export file button
		$tpl->setCurrentBlock("btn_cell");
		$tpl->setVariable("BTN_LINK", $this->ctrl->getLinkTarget($this, "exportHTML"));
		$tpl->setVariable("BTN_TXT", $this->lng->txt("scorm_create_export_file_html"));
		$tpl->parseCurrentBlock();

		
		$export_files = $this->object->getExportFiles();

		// create table
		require_once("./Services/Table/classes/class.ilTableGUI.php");
		$tbl = new ilTableGUI();

		// load files templates
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");

		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.export_file_row.html", "Modules/LearningModule");

		$num = 0;

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		$tbl->setTitle($this->lng->txt("cont_export_files"));

		$tbl->setHeaderNames(array("", $this->lng->txt("type"),
			$this->lng->txt("cont_file"),
			$this->lng->txt("cont_size"), $this->lng->txt("date") ));

		$cols = array("", "type", "file", "size", "date");
		$header_params = array("ref_id" => $_GET["ref_id"], "baseClass" => $_GET["baseClass"],
			"cmd" => "exportList", "cmdClass" => strtolower(get_class($this)));
		$tbl->setHeaderVars($cols, $header_params);
		$tbl->setColumnWidth(array("1%", "9%", "40%", "25%", "25%"));

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($this->maxcount);		// ???
		$tbl->disable("sort");


		$this->tpl->setVariable("COLUMN_COUNTS", 5);

		// delete button
		$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", "confirmDeleteExportFile");
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("delete"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", "downloadExportFile");
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("download"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", "publishExportFile");
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("cont_public_access"));
		$this->tpl->parseCurrentBlock();

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		//$tbl->disable("footer");

		$tbl->setMaxCount(count($export_files));
		$export_files = array_slice($export_files, $_GET["offset"], $_GET["limit"]);
		$tbl->render();
		if(count($export_files) > 0)
		{
			$i=0;
			foreach($export_files as $exp_file)
			{
				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->setVariable("TXT_FILENAME", $exp_file["file"]);

				$css_row = ilUtil::switchColor($i++, "tblrow1", "tblrow2");
				$this->tpl->setVariable("CSS_ROW", $css_row);

				$this->tpl->setVariable("TXT_SIZE", $exp_file["size"]);
				$public_str = ($exp_file["file"] == $this->object->getPublicExportFile($exp_file["type"]))
					? " <b>(".$this->lng->txt("public").")<b>"
					: "";
				$this->tpl->setVariable("TXT_TYPE", $exp_file["type"].$public_str);
				$this->tpl->setVariable("CHECKBOX_ID", $exp_file["type"].":".$exp_file["file"]);

				$file_arr = explode("__", $exp_file["file"]);
				$this->tpl->setVariable("TXT_DATE", date("Y-m-d H:i:s",$file_arr[0]));

				$this->tpl->parseCurrentBlock();
			}
		} //if is_array
		else
		{
			$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
			$this->tpl->setVariable("NUM_COLS", 4);
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->parseCurrentBlock();
		
	}

	/**
	 * Adds tabs to tab gui object
	 *
	 * @param	object		$tabs_gui		ilTabsGUI object
	 */
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem;

		if ($this->ctrl->getCmd() == "delete")
		{
			return;
		}

		if (!$this->object->getEditable())
		{
			return parent::getTabs($tabs_gui);
		}

		// info screen
		$force_active = ($this->ctrl->getNextClass() == "ilinfoscreengui")
		? true
		: false;
		$tabs_gui->addTarget("info_short",
		$this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary"), "",
			"ilinfoscreengui", "", $force_active);
			
		// settings
		$tabs_gui->addTarget("settings",
		$this->ctrl->getLinkTarget($this, "properties"), "properties",
		get_class($this));

		// tracking data
		/*	Later, only if tracking data exists
		 $tabs_gui->addTarget("cont_tracking_data",
			$this->ctrl->getLinkTarget($this, "showTrackingItems"), "showTrackingItems",
			get_class($this));
			*/

		// edit meta
		$tabs_gui->addTarget("meta_data",
		$this->ctrl->getLinkTargetByClass('ilmdeditorgui',''),
			 "", "ilmdeditorgui");

		// organization
		$tabs_gui->addTarget("sahs_organization",
		$this->ctrl->getLinkTarget($this, "showOrganization"), "showOrganization",
		get_class($this));

		/*
		// sequencing
		$tabs_gui->addTarget("sahs_sequencing",
		$this->ctrl->getLinkTarget($this, "showSequencing"), "showSequencing",
		get_class($this));
		*/
		
		// objective alignment
		$tabs_gui->addTarget("sahs_objective_alignment",
		$this->ctrl->getLinkTarget($this, "showLearningObjectivesAlignment"), "showLearningObjectivesAlignment",
		get_class($this));

		// learning progress
		/*	Later, only if tracking data exists
		 include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
		 if(ilObjUserTracking::_enabledLearningProgress())
		 {
			$tabs_gui->addTarget('learning_progress',
			$this->ctrl->getLinkTargetByClass(array('illearningprogressgui'),''),
			'',
			array('illplistofobjectsgui','illplistofsettingsgui','illearningprogressgui','illplistofprogressgui'));
			}
			*/

		// export
		$tabs_gui->addTarget("export",
		$this->ctrl->getLinkTarget($this, "showExportList"), "showExportList",
		get_class($this));

		// perm
		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("perm_settings",
			$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
	}

	/**
	* Get editing tree object
	*/
	function getEditTree()
	{
		$slm_tree = new ilTree($this->object->getId());
		$slm_tree->setTreeTablePK("slm_id");
		$slm_tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
		return $slm_tree;
	}
	
	/**
	 * Show subhiearchy of chapters, scos and pages
	 */
	function showOrganization($a_top_node = 0, $a_form_action = "",
		$a_title = "", $a_icon = "", $a_gui_obj = null, $a_gui_cmd = "")
	{
		global $lng, $ilCtrl, $tpl;

		if ($a_form_action == "")
		{
			$a_form_action = $ilCtrl->getFormAction($this);
		}

		if ($a_icon == "")
		{
			$a_title = $this->object->getTitle();
			$a_icon = ilUtil::getImagePath("icon_lm.gif");
		}

		$slm_tree = $this->getEditTree();

		if ($a_top_node == 0)
		{
			$a_top_node = $slm_tree->getRootId();
		}
		
		if (is_null($a_gui_obj))
		{
			$a_gui_obj = $this;
			$a_gui_cmd = "showOrganization";
		}

		$ilCtrl->setParameter($this, "backcmd", "showOrganization");
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004OrganizationHFormGUI.php");
		$form_gui = new ilSCORM2004OrganizationHFormGUI();
		$form_gui->setParentCommand($a_gui_obj, $a_gui_cmd);
		$form_gui->setFormAction($a_form_action);
		$form_gui->setTitle($a_title);
		$form_gui->setIcon($a_icon);
		$form_gui->setTree($slm_tree);
		$form_gui->setCurrentTopNodeId($a_top_node);
		$form_gui->addMultiCommand($lng->txt("delete"), "deleteNodes");
		$form_gui->addMultiCommand($lng->txt("cut"), "cutItems");
		$form_gui->addMultiCommand($lng->txt("copy"), "copyItems");
		$form_gui->addCommand($lng->txt("cont_save_all_titles"), "saveAllTitles");
		$form_gui->addCommand($lng->txt("expand_all"), "expandAll");
		$form_gui->addCommand($lng->txt("collapse_all"), "collapseAll");
		$form_gui->setTriggeredUpdateCommand("saveAllTitles");
		
		// highlighted nodes
		if ($_GET["highlight"] != "")
		{
			$hl = explode(":", $_GET["highlight"]);
			$form_gui->setHighlightedNodes($hl);
			$form_gui->setFocusId($hl[0]);
		}

		$ilCtrl->setParameter($this, "active_node", $_GET["obj_id"]);
		$form_gui->setExplorerUpdater("tree", "tree_div",
			$ilCtrl->getLinkTarget($this, "showTree", "", true));
		$sc_tpl = new ilTemplate("tpl.scormeditor_orga_screen.html", true, true, "Modules/Scorm2004");
		$sc_tpl->setVariable("ORGANIZATION", $form_gui->getHTML());
		$sc_tpl->setVariable("NOTES", $this->getNotesHTML());
		
		$tpl->setContent($sc_tpl->get());
	}

	/**
	* Get notes HTML
	*/
	function getNotesHTML($a_mode = "")
	{
		global $ilCtrl;
		
		// notes
		$ilCtrl->setParameter($this, "nodes_mode", $a_mode);
		include_once("Services/Notes/classes/class.ilNoteGUI.php");
		$node_id = $_GET["obj_id"];
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");
		$node_type = ($node_id > 0)
			? ilSCORM2004Node::_lookupType($node_id)
			: "sahs";

		$notes_gui = new ilNoteGUI($this->object->getId(),
			(int) $node_id, $node_type);
//		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]))
//		{
//			$notes_gui->enablePublicNotesDeletion(true);
//		}
		$notes_gui->enablePrivateNotes();
		$notes_gui->enablePublicNotes();
		
		$next_class = $ilCtrl->getNextClass($this);
		if ($next_class == "ilnotegui")
		{
			$html = $this->ctrl->forwardCommand($notes_gui);
		}
		else
		{	
			$html = $notes_gui->getNotesHTML();
		}
		return $html;
	}

	/**
	 * Insert (multiple) chapters at node
	 */
	function insertChapter($a_redirect = true)
	{
		global $ilCtrl, $lng;

		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004OrganizationHFormGUI.php");

		$slm_tree =& new ilTree($this->object->getId());
		$slm_tree->setTreeTablePK("slm_id");
		$slm_tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');

		$num = ilSCORM2004OrganizationHFormGUI::getPostMulti();
		$node_id = ilSCORM2004OrganizationHFormGUI::getPostNodeId();

		if (!ilSCORM2004OrganizationHFormGUI::getPostFirstChild())	// insert after node id
		{
			$parent_id = $slm_tree->getParentId($node_id);
			$target = $node_id;
		}
		else													// insert as first child
		{
			$parent_id = $node_id;
			$target = IL_FIRST_NODE;
		}
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Chapter.php");
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");
		
		$chap_ids = array();
		for ($i = 1; $i <= $num; $i++)
		{
			$chap = new ilSCORM2004Chapter($this->object);
			$chap->setTitle($lng->txt("sahs_new_chapter"));
			$chap->setSLMId($this->object->getId());
			$chap->create();
			ilSCORM2004Node::putInTree($chap, $parent_id, $target);
			$chap_ids[] = $chap->getId();
		}
		$chap_ids = array_reverse($chap_ids);
		$chap_ids = implode($chap_ids, ":");

		if ($a_redirect)
		{
			$ilCtrl->setParameter($this, "highlight", $chap_ids);
			$ilCtrl->redirect($this, "showOrganization", "node_".$node_id);
		}
		return array("node_id" => $node_id, "items" => $chap_ids);
	}

	/**
	 * Insert (multiple) scos at node
	 */
	function insertSco($a_redirect = true)
	{
		global $ilCtrl, $lng;

		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004OrganizationHFormGUI.php");

		$slm_tree =& new ilTree($this->object->getId());
		$slm_tree->setTreeTablePK("slm_id");
		$slm_tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');

		$num = ilSCORM2004OrganizationHFormGUI::getPostMulti();
		$node_id = ilSCORM2004OrganizationHFormGUI::getPostNodeId();

		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Sco.php");
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");

		if (!ilSCORM2004OrganizationHFormGUI::getPostFirstChild())	// insert after node id
		{
			$parent_id = $slm_tree->getParentId($node_id);
			$target = $node_id;
		}
		else													// insert as first child
		{
			$parent_id = $node_id;
			$target = IL_FIRST_NODE;
		}

		$sco_ids = array();
		for ($i = 1; $i <= $num; $i++)
		{
			$sco = new ilSCORM2004Sco($this->object);
			$sco->setTitle($lng->txt("sahs_new_sco"));
			$sco->setSLMId($this->object->getId());
			$sco->create();
			ilSCORM2004Node::putInTree($sco, $parent_id, $target);
			$sco_ids[] = $sco->getId();
		}
		$sco_ids = array_reverse($sco_ids);
		$sco_ids = implode($sco_ids, ":");

		if ($a_redirect)
		{
			$ilCtrl->setParameter($this, "highlight", $sco_ids);
			$ilCtrl->redirect($this, "showOrganization", "node_".$node_id);
		}
		return array("node_id" => $node_id, "items" => $sco_ids);
	}

	/**
	 * Insert (multiple) pages at node
	 */
	function insertPage($a_redirect = true)
	{
		global $ilCtrl, $lng;

		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004OrganizationHFormGUI.php");

		$slm_tree =& new ilTree($this->object->getId());
		$slm_tree->setTreeTablePK("slm_id");
		$slm_tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');

		$num = ilSCORM2004OrganizationHFormGUI::getPostMulti();
		$node_id = ilSCORM2004OrganizationHFormGUI::getPostNodeId();

		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004PageNode.php");
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");
		if (!ilSCORM2004OrganizationHFormGUI::getPostFirstChild())	// insert after node id
		{
			$parent_id = $slm_tree->getParentId($node_id);
			$target = $node_id;
		}
		else													// insert as first child
		{
			$parent_id = $node_id;
			$target = IL_FIRST_NODE;
		}

		$page_ids = array();
		for ($i = 1; $i <= $num; $i++)
		{
			$page = new ilSCORM2004PageNode($this->object);
			$page->setTitle($lng->txt("sahs_new_page"));
			$page->setSLMId($this->object->getId());
			$page->create();
			ilSCORM2004Node::putInTree($page, $parent_id, $target);
			$page_ids[] = $page->getId();
		}
		$page_ids = array_reverse($page_ids);
		$page_ids = implode($page_ids, ":");

		if ($a_redirect)
		{
			$ilCtrl->setParameter($this, "highlight", $page_ids);
			$ilCtrl->redirect($this, "showOrganization", "node_".$node_id);
		}
		return array("node_id" => $node_id, "items" => $page_ids);
	}


	/**
	 * Insert sequencing scenario at node
	 */
	function insertScenarioGUI()
	{

		global $ilCtrl,$lng, $tpl;

		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004OrganizationHFormGUI.php");
		include_once("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004SeqTemplate.php");

		$templates = array();
		$description = null;
		$image = null;

		$default_identifier = $_POST["identifier"];

		//get available templates
		$arr_templates = ilSCORM2004SeqTemplate::availableTemplates();

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.scormeditor_seq_chooser.html", "Modules/Scorm2004");

		$this->tpl->setCurrentBlock("option_item");

		$active = null;
		foreach ($arr_templates as $templ)
		{
			$sel= "";
			$item_data = $templ->getMetadataProperties();
			$item_data['identifier'] = $templ->getIdentifier();
			array_push($templates,$item_data);
			if ($default_identifier == $item_data['identifier']) {$sel = 'selected'; $active =  $item_data;}
			$this->tpl->setVariable("VAL_SELECTED",$sel );
			$this->tpl->setVariable("VAL_IDENTIFIER",$item_data['identifier'] );
			$this->tpl->setVariable("VAL_TITLE",$item_data['title'] );
			$this->tpl->parseCurrentBlock();
		}

		//default
		if ($active == null )
		{
			$this->saveAllTitles(false);
			$description = $templates[0]['description'];
			$image = $templates[0]['thumbnail'];
		} else {
			$description = $active['description'];
			$image = $active['thumbnail'];
		}
			
		$this->tpl->setVariable("VAL_DESCRIPTION",$description);
		$this->tpl->setVariable("VAL_IMAGE",ilSCORM2004SeqTemplate::SEQ_TEMPLATE_DIR."/images/".$image);

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("BTN_NAME", "insertScenario");
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_INSERT", $this->lng->txt("insert"));
		$this->tpl->setVariable("TXT_CHANGE", $this->lng->txt("change"));

		$this->tpl->setVariable("TXT_TITLE", "Choose Sequencing Template");

		$node_id = $_POST["node_id"];
		$first_child = $_POST["first_child"];

		if (!$node_id) {$node_id = ilSCORM2004OrganizationHFormGUI::getPostNodeId();}
		if (!$first_child) {$first_child = ilSCORM2004OrganizationHFormGUI::getPostFirstChild();}

		$this->tpl->setVariable("VAL_NODE_ID", $node_id);
		$this->tpl->setVariable("VAL_FIRST_CHILD", $first_child);

	}


	/**
	 * Insert sequencing scenario at node
	 */
	function insertScenario()
	{
		global $ilCtrl;

		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004OrganizationHFormGUI.php");

		$slm_tree =& new ilTree($this->object->getId());
		$slm_tree->setTreeTablePK("slm_id");
		$slm_tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');

		$node_id = $_POST["node_id"];

		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004PageNode.php");
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");
		include_once("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004SeqTemplate.php");

		if (!$_POST["first_child"])	// insert after node id
		{
			$parent_id = $slm_tree->getParentId($node_id);
			$target = $node_id;
		}
		else     // insert as first child
		{
			$parent_id = $node_id;
			$target = IL_FIRST_NODE;
		}

		$template = new ilSCORM2004SeqTemplate($_POST["identifier"]);
		$id = $template->insertTemplateForObjectAtParent($this->object,$parent_id,$target);
		$ilCtrl->setParameter($this, "highlight", $id);
		$ilCtrl->redirect($this, "showOrganization", "node_".$node_id);

	}

	/**
	 * Displays GUI to select template for page
	 */
	function insertTemplateGUI($a_redirect = true) {
		global $ilCtrl,$lng, $tpl;
		
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004OrganizationHFormGUI.php");
		
		$arr_templates = ilPageLayout::activeLayouts();

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.scormeditor_page_layout_chooser.html", "Modules/Scorm2004");

		$this->tpl->setCurrentBlock("option_item");

		$count = 0;
		foreach ($arr_templates as $templ)
		{
			$count++;
			$sel= "";
			$templ->readObject();
			$this->tpl->setVariable("VAL_LAYOUT_TITLE",$templ->getTitle());
			$this->tpl->setVariable("VAL_LAYOUT_IMAGE",$templ->getPreview());
			$this->tpl->setVariable("VAL_LAYOUT_ID",$templ->getId());
			$this->tpl->setVariable("VAL_DISPLAY","inline");
			if ($count==1) {
				$this->tpl->setVariable("VAL_CHECKED","checked");
			}
			if ($count%4 == 0) {
				$this->tpl->setVariable("END_ROW","</tr>");
			}
			if ($count == 1 || ($count-1)%4 == 0) {
				$this->tpl->setVariable("BEGIN_ROW","<tr>");
			}
			$this->tpl->parseCurrentBlock();
		}
		
		//matrix table
		if ($count%4!=0) {
			$rest = 4-($count%4);
		} else {
			$rest=0;
		}
		
		for ($i=1;$i<=$rest;$i++) {
			$this->tpl->setVariable("VAL_DISPLAY","none");			
			$this->tpl->setVariable("VAL_LAYOUT_ID",$templ->getId());
			
			if ($i == $rest) {
				$this->tpl->setVariable("END_ROW","</tr>");
			}
			$this->tpl->parseCurrentBlock();
		}
		
		//empty cells and closing <tr>
		
		$this->tpl->setVariable("VAL_NODE_ID",ilSCORM2004OrganizationHFormGUI::getPostNodeId());
		$this->tpl->setVariable("VAL_MULTI", ilSCORM2004OrganizationHFormGUI::getPostMulti());
		$this->tpl->setVariable("VAL_FIRST_CHILD", ilSCORM2004OrganizationHFormGUI::getPostFirstChild());
		$this->tpl->setVariable("VAL_OBJ_ID", ilSCORM2004OrganizationHFormGUI::getPostFirstChild());
	
		$ilCtrl->saveParameter($this,"obj_id");
	
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("BTN_NAME", "insertTemplate");
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_INSERT", $this->lng->txt("insert"));
		$this->tpl->setVariable("TXT_CHANGE", $this->lng->txt("change"));
		$this->tpl->setVariable("TXT_TITLE", "Choose Page Layout");
		
	}
	
	
	
	/**
	 * Insert (multiple) pages at node
	 */
	function insertTemplate($a_redirect = true)
	{
		global $ilCtrl, $lng;

		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004OrganizationHFormGUI.php");

		$slm_tree =& new ilTree($this->object->getId());
		$slm_tree->setTreeTablePK("slm_id");
		$slm_tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');

		$num = $_POST["multi"];
		$node_id = $_POST["node_id"];
		$layout_id = $_POST["layout_id"];
		

		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004PageNode.php");
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");

		if (!$_POST["first_child"])	// insert after node id
		{
			$parent_id = $slm_tree->getParentId($node_id);
			$target = $node_id;
		}
		else           // insert as first child
		{
			$parent_id = $node_id;
			$target = IL_FIRST_NODE;
		}

		$page_ids = array();
		for ($i = 1; $i <= $num; $i++)
		{
			$page = new ilSCORM2004PageNode($this->object);
			$page->setTitle($lng->txt("sahs_new_page"));
			$page->setSLMId($this->object->getId());
			$page->create(false,$layout_id);
			ilSCORM2004Node::putInTree($page, $parent_id, $target);
			$page_ids[] = $page->getId();
		}
		$page_ids = array_reverse($page_ids);
		$page_ids = implode($page_ids, ":");

		if ($a_redirect)
		{	
			if ($_GET["obj_id"] != "")
			{
				$this->jumpToNode($node_id, $page_ids);
			}
			else
			{
				$ilCtrl->setParameter($this, "highlight", $page_ids);
				$ilCtrl->redirect($this, "showOrganization", "node_".$node_id);
			}
		}
	}
	
	/**
	* Expand all
	*/
	function expandAll($a_redirect = true)
	{
		$_GET["scexpand"] = "";
		$mtree = $this->object->getTree();
		$n_id = ($_GET["obj_id"] > 0)
			? $_GET["obj_id"]
			: $mtree->readRootId();
		$stree = $mtree->getSubTree($mtree->getNodeData($n_id));
		$n_arr = array();
		foreach ($stree as $n)
		{
			$n_arr[] = $n["child"];
			$_SESSION["scexpand"] = $n_arr;
		}
		$this->saveAllTitles($a_redirect);
	}
	
	/**
	* Collapse all
	*/
	function collapseAll($a_redirect = true)
	{
		$_GET["scexpand"] = "";
		$mtree = $this->object->getTree();
		$n_id = ($_GET["obj_id"] > 0)
			? $_GET["obj_id"]
			: $mtree->readRootId();
		$stree = $mtree->getSubTree($mtree->getNodeData($n_id));
		$old = $_SESSION["scexpand"];
		foreach ($stree as $n)
		{
			if (in_array($n["child"], $old) && $n["child"] != $n_id)
			{
				$k = array_search($n["child"], $old);
				unset($old[$k]);
			}
		}
		$_SESSION["scexpand"] = $old;
		$this->saveAllTitles($a_redirect);
	}
	
	/**
	 * Save all titles of chapters/scos/pages
	 */
	function saveAllTitles($a_redirect = true)
	{
		global $ilCtrl;

		if (is_array($_POST["title"]))
		{
			include_once("./Services/MetaData/classes/class.ilMD.php");
			include_once("./Modules/Scorm2004/classes/class.ilSCORM2004NodeFactory.php");
			foreach($_POST["title"] as $id => $title)
			{
				$node_obj = ilSCORM2004NodeFactory::getInstance($this->object, $id, false);
				if (is_object($node_obj))
				{
					// Update Title and description
					$md = new ilMD($this->object->getId(), $id, $node_obj->getType());
					$md_gen = $md->getGeneral();
					$md_gen->setTitle(ilUtil::stripSlashes($title));
					$md_gen->update();
					$md->update();
					ilSCORM2004Node::_writeTitle($id, ilUtil::stripSlashes($title));
				}
			}
		}
		if ($a_redirect)
		{
			$ilCtrl->redirect($this, "showOrganization");
		}
	}

	/**
	 * confirm deletion screen of chapters/scos/pages
	 *
	 * @param	string		form action
	 */
	function deleteNodes($a_form_action = "")
	{
		global $lng, $tpl;

		if(!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		// SAVE POST VALUES
		$_SESSION["saved_post"] = $_POST["id"];

		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$confirmation_gui = new ilConfirmationGUI();

		if ($a_form_action == "")
		{
			$this->ctrl->setParameter($this, "backcmd", $_GET["backcmd"]);
			$a_form_action = $this->ctrl->getFormAction($this);
		}
		$confirmation_gui->setFormAction($a_form_action);
		$confirmation_gui->setHeaderText($this->lng->txt("info_delete_sure"));

		// Add items to delete
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004NodeFactory.php");
		foreach($_POST["id"] as $id)
		{
			if ($id != IL_FIRST_NODE)
			{
				$node_obj = ilSCORM2004NodeFactory::getInstance($this->object, $id, false);
				$confirmation_gui->addItem("id[]", $node_obj->getId(),
				$node_obj->getTitle(), ilUtil::getImagePath("icon_".$node_obj->getType().".gif"));
			}
		}

		$confirmation_gui->setCancel($lng->txt("cancel"), "cancelDelete");
		$confirmation_gui->setConfirm($lng->txt("confirm"), "confirmedDelete");

		$tpl->setContent($confirmation_gui->getHTML());
	}

	/**
	 * cancel delete
	 */
	function cancelDelete()
	{
		$this->ctrl->redirect($this, $_GET["backcmd"]);
	}

	/**
	 * Delete chapters/scos/pages
	 */
	function confirmedDelete($a_redirect = true)
	{
		global $ilCtrl;

		$tree = new ilTree($this->object->getId());
		$tree->setTableNames('sahs_sc13_tree','sahs_sc13_tree_node');
		$tree->setTreeTablePK("slm_id");

		// delete all selected objects
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004NodeFactory.php");
		foreach ($_POST["id"] as $id)
		{
			if ($id != IL_FIRST_NODE)
			{
				$obj = ilSCORM2004NodeFactory::getInstance($this->object, $id, false);
				$node_data = $tree->getNodeData($id);
				if (is_object($obj))
				{
					$obj->setSLMId($this->object->getId());

					/*include_once("classes/class.ilHistory.php");
					 ilHistory::_createEntry($this->object->getId(), "delete_".$obj->getType(),
						array(ilLMObject::_lookupTitle($id), $id),
						$this->object->getType());*/

					$obj->delete();
				}
				if($tree->isInTree($id))
				{
					$tree->deleteTree($node_data);
				}
			}
		}

		// check the tree
		//		$this->object->checkTree();

		// feedback
		ilUtil::sendInfo($this->lng->txt("info_deleted"),true);

		if ($a_redirect)
		{
			$ilCtrl->redirect($this, "showOrganization");
		}
	}
	
	/**
	* Perform drag and drop action
	*/
	function proceedDragDrop()
	{
		global $ilCtrl;

		$this->object->executeDragDrop($_POST["il_hform_source_id"], $_POST["il_hform_target_id"],
			$_POST["il_hform_fc"], $_POST["il_hform_as_subitem"]);
		$ilCtrl->redirect($this, "showOrganization");
	}

	/**
	* Copy items to clipboard
	*/
	function copyItems($a_return = "showOrganization")
	{
		global $ilCtrl, $lng;

		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");
		
		$items = ilUtil::stripSlashesArray($_POST["id"]);
		$todel = array();				// delete IDs < 0 (needed for non-js editing)
		foreach($items as $k => $item)
		{
			if ($item < 0)
			{
				$todel[] = $k;
			}
		}
		foreach($todel as $k)
		{
			unset($items[$k]);
		}
		if (!ilSCORM2004Node::uniqueTypesCheck($items))
		{
			ilUtil::sendInfo($lng->txt("sahs_choose_pages_or_chapters_or_scos_only"), true);
			$ilCtrl->redirect($this, $a_return);
		}
		ilSCORM2004Node::clipboardCopy($this->object->getId(), $items);

		// @todo: move this to a service since it can be used here, too
		include_once("./Modules/LearningModule/classes/class.ilEditClipboard.php");
		ilEditClipboard::setAction("copy");
		ilUtil::sendInfo($lng->txt("cont_selected_items_have_been_copied"), true);

		$ilCtrl->redirect($this, $a_return);
	}

	/**
	* Copy items to clipboard, then cut them from the current tree
	*/
	function cutItems($a_return = "showOrganization")
	{
		global $ilCtrl, $lng;
		
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");
		
		$items = ilUtil::stripSlashesArray($_POST["id"]);
		$todel = array();			// delete IDs < 0 (needed for non-js editing)
		foreach($items as $k => $item)
		{
			if ($item < 0)
			{
				$todel[] = $k;
			}
		}
		foreach($todel as $k)
		{
			unset($items[$k]);
		}
		
		if (!ilSCORM2004Node::uniqueTypesCheck($items))
		{
			ilUtil::sendInfo($lng->txt("sahs_choose_pages_or_chapters_or_scos_only"), true);
			$ilCtrl->redirect($this, $a_return);
		}

		ilSCORM2004Node::clipboardCut($this->object->getId(), $items);
		
		include_once("./Modules/LearningModule/classes/class.ilEditClipboard.php");
		ilEditClipboard::setAction("cut");

		ilUtil::sendInfo($lng->txt("cont_selected_items_have_been_cut"), true);

		$ilCtrl->redirect($this, $a_return);
	}

	/**
	* Insert pages from clipboard
	*/
	function insertPageClip()
	{
		global $ilCtrl, $ilUser;
		
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");
		ilSCORM2004Node::insertPageClip($this->object);
		
		$ilCtrl->redirect($this, "showOrganization",
			"node_".ilSCORM2004OrganizationHFormGUI::getPostNodeId());
	}

	/**
	* Insert scos from clipboard
	*/
	function insertScoClip()
	{
		global $ilCtrl, $ilUser;
		
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");
		ilSCORM2004Node::insertScoClip($this->object);
		
		$ilCtrl->redirect($this, "showOrganization",
			"node_".ilSCORM2004OrganizationHFormGUI::getPostNodeId());
	}

	/**
	* Insert chapter from clipboard
	*/
	function insertChapterClip()
	{
		global $ilCtrl, $ilUser;
		
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");
		ilSCORM2004Node::insertChapterClip($this->object);
		
		$ilCtrl->redirect($this, "showOrganization",
			"node_".ilSCORM2004OrganizationHFormGUI::getPostNodeId());
	}

	function exportScorm2004()
	{
		$export = new ilScorm2004Export($this->object,'SCORM 2004');
		$export->buildExportFile();
		$this->ctrl->redirect($this, "showExportList");
	}
	
	function exportScorm12()
	{
		$export = new ilScorm2004Export($this->object,'SCORM 1.2');
		$export->buildExportFile();
		$this->ctrl->redirect($this, "showExportList");
	}
	
	function exportHTML()
	{
		$export = new ilScorm2004Export($this->object,'HTML');
		$export->buildExportFile();
		$this->ctrl->redirect($this, "showExportList");
	}

	function exportISO()
	{
		$export = new ilScorm2004Export($this->object,'ISO');
		$export->buildExportFile();
		$this->ctrl->redirect($this, "showExportList");
	}
	
	function exportPDF()
	{
		$export = new ilScorm2004Export($this->object,'PDF');
		$export->buildExportFile();
		$this->ctrl->redirect($this, "showExportList");
	}
	
	function downloadExportFile()
	{
		if(!isset($_POST["file"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		if (count($_POST["file"]) > 1)
		{
			$this->ilias->raiseError($this->lng->txt("cont_select_max_one_item"),$this->ilias->error_obj->MESSAGE);
		}

		$file = explode(":", $_POST["file"][0]);
		$export = new ilSCORM2004Export($this->object);
		$export_dir = $export->getExportDirectoryForType($file[0]);
		ilUtil::deliverFile($export_dir."/".$file[1], $file[1]);
	}
	
	/* confirmation screen for export file deletion
	*/
	function confirmDeleteExportFile()
	{
		if(!isset($_POST["file"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		$this->setTabs();

		// SAVE POST VALUES
		$_SESSION["ilExportFiles"] = $_POST["file"];

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.confirm_deletion.html", "Modules/LearningModule");

		ilUtil::sendInfo($this->lng->txt("info_delete_sure"));

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		// BEGIN TABLE HEADER
		$this->tpl->setCurrentBlock("table_header");
		$this->tpl->setVariable("TEXT",$this->lng->txt("objects"));
		$this->tpl->parseCurrentBlock();

		// BEGIN TABLE DATA
		$counter = 0;
		foreach($_POST["file"] as $file)
		{
				$file = explode(":", $file);
				$this->tpl->setCurrentBlock("table_row");
				$this->tpl->setVariable("CSS_ROW",ilUtil::switchColor(++$counter,"tblrow1","tblrow2"));
				$this->tpl->setVariable("TEXT_CONTENT", $file[1]." (".$file[0].")");
				$this->tpl->parseCurrentBlock();
		}

		// cancel/confirm button
		$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$buttons = array( "cancelDeleteExportFile"  => $this->lng->txt("cancel"),
			"deleteExportFile"  => $this->lng->txt("confirm"));
		foreach ($buttons as $name => $value)
		{
			$this->tpl->setCurrentBlock("operation_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}
	}


	/**
	* cancel deletion of export files
	*/
	function cancelDeleteExportFile()
	{
		session_unregister("ilExportFiles");
		$this->ctrl->redirect($this, "showExportList");
	}


	/**
	* delete export files
	*/
	function deleteExportFile()
	{
		foreach($_SESSION["ilExportFiles"] as $file)
		{
			$file = explode(":", $file);
			$export = new ilSCORM2004Export($this->object);
			$export_dir = $export->getExportDirectoryForType($file[0]);
		
			$exp_file = $export_dir."/".$file[1];
			$exp_dir = $export_dir."/".substr($file[1], 0, strlen($file[1]) - 4);
			if (@is_file($exp_file))
			{
				unlink($exp_file);
			}
			if (@is_dir($exp_dir))
			{
				ilUtil::delDir($exp_dir);
			}
		}
		$this->ctrl->redirect($this, "showExportList");
	}
	
	/*
	* download export file
	*/
	function publishExportFile()
	{
		if(!isset($_POST["file"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		if (count($_POST["file"]) > 1)
		{
			$this->ilias->raiseError($this->lng->txt("cont_select_max_one_item"),$this->ilias->error_obj->MESSAGE);
		}

		$file = explode(":", $_POST["file"][0]);
		$export = new ilSCORM2004Export($this->object);
		$export_dir = $export->getExportDirectoryForType($file[0]);
		

		if ($this->object->getPublicExportFile($file[0]) ==	$file[1])
		{
			$this->object->setPublicExportFile($file[0], "");
		}
		else
		{
			$this->object->setPublicExportFile($file[0], $file[1]);
		}
		$this->object->update();
		$this->ctrl->redirect($this, "showExportList");
	}
	
	/*
	 * perform silent scorm 2004 export and import for view player
	 */
	function preview()
	{
		global $ilias;
		
		$export = new ilScorm2004Export($this->object,'SCORM 2004');
		$zipfile = $export->buildExportFile();
		$zipPathinfo = pathinfo($zipfile);
		$file_path = $this->object->getDataDirectory()."/".($zipPathinfo["basename"]);
		copy($zipfile,$file_path);
		unlink($zipfile);
		
		ilUtil::unzip($file_path,true);
		ilUtil::renameExecutables($this->object->getDataDirectory());
		unlink($file_path);
		
		include_once ("./Modules/Scorm2004/classes/ilSCORM13Package.php");
		$rte_pkg = new ilSCORM13Package();
		$rte_pkg->il_import($this->object->getDataDirectory(),$this->object->getId(),$ilias,false,true);

		//increase module version is it necessary?
		//$this->object->setModuleVersion($module_version+1);
		//$this->object->update();
			
		//redirect to view player
		ilUtil::redirect("ilias.php?baseClass=ilSAHSPresentationGUI&ref_id=".$this->object->getRefID());
	}

} // END class.ilObjSCORM2004LearningModuleGUI
?>
