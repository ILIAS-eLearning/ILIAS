<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Object/classes/class.ilObjectGUI.php";
include_once("./Services/Style/classes/class.ilPageLayout.php");

/**
 * Style settings GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * 
 * @ilCtrl_Calls ilObjStyleSettingsGUI: ilPermissionGUI, ilPageLayoutGUI
 * 
 * @ingroup	ServicesStyle
 */
class ilObjStyleSettingsGUI extends ilObjectGUI
{
	//page_layout editing
	var $peditor_active = false;
	var $pg_id = null;
	
	/**
	 * Constructor
	 */
	function ilObjStyleSettingsGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output = true)
	{
		global $lng,$ilCtrl;
		
		$this->type = "stys";
		
		$cmd = $ilCtrl->getCmd();
		
		if ($cmd == "editPg") {
			$this->peditor_active = true;
		}
					
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);
		
		$lng->loadLanguageModule("style");
	}
	
	/**
	 * Execute command
	 */
	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		 
		if ($next_class == "ilpagelayoutgui" || $cmd =="createPg") {
			$this->peditor_active =true;
		}
		
		$this->prepareOutput();
		
		switch($next_class)
		{
			case 'ilpermissiongui':
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;
				
			case 'ilpagelayoutgui':
				include_once("./Services/Style/classes/class.ilPageLayoutGUI.php");
				$this->tpl->getStandardTemplate();
				$this->ctrl->setReturn($this, "edit");
				if ($this->pg_id!=null) {
					$layout_gui =& new ilPageLayoutGUI($this->type,$this->pg_id);
				} else {
					$layout_gui =& new ilPageLayoutGUI($this->type,$_GET["obj_id"]);	
				}				
				$layout_gui->setTabs();
				$layout_gui->setEditPreview(true);
				$this->ctrl->saveParameter($this, "obj_id");
				$ret =& $this->ctrl->forwardCommand($layout_gui);
				$this->tpl->setContent($ret);
				break;	

			default:
				if ($cmd == "" || $cmd == "view")
				{
					$cmd = "editBasicSettings";
				}
				$cmd .= "Object";
				$this->$cmd();

				break;
		}
		return true;
	}
	
	/**
	 * Save object
	 */
	function saveObject()
	{
		global $rbacadmin;

		// create and insert forum in objecttree
		$newObj = parent::saveObject();

		// put here object specific stuff
			
		// always send a message
		ilUtil::sendInfo($this->lng->txt("object_added"),true);
		
		ilUtil::redirect($this->getReturnLocation("save",$this->ctrl->getLinkTarget($this,"","",false,false)));
	}
	
	/**
	* edit basic style settings
	*/
	function editBasicSettingsObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		
		$this->tpl->addBlockfile("ADM_CONTENT", "style_basic_settings", "tpl.stys_basic_settings.html", "Services/Style");
		//$this->tpl->setCurrentBlock("style_settings");

		$settings = $this->ilias->getAllSettings();
		
		if ($rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->tpl->setCurrentBlock("save_but");
			$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("FORMACTION_STYLESETTINGS", $this->ctrl->getFormAction($this));

		$this->tpl->setVariable("TXT_TREE_FRAME", $this->lng->txt("tree_frame"));
		$this->tpl->setVariable("TXT_TREE_FRAME_INFO", $this->lng->txt("tree_frame_info"));
		$this->tpl->setVariable("TXT_FRAME_LEFT", $this->lng->txt("tree_left"));
		$this->tpl->setVariable("TXT_FRAME_RIGHT", $this->lng->txt("tree_right"));

		$this->tpl->setVariable("TXT_STYLE_SETTINGS", $this->lng->txt("basic_settings"));
		$this->tpl->setVariable("TXT_ICONS_IN_TYPED_LISTS", $this->lng->txt("icons_in_typed_lists"));
		$this->tpl->setVariable("TXT_ICONS_IN_HEADER", $this->lng->txt("icons_in_header"));
		$this->tpl->setVariable("TXT_ICONS_IN_ITEM_ROWS", $this->lng->txt("icons_in_item_rows"));
		$this->tpl->setVariable("TXT_ICONS_IN_TYPED_LISTS_INFO", $this->lng->txt("icons_in_typed_lists_info"));
		
		$this->tpl->setVariable("TXT_ENABLE_CUSTOM_ICONS", $this->lng->txt("enable_custom_icons"));
		$this->tpl->setVariable("TXT_ENABLE_CUSTOM_ICONS_INFO", $this->lng->txt("enable_custom_icons_info"));
		$this->tpl->setVariable("TXT_CUSTOM_ICON_SIZE_BIG", $this->lng->txt("custom_icon_size_big"));
		$this->tpl->setVariable("TXT_CUSTOM_ICON_SIZE_SMALL", $this->lng->txt("custom_icon_size_standard"));
		$this->tpl->setVariable("TXT_CUSTOM_ICON_SIZE_TINY", $this->lng->txt("custom_icon_size_tiny"));
		$this->tpl->setVariable("TXT_WIDTH_X_HEIGHT", $this->lng->txt("width_x_height"));
		
		// set current values
		if ($settings["tree_frame"] == "right")
		{
			$this->tpl->setVariable("SEL_FRAME_RIGHT","selected=\"selected\"");
		}
		else
		{
			$this->tpl->setVariable("SEL_FRAME_LEFT","selected=\"selected\"");
		}
		
		if ($settings["custom_icons"])
		{
			$this->tpl->setVariable("CHK_CUSTOM_ICONS","checked=\"checked\"");
		}
/*		if ($settings["icon_position_in_lists"] == "item_rows")
		{
			$this->tpl->setVariable("SEL_ICON_POS_ITEM_ROWS","selected=\"selected\"");
		}
		else
		{
			$this->tpl->setVariable("SEL_ICON_POS_HEADER","selected=\"selected\"");
		}*/
		$this->tpl->setVariable("CUST_ICON_BIG_WIDTH", $settings["custom_icon_big_width"]);
		$this->tpl->setVariable("CUST_ICON_BIG_HEIGHT", $settings["custom_icon_big_height"]);
		$this->tpl->setVariable("CUST_ICON_SMALL_WIDTH", $settings["custom_icon_small_width"]);
		$this->tpl->setVariable("CUST_ICON_SMALL_HEIGHT", $settings["custom_icon_small_height"]);
		$this->tpl->setVariable("CUST_ICON_TINY_WIDTH", $settings["custom_icon_tiny_width"]);
		$this->tpl->setVariable("CUST_ICON_TINY_HEIGHT", $settings["custom_icon_tiny_height"]);

//		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* save basic style settings
	*/
	function saveBasicStyleSettingsObject()
	{
		$this->ilias->setSetting("tree_frame", $_POST["tree_frame"]);
//		$this->ilias->setSetting("icon_position_in_lists", $_POST["icon_position_in_lists"]);
		$this->ilias->setSetting("custom_icons", $_POST["custom_icons"]);
		$this->ilias->setSetting("custom_icon_big_width", (int) $_POST["custom_icon_big_width"]);
		$this->ilias->setSetting("custom_icon_big_height", (int) $_POST["custom_icon_big_height"]);
		$this->ilias->setSetting("custom_icon_small_width", (int) $_POST["custom_icon_small_width"]);
		$this->ilias->setSetting("custom_icon_small_height", (int) $_POST["custom_icon_small_height"]);
		$this->ilias->setSetting("custom_icon_tiny_width", (int) $_POST["custom_icon_tiny_width"]);
		$this->ilias->setSetting("custom_icon_tiny_height", (int) $_POST["custom_icon_tiny_height"]);
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		ilUtil::redirect($this->ctrl->getLinkTarget($this,"editBasicSettings","",false,false));
	}
	
	/**
	* view list of styles
	*/
	function editContentStylesObject()
	{
		global $rbacsystem, $ilias, $tpl, $ilToolbar, $ilCtrl, $lng;
		
		if (!$rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		// this may not be cool, if styles are organised as (independent) Service
		include_once("./Modules/LearningModule/classes/class.ilObjContentObject.php");
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");

		$from_styles = $to_styles = $data = array();
		$styles = $this->object->getStyles();

		foreach($styles as $style)
		{
			$style["active"] = ilObjStyleSheet::_lookupActive($style["id"]);
			$style["lm_nr"] = ilObjContentObject::_getNrOfAssignedLMs($style["id"]);
			$data[$style["title"].":".$style["id"]]
				= $style;
			if ($style["lm_nr"] > 0)
			{
				$from_styles[$style["id"]] = $style["title"];
			}
			if ($style["active"] > 0)
			{
				$to_styles[$style["id"]] = $style["title"];
			}
		}

		// number of individual styles
		if ($fixed_style <= 0)
		{
			$data[-1] =
				array("title" => $this->lng->txt("sty_individual_styles"),
					"id" => 0, "lm_nr" => ilObjContentObject::_getNrLMsIndividualStyles());
			$from_styles[-1] = $this->lng->txt("sty_individual_styles");
		}

		// number of default style (fallback default style)
		if ($default_style <= 0 && $fixed_style <= 0)
		{
			$data[0] =
				array("title" => $this->lng->txt("sty_default_style"),
					"id" => 0, "lm_nr" => ilObjContentObject::_getNrLMsNoStyle());
			$from_styles[0] = $this->lng->txt("sty_default_style");
			$to_styles[0] = $this->lng->txt("sty_default_style");
		}

		if ($rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$ilToolbar->addButton($lng->txt("sty_add_content_style"),
				$ilCtrl->getLinkTarget($this, "createStyle"));
			$ilToolbar->addSeparator();
			include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
			
			// from styles selector
			$si = new ilSelectInputGUI($lng->txt("sty_move_lm_styles").": ".$lng->txt("sty_from"), "from_style");
			$si->setOptions($from_styles);
			$ilToolbar->addInputItem($si, true);
	
			// from styles selector
			$si = new ilSelectInputGUI($lng->txt("sty_to"), "to_style");
			$si->setOptions($to_styles);
			$ilToolbar->addInputItem($si, true);
			$ilToolbar->addFormButton($lng->txt("sty_move_style"), "moveLMStyles");
	
			$ilToolbar->setFormAction($ilCtrl->getFormAction($this));
		}

		include_once("./Services/Style/classes/class.ilContentStylesTableGUI.php");
		$table = new ilContentStylesTableGUI($this, "editContentStyles", $data, $this->object);
		$tpl->setContent($table->getHTML());

	}
	
	/**
	* move learning modules from one style to another
	*/
	function moveLMStylesObject()
	{
		if ($_POST["from_style"] == -1)
		{
			$this->confirmDeleteIndividualStyles();
			return;
		}
		
		include_once("./Modules/LearningModule/classes/class.ilObjContentObject.php");
		ilObjContentObject::_moveLMStyles($_POST["from_style"], $_POST["to_style"]);
		$this->ctrl->redirect($this, "editContentStyles");
	}
	
	
	/**
	* move all learning modules with individual styles to new style
	*/
	function moveIndividualStylesObject()
	{
		include_once("./Modules/LearningModule/classes/class.ilObjContentObject.php");
		ilObjContentObject::_moveLMStyles(-1, $_GET["to_style"]);
		$this->ctrl->redirect($this, "editContentStyles");
	}

	/**
	 *
	 */
	function confirmDeleteIndividualStyles()
	{
		global $ilCtrl, $tpl, $lng;

		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");

		$ilCtrl->setParameter($this, "to_style", $_POST["to_style"]);

		$cgui = new ilConfirmationGUI();
		$cgui->setFormAction($ilCtrl->getFormAction($this));
		$cgui->setHeaderText($lng->txt("sty_confirm_del_ind_styles").": ".
			sprintf($this->lng->txt("sty_confirm_del_ind_styles_desc"),
			ilObject::_lookupTitle($_POST["to_style"])));
		$cgui->setCancel($lng->txt("cancel"), "editContentStyles");
		$cgui->setConfirm($lng->txt("ok"), "moveIndividualStyles");
		$tpl->setContent($cgui->getHTML());
	}
		
	/**
	* edit system styles
	*/
	function editSystemStylesObject()
	{
		global $rbacsystem, $ilias, $styleDefinition;;
		
		if (!$rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		
		$this->tpl->addBlockfile("ADM_CONTENT", "style_settings", "tpl.stys_settings.html", "Services/Style");
		$this->tpl->setCurrentBlock("style_settings");

		$settings = $this->ilias->getAllSettings();

		if ($rbacsystem->checkAccess("write", (int) $_GET["ref_id"]))
		{
			$this->tpl->setCurrentBlock("save_but");
			$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setVariable("FORMACTION_STYLESETTINGS", $this->ctrl->getFormAction($this));		
		$this->tpl->setVariable("TXT_STYLE_SETTINGS", $this->lng->txt("system_style_settings"));
		$this->tpl->setVariable("TXT_DEFAULT_SKIN_STYLE", $this->lng->txt("default_skin_style"));
		$this->tpl->setVariable("TXT_SKIN_STYLE_ACTIVATION", $this->lng->txt("style_activation"));
		$this->tpl->setVariable("TXT_NUMBER_OF_USERS", $this->lng->txt("num_users"));
		$this->tpl->setVariable("TXT_MOVE_USERS_TO_STYLE", $this->lng->txt("move_users_to_style"));
		
		// get all templates
		$templates = $styleDefinition->getAllTemplates();

		$all_styles = array();
		
		foreach ($templates as $template)
		{
			// get styles definition for template
			$styleDef =& new ilStyleDefinition($template["id"]);
			$styleDef->startParsing();
			$styles = $styleDef->getStyles();

			foreach ($styles as $style)
			{
				if ($this->ilias->ini->readVariable("layout","skin") == $template["id"] &&
					$this->ilias->ini->readVariable("layout","style") == $style["id"])
				{
					$this->tpl->setVariable("SKINSELECTED", "selected=\"selected\"");
				}

				// default selection list
				$this->tpl->setCurrentBlock("selectskin");
				$this->tpl->setVariable("SKINVALUE", $template["id"].":".$style["id"]);
				$this->tpl->setVariable("SKINOPTION", $styleDef->getTemplateName()." / ".$style["name"]);
				$this->tpl->parseCurrentBlock();
				
				// can be optimized
				foreach ($templates as $template2)
				{
					// get styles definition for template
					$styleDef2 =& new ilStyleDefinition($template2["id"]);
					$styleDef2->startParsing();
					$styles2 = $styleDef2->getStyles();
		
					foreach ($styles2 as $style2)
					{
						if (ilObjStyleSettings::_lookupActivatedStyle($template2["id"], $style2["id"]))
						{
							$this->tpl->setCurrentBlock("move_to_skin");
							$this->tpl->setVariable("TOSKINVALUE", $template2["id"].":".$style2["id"]);
							$this->tpl->setVariable("TOSKINOPTION", $styleDef2->getTemplateName()." / ".$style2["name"]);
							$this->tpl->parseCurrentBlock();
						}
					}
				}
				
				// activation checkbox
				$this->tpl->setCurrentBlock("activation_checkbox");
				$this->tpl->setVariable("VAL_SKIN_STYLE", $template["id"].":".$style["id"]);
				if (ilObjStyleSettings::_lookupActivatedStyle($template["id"], $style["id"]))
				{
					$this->tpl->setVariable("CHK_SKIN_STYLE", " checked=\"1\" ");
				}
				$this->tpl->parseCurrentBlock();
				
				// activation row
				$this->tpl->setCurrentBlock("style_activation");
				$this->tpl->setVariable("VAL_MOVE_SKIN_STYLE", $template["id"].":".$style["id"]);
				$this->tpl->setVariable("TXT_SKIN_STYLE_TITLE", 
					$styleDef->getTemplateName()." / ".$style["name"]);
				$num_users = ilObjUser::_getNumberOfUsersForStyle($template["id"], $style["id"]);
				$this->tpl->setVariable("VAL_NUM_USERS", $num_users);
				$this->tpl->parseCurrentBlock();
				
				$all_styles[] = $template["id"].":".$style["id"];
			}
		}
		
		// get all user assigned styles
		$all_user_styles = ilObjUser::_getAllUserAssignedStyles();
		
		// output "other" row for all users, that are not assigned to
		// any existing style
		$users_missing_styles = 0;
		foreach($all_user_styles as $style)
		{
			if (!in_array($style, $all_styles))
			{
				$style_arr = explode(":", $style);
				$users_missing_styles += ilObjUser::_getNumberOfUsersForStyle($style_arr[0], $style_arr[1]);
			}
		}

		if ($users_missing_styles > 0)
		{			
			// can be optimized
			foreach ($templates as $template2)
			{
				// get styles definition for template
				$styleDef2 =& new ilStyleDefinition($template2["id"]);
				$styleDef2->startParsing();
				$styles2 = $styleDef2->getStyles();
	
				foreach ($styles2 as $style2)
				{
					if (ilObjStyleSettings::_lookupActivatedStyle($template2["id"], $style2["id"]))
					{
						$this->tpl->setCurrentBlock("move_to_skin");
						$this->tpl->setVariable("TOSKINVALUE", $template2["id"].":".$style2["id"]);
						$this->tpl->setVariable("TOSKINOPTION", $styleDef2->getTemplateName()." / ".$style2["name"]);
						$this->tpl->parseCurrentBlock();
					}
				}
			}
			
			$this->tpl->setCurrentBlock("style_activation");
			$this->tpl->setVariable("TXT_SKIN_STYLE_TITLE",
				$this->lng->txt("other"));
			$this->tpl->setVariable("VAL_NUM_USERS",
				$users_missing_styles);
			$this->tpl->setVariable("VAL_MOVE_SKIN_STYLE", "other");
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->parseCurrentBlock();
	}
	

	/**
	* save skin and style settings
	*/
	function saveStyleSettingsObject()
	{
		global $styleDefinition, $ilCtrl;
		
		// check if one style is activated
		if (count($_POST["st_act"]) < 1)
		{
			$this->ilias->raiseError($this->lng->txt("at_least_one_style"), $this->ilias->error_obj->MESSAGE);
		}
		
		// check if a style should be deactivated, that still has
		// a user assigned to
		$templates = $styleDefinition->getAllTemplates();
		$all_styles = array();
		foreach ($templates as $template)
		{
			// get styles definition for template
			$styleDef =& new ilStyleDefinition($template["id"]);
			$styleDef->startParsing();
			$styles = $styleDef->getStyles();
			foreach ($styles as $style)
			{
				if (!isset($_POST["st_act"][$template["id"].":".$style["id"]]))
				{
					if (ilObjUser::_getNumberOfUsersForStyle($template["id"], $style["id"]) > 1)
					{
						$this->ilias->raiseError($this->lng->txt("cant_deactivate_if_users_assigned"), $this->ilias->error_obj->MESSAGE);
					}
					else
					{
						ilObjStyleSettings::_deactivateStyle($template["id"], $style["id"]);
					}
				}
				else
				{
					ilObjStyleSettings::_activateStyle($template["id"], $style["id"]);
				}
				$all_styles[] = $template["id"].":".$style["id"];
			}
		}
		
		// move users to other skin
		foreach($_POST["move_users"] as $key => $value)
		{
			if ($value != "")
			{
				$to = explode(":", $value);
				
				if ($key != "other")
				{
					$from = explode(":", $key);
					ilObjUser::_moveUsersToStyle($from[0],$from[1],$to[0],$to[1]);
				}
				else
				{
					// get all user assigned styles
					$all_user_styles = ilObjUser::_getAllUserAssignedStyles();
					
					// move users that are not assigned to
					// currently existing style
					foreach($all_user_styles as $style)
					{
						if (!in_array($style, $all_styles))
						{
							$style_arr = explode(":", $style);
							ilObjUser::_moveUsersToStyle($style_arr[0],$style_arr[1],$to[0],$to[1]);
						}
					}
				}
			}
		}
		
		//set default skin and style
		if ($_POST["default_skin_style"] != "")
		{
			$sknst = explode(":", $_POST["default_skin_style"]);

			if ($this->ilias->ini->readVariable("layout","style") != $sknst[1] ||
				$this->ilias->ini->readVariable("layout","skin") != $sknst[0])
			{
				$this->ilias->ini->setVariable("layout","skin", $sknst[0]);
				$this->ilias->ini->setVariable("layout","style",$sknst[1]);
			}
		}
		$this->ilias->ini->write();

		ilUtil::sendInfo($this->lng->txt("msg_obj_modified"), true);
		$ilCtrl->redirect($this , "editSystemStyles");
	}
	
	/**
	* display deletion confirmation screen
	*
	* @access	public
 	*/
	function deleteStyleObject($a_error = false)
	{
		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		// SAVE POST VALUES
		$_SESSION["saved_post"] = $_POST["id"];

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.confirm_deletion.html");

		if(!$a_error)
		{
			ilUtil::sendInfo($this->lng->txt("info_delete_sure"));
		}

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		// BEGIN TABLE HEADER
		$this->tpl->setCurrentBlock("table_header");
		$this->tpl->setVariable("TEXT", $this->lng->txt("objects"));
		$this->tpl->parseCurrentBlock();
		
		// END TABLE HEADER

		// BEGIN TABLE DATA
		$counter = 0;

		foreach ($_POST["id"] as $id)
		{
			$this->tpl->setCurrentBlock("table_row");
			$this->tpl->setVariable("IMG_OBJ",ilUtil::getImagePath("icon_sty.gif"));
			$this->tpl->setVariable("CSS_ROW",ilUtil::switchColor(++$counter,"tblrow1","tblrow2"));
			$this->tpl->setVariable("TEXT_CONTENT",ilObject::_lookupTitle($id));
			$this->tpl->parseCurrentBlock();
		}
		
		// END TABLE DATA

		// BEGIN OPERATION_BTN
		$buttons = array("confirmedDelete"  => $this->lng->txt("confirm"),
			"cancelDelete"  => $this->lng->txt("cancel"));
		foreach ($buttons as $name => $value)
		{
			$this->tpl->setCurrentBlock("operation_btn");
			$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}
	}
	
	
	/**
	* delete selected style objects
	*/
	function confirmedDeleteObject()
	{
		global $ilias;
		
		foreach($_SESSION["saved_post"] as $id)
		{
			$this->object->removeStyle($id);
			$style_obj =& $ilias->obj_factory->getInstanceByObjId($id);
			$style_obj->delete();
		}
		$this->object->update();
		
		ilUtil::redirect($this->getReturnLocation("delete",
			$this->ctrl->getLinkTarget($this,"editContentStyles","",false,false)));
	}
	
	
	/**
	 * Toggle global default style
 	 */
	function toggleGlobalDefaultObject()
	{
		global $ilSetting, $lng;
		
		if ($_GET["id"] > 0)
		{
			$ilSetting->delete("fixed_content_style_id");
			$def_style = $ilSetting->get("default_content_style_id");
		
			if ($def_style != $_GET["id"])
			{
				$ilSetting->set("default_content_style_id", (int) $_GET["id"]);
			}
			else
			{
				$ilSetting->delete("default_content_style_id");
			}
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
		}
		ilUtil::redirect($this->ctrl->getLinkTarget($this, "editContentStyles", "", false, false));
	}

	/**
	 * Toggle global fixed style
 	 */
	function toggleGlobalFixedObject()
	{
		global $ilSetting, $lng;
		
		if ($_GET["id"] > 0)
		{
			$ilSetting->delete("default_content_style_id");
			$fixed_style = $ilSetting->get("fixed_content_style_id");
			if ($fixed_style == (int) $_GET["id"])
			{
				$ilSetting->delete("fixed_content_style_id");
			}
			else
			{
				$ilSetting->set("fixed_content_style_id", (int) $_GET["id"]);
			}
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
		}
		ilUtil::redirect($this->ctrl->getLinkTarget($this, "editContentStyles", "", false, false));
	}
	
	
	/**
	 * Save active styles
	 */
	function saveActiveStylesObject()
	{
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		$styles = $this->object->getStyles();
		foreach($styles as $style)
		{
			if ($_POST["std_".$style["id"]] == 1)
			{
				ilObjStyleSheet::_writeActive((int) $style["id"], 1);
			}
			else
			{
				ilObjStyleSheet::_writeActive((int) $style["id"], 0);
			}
		}
		ilUtil::redirect($this->ctrl->getLinkTarget($this, "editContentStyles", "", false, false));
	}
	
	/**
	* show possible action (form buttons)
	*
	* @param	boolean
	* @access	public
 	*/
	function showActions($with_subobjects = false)
	{

		// delete
		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", "deleteStyle");
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("delete"));
		$this->tpl->parseCurrentBlock();

		// set global default
		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", "toggleGlobalDefault");
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("toggleGlobalDefault"));
		$this->tpl->parseCurrentBlock();
		
		// set global default
		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", "toggleGlobalFixed");
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("toggleGlobalFixed"));
		$this->tpl->parseCurrentBlock();

		// set global default
		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", "setScope");
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("sty_set_scope"));
		$this->tpl->parseCurrentBlock();

		// save active styles
		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", "saveActiveStyles");
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("sty_save_active_styles"));
		$this->tpl->parseCurrentBlock();

		if ($with_subobjects === true)
		{
			$this->showPossibleSubObjects();
		}
		
		$this->tpl->setCurrentBlock("tbl_action_row");
		$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
		$this->tpl->parseCurrentBlock();
	}

	/**
	* cancel deletion of object
	*
	* @access	public
	*/
	function cancelDeleteObject()
	{
		session_unregister("saved_post");

		ilUtil::sendInfo($this->lng->txt("msg_cancel"),true);
		$this->ctrl->redirect($this, "editContentStyles");

	}

	function setScopeObject()
	{
		if ($_GET["id"] > 0)
		{		
			include_once ("./Services/Style/classes/class.ilStyleScopeExplorer.php");
			$exp = new ilStyleScopeExplorer("ilias.php?baseClass=ilRepositoryGUI&amp;cmd=goto");
			$exp->setExpandTarget("ilias.php?baseClass=ilRepositoryGUI&amp;cmd=showTree");
			$exp->setTargetGet("ref_id");
			$exp->setFilterMode(IL_FM_POSITIVE);
			$exp->forceExpandAll(true, false);
			$exp->addFilter("root");
			$exp->addFilter("cat");

			if ($_GET["expand"] == "")
			{
				$expanded = $this->tree->readRootId();
			}
			else
			{
				$expanded = $_GET["expand"];
			}

			$exp->setExpand($expanded);

			// build html-output
			$exp->setOutput(0);
			$output = $exp->getOutput();
		}

		$this->tpl->setVariable("ADM_CONTENT", $output);
	}
	
	/**
	* save scope for style
	*/
	function saveScopeObject()
	{
		global $ilias;
		
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		if ($_GET["cat"] == 0)
		{
			$_GET["cat"] == "";
		}
		ilObjStyleSheet::_writeScope($_GET["style_id"], $_GET["cat"]);
		
		ilUtil::redirect($this->ctrl->getLinkTarget($this, "editContentStyles", "", false, false));
	}


	/**
	* view list of page layouts
	*/
	function viewPageLayoutsObject()
	{
		global $tpl, $lng, $ilCtrl, $ilTabs, $ilToolbar, $rbacsystem;
		
		$ilTabs->setTabActive('page_layouts');
		
		// show toolbar, if write permission is given
		if ($rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$ilToolbar->addButton($lng->txt("sty_add_pgl"),
				$ilCtrl->getLinkTarget($this, "addPageLayout"));
			$ilToolbar->addButton($lng->txt("sty_import_page_layout"),
				$ilCtrl->getLinkTarget($this, "importPageLayoutForm"));
		}

		$oa_tpl = new ilTemplate("tpl.stys_pglayout.html", true, true, "Services/Style");
   		
		include_once("./Services/Style/classes/class.ilPageLayoutTableGUI.php");
		$pglayout_table = new ilPageLayoutTableGUI($this, "viewPageLayouts");
		$oa_tpl->setVariable("PGLAYOUT_TABLE", $pglayout_table->getHTML());
		$tpl->setContent($oa_tpl->get());
		
	}
	
	
	function activateObject($a_activate=true){
		if (!isset($_POST["pglayout"]))
		{
			ilUtil::sendInfo($this->lng->txt("no_checkbox"),true);
		} else {
			ilUtil::sendSuccess($this->lng->txt("sty_opt_saved"),true);
			foreach ($_POST["pglayout"] as $item)
			{
				$pg_layout = new ilPageLayout($item);
				$pg_layout->activate($a_activate);
			}
		}	
		$this->ctrl->redirect($this, "viewPageLayouts");
	}
	
	function deactivateObject(){
		$this->activateObject(false);
	}
	
	
	
	/**
	* display deletion confirmation screen
	*/
	function deletePglObject()
	{
		global $ilTabs;
		
		if(!isset($_POST["pglayout"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		
		$ilTabs->setTabActive('page_layouts');
		
		// SAVE POST VALUES
		$_SESSION["pglayout_user_delete"] = $_POST["pglayout"];

		unset($this->data);
		$this->data["cols"] = array("type","title");

		foreach($_POST["pglayout"] as $id)
		{
			$pg_obj = new ilPageLayout($id);
			$pg_obj->readObject();
			$this->data["data"]["$id"] = array(
				"type"		  => "stys",
				"title"       => $pg_obj->getTitle()
			);

		}

		$this->data["buttons"] = array( "cancelDeletePg"  => $this->lng->txt("cancel"),
								  "confirmedDeletePg"  => $this->lng->txt("confirm"));

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.obj_confirm.html');

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
	
	
	/**
	* cancel deletion of Page Layout
	*/
	function cancelDeletePgObject()
	{
		session_unregister("pglayout_user_delete");
		ilUtil::sendInfo($this->lng->txt("msg_cancel"),true);
		$this->ctrl->redirect($this, "viewPageLayouts");
	}	
	
	
	/**
	* conform deletion of Page Layout
	*/
	function confirmedDeletePgObject()
	{
	 	global $ilDB, $ilUser;
        
	 	foreach ($_SESSION["pglayout_user_delete"] as $id)
	 	{
   	 		$pg_obj = new ilPageLayout($id);
			$pg_obj->delete();	 		
	 	}
  
	 	$this->ctrl->redirect($this, "viewPageLayouts");
	}
	
	function addPageLayoutObject($a_form = null)
	{
    	global $ilTabs;
   
		$ilTabs->setTabActive('page_layouts');
		
		if(!$a_form)
		{
			$a_form = $this->initAddPageLayoutForm();
		}

    	$this->tpl->setContent($a_form->getHTML());
	}
	
	function initAddPageLayoutForm()
	{
		global $lng, $ilCtrl;
		
		$lng->loadLanguageModule("content");
		
    	include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
    	$form_gui = new ilPropertyFormGUI();
    	$form_gui->setFormAction($ilCtrl->getFormAction($this));
    	$form_gui->setTitle($lng->txt("sty_create_pgl"));
   
    	include_once("Services/Form/classes/class.ilRadioMatrixInputGUI.php");
   
   
    	$title_input = new ilTextInputGUI($lng->txt("title"),"pgl_title");
    	$title_input->setSize(50);
    	$title_input->setMaxLength(128);
    	$title_input->setValue($this->layout_object->title);
    	$title_input->setTitle($lng->txt("title"));
    	$title_input->setRequired(true);
   
    	$desc_input = new ilTextAreaInputGUI($lng->txt("description"),"pgl_desc");
    	$desc_input->setValue($this->layout_object->description);
    	$desc_input->setRows(3);
    	$desc_input->setCols(37);
    	
    	// special page? 
    	$options = array(
    		"0" => $lng->txt("cont_layout_template"),
    		"1" => $lng->txt("cont_special_page"),
    		);
    	$si = new ilSelectInputGUI($this->lng->txt("type"), "special_page");
    	$si->setOptions($options);
		
		// modules
		$mods = new ilCheckboxGroupInputGUI($this->lng->txt("modules"), "module");
		// $mods->setRequired(true);
		foreach(ilPageLayout::getAvailableModules() as $mod_id => $mod_caption)
		{
			$mod = new ilCheckboxOption($mod_caption, $mod_id);
			$mods->addOption($mod);			
		}

		$ttype_input = new ilSelectInputGUI($lng->txt("sty_based_on"), "pgl_template");
		
		$arr_templates = ilPageLayout::getLayouts();
		$arr_templates1 = ilPageLayout::getLayouts(false, true);
		foreach ($arr_templates1 as $v)
		{
			$arr_templates[] = $v;
		}
		
		$options = array();
		$options['-1'] = $lng->txt("none");
		
		foreach ($arr_templates as $templ) {
			$templ->readObject();
			$key = $templ->getId();
			$value = $templ->getTitle();
			$options[$key] = $value;
		}
		
		$ttype_input->setOptions($options);
		$ttype_input->setValue(-1);
		$ttype_input->setRequired(true);
   
    	$desc_input->setTitle($lng->txt("description"));
    	$desc_input->setRequired(false);
   
    	$form_gui->addItem($title_input);
    	$form_gui->addItem($desc_input);
    	$form_gui->addItem($si);
    	$form_gui->addItem($mods);
    	$form_gui->addItem($ttype_input);

   
    	$form_gui->addCommandButton("createPg", $lng->txt("save"));
		$form_gui->addCommandButton("cancelCreate", $lng->txt("cancel"));
		
		return $form_gui;		
	}
	

	function createPgObject()
	{
		global $ilCtrl;
		
		$form_gui = $this->initAddPageLayoutForm();
		if(!$form_gui->checkInput())
		{
			$form_gui->setValuesByPost();
			return $this->addPageLayoutObject($form_gui);			
		}
				
		//create Page-Layout-Object first
		$pg_object = new ilPageLayout();
		$pg_object->setTitle($form_gui->getInput('pgl_title'));
		$pg_object->setDescription($form_gui->getInput('pgl_desc'));
		$pg_object->setSpecialPage($form_gui->getInput('special_page'));
		$pg_object->setModules($form_gui->getInput('module'));		
		$pg_object->update();
		
		include_once("./Services/COPage/classes/class.ilPageObject.php");
		
		//create Page
		if(!is_object($pg_content))
		{
			$this->pg_content =& new ilPageObject($this->type);
		}
		
		$this->pg_content->setId($pg_object->getId());
		
		$tmpl = $form_gui->getInput('pgl_template');
		if ($tmpl != "-1") 
		{
			$layout_obj = new ilPageLayout($tmpl);
			$this->pg_content->setXMLContent($layout_obj->getXMLContent());
			$this->pg_content->create(false);
		} 
		else 
		{
			$this->pg_content->create(false);
		}
		
		$ilCtrl->setParameterByClass("ilpagelayoutgui", "obj_id", $pg_object->getId());
		$ilCtrl->redirectByClass("ilpagelayoutgui", "edit");
	}
	
	function cancelCreateObject() {
		$this->viewPageLayoutsObject();
	}
	
	function editPgObject()
	{
		global $ilCtrl, $rbacsystem;
		
		if (!$rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$ilCtrl->setCmdClass("ilpagelayoutgui");
		$ilCtrl->setCmd("edit");
		$this->executeCommand();
	}
	
	
	function setTabs()
	{
		echo "settings_setTabs";
	}
	
	function getAdminTabs(&$tabs_gui)
	{
		$this->getTabs($tabs_gui);
	}
		
	/**
	* get tabs
	* @access	public
	* @param	object	tabs gui object
	*/
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem, $lng, $ilTabs;
		
		if ($this->peditor_active) {
			$tabs_gui->setBackTarget($this->lng->txt("page_layouts"),
			$this->ctrl->getLinkTarget($this, "viewPageLayouts"));
		}
			
		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()) && !$this->peditor_active)
		{
			$tabs_gui->addTarget("basic_settings",
				$this->ctrl->getLinkTarget($this, "editBasicSettings"), array("editBasicSettings","", "view"), "", "");

			$tabs_gui->addTarget("system_styles",
				$this->ctrl->getLinkTarget($this, "editSystemStyles"), "editSystemStyles", "", "");
				
			$tabs_gui->addTarget("content_styles",
				$this->ctrl->getLinkTarget($this, "editContentStyles"), "editContentStyles", "", "");
				
			$tabs_gui->addTarget("page_layouts",
				$this->ctrl->getLinkTarget($this, "viewPageLayouts"), "viewPageLayouts", "", "");
				
		}
		
		
		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()) && !$this->peditor_active)
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
	}

	/**
	 * Create new style
	 */
	function createStyleObject()
	{
		global $ilCtrl;

		$ilCtrl->setParameter($this, "new_type", "sty");
		$ilCtrl->redirect($this, "create");
	}

	/**
	 * Save page layout types
	 */
	function savePageLayoutTypesObject()
	{
		global $lng, $ilCtrl;

		include_once("./Services/Style/classes/class.ilPageLayout.php");

		if (is_array($_POST["type"]))
		{
			foreach($_POST["type"] as $id => $t)
			{
				if ($id > 0)
				{
					$l = new ilPageLayout($id);
					$l->readObject();
					$l->setSpecialPage($t);		
					if(is_array($_POST["module"][$id]))
					{
						$l->setModules(array_keys($_POST["module"][$id]));
					}
					else
					{
						$l->setModules();
					}
					$l->update();
				}
			}						
			
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"));
		}

		$ilCtrl->redirect($this, "viewPageLayouts");
	}


	/**
	 * Export page layout template object
	 */
	function exportLayoutObject()
	{
		include_once("./Services/Export/classes/class.ilExport.php");
		$exp = new ilExport();
		
		$tmpdir = ilUtil::ilTempnam();
		ilUtil::makeDir($tmpdir);

		$succ = $exp->exportEntity("pgtp", (int) $_GET["layout_id"], "4.2.0",
			"Services/COPage", "Title", $tmpdir);
		
		if ($succ["success"])
		{
			ilUtil::deliverFile($succ["directory"]."/".$succ["file"], $succ["file"],
				"", false, false, false);
		}
		if (is_file($succ["directory"]."/".$succ["file"]))
		{
			unlink($succ["directory"]."/".$succ["file"]);
		}
		if (is_dir($succ["directory"]))
		{
			unlink($succ["directory"]);
		}
	}
	
	/**
	 * Import page layout
	 */
	function importPageLayoutFormObject()
	{
		global $tpl, $ilTabs;
		
		$ilTabs->setTabActive('page_layouts');
		$form = $this->initPageLayoutImportForm();
		$tpl->setContent($form->getHTML());
	}
	
	/**
	 * Init page layout import form.
	 */
	public function initPageLayoutImportForm()
	{
		global $lng, $ilCtrl;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		
		// template file
		$fi = new ilFileInputGUI($lng->txt("file"), "file");
		$fi->setSuffixes(array("zip"));
		$fi->setRequired(true);
		$form->addItem($fi);
		
		$form->addCommandButton("importPageLayout", $lng->txt("import"));
		$form->addCommandButton("viewPageLayouts", $lng->txt("cancel"));
	                
		$form->setTitle($lng->txt("sty_import_page_layout"));
		$form->setFormAction($ilCtrl->getFormAction($this));
		
		return $form;
	 }
	 
	 /**
	  * Import page layout
	  */
	 public function importPageLayoutObject()
	 {
	 	global $tpl, $lng, $ilCtrl, $ilTabs;
	 
	 	$form = $this->initPageLayoutImportForm();
	 	if ($form->checkInput())
	 	{
	 		include_once("./Services/Style/classes/class.ilPageLayout.php");
	 		$pg = ilPageLayout::import($_FILES["file"]["name"], $_FILES["file"]["tmp_name"]);
	 		if ($pg > 0)
	 		{
	 			ilUtil::sendSuccess($lng->txt("sty_imported_layout"), true);
	 		}
	 		$ilCtrl->redirect($this, "viewPageLayouts");
	 	}
	 	else
	 	{
	 		$ilTabs->setTabActive('page_layouts');
	 		$form->setValuesByPost();
	 		$tpl->setContent($form->getHtml());
	 	}
	 }
}
?>
