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
* Class ilObjStyleSheetFolderGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* 
* @extends ilObjectGUI
*/


//
//
// THIS CLASS IS CURRENTLY NOT IN USE
//
//
include_once "./classes/class.ilObjectGUI.php";

class ilObjStyleSheetFolderGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjStyleSheetFolderGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output = true)
	{
		$this->type = "styf";
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
		ilUtil::sendInfo($this->lng->txt("object_added"),true);
		
		ilUtil::redirect($this->getReturnLocation("save",$this->ctrl->getLinkTarget($this,"")));
	}
	
	/**
	* view list of styles
	*/
	function viewObject()
	{
		global $rbacsystem, $ilias;
		
		if (!$rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		
		include_once "./Services/Table/classes/class.ilTableGUI.php";

		// load template for table
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");
		
		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.styf_row.html");

		$num = 0;

//		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id."$obj_str&cmd=gateway");

		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->object->getTitle(),"icon_styf.gif",
			$this->lng->txt("obj_".$this->object->getType()));

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

		include_once ("./Services/Style/classes/class.ilObjStyleSheet.php");
		
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
//			$this->tpl->setVariable("LINK_STYLE",
//				"adm_object.php?ref_id=".$_GET["ref_id"].
//				"&obj_id=".$style["id"]);
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

//		$this->tpl->setVariable("FORMACTION", $this->getFormAction("delete",
//			"adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=gateway"));

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
		
		ilUtil::redirect($this->getReturnLocation("delete",$this->ctrl->getLinkTarget($this,"")));
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
		
		ilUtil::redirect($this->ctrl->getLinkTarget($this,"view"));
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
		ilUtil::redirect($this->ctrl->getLinkTarget($this,"view"));
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
	* get tabs
	* @access	public
	* @param	object	tabs gui object
	*/
	function getTabs(&$tabs_gui)
	{
		// tabs are defined manually here. The autogeneration via objects.xml will be deprecated in future
		// for usage examples see ilObjGroupGUI or ilObjSystemFolderGUI
	}
} // END class.ilObjStyleSheetFolder
?>
