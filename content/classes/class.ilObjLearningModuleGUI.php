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

require_once("content/classes/class.ilObjLearningModule.php");
require_once("content/classes/class.ilObjContentObjectGUI.php");

/**
* Class ilLearningModuleGUI
*
* GUI class for ilLearningModule
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilObjLearningModuleGUI extends ilObjContentObjectGUI
{
	var $lm_obj;
	var $lm_tree;

	/**
	* Constructor
	* @access	public
	*/
	function ilObjLearningModuleGUI($a_data,$a_id = 0,$a_call_by_reference = true, $a_prepare_output = true)
	{
		$this->type = "lm";
		parent::ilObjContentObjectGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
		if($a_id != 0)
		{
			$this->lm_tree =& $this->object->getLMTree();
		}
		/*
		global $ilias, $tpl, $lng, $objDefinition;

		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->objDefinition =& $objDefinition;
		$this->lm_tree =& $a_tree;*/

		//$this->read(); todo
	}

	/**
	*
	*/
	function assignObject()
	{
		$this->link_params = "ref_id=".$this->ref_id;
		$this->object =& new ilObjLearningModule($this->id, true);
	}

	/*
	function setLearningModuleObject(&$a_lm_obj)
	{
		$this->lm_obj =& $a_lm_obj;
		//$this->obj =& $this->lm_obj;
	}*/

	function properties()
	{
		//add template for view button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		// view button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK","lm_presentation.php?ref_id=".$this->object->getRefID());
		$this->tpl->setVariable("BTN_TARGET"," target=\"_top\" ");
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt("view"));
		$this->tpl->parseCurrentBlock();

		// test purpose: create stylesheet
		if ($this->object->getStyleSheetId() == 0)
		{
			$this->tpl->setCurrentBlock("btn_cell");
			$this->tpl->setVariable("BTN_LINK","lm_edit.php?cmd=createStyle&ref_id=".$this->object->getRefID());
			//$this->tpl->setVariable("BTN_TARGET"," target=\"_top\" ");
			$this->tpl->setVariable("BTN_TXT",$this->lng->txt("create_stylesheet"));
			$this->tpl->parseCurrentBlock();
		}
		else // test purpose: edit stylesheet
		{
			$this->tpl->setCurrentBlock("btn_cell");
			$this->tpl->setVariable("BTN_LINK","lm_edit.php?cmd=editStyle&ref_id=".$this->object->getRefID());
			//$this->tpl->setVariable("BTN_TARGET"," target=\"_top\" ");
			$this->tpl->setVariable("BTN_TXT",$this->lng->txt("edit_stylesheet"));
			$this->tpl->parseCurrentBlock();
		}

		// lm properties
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.lm_properties.html", true);
		$this->tpl->setVariable("FORMACTION", "lm_edit.php?ref_id=".
			$this->object->getRefId()."&cmd=post");
		$this->tpl->setVariable("TXT_PROPERTIES", $this->lng->txt("cont_lm_properties"));

		$this->tpl->setVariable("TXT_LAYOUT", $this->lng->txt("cont_def_layout"));
		$layouts = ilObjLearningModule::getAvailableLayouts();
		$select_layout = ilUtil::formSelect ($this->object->getLayout(), "lm_layout",
			$layouts, false, true);
		$this->tpl->setVariable("SELECT_LAYOUT", $select_layout);

		$this->tpl->setVariable("TXT_PAGE_HEADER", $this->lng->txt("cont_page_header"));
		$pg_header = array ("st_title" => $this->lng->txt("cont_st_title"),
			"pg_title" => $this->lng->txt("cont_pg_title"),
			"none" => $this->lng->txt("cont_none"));
		$select_pg_head = ilUtil::formSelect ($this->object->getPageHeader(), "lm_pg_header",
			$pg_header, false, true);
		$this->tpl->setVariable("SELECT_PAGE_HEADER", $select_pg_head);

		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_NAME", "saveProperties");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->parseCurrentBlock();
	}

	function saveProperties()
	{
		$this->object->setLayout($_POST["lm_layout"]);
		$this->object->setPageHeader($_POST["lm_pg_header"]);
		$this->object->updateProperties();
		sendInfo($this->lng->txt("msg_obj_modified"));
		$this->view();
	}

	function createStyle()
	{
		require_once ("classes/class.ilObjStyleSheetGUI.php");
		$style_gui =& new ilObjStyleSheetGUI("", $this->object->getRefId(), true);
		$style_gui->setFormAction("save", "lm_edit.php?ref_id=".
			$this->object->getRefId()."&cmd=saveStyle");
		$style_gui->createObject();

	}

	function saveStyle()
	{
		require_once ("classes/class.ilObjStyleSheetGUI.php");
		$style_gui =& new ilObjStyleSheetGUI("", $this->object->getRefId(), true);
		$style_gui->setReturnLocation("save", "return");
		$style_id = $style_gui->saveObject();
		$this->object->setStyleSheetId($style_id);
		$this->object->update();

		header("Location: lm_edit.php?ref_id=".$this->object->getRefId()."&cmd=view");
		exit;
	}

	function editStyle()
	{
		require_once ("classes/class.ilObjStyleSheetGUI.php");
		$style_gui =& new ilObjStyleSheetGUI("", $this->object->getStyleSheetId(), false);
		$style_gui->setCmdUpdate("updateStyle");
		$style_gui->setCmdRefresh("refreshStyle");
		$style_gui->setFormAction("update", "lm_edit.php?ref_id=".
			$this->object->getRefId()."&cmd=post");
		$style_gui->editObject();
	}

	function updateStyle()
	{
		require_once ("classes/class.ilObjStyleSheetGUI.php");
		$style_gui =& new ilObjStyleSheetGUI("", $this->object->getStyleSheetId(), false);
		$style_gui->setReturnLocation("update", "lm_edit.php?ref_id=".$this->object->getRefId()."&cmd=view");
		$style_id = $style_gui->updateObject();
	}

	function newStyleParameter()
	{
		require_once ("classes/class.ilObjStyleSheetGUI.php");
		$style_gui =& new ilObjStyleSheetGUI("", $this->object->getStyleSheetId(), false);
		$style_gui->setCmdUpdate("updateStyle");
		$style_gui->setCmdRefresh("refreshStyle");
		$style_gui->setFormAction("update", "lm_edit.php?ref_id=".
			$this->object->getRefId()."&cmd=post");
		$style_id = $style_gui->newStyleParameterObject();
	}

	function refreshStyle()
	{
		require_once ("classes/class.ilObjStyleSheetGUI.php");
		$style_gui =& new ilObjStyleSheetGUI("", $this->object->getStyleSheetId(), false);
		$style_gui->setCmdUpdate("updateStyle");
		$style_gui->setCmdRefresh("refreshStyle");
		$style_gui->setFormAction("update", "lm_edit.php?ref_id=".
			$this->object->getRefId()."&cmd=post");
		$style_id = $style_gui->refreshObject();
	}

	function deleteStyleParameter()
	{
		require_once ("classes/class.ilObjStyleSheetGUI.php");
		$style_gui =& new ilObjStyleSheetGUI("", $this->object->getStyleSheetId(), false);
		$style_gui->setCmdUpdate("updateStyle");
		$style_gui->setCmdRefresh("refreshStyle");
		$style_gui->setFormAction("update", "lm_edit.php?ref_id=".
			$this->object->getRefId()."&cmd=post");
		$style_id = $style_gui->deleteStyleParameterObject();
	}
    function setilLMMenu()
	{
		return "";
	}
}

?>
