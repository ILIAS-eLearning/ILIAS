<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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
* @extends ilObjectGUI
* @package ilias-core
*/

include_once "class.ilObjectGUI.php";

class ilObjStyleSettingsGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjStyleSettingsGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output = true)
	{
		$this->type = "stys";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);
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
		sendInfo($this->lng->txt("object_added"),true);
		
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
		$this->tpl->setVariable("TXT_STYLE_SETTINGS", $this->lng->txt("basic_settings"));
		$this->tpl->setVariable("TXT_ENABLE_CUSTOM_ICONS", $this->lng->txt("enable_custom_icons"));
		$this->tpl->setVariable("TXT_ENABLE_CUSTOM_ICONS_INFO", $this->lng->txt("enable_custom_icons_info"));
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		$this->tpl->parseCurrentBlock();
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
		
		include_once "./classes/class.ilTableGUI.php";

		// load template for table
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");
		
		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.styf_row.html");

		$num = 0;

		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id."$obj_str&cmd=gateway");

		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->lng->txt("content_styles"),"icon_styf.gif",
			$this->lng->txt("content_styles"));

		//$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));

		// title
		$header_names = array("", $this->lng->txt("title"),
			$this->lng->txt("purpose"));
		$tbl->setHeaderNames($header_names);

		$header_params = array("ref_id" => $this->ref_id);
		$tbl->setHeaderVars(array("", "title", "purpose"), $header_params);
		$tbl->setColumnWidth(array("0%", "80%", "20%"));

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		
		// get style ids
		$style_entries = array();
		$styles = $this->object->getStyles();
		foreach($styles as $style)
		{
			$style_entries[$style["title"].":".$style["id"]]
				= $style;
		}
		ksort($style_entries);
		
		// todo
		$tbl->setMaxCount(count($style_entries));

		$this->tpl->setVariable("COLUMN_COUNTS", 3);

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));

		$this->showActions(true);

		include_once ("classes/class.ilObjStyleSheet.php");
		
		$fixed_style = $ilias->getSetting("fixed_content_style_id");
		$default_style = $ilias->getSetting("default_content_style_id");

		foreach ($style_entries as $style)
		{
			$this->tpl->setCurrentBlock("style_row");
		
			// color changing
			$css_row = ($css_row == "tblrow2")
				? "tblrow1"
				: "tblrow2";

			$this->tpl->setVariable("CHECKBOX_ID", $style["id"]);
			$this->tpl->setVariable("TXT_TITLE", $style["title"]);
			$this->tpl->setVariable("TXT_DESC", ilObject::_lookupDescription($style["id"]));
			$this->tpl->setVariable("LINK_STYLE",
				"adm_object.php?ref_id=".$_GET["ref_id"].
				"&obj_id=".$style["id"]);
			$this->tpl->setVariable("ROWCOL", $css_row);
			if ($style["id"] == $fixed_style)
			{
				$this->tpl->setVariable("TXT_PURPOSE", $this->lng->txt("global_fixed"));
			}
			if ($style["id"] == $default_style)
			{
				$this->tpl->setVariable("TXT_PURPOSE", $this->lng->txt("global_default"));
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
				
				// activation list
				$this->tpl->setCurrentBlock("style_activation");
				$this->tpl->setVariable("TXT_SKIN_STYLE_TITLE", 
					$styleDef->getTemplateName()." / ".$style["name"]);
				$this->tpl->setVariable("VAL_SKIN_STYLE", $template["id"].":".$style["id"]);
				$num_users = ilObjUser::_getNumberOfUsersForStyle($template["id"], $style["id"]);
				$this->tpl->setVariable("VAL_NUM_USERS", $num_users);
				if (ilObjStyleSettings::_lookupActivatedStyle($template["id"], $style["id"]))
				{
					$this->tpl->setVariable("CHK_SKIN_STYLE", " checked=\"1\" ");
				}
				$this->tpl->parseCurrentBlock();
			}
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
			}
		}
		
		// move users to other skin
		foreach($_POST["move_users"] as $key => $value)
		{
			if ($value != "")
			{
				$from = explode(":", $key);
				$to = explode(":", $value);
				ilObjUser::_moveUsersToStyle($from[0],$from[1],$to[0],$to[1]);
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
		sendInfo($this->lng->txt("msg_obj_modified"), true);
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
			sendInfo($this->lng->txt("info_delete_sure"));
		}

		$this->tpl->setVariable("FORMACTION", $this->getFormAction("delete",
			"adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=gateway"));

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
			$this->tpl->setVariable("IMG_OBJ",ilUtil::getImagePath("icon_styf.gif"));
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

		sendInfo($this->lng->txt("msg_cancel"),true);
		ilUtil::redirect($this->getReturnLocation("cancelDelete",
			"adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=editContentStyles"));

	}

	
	function setTabs()
	{
		echo "settings_setTabs";
	}
	
	/**
	* get tabs
	* @access	public
	* @param	object	tabs gui object
	*/
	function getTabs(&$tabs_gui)
	{
		// tabs are defined manually here. The autogeneration via objects.xml will be deprecated in future
		// for usage examples see ilObjGroupGUI or ilObjSystemFolderGUI
	}
} // END class.ilObjStyleSettings
?>
