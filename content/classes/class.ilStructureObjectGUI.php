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

require_once("./content/classes/class.ilLMObjectGUI.php");

/**
* Class ilStructureObjectGUI
*
* User Interface for Structure Objects Editing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilStructureObjectGUI extends ilLMObjectGUI
{
	var $obj;	// structure object
	var $tree;

	/**
	* Constructor
	* @access	public
	*/
	function ilStructureObjectGUI(&$a_content_obj, &$a_tree)
	{
		global $ilias, $tpl, $lng;

		parent::ilLMObjectGUI($a_content_obj);
		$this->tree =& $a_tree;
	}

	function setStructureObject(&$a_st_object)
	{
		$this->obj =& $a_st_object;
		$this->actions = $this->objDefinition->getActions("st");
	}

	/*
	* display pages of structure object
	*/
	function view()
	{
		global $tree;

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.structure_edit.html", true);
		$num = 0;

		$this->tpl->setCurrentBlock("form");
		$this->tpl->setVariable("FORMACTION", "lm_edit.php?ref_id=".
			$this->content_object->getRefId()."&obj_id=".$this->obj->getId()."&backcmd=view&cmd=post");
		$this->tpl->setVariable("HEADER_TEXT", $this->lng->txt("cont_pages"));
		$this->tpl->setVariable("CHECKBOX_TOP", IL_FIRST_NODE);

		$cnt = 0;
		$childs = $this->tree->getChilds($this->obj->getId());
		foreach ($childs as $child)
		{
			if($child["type"] != "pg")
			{
				continue;
			}
			$this->tpl->setCurrentBlock("table_row");
			// color changing
			$css_row = ilUtil::switchColor($cnt++,"tblrow1","tblrow2");

			// checkbox
			$this->tpl->setVariable("CHECKBOX_ID", $child["obj_id"]);
			$this->tpl->setVariable("CSS_ROW", $css_row);
			$this->tpl->setVariable("IMG_OBJ", ilUtil::getImagePath("icon_le.gif"));

			// type
			$link = "lm_edit.php?cmd=view&ref_id=".$this->content_object->getRefId()."&obj_id=".
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
		//else
		//{
			// SHOW VALID ACTIONS
			$this->tpl->setVariable("NUM_COLS", 3);
			//$this->setActions(array("confirmTermDeletion" => "delete", "addDefinition" => "cont_add_definition"));
			$acts = array("delete" => "delete", "cutPage" => "cutPage");
//echo ":".$this->checkClipboardContentType().":<br>";
			if(ilEditClipboard::getContentObjectType() == "pg")
			{
				$acts["pastePage"] = "pastePage";
			}
			$this->setActions($acts);
			$this->showActions();
		//}

		// SHOW POSSIBLE SUB OBJECTS
		$this->tpl->setVariable("NUM_COLS", 3);
		//$this->showPossibleSubObjects("st");
		$subobj = array("pg");
		$opts = ilUtil::formSelect(12,"new_type",$subobj);
		//$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
		$this->tpl->setCurrentBlock("add_object");
		$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
		//$this->tpl->setVariable("FORMACTION_OBJ_ADD", "adm_object.php?cmd=create&ref_id=".$_GET["ref_id"]);
		$this->tpl->setVariable("BTN_NAME", "create");
		$this->tpl->setVariable("TXT_ADD", $this->lng->txt("insert"));
		$this->tpl->parseCurrentBlock();


		$this->tpl->setCurrentBlock("form");
		$this->tpl->parseCurrentBlock();

	}


	/*
	* display subchapters of structure object
	*/
	function subchap()
	{
		global $tree;

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.structure_edit.html", true);
		$num = 0;

		$this->tpl->setCurrentBlock("form");
		$this->tpl->setVariable("FORMACTION", "lm_edit.php?ref_id=".
			$this->content_object->getRefId()."&obj_id=".$this->obj->getId()."&backcmd=subchap&cmd=post");
		$this->tpl->setVariable("HEADER_TEXT", $this->lng->txt("cont_subchapters"));
		$this->tpl->setVariable("CHECKBOX_TOP", IL_FIRST_NODE);

		$cnt = 0;
		$childs = $this->tree->getChilds($this->obj->getId());
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
			$link = "lm_edit.php?cmd=view&ref_id=".$this->content_object->getRefId()."&obj_id=".
				$child["obj_id"];
			$this->tpl->setVariable("LINK_TARGET", $link);

			// title
			$this->tpl->setVariable("TEXT_CONTENT", $child["title"]);

			$this->tpl->parseCurrentBlock();
		}
		if($cnt == 0)
		{
			$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("NUM_COLS", 2);
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
			$this->tpl->parseCurrentBlock();
		}
		//else
		//{
			// SHOW VALID ACTIONS
			$this->tpl->setVariable("NUM_COLS", 3);
			$acts = array("delete" => "delete", "move" => "moveChapter");
			if(ilEditClipboard::getContentObjectType() == "st")
			{
				$acts["pasteChapter"] =  "pasteChapter";
			}
			/*if(!empty($_SESSION["ilEditClipboard"]))
			{
				$acts["paste"] = "paste";
			}*/
			$this->setActions($acts);
			$this->showActions();
		//}

		// SHOW POSSIBLE SUB OBJECTS
		$this->tpl->setVariable("NUM_COLS", 3);
		//$this->showPossibleSubObjects("st");
		$subobj = array("st");
		$opts = ilUtil::formSelect(12,"new_type",$subobj);
		//$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
		$this->tpl->setCurrentBlock("add_object");
		$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
		//$this->tpl->setVariable("FORMACTION_OBJ_ADD", "adm_object.php?cmd=create&ref_id=".$_GET["ref_id"]);
		$this->tpl->setVariable("BTN_NAME", "create");
		$this->tpl->setVariable("TXT_ADD", $this->lng->txt("insert"));
		$this->tpl->parseCurrentBlock();

		//$this->tpl->setVariable("NUM_COLS", 2);
		//$this->showPossibleSubObjects("st");

		$this->tpl->setCurrentBlock("form");
		$this->tpl->parseCurrentBlock();

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

	function save()
	{
		$meta_data =& new ilMetaData($_GET["new_type"], $this->content_object->getId());

		$this->obj =& new ilStructureObject($this->content_object);
		$this->obj->assignMetaData($meta_data);
		$this->obj->setType($_GET["new_type"]);
		$this->obj->setTitle($_POST["Fobject"]["title"]);
		$this->obj->setDescription($_POST["Fobject"]["desc"]);
		$this->obj->setLMId($this->content_object->getId());
		$this->obj->create();

		$this->putInTree();

		// check the tree
		$this->checkTree();

		if (!empty($_GET["obj_id"]))
		{
			ilUtil::redirect("lm_edit.php?cmd=subchap&ref_id=".$this->content_object->getRefId()."&obj_id=".
				$_GET["obj_id"]);
		}
		else
		{
			ilUtil::redirect("lm_edit.php?cmd=chapters&ref_id=".$this->content_object->getRefId());
		}

	}

	function putInTree()
	{
		// chapters should be behind pages in the tree
		// so if target is first node, the target is substituted with
		// the last child of type pg
		if ($_GET["target"] == IL_FIRST_NODE)
		{
			$tree = new ilTree($this->content_object->getId());
			$tree->setTableNames('lm_tree','lm_data');
			$tree->setTreeTablePK("lm_id");

			// determine parent node id
			$parent_id = (!empty($_GET["obj_id"]))
				? $_GET["obj_id"]
				: $tree->getRootId();
			// determine last child of type pg
			$childs =& $tree->getChildsByType($parent_id, "pg");
			if (count($childs) != 0)
			{
				$_GET["target"] = $childs[count($childs) - 1]["obj_id"];
			}
		}
		if (empty($_GET["target"]))
		{
			$_GET["target"] = IL_LAST_NODE;
		}

		parent::putInTree();
	}

	/**
	* cut page
	*/
	function cutPage()
	{
		if(!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		if(count($_POST["id"]) > 1)
		{
			$this->ilias->raiseError($this->lng->txt("cont_select_max_one_item"),$this->ilias->error_obj->MESSAGE);
		}
		// SAVE POST VALUES
		ilEditClipboard::storeContentObject("pg",$_POST["id"][0]);

		$tree = new ilTree($this->content_object->getId());
		$tree->setTableNames('lm_tree','lm_data');
		$tree->setTreeTablePK("lm_id");

		// cut selected object
		$cutted = 0;
		foreach ($_POST["id"] as $id)
		{
			if ($id == -1)
			{
				continue;
			}
			$obj =& ilLMObjectFactory::getInstance($this->content_object, $id);
			$obj->setLMId($this->content_object->getId());
			$node_data = $tree->getNodeData($id);
			//$obj->delete();
			if($tree->isInTree($id))
			{
				$tree->deleteTree($node_data);
			}
			$cutted++;
		}

		// tree check
		$this->checkTree();

		if($cutted > 0)
		{
			sendInfo($this->lng->txt("msg_cut_clipboard"), true);
		}
		//$this->view();
		ilUtil::redirect("lm_edit.php?cmd=view&obj_id=".$_GET["obj_id"].
			"&ref_id=".$_GET["ref_id"]);
	}

	/**
	* paste page
	*/
	function pastePage()
	{
		if(ilEditClipboard::getContentObjectType() != "pg")
		{
			$this->ilias->raiseError($this->lng->txt("no_page_in_clipboard"),$this->ilias->error_obj->MESSAGE);
		}

		$tree = new ilTree($this->content_object->getId());
		$tree->setTableNames('lm_tree','lm_data');
		$tree->setTreeTablePK("lm_id");

		// paste selected object
		$id = ilEditClipboard::getContentObjectId();
		if(!$tree->isInTree($id))
		{
			if(!isset($_POST["id"]))
			{
				$target = IL_FIRST_NODE;
			}
			else
			{
				$target = $_POST["id"][0];
			}
			$tree->insertNode($id, $this->obj->getId(), $target);
			ilEditClipboard::clear();
		}

		// check the tree
		$this->checkTree();

		ilUtil::redirect("lm_edit.php?cmd=view&obj_id=".$_GET["obj_id"].
			"&ref_id=".$_GET["ref_id"]);

		//$this->view();
	}


	/**
	* move a single chapter  (selection)
	*/
	function moveChapter()
	{
		$cont_obj_gui =& new ilObjContentObjectGUI("",$this->content_object->getRefId(),
			true, false);
		$cont_obj_gui->moveChapter($this->obj->getId());
		ilUtil::redirect("lm_edit.php?cmd=subchap&obj_id=".$_GET["obj_id"].
			"&ref_id=".$_GET["ref_id"]);
	}


	/**
	* paste chapter
	*/
	function pasteChapter()
	{
		$id = ilEditClipboard::getContentObjectId();
		if($id == $_POST["id"][0])
		{
			ilEditClipboard::clear();
			$this->subchap();
			return;
		}
		$cont_obj_gui =& new ilObjContentObjectGUI("",$this->content_object->getRefId(),
			true, false);
		$cont_obj_gui->pasteChapter($this->obj->getId());

		ilUtil::redirect("lm_edit.php?cmd=subchap&obj_id=".$_GET["obj_id"].
			"&ref_id=".$_GET["ref_id"]);
	}

	function cancel()
	{
		if ($_GET["new_type"] == "pg")
		{
			ilUtil::redirect("lm_edit.php?cmd=view&ref_id=".$this->content_object->getRefId()."&obj_id=".
				$_GET["obj_id"]);
		}
		else
		{
			ilUtil::redirect("lm_edit.php?cmd=subchap&ref_id=".$this->content_object->getRefId()."&obj_id=".
				$_GET["obj_id"]);
		}
	}
}
?>
