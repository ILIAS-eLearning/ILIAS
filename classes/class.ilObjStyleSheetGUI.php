<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";

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
	function ilObjStyleSheetGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "sty";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference, false);

		$this->cmd_update = "update";
		$this->cmd_new_par = "newStyleParameter";
		$this->cmd_refresh = "refresh";
		$this->cmd_delete = "deleteStyleParameter";
	}

	function setCmdUpdate($a_cmd = "update")
	{
		$this->cmd_update = $a_cmd;
	}

	function setCmdNewStyleParameter($a_cmd = "newStyleParameter")
	{
		$this->cmd_new_par = $a_cmd;
	}

	function setCmdRefresh($a_cmd = "refresh")
	{
		$this->cmd_refresh = $a_cmd;
	}

	function setCmdDeleteStyleParameter($a_cmd = "deleteStyleParameter")
	{
		$this->cmd_delete = $a_cmd;
	}

	/**
	*
	*/
	function createObject()
	{
		global $rbacsystem, $lng;

		$this->getTemplateFile("create", "sty");
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("create_stylesheet"));
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TXT_DESC", $this->lng->txt("description"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setVariable("FORMACTION", $this->getFormAction("save",
			"adm_object.php?ref_id=".$this->ref_id."$obj_str&cmd=save"));
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));

	}

	/**
	*
	*/
	function editObject()
	{
		global $rbacsystem, $lng;

		// set style sheet
		$this->tpl->setCurrentBlock("ContentStyle");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			$this->object->getContentStylePath($this->object->getId()));
		$this->tpl->parseCurrentBlock();

		$this->getTemplateFile("edit", "sty");
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("edit_stylesheet"));

		// output style parameters
		$avail_pars = $this->object->getAvailableParameters();
		$style = $this->object->getStyle();
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
			if (!is_int(strpos($tag[0]["class"], ":hover")))
			{
				$this->tpl->setCurrentBlock("Example_".$tag[0]["tag"]);
				$this->tpl->setVariable("EX_CLASS", "ilc_".$tag[0]["class"]);
				$this->tpl->setVariable("EX_TEXT", "ABC abc 123");
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("StyleTag");
			$this->tpl->setVariable("TXT_TAG", $tag[0]["tag"].".".$tag[0]["class"]);
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

		$this->tpl->setVariable("FORMACTION", $this->getFormAction("update",
			"adm_object.php?ref_id=".$this->ref_id."$obj_str&cmd=post"));
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save_return"));
		$this->tpl->setVariable("BTN_SAVE", $this->cmd_update);
		$this->tpl->setVariable("TXT_REFRESH", $this->lng->txt("save_refresh"));
		$this->tpl->setVariable("BTN_REFRESH", $this->cmd_refresh);
		$this->tpl->setVariable("TXT_DELETE", $this->lng->txt("delete_selected"));
		$this->tpl->setVariable("BTN_DELETE", $this->cmd_delete);
		$this->tpl->setVariable("BTN_NEW_PAR", $this->cmd_new_par);
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
	}

	function newStyleParameterObject()
	{
		$this->object->addParameter($_POST["tag"], $_POST["parameter"]);
		$this->editObject();
	}

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

	function saveObject()
	{

		$class_name = "ilObjStyleSheet";
		require_once("classes/class.ilObjStyleSheet.php");
		$newObj = new ilObjStyleSheet();
		$newObj->setTitle($_POST["style_title"]);
		$newObj->setDescription($_POST["style_description"]);
		$newObj->create();

		$location = $this->getReturnLocation("save","adm_object.php?".$this->link_params);

		// "return" path is needed in content/classes/class.ilLearningModuleGUI
		// i don't know if this is a good solution, but it works
		if ($location == "return")
		{
			return $newObj->getId();
		}
		else
		{
			header("Location:".$location);
			exit();
		}
	}

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

		$location = $this->getReturnLocation("update","adm_object.php?".$this->link_params);

		header("Location:".$location);
		exit();
	}

} // END class.ObjStyleSheetGUI
?>
