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
* Class ilObjStyleSheetGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @ilCtrl_Calls ilObjStyleSheetGUI:
*
* @extends ilObjectGUI
*/

require_once "class.ilObjectGUI.php";
require_once "class.ilObjStyleSheet.php";

class ilObjStyleSheetGUI extends ilObjectGUI
{
	var $cmd_update;
	var $cmd_new_par;
	var $cmd_refresh;
	var $cmd_delete;

	/**
	* Constructor
	* @access public
	*/
	function ilObjStyleSheetGUI($a_data,$a_id,$a_call_by_reference, $a_prep = true)
	{
		global $ilCtrl, $lng, $tpl;

		$this->ctrl =& $ilCtrl;
		$this->lng =& $lng;
		$this->lng->loadLanguageModule("style");

		$this->type = "sty";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference, false);
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd("edit");

		$this->prepareOutput();
		switch($next_class)
		{
			default:
				$cmd.= "Object";
				$ret =& $this->$cmd();
				break;
		}

		return $ret;
	}
	
	function viewObject()
	{
		$this->editObject();
	}

	/**
	* create
	*/
	function createObject()
	{
		global $rbacsystem, $lng, $tpl;

		//$this->setTabs();
		

		$this->lng =& $lng;
		//$this->ctrl->setParameter($this,'new_type',$this->type);
		$this->getTemplateFile("create", "sty");

		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("sty_create_new_stylesheet"));

		$this->tpl->setVariable("TXT_STYLE_BY_IMPORT", $this->lng->txt("sty_import_stylesheet"));
		$this->tpl->setVariable("TXT_STYLE_BY_COPY", $this->lng->txt("sty_copy_other_stylesheet"));
		$this->tpl->setVariable("TXT_SELECT_FILE", $this->lng->txt("import_file"));
		$this->tpl->setVariable("TXT_SOURCE", $this->lng->txt("sty_source"));
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TXT_DESC", $this->lng->txt("description"));
		
		$this->ctrl->setParameter($this, "new_type", "sty");
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("TXT_IMPORT", $this->lng->txt("import"));
		$this->tpl->setVariable("TXT_COPY", $this->lng->txt("copy"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		
		// get all learning module styles
		$clonable_styles = ilObjStyleSheet::_getClonableContentStyles();
		$select = ilUtil::formSelect("", "source_style", $clonable_styles, false, true);
		$this->tpl->setVariable("SOURCE_SELECT", $select);

	}

	/**
	* edit style sheet
	*/
	function editObject()
	{
		global $rbacsystem, $lng;

		//$this->setTabs();

		// set style sheet
		$this->tpl->setCurrentBlock("ContentStyle");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			$this->object->getContentStylePath($this->object->getId()));
		$this->tpl->parseCurrentBlock();

		$this->getTemplateFile("edit", "sty");
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("edit_stylesheet"));
		
		// add button button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		// export button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK", $this->ctrl->getLinkTarget($this, "exportStyle"));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt("export"));
		$this->tpl->parseCurrentBlock();

		// output style parameters
		$avail_pars = $this->object->getAvailableParameters();
		$style = $this->object->getStyle();
		foreach($style as $tag)
		{
			foreach($tag as $par)
			{
				$this->tpl->setCurrentBlock("StyleParameter");
				$this->tpl->setVariable("PAR_ID", $par["id"]);
				$var = str_replace("-", "_", $par["parameter"]);
				
				// replace _bottom, _top, _left, _right
				$add = "";
				$location = array("bottom", "top", "left", "right");
				foreach ($location as $loc)
				{
					if (is_int(strpos($var, "_".$loc)))
					{
						$var = str_replace("_".$loc, "", $var);
						$add = ", ".$this->lng->txt("sty_".$loc); 
					}
				}
				$this->tpl->setVariable("TXT_PAR",
					$this->lng->txt("sty_".$var).$add);

				if (count($avail_pars[$par["parameter"]]) == 0)
				{
					$input = "<input type=\"text\" size=\"30\" maxlength=\"100\" ".
						"name=\"styval[".$par["id"]."]\" value=\"".$par["value"]."\"";
				}
				else
				{
					$sel_avail_vals = array();
					foreach($avail_pars[$par["parameter"]] as $key => $val)
					{
						$sel_avail_vals[$val] = $val;
					}
					$input = ilUtil::formSelect($par["value"], "styval[".$par["id"]."]", $sel_avail_vals, false, true);
				}
				$this->tpl->setVariable("INPUT_VAL", $input);
				$this->tpl->parseCurrentBlock();
			}
			if ((!is_int(strpos($tag[0]["class"], ":hover"))) &&
				(!is_int(strpos($tag[0]["class"], ":visited"))) &&
				(!is_int(strpos($tag[0]["class"], ":active")))
				)
			{
				$this->tpl->setCurrentBlock("Example_".$tag[0]["tag"]);
				$this->tpl->setVariable("EX_CLASS", "ilc_".$tag[0]["class"]);
				$this->tpl->setVariable("EX_TEXT", "ABC abc 123");
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("StyleTag");
			$tag_str = $tag[0]["tag"].".".$tag[0]["class"];
			$this->tpl->setVariable("TXT_TAG", $tag_str);
			$this->tpl->setVariable("TXT_EDIT", $this->lng->txt("edit"));
			$this->ctrl->setParameter($this, "tag", $tag_str);
			$this->tpl->setVariable("LINK_EDIT_TAG_STYLE",
				$this->ctrl->getLinkTarget($this, "editTagStyle"));
			$this->tpl->setVariable("STY_ROWSPAN", (count($tag) + 1));
			$this->tpl->setVariable("TXT_PARAMETER", $this->lng->txt("parameter"));
			$this->tpl->setVariable("TXT_VALUE", $this->lng->txt("value"));
			$this->tpl->parseCurrentBlock();
		}

		// title and description
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable(strtoupper("TITLE"), $this->object->getTitle());
		$this->tpl->setVariable("TXT_DESC", $this->lng->txt("description"));
		$this->tpl->setVariable(strtoupper("DESCRIPTION"), $this->object->getDescription());
		$this->tpl->parseCurrentBlock();

		// new parameter
		$temptags = $this->object->getAvailableTags();
		$tags = array();
		foreach($temptags as $key => $val)
		{
			$tags[$val] = $val;
		}
		$tag_select = ilUtil::formSelect("", "tag", $tags, false, true);
		foreach($avail_pars as $key => $val)
		{
			$sel_avail_pars[$key] = $key;
		}
		$this->tpl->setVariable("SELECT_TAG", $tag_select);
		$par_select = ilUtil::formSelect("", "parameter", $sel_avail_pars, false, true);
		$this->tpl->setVariable("SELECT_PAR", $par_select);
		$this->tpl->setVariable("TXT_NEW_PAR", $this->lng->txt("add"));

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save_return"));
		$this->tpl->setVariable("BTN_SAVE", "update");
		$this->tpl->setVariable("TXT_REFRESH", $this->lng->txt("save_refresh"));
		$this->tpl->setVariable("BTN_REFRESH", "refresh");
		$this->tpl->setVariable("TXT_DELETE", $this->lng->txt("delete_selected"));
		$this->tpl->setVariable("BTN_DELETE", "deleteStyleParameter");
		$this->tpl->setVariable("BTN_NEW_PAR", "newStyleParameter");
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
	}

	/**
	* edit style of single tag
	*/
	function editTagStyleObject()
	{
		global $rbacsystem, $lng;

		//$this->setTabs();

		// set style sheet
		$this->tpl->setCurrentBlock("ContentStyle");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			$this->object->getContentStylePath($this->object->getId()));
		$this->tpl->parseCurrentBlock();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content",
			"tpl.sty_tag_edit.html", false, false);
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("edit_stylesheet"));

		// output style parameters
		$avail_pars = $this->object->getAvailableParameters();
		$style = $this->object->getStyle();
		$this->tpl->setVariable("TXT_TEXT", $this->lng->txt("sty_text"));
		$this->tpl->setVariable("TXT_MARGIN_AND_PADDING", $this->lng->txt("sty_margin_and_padding"));
		$this->tpl->setVariable("TXT_ALL", $this->lng->txt("sty_all"));
		$this->tpl->setVariable("TXT_TOP", $this->lng->txt("sty_top"));
		$this->tpl->setVariable("TXT_BOTTOM", $this->lng->txt("sty_bottom"));
		$this->tpl->setVariable("TXT_LEFT", $this->lng->txt("sty_left"));
		$this->tpl->setVariable("TXT_RIGHT", $this->lng->txt("sty_right"));
		$this->tpl->setVariable("TXT_BORDER", $this->lng->txt("sty_border"));
		$this->tpl->setVariable("TXT_BACKGROUND", $this->lng->txt("sty_background"));
		$this->tpl->setVariable("TXT_SPECIAL", $this->lng->txt("sty_special"));
		
		$cur = explode(".",$_GET["tag"]);
		$cur_tag = $cur[0];
		$cur_class = $cur[1];
		$parameters = $this->extractParametersOfTag($cur_tag, $cur_class, $style);
		
		$this->tpl->setCurrentBlock("Example_".$cur_tag);
		$this->tpl->setVariable("EX_CLASS", "ilc_".$cur_class);
		$this->tpl->setVariable("EX_TEXT", "ABC abc 123");
		$this->tpl->parseCurrentBlock();

		// for all tag parameters
		foreach ($avail_pars as $par => $vals)
		{
			$var = str_replace("-", "_", $par);
			$up_par = strtoupper($var);
			$this->tpl->setVariable("TXT_".$up_par, $this->lng->txt("sty_".$var));
			
			// output select lists
			if (count($avail_pars[$par]) > 0)
			{
				$sel_avail_vals = array("" => "");
				foreach($avail_pars[$par] as $key => $val)
				{
					$sel_avail_vals[$val] = $val;
				}
				$sel_str = ilUtil::formSelect($parameters[$par], $var, $sel_avail_vals, false, true);
				$this->tpl->setVariable("SEL_".$up_par, $sel_str);
			}
			else
			{
				$this->tpl->setVariable("VAL_".$up_par, $parameters[$par]);
			}
		}
		
		/*
		foreach($style as $tag)
		{
			foreach($tag as $par)
			{
				$this->tpl->setCurrentBlock("StyleParameter");
				$this->tpl->setVariable("PAR_ID", $par["id"]);
				$this->tpl->setVariable("TXT_PAR", $par["parameter"]);
				if (count($avail_pars[$par["parameter"]]) == 0)
				{
					$input = "<input type=\"text\" size=\"30\" maxlength=\"100\" ".
						"name=\"styval[".$par["id"]."]\" value=\"".$par["value"]."\"";
				}
				else
				{
					$sel_avail_vals = array();
					foreach($avail_pars[$par["parameter"]] as $key => $val)
					{
						$sel_avail_vals[$val] = $val;
					}
					$input = ilUtil::formSelect($par["value"], "styval[".$par["id"]."]", $sel_avail_vals, false, true);
				}
				$this->tpl->setVariable("INPUT_VAL", $input);
				$this->tpl->parseCurrentBlock();
			}
			if ((!is_int(strpos($tag[0]["class"], ":hover"))) &&
				(!is_int(strpos($tag[0]["class"], ":visited"))) &&
				(!is_int(strpos($tag[0]["class"], ":active")))
				)
			{
				$this->tpl->setCurrentBlock("Example_".$tag[0]["tag"]);
				$this->tpl->setVariable("EX_CLASS", "ilc_".$tag[0]["class"]);
				$this->tpl->setVariable("EX_TEXT", "ABC abc 123");
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("StyleTag");
			$tag_str = $tag[0]["tag"].".".$tag[0]["class"];
			$this->tpl->setVariable("TXT_TAG", $tag_str);
			$this->ctrl->setParameter($this, "tag", $tag_str);
			$this->tpl->setVariable("LINK_EDIT_TAG_STYLE",
				$this->ctrl->getLinkTarget($this, "editTagStyle"));
			$this->tpl->setVariable("STY_ROWSPAN", (count($tag) + 1));
			$this->tpl->setVariable("TXT_PARAMETER", $this->lng->txt("parameter"));
			$this->tpl->setVariable("TXT_VALUE", $this->lng->txt("value"));
			$this->tpl->parseCurrentBlock();
		}

		// title and description
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable(strtoupper("TITLE"), $this->object->getTitle());
		$this->tpl->setVariable("TXT_DESC", $this->lng->txt("description"));
		$this->tpl->setVariable(strtoupper("DESCRIPTION"), $this->object->getDescription());
		$this->tpl->parseCurrentBlock();

		// new parameter
		$temptags = $this->object->getAvailableTags();
		$tags = array();
		foreach($temptags as $key => $val)
		{
			$tags[$val] = $val;
		}
		$tag_select = ilUtil::formSelect("", "tag", $tags, false, true);
		foreach($avail_pars as $key => $val)
		{
			$sel_avail_pars[$key] = $key;
		}
		$this->tpl->setVariable("SELECT_TAG", $tag_select);
		$par_select = ilUtil::formSelect("", "parameter", $sel_avail_pars, false, true);
		$this->tpl->setVariable("SELECT_PAR", $par_select);
		$this->tpl->setVariable("TXT_NEW_PAR", $this->lng->txt("add"));
		*/

		$this->ctrl->setParameter($this, "tag", $_GET["tag"]);
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save_return"));
		$this->tpl->setVariable("BTN_SAVE", "updateTagStyle");
		$this->tpl->setVariable("TXT_REFRESH", $this->lng->txt("save_refresh"));
		$this->tpl->setVariable("BTN_REFRESH", "refreshTagStyle");
	}
	
	/**
	* save and refresh tag editing
	*/
	function refreshTagStyleObject()
	{
		$avail_pars = $this->object->getAvailableParameters();
		$cur = explode(".",$_GET["tag"]);
		$cur_tag = $cur[0];
		$cur_class = $cur[1];
		foreach ($avail_pars as $par => $vals)
		{
			$var = str_replace("-", "_", $par);
			if ($_POST[$var] != "")
			{
				$this->object->replaceStylePar($cur_tag, $cur_class, $par, $_POST[$var]);
			}
			else
			{
				$this->object->deleteStylePar($cur_tag, $cur_class, $par);
			}

			//$this->object->updateStyleParameter($id, $value);
		}
		$this->object->update();
		$this->editTagStyleObject();
	}

	/**
	* save and refresh tag editing
	*/
	function updateTagStyleObject()
	{
		$avail_pars = $this->object->getAvailableParameters();
		$cur = explode(".", $_GET["tag"]);
		$cur_tag = $cur[0];
		$cur_class = $cur[1];
		foreach ($avail_pars as $par => $vals)
		{
			$var = str_replace("-", "_", $par);
			if ($_POST[$var] != "")
			{
				$this->object->replaceStylePar($cur_tag, $cur_class, $par, $_POST[$var]);
			}
			else
			{
				$this->object->deleteStylePar($cur_tag, $cur_class, $par);
			}

			//$this->object->updateStyleParameter($id, $value);
		}
		$this->object->update();
		$this->editObject();
	}

	/**
	* export style
	*/
	function exportStyleObject()
	{
		ilUtil::deliverData($this->object->getXML(), "style_".$this->object->getId().".xml");
	}

	function extractParametersOfTag($a_tag, $a_class, $a_style)
	{
		$parameters = array();
		foreach($a_style as $tag)
		{
			foreach($tag as $par)
			{
				if ($par["tag"] == $a_tag && $par["class"] == $a_class)
				{
					$parameters[$par["parameter"]] = $par["value"]; 
				}
			}
		}
		return $parameters;
	}
	
	/**
	* add style parameter
	*/
	function newStyleParameterObject()
	{
		$this->object->addParameter($_POST["tag"], $_POST["parameter"]);
		$this->editObject();
	}

	/**
	* refresh style sheet
	*/
	function refreshObject()
	{
		//$class_name = "ilObjStyleSheet";
		//require_once("classes/class.ilObjStyleSheet.php");
		$this->object->setTitle($_POST["style_title"]);
		$this->object->setDescription($_POST["style_description"]);

		foreach($_POST["styval"] as $id => $value)
		{
			$this->object->updateStyleParameter($id, $value);
		}
		$this->object->update();
		$this->editObject();
	}
	
	/**
	* display deletion confirmation screen
	*
	* @access	public
 	*/
	function deleteObject($a_error = false)
	{
		//$this->setTabs();

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

		$this->tpl->setCurrentBlock("table_row");
		$this->tpl->setVariable("IMG_OBJ",ilUtil::getImagePath("icon_styf.gif"));
		$this->tpl->setVariable("CSS_ROW",ilUtil::switchColor(++$counter,"tblrow1","tblrow2"));
		$this->tpl->setVariable("TEXT_CONTENT",ilObject::_lookupTitle($this->object->getId()));
		$this->tpl->parseCurrentBlock();
		
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
	* cancel oobject deletion
	*/
	function cancelDeleteObject()
	{
		$this->ctrl->returnToParent($this);
	}

	/**
	* delete selected style objects
	*/
	function confirmedDeleteObject()
	{
		global $ilias;
		
		$this->object->delete();
		
		$this->ctrl->returnToParent($this);
	}

	/**
	* delete style parameters
	*/
	function deleteStyleParameterObject()
	{
		if (is_array($_POST["sty_select"]))
		{
			foreach($_POST["sty_select"] as $id => $dummy)
			{
				$this->object->deleteParameter($id);
			}
		}
		$this->object->read();
		$this->object->writeCSSFile();
		$this->editObject();
	}

	/**
	* save style sheet
	*/
	function saveObject()
	{
//echo "HH"; exit;
		$class_name = "ilObjStyleSheet";
		require_once("classes/class.ilObjStyleSheet.php");
		$newObj = new ilObjStyleSheet();
		$newObj->setTitle($_POST["style_title"]);
		$newObj->setDescription($_POST["style_description"]);
		$newObj->create();

		// assign style to style sheet folder,
		// if parent is style sheet folder
		if ($_GET["ref_id"] > 0)
		{

			$fold =& ilObjectFactory::getInstanceByRefId($_GET["ref_id"]);
			if ($fold->getType() == "stys")
			{
				$fold->addStyle($newObj->getId());
				$fold->update();
				ilObjStyleSheet::_writeStandard($newObj->getId(), "1");
				$this->ctrl->redirectByClass("ilobjstylesettingsgui", "editContentStyles");
			}
		}

		return $newObj->getId();
	}

	/**
	* update style sheet
	*/
	function updateObject()
	{
		//$class_name = "ilObjStyleSheet";
		//require_once("classes/class.ilObjStyleSheet.php");
		$this->object->setTitle($_POST["style_title"]);
		$this->object->setDescription($_POST["style_description"]);

		foreach($_POST["styval"] as $id => $value)
		{
			$this->object->updateStyleParameter($id, $value);
		}
		$this->object->update();
		
		if ($_GET["ref_id"] > 0)
		{

			$fold =& ilObjectFactory::getInstanceByRefId($_GET["ref_id"]);
			if ($fold->getType() == "stys")
			{
				$this->ctrl->redirectByClass("ilobjstylesettingsgui",
					"editContentStyles");
			}
		}

		$this->ctrl->returnToParent($this);
	}

	/**
	* save style sheet
	*/
	function copyStyleObject()
	{
		global $ilias;
		
		if ($_POST["source_style"] > 0)
		$style_obj =& $ilias->obj_factory->getInstanceByObjId($_POST["source_style"]);
		$new_id = $style_obj->ilClone();

		// assign style to style sheet folder,
		// if parent is style sheet folder
		if ($_GET["ref_id"] > 0)
		{

			$fold =& ilObjectFactory::getInstanceByRefId($_GET["ref_id"]);
			if ($fold->getType() == "stys")
			{
				$fold->addStyle($new_id);
				$fold->update();
				ilObjStyleSheet::_writeStandard($new_id, "1");
				$this->ctrl->redirectByClass("ilobjstylesettingsgui", "editContentStyles");
			}
		}

		return $new_id;
	}

	/**
	* import style sheet
	*/
	function importStyleObject()
	{
		// check file
		$source = $_FILES["stylefile"]["tmp_name"];
		if (($source == 'none') || (!$source))
		{
			$this->ilias->raiseError("No file selected!",$this->ilias->error_obj->MESSAGE);
		}
		
		// check correct file type
		$info = pathinfo($_FILES["stylefile"]["name"]);
		if (strtolower($info["extension"]) != "xml")
		{
			$this->ilias->raiseError("File must be a xml file!",$this->ilias->error_obj->MESSAGE);
		}

		$class_name = "ilObjStyleSheet";
		require_once("classes/class.ilObjStyleSheet.php");
		$newObj = new ilObjStyleSheet();
		//$newObj->setTitle();
		//$newObj->setDescription($_POST["style_description"]);
		$newObj->createFromXMLFile($_FILES["stylefile"]["tmp_name"]);

		// assign style to style sheet folder,
		// if parent is style sheet folder
		if ($_GET["ref_id"] > 0)
		{

			$fold =& ilObjectFactory::getInstanceByRefId($_GET["ref_id"]);
			if ($fold->getType() == "stys")
			{
				$fold->addStyle($newObj->getId());
				$fold->update();
				ilObjStyleSheet::_writeStandard($newObj->getId(), "1");
				$this->ctrl->redirectByClass("ilobjstylesettingsgui", "editContentStyles");
			}
		}

		return $newObj->getId();
	}

	/**
	* update style sheet
	*/
	function cancelObject()
	{
		global $lng;

		ilUtil::sendInfo($lng->txt("msg_cancel"), true);
		$this->ctrl->returnToParent($this);
	}
	
	/**
	* admin and normal tabs are equal for roles
	*/
	function getAdminTabs(&$tabs_gui)
	{
		$this->getTabs($tabs_gui);
	}

	/**
	* output tabs
	*/
	function setTabs()
	{
		global $lng;

		// catch feedback message
		#include_once("classes/class.ilTabsGUI.php");
		#$tabs_gui =& new ilTabsGUI();
		$this->getTabs($this->tabs_gui);
		#$this->tpl->setVariable("TABS", $tabs_gui->getHTML());

		if (strtolower(get_class($this->object)) == "ilobjstylesheet")
		{
			$this->tpl->setVariable("HEADER", $this->object->getTitle());
		}
		else
		{
			$this->tpl->setVariable("HEADER", $lng->txt("create_stylesheet"));
		}
	}

	/**
	* adds tabs to tab gui object
	*
	* @param	object		$tabs_gui		ilTabsGUI object
	*/
	function getTabs(&$tabs_gui)
	{		
		// back to upper context
		$tabs_gui->addTarget("cont_back",
			$this->ctrl->getParentReturn($this), "",
			"");
	}

	/**
	* should be overwritten to add object specific items
	* (repository items are preloaded)
	*/
	function addAdminLocatorItems()
	{
		global $ilLocator;

		if ($_GET["admin_mode"] == "settings")	// system settings
		{		
			$ilLocator->addItem($this->lng->txt("administration"),
				$this->ctrl->getLinkTargetByClass("iladministrationgui", "frameset"),
				ilFrameTargetInfo::_getFrame("MainContent"));
				
			$ilLocator->addItem(ilObject::_lookupTitle(
				ilObject::_lookupObjId($_GET["ref_id"])),
				$this->ctrl->getLinkTargetByClass("ilobjstylesettingsgui", "view"));

			if ($_GET["obj_id"] > 0)
			{
				$ilLocator->addItem($this->object->getTitle(),
					$this->ctrl->getLinkTarget($this, "edit"));
			}
		}
		else							// repository administration
		{
			//?
		}

	}
	
	function showUpperIcon()
	{
		global $tree, $tpl, $objDefinition;
		
		if (strtolower($_GET["baseClass"]) == "iladministrationgui")
		{
				$tpl->setUpperIcon(
					$this->ctrl->getLinkTargetByClass("ilobjstylesettingsgui",
						"editContentStyles"));
		}
		else
		{
			// ?
		}
	}

} // END class.ObjStyleSheetGUI
?>
