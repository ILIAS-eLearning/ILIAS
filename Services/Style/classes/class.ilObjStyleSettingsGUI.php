<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
* Class ilObjStyleSettingsGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* 
* @ilCtrl_Calls ilObjStyleSettingsGUI: ilPermissionGUI
* 
* @extends ilObjectGUI
*/

include_once "./classes/class.ilObjectGUI.php";

class ilObjStyleSettingsGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjStyleSettingsGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output = true)
	{
		global $lng;
		
		$this->type = "stys";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);
		
		$lng->loadLanguageModule("style");
	}
	
	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		$this->prepareOutput();

//echo "-$next_class-$cmd-"; exit;
		
		switch($next_class)
		{
			case 'ilpermissiongui':
				include_once("./classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
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
	* save object
	* @access	public
	*/
	function saveObject()
	{
		global $rbacadmin;

		// create and insert forum in objecttree
		$newObj = parent::saveObject();

		// setup rolefolder & default local roles
		//$roles = $newObj->initDefaultRoles();

		// ...finally assign role to creator of object
		//$rbacadmin->assignUser($roles[0], $newObj->getOwner(), "y");

		// put here object specific stuff
			
		// always send a message
		ilUtil::sendInfo($this->lng->txt("object_added"),true);
		
		ilUtil::redirect($this->getReturnLocation("save",$this->ctrl->getLinkTarget($this,"")));
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
		
		$this->tpl->addBlockfile("ADM_CONTENT", "style_basic_settings", "tpl.stys_basic_settings.html");
		$this->tpl->setCurrentBlock("style_settings");

		$settings = $this->ilias->getAllSettings();

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
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		
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
		if ($settings["icon_position_in_lists"] == "item_rows")
		{
			$this->tpl->setVariable("SEL_ICON_POS_ITEM_ROWS","selected=\"selected\"");
		}
		else
		{
			$this->tpl->setVariable("SEL_ICON_POS_HEADER","selected=\"selected\"");
		}
		$this->tpl->setVariable("CUST_ICON_BIG_WIDTH", $settings["custom_icon_big_width"]);
		$this->tpl->setVariable("CUST_ICON_BIG_HEIGHT", $settings["custom_icon_big_height"]);
		$this->tpl->setVariable("CUST_ICON_SMALL_WIDTH", $settings["custom_icon_small_width"]);
		$this->tpl->setVariable("CUST_ICON_SMALL_HEIGHT", $settings["custom_icon_small_height"]);
		$this->tpl->setVariable("CUST_ICON_TINY_WIDTH", $settings["custom_icon_tiny_width"]);
		$this->tpl->setVariable("CUST_ICON_TINY_HEIGHT", $settings["custom_icon_tiny_height"]);

		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* save basic style settings
	*/
	function saveBasicStyleSettingsObject()
	{
		$this->ilias->setSetting("tree_frame", $_POST["tree_frame"]);
		$this->ilias->setSetting("icon_position_in_lists", $_POST["icon_position_in_lists"]);
		$this->ilias->setSetting("custom_icons", $_POST["custom_icons"]);
		$this->ilias->setSetting("custom_icon_big_width", (int) $_POST["custom_icon_big_width"]);
		$this->ilias->setSetting("custom_icon_big_height", (int) $_POST["custom_icon_big_height"]);
		$this->ilias->setSetting("custom_icon_small_width", (int) $_POST["custom_icon_small_width"]);
		$this->ilias->setSetting("custom_icon_small_height", (int) $_POST["custom_icon_small_height"]);
		$this->ilias->setSetting("custom_icon_tiny_width", (int) $_POST["custom_icon_tiny_width"]);
		$this->ilias->setSetting("custom_icon_tiny_height", (int) $_POST["custom_icon_tiny_height"]);
		ilUtil::sendInfo($this->lng->txt("msg_obj_modified"), true);
		ilUtil::redirect($this->ctrl->getLinkTarget($this,"editBasicSettings"));		
	}
	
	/**
	* view list of styles
	*/
	function editContentStylesObject()
	{
		global $rbacsystem, $ilias;
		
		if (!$rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		
		include_once "./Services/Table/classes/class.ilTableGUI.php";

		// load template content style settings
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.styf_content_styles.html");

		// load template for table
		$this->tpl->addBlockfile("STYLE_TABLE", "style_table", "tpl.table.html");
		
		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.styf_row.html");

		$num = 0;

		$this->tpl->setVariable("FORMACTION",
			$this->ctrl->getFormAction($this));

		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->lng->txt("content_styles"),"icon_sty.gif",
			$this->lng->txt("content_styles"));

		//$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));

		// title
		$header_names = array("", $this->lng->txt("title"),
			$this->lng->txt("sty_nr_learning_modules"),
			$this->lng->txt("purpose"), $this->lng->txt("sty_scope"),
			$this->lng->txt("active"));
		$tbl->setHeaderNames($header_names);

		$header_params = array("ref_id" => $this->ref_id);
		$tbl->setHeaderVars(array("", "title", "nr_lms", "purpose", "scope", "active"), $header_params);
		$tbl->setColumnWidth(array("1px", "", "", "", ""));

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->disable("sort");
		
		// get style ids
		$style_entries = array();
		$styles = $this->object->getStyles();
		foreach($styles as $style)
		{
			$style_entries[$style["title"].":".$style["id"]]
				= $style;
		}
		ksort($style_entries);
		$from_styles = $to_styles = array();
		// this may not be cool, if styles are organised as (independent) Service
		include_once("./Modules/LearningModule/classes/class.ilObjContentObject.php");

		// number of individual styles
		if ($fixed_style <= 0)
		{
			$style_entries[-1] = 
				array("title" => $this->lng->txt("sty_individual_styles"),
					"id" => 0, "nr" => ilObjContentObject::_getNrLMsIndividualStyles());
			$from_styles[-1] = $this->lng->txt("sty_individual_styles");
		}
		
		// number of default style (fallback default style)
		if ($default_style <= 0 && $fixed_style <= 0)
		{
			$style_entries[0] = 
				array("title" => $this->lng->txt("sty_default_style"),
					"id" => 0, "nr" => ilObjContentObject::_getNrLMsNoStyle());
			$from_styles[0] = $this->lng->txt("sty_default_style");
			$to_styles[0] = $this->lng->txt("sty_default_style");
		}
		
		// todo
		$tbl->setMaxCount(count($style_entries));

		$this->tpl->setVariable("COLUMN_COUNTS", 6);

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));

		$this->showActions(true);
		$table_empty = true;

		include_once ("./Services/Style/classes/class.ilObjStyleSheet.php");
		
		$fixed_style = $ilias->getSetting("fixed_content_style_id");
		$default_style = $ilias->getSetting("default_content_style_id");
		
		foreach ($style_entries as $style)
		{
			// color changing
			$css_row = ($css_row == "tblrow2")
				? "tblrow1"
				: "tblrow2";

			// command checkbox
			if ($style["id"] > 0)
			{
				$this->tpl->setCurrentBlock("check_box");
				$this->tpl->setVariable("CHECKBOX_ID", $style["id"]);
				$this->tpl->parseCurrentBlock();
			}
			
			// activation checkbox
			if ($fixed_style <= 0 && $style["id"] > 0)
			{
				$this->tpl->setCurrentBlock("active_box");
				if (ilObjStyleSheet::_lookupActive($style["id"]))
				{
					$this->tpl->setVariable("CHECKED_STY", 'checked="checked"');
				}
				$this->tpl->setVariable("ACTIVE_ID", $style["id"]);
				$this->tpl->parseCurrentBlock();
			}
			
			// link to style edit screen
			if ($style["id"] > 0)
			{
				$this->ctrl->setParameterByClass("ilobjstylesheetgui", "obj_id", $style["id"]);
				$this->tpl->setCurrentBlock("linka");
				$this->tpl->setVariable("TXT_TITLE", $style["title"]);
				$this->tpl->setVariable("LINK_STYLE",
					$this->ctrl->getLinkTargetByClass("ilobjstylesheetgui"), "view");
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock("texta");
				$this->tpl->setVariable("TXT_TEXT", $style["title"]);
				$this->tpl->parseCurrentBlock();
			}
			
			$this->tpl->setCurrentBlock("style_row");

			// number of assigned lms
			if ($style["id"] > 0)
			{
				$nr_lm = ilObjContentObject::_getNrOfAssignedLMs($style["id"]);
				
				// fill from/to array
				if ($nr_lm > 0)
				{
					$from_styles[$style["id"]] = $style["title"];
				}
				if (ilObjStyleSheet::_lookupActive($style["id"]))
				{
					$to_styles[$style["id"]] = $style["title"];
				}
				
				$this->tpl->setVariable("TXT_NR_LEARNING_MODULES", $nr_lm);
				$this->tpl->setVariable("TXT_DESC", ilObject::_lookupDescription($style["id"]));
			}
			else
			{
				$this->tpl->setVariable("TXT_NR_LEARNING_MODULES", $style["nr"]);
			}
 
			$this->tpl->setVariable("ROWCOL", $css_row);
			
			// purpose and scope
			if ($style["id"] > 0)
			{
				if ($style["id"] == $fixed_style)
				{
					$this->tpl->setVariable("TXT_PURPOSE", $this->lng->txt("global_fixed"));
				}
				if ($style["id"] == $default_style)
				{
					$this->tpl->setVariable("TXT_PURPOSE", $this->lng->txt("global_default"));
				}
				if ($style["category"] > 0)
				{
					$this->tpl->setVariable("TXT_SCOPE",
						ilObject::_lookupTitle(
						ilObject::_lookupObjId($style["category"])
						));
				}
			}
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("tbl_content");
			$this->tpl->parseCurrentBlock();

		} //if is_array

		if (count($style_entries) == 0)
		{
            $tbl->disable("header");
			$tbl->disable("footer");
			
			$this->tpl->setCurrentBlock("text");
			$this->tpl->setVariable("TXT_CONTENT", $this->lng->txt("obj_not_found"));
			$this->tpl->parseCurrentBlock();
			
			$this->tpl->setCurrentBlock("tbl_content");
			$this->tpl->parseCurrentBlock();
		}

		// render table
		$tbl->render();
		
		
		// move form
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TXT_MOVE_LM_STYLE", $this->lng->txt("sty_move_lm_styles"));
		$this->tpl->setVariable("TXT_FROM", $this->lng->txt("from"));
		$this->tpl->setVariable("TXT_TO", $this->lng->txt("to"));
		$this->tpl->setVariable("TXT_MOVE_LM_STYLE", $this->lng->txt("sty_move_lm_styles"));
		$this->tpl->setVariable("TXT_MOVE_STYLE", $this->lng->txt("sty_move_style"));
		$this->tpl->setVariable("SELECT_FROM",
			ilUtil::formSelect("", "from_style", $from_styles, false, true));
		$this->tpl->setVariable("SELECT_TO",
			ilUtil::formSelect("", "to_style", $to_styles, false, true));
		$this->tpl->setVariable("FORMACTION2", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
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
	* confirmation screen change (delete) individual styles
	*/
	function confirmDeleteIndividualStyles()
	{
		// load template content style settings
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.confirm.html");
		$this->tpl->setVariable("CMD_OK", "moveIndividualStyles");
		$this->tpl->setVariable("TXT_OK", $this->lng->txt("ok"));
		$this->tpl->setVariable("CMD_CANCEL", "editContentStyles");
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_CONFIRM", $this->lng->txt("sty_confirm_del_ind_styles"));
		$this->tpl->setVariable("TXT_CONTENT",
			sprintf($this->lng->txt("sty_confirm_del_ind_styles_desc"),
			ilObject::_lookupTitle($_POST["to_style"])));
		$this->ctrl->setParameter($this, "to_style", $_POST["to_style"]);
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormaction($this));
		$this->tpl->parseCurrentBlock();
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
		
		$this->tpl->addBlockfile("ADM_CONTENT", "style_settings", "tpl.stys_settings.html");
		$this->tpl->setCurrentBlock("style_settings");

		$settings = $this->ilias->getAllSettings();

		$this->tpl->setVariable("FORMACTION_STYLESETTINGS", $this->ctrl->getFormAction($this));		
		$this->tpl->setVariable("TXT_STYLE_SETTINGS", $this->lng->txt("system_style_settings"));
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
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
		global $styleDefinition;
		
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
//echo "redirect-".$this->ctrl->getLinkTarget($this,"editSystemStyles")."-";
		ilUtil::sendInfo($this->lng->txt("msg_obj_modified"), true);
		ilUtil::redirect($this->ctrl->getLinkTarget($this,"editSystemStyles"));
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
			$this->ctrl->getLinkTarget($this,"editContentStyles")));
	}
	
	
	/**
	* toggle global default style
	*
	* @access	public
 	*/
	function toggleGlobalDefaultObject()
	{
		global $ilias;
		
		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		if(count($_POST["id"]) > 1)
		{
			$this->ilias->raiseError($this->lng->txt("cont_select_max_one_item"),$this->ilias->error_obj->MESSAGE);
		}

		$ilias->deleteSetting("fixed_content_style_id");
		$def_style = $ilias->getSetting("default_content_style_id");
		
		if ($def_style != $_POST["id"][0])
		{
			$ilias->setSetting("default_content_style_id", $_POST["id"][0]);
		}
		else
		{
			$ilias->deleteSetting("default_content_style_id");
		}
		
		ilUtil::redirect($this->ctrl->getLinkTarget($this, "editContentStyles"));
	}

	/**
	* toggle global fixed style
	*
	* @access	public
 	*/
	function toggleGlobalFixedObject()
	{
		global $ilias;
		
		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		if(count($_POST["id"]) > 1)
		{
			$this->ilias->raiseError($this->lng->txt("cont_select_max_one_item"),$this->ilias->error_obj->MESSAGE);
		}

		$ilias->deleteSetting("default_content_style_id");
		$fixed_style = $ilias->getSetting("fixed_content_style_id");
		if ($fixed_style == $_POST["id"][0])
		{
			$ilias->deleteSetting("fixed_content_style_id");
		}
		else
		{
			$ilias->setSetting("fixed_content_style_id", $_POST["id"][0]);
		}
		ilUtil::redirect($this->ctrl->getLinkTarget($this, "editContentStyles"));
	}
	
	
	/**
	* save active styles
	*/
	function saveActiveStylesObject()
	{
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		$styles = $this->object->getStyles();
		foreach($styles as $style)
		{
			if ($_POST["std_".$style["id"]] == 1)
			{
				ilObjStyleSheet::_writeActive($style["id"], 1);
			}
			else
			{
				ilObjStyleSheet::_writeActive($style["id"], 0);
			}
		}
		ilUtil::redirect($this->ctrl->getLinkTarget($this, "editContentStyles"));
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
		//$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.confirm_deletion.html");
		//$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.explorer.html");
		
		include_once ("./Services/Style/classes/class.ilStyleScopeExplorer.php");
		$exp = new ilStyleScopeExplorer("repository.php?cmd=goto");
		$exp->setExpandTarget("repository.php?cmd=showTree");
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
		
		ilUtil::redirect($this->ctrl->getLinkTarget($this, "editContentStyles"));
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
		global $rbacsystem;

		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$tabs_gui->addTarget("basic_settings",
				$this->ctrl->getLinkTarget($this, "editBasicSettings"), array("editBasicSettings","", "view"), "", "");

			$tabs_gui->addTarget("system_styles",
				$this->ctrl->getLinkTarget($this, "editSystemStyles"), "editSystemStyles", "", "");
				
			$tabs_gui->addTarget("content_styles",
				$this->ctrl->getLinkTarget($this, "editContentStyles"), "editContentStyles", "", "");
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
	}
} // END class.ilObjStyleSettingsGUI
?>
