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

require_once("classes/class.ilObjectGUI.php");
require_once("content/classes/class.ilObjGlossary.php");
require_once("content/classes/class.ilGlossaryTermGUI.php");

/**
* Class ilGlossaryGUI
*
* GUI class for ilGlossary
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilObjGlossaryGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access	public
	*/
	function ilObjGlossaryGUI($a_data,$a_id = 0,$a_call_by_reference = true, $a_prepare_output = true)
	{
		$this->type = "glo";
		parent::ilObjectGUI($a_data, $a_id, $a_call_by_reference, false);
		if (defined("ILIAS_MODULE"))
		{
			$this->setTabTargetScript("glossary_edit.php");
		}
		if ($a_prepare_output)
		{
			$this->prepareOutput();
		}
	}

	/**
	* form for new content object creation
	*/
	function createObject()
	{
		include_once "classes/class.ilMetaDataGUI.php";
		$meta_gui =& new ilMetaDataGUI();
		//$meta_gui->setObject($this->object);

		$meta_gui->setTargetFrame("save",$this->getTargetFrame("save"));

		$new_type = $_POST["new_type"] ? $_POST["new_type"] : $_GET["new_type"];

		$meta_gui->edit("ADM_CONTENT", "adm_content",
			$this->getFormAction("save","adm_object.php?ref_id=".$_GET["ref_id"]."&new_type=".$new_type."&cmd=save"));
	}

	/**
	* save new content object to db
	*/
	function saveObject()
	{
		global $rbacadmin, $rbacsystem;

		// always call parent method first to create an object_data entry & a reference
		//$newObj = parent::saveObject();
		// TODO: fix MetaDataGUI implementation to make it compatible to use parent call
		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], $_GET["new_type"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_create_permission"), $this->ilias->error_obj->MESSAGE);
		}
		else
		{
			// create and insert object in objecttree
			include_once("content/classes/class.ilObjGlossary.php");
			$newObj = new ilObjGlossary();
			$newObj->setType($this->type);
			$newObj->setTitle("content object ".$newObj->getId());		// set by meta_gui->save
			$newObj->setDescription("");	// set by meta_gui->save
			$newObj->create();
			$newObj->createReference();
			$newObj->putInTree($_GET["ref_id"]);
			$newObj->setPermissions($_GET["ref_id"]);

			//$roles = $newObj->initDefaultRoles();

			// assign author role to creator of forum object
			//$rbacadmin->assignUser($roles[0], $newObj->getOwner(), "n");
			//ilObjUser::updateActiveRoles($newObj->getOwner());

			// save meta data
			include_once "classes/class.ilMetaDataGUI.php";
			$meta_gui =& new ilMetaDataGUI();
			$meta_gui->setObject($newObj);
			$meta_gui->save();

			// create content object tree
			//$newObj->createLMTree();

			unset($newObj);

			// always send a message
			sendInfo($this->lng->txt("glo_added"),true);
			header("Location:".$this->getReturnLocation("save","adm_object.php?".$this->link_params));
			exit();
		}
	}

	function editMetaObject()
	{
		include_once "classes/class.ilMetaDataGUI.php";
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_gui->edit("ADM_CONTENT", "adm_content",
			"adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=saveMeta");
	}

	function saveMetaObject()
	{
		include_once "classes/class.ilMetaDataGUI.php";
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_gui->save();
		header("Location: adm_object.php?ref_id=".$_GET["ref_id"]);
		exit;
	}



	function viewObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		// edit button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		if (!defined("ILIAS_MODULE"))
		{
			$this->tpl->setCurrentBlock("btn_cell");
			$this->tpl->setVariable("BTN_LINK","content/glossary_edit.php?ref_id=".$this->object->getRefID());
			$this->tpl->setVariable("BTN_TARGET"," target=\"bottom\" ");
			$this->tpl->setVariable("BTN_TXT",$this->lng->txt("edit"));
			$this->tpl->parseCurrentBlock();
		}

		parent::viewObject();
	}

	function executeCommand()
	{
		$cmd = $_GET["cmd"];
		if($cmd == "")
		{
			$cmd = "listTerms";
		}

		if ($cmd == "post")
		{
			$cmd = key($_POST["cmd"]);
		}

		$this->$cmd();
		$this->tpl->show();
	}

	function listTerms()
	{
		include_once "./classes/class.ilTableGUI.php";

		// load template for table
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");

		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.term_tbl_row.html", true);

		$num = 0;

		$obj_str = ($this->call_by_reference) ? "" : "&obj_id=".$this->obj_id;
		$this->tpl->setVariable("FORMACTION", "glossary_edit.php?ref_id=".$this->ref_id."$obj_str&cmd=post");

		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->lng->txt("cont_terms"));
		//$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));

		$tbl->setHeaderNames(array("", $this->lng->txt("cont_term"),
			 $this->lng->txt("language"), $this->lng->txt("last_change")));

		$cols = array("", "term", "language", "last_change", "id");
		$header_params = array("ref_id" => $this->ref_id);
		$tbl->setHeaderVars($cols, $header_params);
		$tbl->setColumnWidth(array("15","45%","30%","25%"));

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($this->maxcount);

		$this->tpl->setVariable("COLUMN_COUNTS", 4);
		$this->setActions(array("deleteTerm" => "delete"));
		$this->setSubObjects(array("term" => array()));
		$this->showActions(true);

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		//$tbl->disable("footer");

		$term_list = $this->object->getTermList();
		$tbl->setMaxCount(count($term_list));

		// sorting array
		include_once "./include/inc.sort.php";
		$term_list = sortArray($term_list, $_GET["sort_by"], $_GET["sort_order"]);
		$term_list = array_slice($term_list, $_GET["offset"], $_GET["limit"]);

		// render table
		$tbl->render();

		if (count($term_list) > 0)
		{
			$i=1;
			foreach($term_list as $key => $term)
			{
				$css_row = ilUtil::switchColor($i++,"tblrow1","tblrow2");
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->setVariable("TEXT_TERM", $term["term"]);
				$this->tpl->setVariable("TEXT_LANGUAGE", $term["language"]);
				$this->tpl->setVariable("TEXT_LASTCHANGE", $term["last_change"]);
				$this->tpl->setCurrentBlock("tbl_content");
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

	function editMeta()
	{
		include_once("classes/class.ilMetaDataGUI.php");
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_gui->edit("ADM_CONTENT", "adm_content", "glossary_edit.php?ref_id=".
			$this->object->getRefId()."&cmd=saveMeta");
	}

	function saveMeta()
	{
		include_once("classes/class.ilMetaDataGUI.php");
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_gui->save();
		header("location: glossary_edit.php?cmd=view&ref_id=".$this->object->getRefId());
	}

	function perm()
	{
		$this->setFormAction("addRole", "glossary_edit.php?ref_id=".$this->object->getRefId()."&cmd=addRole");
		$this->setFormAction("permSave", "glossary_edit.php?ref_id=".$this->object->getRefId()."&cmd=permSave");
		$this->permObject();
	}

	function permSave()
	{
		$this->setReturnLocation("permSave", "glossary_edit.php?ref_id=".$this->object->getRefId()."&cmd=perm");
		$this->permSaveObject();
	}

	function addRole()
	{
		$this->setReturnLocation("addRole", "glossary_edit.php?ref_id=".$this->object->getRefId()."&cmd=perm");
		$this->addRoleObject();
	}

	function owner()
	{
		$this->ownerObject();
	}

	function view()
	{
		$this->viewObject();
	}

	/**
	* create new (subobject) in glossary
	*/
	function create()
	{
		switch($_POST["new_type"])
		{
			case "term":
				$term_gui =& new ilGlossaryTermGUI();
				$term_gui->create();
				break;
		}
	}

	function saveTerm()
	{
		$term_gui =& new ilGlossaryTermGUI();
		$term_gui->setGlossary($this->object);
		$term_gui->save();

		sendinfo($this->lng->txt("added_glossary_term"),true);

		header("location: glossary_edit.php?ref_id=".$_GET["ref_id"]."cmd=listTerms");
		exit();

	}
}

?>
