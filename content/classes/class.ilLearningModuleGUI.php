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

require_once("content/classes/class.ilLearningModule.php");
require_once("classes/class.ilObjLearningModuleGUI.php");

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
class ilLearningModuleGUI extends ilObjLearningModuleGUI
{
	var $lm_obj;
	var $lm_tree;

	/**
	* Constructor
	* @access	public
	*/
	function ilLearningModuleGUI($a_ref_id = 0)
	{
		parent::ilObjLearningModuleGUI("", $a_ref_id, true, false);
		if($a_ref_id != 0)
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

	/*
	function setLearningModuleObject(&$a_lm_obj)
	{
		$this->lm_obj =& $a_lm_obj;
		//$this->obj =& $this->lm_obj;
	}*/

	function view()
	{
		//add template for buttons
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK","lm_presentation.php?ref_id=".$this->object->getRefID());
		$this->tpl->setVariable("BTN_TARGET"," target=\"_top\" ");
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt("view"));
		$this->tpl->parseCurrentBlock();
	}

	function chapters()
	{
		global $tree;

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.structure_edit.html", true);
		$num = 0;

		$this->tpl->setVariable("FORMACTION", "lm_edit.php?ref_id=".
			$this->object->getRefId()."&cmd=post");
		$this->tpl->setVariable("HEADER_TEXT", $this->lng->txt("cont_chapters"));
		$this->tpl->setVariable("CHECKBOX_TOP", IL_FIRST_NODE);


		$cnt = 0;
		$childs = $this->lm_tree->getChilds($this->lm_tree->getRootId());
		foreach ($childs as $child)
		{
			if($child["type"] != "st")
			{
				continue;
			}

			$this->tpl->setCurrentBlock("table_row");
			// color changing
			$css_row = ilUtil::switchColor($cnt++,"tblrow1","tblrow2");

			// checkbox
			$this->tpl->setVariable("CHECKBOX_ID", $child["obj_id"]);
			$this->tpl->setVariable("CSS_ROW", $css_row);
			$this->tpl->setVariable("IMG_OBJ", ilUtil::getImagePath("icon_cat.gif"));

			// type
			$link = "lm_edit.php?cmd=view&ref_id=".$this->object->getRefId()."&obj_id=".
				$child["obj_id"];
			$this->tpl->setVariable("LINK_TARGET", $link);

			// title
			$this->tpl->setVariable("TEXT_CONTENT", $child["title"]);

			$this->tpl->parseCurrentBlock();
		}
		if($cnt == 0)
		{
			$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("NUM_COLS", 3);
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			// SHOW VALID ACTIONS
			$this->tpl->setVariable("NUM_COLS", 3);
			//$this->showActions();
		}

		// SHOW POSSIBLE SUB OBJECTS
		$this->tpl->setVariable("NUM_COLS", 3);
		$subobj = array("st");
		$opts = ilUtil::formSelect(12,"new_type",$subobj);
		$this->tpl->setCurrentBlock("add_object");
		$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
		$this->tpl->setVariable("BTN_NAME", "create");
		$this->tpl->setVariable("TXT_ADD", $this->lng->txt("insert"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("form");
		$this->tpl->parseCurrentBlock();

	}


	/*
	* list all pages of learning module
	*/
	function pages()
	{
		global $tree;

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.all_pages.html", true);
		$num = 0;

		$this->tpl->setCurrentBlock("form");
		$this->tpl->setVariable("FORMACTION", "lm_edit.php?ref_id=".
			$this->object->getRefId()."&backcmd=pages&cmd=post");
		$this->tpl->setVariable("HEADER_TEXT", $this->lng->txt("cont_pages"));
		$this->tpl->setVariable("CONTEXT", $this->lng->txt("context"));
		$this->tpl->setVariable("CHECKBOX_TOP", IL_FIRST_NODE);

		$cnt = 0;
		$pages = ilPageObject::getPageList($this->object->getId());
		foreach ($pages as $page)
		{
			$this->tpl->setCurrentBlock("table_row");
			// color changing
			$css_row = ilUtil::switchColor($cnt++,"tblrow1","tblrow2");

			// checkbox
			$this->tpl->setVariable("CHECKBOX_ID", $page["obj_id"]);
			$this->tpl->setVariable("CSS_ROW", $css_row);
			$this->tpl->setVariable("IMG_OBJ", ilUtil::getImagePath("icon_le.gif"));

			// type
			$link = "lm_edit.php?cmd=view&ref_id=".$this->object->getRefId()."&obj_id=".
				$page["obj_id"];
			$this->tpl->setVariable("LINK_TARGET", $link);

			// title
			$this->tpl->setVariable("TEXT_CONTENT", $page["title"]);

			// context
			if ($this->lm_tree->isInTree($page["obj_id"]))
			{
				$path_str = $this->getContextPath($page["obj_id"]);
			}
			else
			{
				$path_str = "---";
			}
			$this->tpl->setVariable("TEXT_CONTEXT", $path_str);

			$this->tpl->parseCurrentBlock();
		}
		if($cnt == 0)
		{
			$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("NUM_COLS", 3);
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			// SHOW VALID ACTIONS
			$this->tpl->setVariable("NUM_COLS", 4);
			$this->tpl->setCurrentBlock("operation_btn");
			$this->tpl->setVariable("BTN_NAME", "delete");
			$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("delete"));
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("operation");
			$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
			$this->tpl->parseCurrentBlock();
		}

		// SHOW POSSIBLE SUB OBJECTS
		$this->tpl->setVariable("NUM_COLS", 4);
		//$this->showPossibleSubObjects("st");
		$subobj = array("pg");
		$opts = ilUtil::formSelect(12,"new_type",$subobj);
		$this->tpl->setCurrentBlock("add_object");
		$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
		$this->tpl->setVariable("BTN_NAME", "create");
		$this->tpl->setVariable("TXT_ADD", $this->lng->txt("create"));
		$this->tpl->parseCurrentBlock();


		$this->tpl->setCurrentBlock("form");
		$this->tpl->parseCurrentBlock();

	}

	/**
	* confirm deletion screen
	*/
	function delete()
	{
		if(!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		// SAVE POST VALUES
		$_SESSION["saved_post"] = $_POST["id"];

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.confirm_deletion.html", true);

		sendInfo($this->lng->txt("info_delete_sure"));
		$this->tpl->setVariable("FORMACTION", "lm_edit.php?ref_id=".
			$this->object->getRefId()."&backcmd=".$_GET["backcmd"]."&cmd=post");
		// BEGIN TABLE HEADER
		$this->tpl->setCurrentBlock("table_header");
		$this->tpl->setVariable("TEXT",$this->lng->txt("objects"));
		$this->tpl->parseCurrentBlock();

		// END TABLE HEADER

		// BEGIN TABLE DATA
		$counter = 0;
		foreach($_POST["id"] as $id)
		{
			$obj =& new ilLMObject($id);
			switch($obj->getType())		// ok that's not so nice, could be done better
			{
				case "pg":
					$this->tpl->setVariable("IMG_OBJ", ilUtil::getImagePath("icon_le.gif"));
					break;
				case "st":
					$this->tpl->setVariable("IMG_OBJ", ilUtil::getImagePath("icon_cat.gif"));
					break;
			}
			$this->tpl->setCurrentBlock("table_row");
			$this->tpl->setVariable("CSS_ROW",ilUtil::switchColor(++$counter,"tblrow1","tblrow2"));
			$this->tpl->setVariable("TEXT_CONTENT", $obj->getTitle());
			$this->tpl->parseCurrentBlock();
		}

		// cancel/confirm button
		$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$buttons = array( "cancelDelete"  => $this->lng->txt("cancel"),
								  "confirmedDelete"  => $this->lng->txt("confirm"));
		foreach ($buttons as $name => $value)
		{
			$this->tpl->setCurrentBlock("operation_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}
	}

	function cancelDelete()
	{
		session_unregister("saved_post");

		header("location: lm_edit.php?cmd=".$_GET["backcmd"]."&ref_id=".$this->object->getRefId());
		exit();

	}

	function confirmedDelete()
	{
		$tree = new ilTree($this->object->getId());
		$tree->setTableNames('lm_tree','lm_data');
		$tree->setTreeTablePK("lm_id");

		// check number of objects
		if (!isset($_SESSION["saved_post"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		// delete all selected objects
		foreach ($_SESSION["saved_post"] as $id)
		{
			$tree->deleteTree($tree->getNodeData($id));
			$obj =& ilLMObjectFactory::getInstance($id);
			$obj->delete();
		}

		// feedback
		sendInfo($this->lng->txt("info_deleted"),true);

		header("location: lm_edit.php?cmd=".$_GET["backcmd"]."&ref_id=".$this->object->getRefId());
		exit();
	}



	/**
	*
	*/
	function getContextPath($a_endnode_id, $a_startnode_id = 1)
	{
		$path = "";

		$tmpPath = $this->lm_tree->getPathFull($a_endnode_id, $a_startnode_id);

		// count -1, to exclude the learning module itself
		for ($i = 1; $i < (count($tmpPath) - 1); $i++)
		{
			if ($path != "")
			{
				$path .= " > ";
			}

			$path .= $tmpPath[$i]["title"];
		}

		return $path;
	}



	/**
	* show possible action (form buttons)
	*
	* @access	public
	*/
	function showActions()
	{
		$notoperations = array();
		// NO PASTE AND CLEAR IF CLIPBOARD IS EMPTY
		if (empty($_SESSION["clipboard"]))
		{
			$notoperations[] = "paste";
			$notoperations[] = "clear";
		}
		// CUT COPY PASTE LINK DELETE IS NOT POSSIBLE IF CLIPBOARD IS FILLED
		if ($_SESSION["clipboard"])
		{
			$notoperations[] = "cut";
			$notoperations[] = "copy";
			$notoperations[] = "link";
		}

		$operations = array();

		$d = $this->objDefinition->getActions("lm");

		foreach ($d as $row)
		{
			if (!in_array($row["name"], $notoperations))
			{
				$operations[] = $row;
			}
		}

		if (count($operations)>0)
		{
			foreach ($operations as $val)
			{
				$this->tpl->setCurrentBlock("operation_btn");
				$this->tpl->setVariable("BTN_NAME", $val["lng"]);
				$this->tpl->setVariable("BTN_VALUE", $this->lng->txt($val["lng"]));
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("operation");
			$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
			$this->tpl->parseCurrentBlock();
		}
	}

	function editMeta()
	{
		require_once("classes/class.ilMetaDataGUI.php");
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_gui->edit("ADM_CONTENT", "adm_content", "lm_edit.php?ref_id=".
			$this->object->getRefId()."&cmd=saveMeta");
	}

	function saveMeta()
	{
		require_once("classes/class.ilMetaDataGUI.php");
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_gui->save();
		header("location: lm_edit.php?cmd=view&ref_id=".$this->object->getRefId());
	}

	function perm()
	{
		$this->setFormAction("addRole", "lm_edit.php?ref_id=".$this->object->getRefId()."&cmd=addRole");
		$this->setFormAction("permSave", "lm_edit.php?ref_id=".$this->object->getRefId()."&cmd=permSave");
		$this->permObject();
	}

	function permSave()
	{
		$this->setReturnLocation("permSave", "lm_edit.php?ref_id=".$this->object->getRefId()."&cmd=perm");
		$this->permSaveObject();
	}

	function addRole()
	{
		$this->setReturnLocation("addRole", "lm_edit.php?ref_id=".$this->object->getRefId()."&cmd=perm");
		$this->addRoleObject();
	}

	function owner()
	{
		$this->ownerObject();
	}
}
?>
