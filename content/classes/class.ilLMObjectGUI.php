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

require_once("classes/class.ilMetaData.php");
require_once("classes/class.ilMetaDataGUI.php");

/**
* Class ilLMObject
*
* Base class for ilStructureObjects and ilPageObjects (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilLMObjectGUI
{
	var $lm_obj;	// learning module object
	var $ilias;
	var $tpl;
	var $lng;
	var $obj;
	var $objDefinition;

	function ilLMObjectGUI()
	{
		global $ilias, $tpl, $lng, $objDefinition;

		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->objDefinition =& $objDefinition;
	}

	function edit_meta()
	{
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->obj);
		$meta_gui->edit("ADM_CONTENT", "adm_content", "lm_edit.php?ref_id=".
			$this->lm_obj->getRefId()."&obj_id=".$this->obj->getId()."&cmd=save_meta");
	}

	function save_meta()
	{
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->obj);
		$meta_gui->save();
		header("location: lm_edit.php?cmd=view&ref_id=".$this->lm_obj->getRefId()."&obj_id=".
			$this->obj->getId());
	}

	function create()
	{
		if(count($_POST["id"]) > 1)
		{
			$this->ilias->raiseError($this->lng->txt("cont_max_one_pos"),$this->ilias->error_obj->MESSAGE);
		}
		$target = (count($_POST["id"]) == 1)
			? $_POST["id"][0]
			: "";

		$meta_gui =& new ilMetaDataGUI();
		$obj_str = (is_object($this->obj))
			? "&obj_id=".$this->obj->getId()
			: "";
		$meta_gui->edit("ADM_CONTENT", "adm_content", "lm_edit.php?ref_id=".
			$this->lm_obj->getRefId().$obj_str."&new_type=".$_POST["new_type"].
			"&target=".$target."&cmd=save");
	}

	function putInTree()
	{
		$tree = new ilTree($this->lm_obj->getId());
		$tree->setTableNames('lm_tree','lm_data');
		$tree->setTreeTablePK("lm_id");

		$parent_id = (!empty($_GET["obj_id"]))
			? $_GET["obj_id"]
			: $tree->getRootId();

		if (!empty($_GET["target"]))
		{
			$target = $_GET["target"];
		}
		else
		{
			// determine last child of current type
			$childs =& $tree->getChildsByType($parent_id, $this->obj->getType());
			if (count($childs) == 0)
			{
				$target = IL_FIRST_NODE;
			}
			else
			{
				$target = $childs[count($childs) - 1]["obj_id"];
			}
		}
		$tree->insertNode($this->obj->getId(), $parent_id, $target);
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
			$this->lm_obj->getRefId()."&obj_id=".$this->obj->getId()."&backcmd=".$_GET["backcmd"]."&cmd=post");
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

		header("location: lm_edit.php?cmd=".$_GET["backcmd"]."&ref_id=".$this->lm_obj->getRefId()."&obj_id=".
			$this->obj->getId());
		exit();

	}

	function confirmedDelete()
	{
		$tree = new ilTree($this->lm_obj->getId());
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
			$obj =& ilLMObjectFactory::getInstance($id);
			$obj->setLMId($this->lm_obj->getId());
			$node_data = $tree->getNodeData($id);
			$obj->delete();
			if($tree->isInTree($id))
			{
				$tree->deleteTree($node_data);
			}
		}

		// feedback
		sendInfo($this->lng->txt("info_deleted"),true);

		header("location: lm_edit.php?cmd=".$_GET["backcmd"]."&ref_id=".$this->lm_obj->getRefId()."&obj_id=".
			$this->obj->getId());
		exit();
	}


	function showPossibleSubObjects($a_type)
	{
		$d = $this->objDefinition->getSubObjects($a_type);
		if (count($d) > 0)
		{
			foreach ($d as $row)
			{
				$subobj[] = $row["name"];
			}
		}

		if (is_array($subobj))
		{
			//build form
			$opts = ilUtil::formSelect(12,"new_type",$subobj);
			//$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
			$this->tpl->setCurrentBlock("add_object");
			$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
			//$this->tpl->setVariable("FORMACTION_OBJ_ADD", "adm_object.php?cmd=create&ref_id=".$_GET["ref_id"]);
			$this->tpl->setVariable("BTN_NAME", "create");
			$this->tpl->setVariable("TXT_ADD", $this->lng->txt("add"));
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* output a cell in object list
	*/
	function add_cell($val, $link = "")
	{
		if(!empty($link))
		{
			$this->tpl->setCurrentBlock("begin_link");
			$this->tpl->setVariable("LINK_TARGET", $link);
			$this->tpl->parseCurrentBlock();
			$this->tpl->touchBlock("end_link");
		}

		$this->tpl->setCurrentBlock("text");
		$this->tpl->setVariable("TEXT_CONTENT", $val);
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("table_cell");
		$this->tpl->parseCurrentBlock();
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

		$d = $this->objDefinition->getActions($this->obj->getType());

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


}
?>
